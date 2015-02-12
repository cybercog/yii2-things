<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 11.07.14
 * Time: 23:28
 */

namespace insolita\things\components;

use common\Scoper;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\caching\DbDependency;
use yii\helpers\Html;

/**
 * Class SActiveRecord
 *
 * @package insolita\things\components
 */
class SActiveRecord extends ActiveRecord
{
    /**
     *  Событие перед мягким удалением
     */
    const EVENT_BEFORE_SOFTDEL='beforeSoftdel';
    /**
     * Событие после мягкого удаления
     */
    const EVENT_AFTER_SOFTDEL='afterSoftdel';


    /**
     * Аттрибут используемы как заголовок записи
     * @var string $titledAttribute
     */
    public static $titledAttribute = 'name';

    /**
     * Столбцы для грида по умолчанию
     * @var array $gridDefaults
     */
    public  $gridDefaults = [];
    /**
     * Игнорируемые гридом аттрибуты
     * @var array
     */
    public  $ignoredAttributes = [];

    /**
     * На страницу по умолчанию
     * @var int
     */
    public  $pageSize=20;


    /**
     *
     */
    public function init(){
        parent::init();

    }

    /**
     * Заголовок модели
     * @return mixed
     */
    public  function modelCaption(){
        return $this->{static::$titledAttribute};
    }

    /**
     * Список колонок для грида с учетом игнорируемых
     * @return array
     */
    public function getGridedAttributes()
    {
        $ignor = $this->ignoredAttributes;
        $arr = $this->attributeLabels();
        if (!empty($ignor)) {
            foreach ($ignor as $attr) {
                if (isset($arr[$attr])) {
                    unset($arr[$attr]);
                }
            }
        }
        return $arr;
    }

    /**
     * Таблицы для которых есть главенствующий элемент который должен быть первым во всех списках
     * @return bool
     */
    public static function hasMain()
    {
        return in_array(static::tableName(), ['{{%city}}', '{{%sklad}}', '{{%doctype}}']);
    }

    /**
     * Возвращает значение главенствующего элемента
     * @return mixed
     */
    public static function getMain()
    {
        if (static::hasMain()) {
            $main = static::findOne(['active' => 1, 'is_main' => 1]);
            if ($main) {
                return $main->{static::getPk()};
            }
        } else {
            return static::findOne(['active' => 1])->{static::getPk()};
        }
    }

    /**
     * Переопределение для дополнения в вывод ошибки названия аттрибута
     * @param string $attribute
     * @param string $error
     */
    public function addError($attribute, $error='')
    {
        if (strpos($error, $this->getAttributeLabel($attribute)) === false) {
            $error = '"' . $this->getAttributeLabel($attribute) . '" - ' . $error;
        }
        parent::addError($attribute, $error);
    }

    /**
     *
     */
    public function afterValidate()
    {
        parent::afterValidate();
    }

    /**
     * Выводит простой список ошибок без Html-разметки
     * @return string
     */
    public function formattedErrors()
    {
        $errs = [];
        foreach ($this->getFirstErrors() as $error) {
            $errs[] = Html::encode($error);
        }
        return implode("\n", $errs);
    }

    /**
     * @inheritdoc
     * @return Scoper
     */
    public static function find()
    {
        return new Scoper(get_called_class());
    }

    /**
     * @return $this
     */
    public static function active()
    {
        return static::find()->active();
    }

    /**
     * @return \string[]
     */
    public static function getPk()
    {
        $pks = static::primaryKey();
        return !is_array($pks) ? $pks : $pks[0];
    }

    /**
     * @param bool $idattr
     * @param bool $nameattr
     *
     * @return array|mixed
     */
    public static function getActiveList($idattr = false, $nameattr = false){
        return static::getList(false,false,$idattr, $nameattr);
    }

    /**
     * @param bool $item
     * @param bool $nofilter
     * @param bool $idattr
     * @param bool $nameattr
     * @param null $exclude
     *
     * @return array|mixed
     */
    public static function getList($item = false, $nofilter = true, $idattr = false, $nameattr = false, $exclude = null)
    {
        $idattr = $idattr ? $idattr : static::getPk();
        $nameattr = $nameattr ? $nameattr : static::$titledAttribute;
        $listname = md5(static::tableName() . '_' . $nofilter . $idattr . $nameattr . $exclude);
        $listcache = \Yii::$app->cache->get($listname);
        if ($listcache) {
            $items = $listcache;
        } else {
            if (static::hasMain()) {
                $items = ($nofilter)
                    ? ArrayHelper::map(
                        static::find()->mainer()->select([$idattr, $nameattr])->all(),
                        $idattr,
                        $nameattr
                    )
                    :
                    ArrayHelper::map(
                        self::find()->active()->mainer()->select([$idattr, $nameattr])->all(),
                        $idattr,
                        $nameattr
                    );
            } else {
                $items = ($nofilter)
                    ? ArrayHelper::map(static::find()->select([$idattr, $nameattr])->all(), $idattr, $nameattr)
                    :
                    ArrayHelper::map(self::find()->active()->select([$idattr, $nameattr])->all(), $idattr, $nameattr);
            }
            if ($exclude && isset($items[$exclude])) {
                unset($items[$exclude]);
            }
            $dep = new DbDependency();
            $dep->sql = 'SELECT max(updated) FROM ' . static::tableName();
            $dep->reusable = true;
            \Yii::$app->cache->add($listname, $items, 3600, $dep);
        }

        return $item ? ArrayHelper::getValue($items, $item, null) : $items;
    }


    /**
     * @param bool $insert
     *
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($this->hasAttribute('bymanager')) {
            $this->bymanager = \Yii::$app->user->id;
        }
        if ($this->hasAttribute('created') && $this->isNewRecord) {
            $this->created = date('Y-m-d H:i:s', time());
        }
        return parent::beforeSave($insert);
    }

    /**
     * Мягкое удаление
     */
    public function softdelete()
    {
        if($this->beforeSoftdel()){
            if ($this->hasAttribute('bymanager')) {
                $this->updateAttributes(['active' => 0, 'bymanager' => \Yii::$app->user->id]);
            } else {
                $this->updateAttributes(['active' => 0]);
            }
            $this->afterSoftdel();
        }

    }

    /**
     * @return bool
     */
    public function beforeSoftdel()
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_SOFTDEL, $event);

        return $event->isValid;
    }

    /**
     *
     */
    public function afterSoftdel()
    {
        $this->trigger(self::EVENT_AFTER_SOFTDEL);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function search($query){
        return $query;
    }
} 