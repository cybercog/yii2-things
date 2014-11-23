<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 11.07.14
 * Time: 23:28
 */

namespace insolita\things\components;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\caching\DbDependency;
use yii\helpers\Html;

class SActiveRecord extends ActiveRecord
{
    public static $titledAttribute = 'name';
    public  $gridDefaults = [];
    public  $ignoredAttributes = [];

    public  $pageSize=20;


    public function init()
    {
        parent::init();

    }

    public  function modelCaption(){
        return $this->{static::$titledAttribute};
    }

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

    public static function hasMain()
    {
        return in_array(static::tableName(), ['{{%city}}', '{{%sklad}}', '{{%doctype}}']);
    }

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

    public function addError($attribute, $error='')
    {
        if (strpos($error, $this->getAttributeLabel($attribute)) === false) {
            $error = '"' . $this->getAttributeLabel($attribute) . '" - ' . $error;
        }
        parent::addError($attribute, $error);
    }

    public function afterValidate()
    {
        parent::afterValidate();
    }

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

    public static function active()
    {
        return static::find()->active();
    }

    public static function getPk()
    {
        $pks = static::primaryKey();
        return !is_array($pks) ? $pks : $pks[0];
    }

    public static function getActiveList($idattr = false, $nameattr = false){
        return static::getList(false,false,$idattr, $nameattr);
    }

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

    public function softdelete()
    {
        if ($this->hasAttribute('bymanager')) {
            $this->updateAttributes(['active' => 0, 'bymanager' => \Yii::$app->user->id]);
        } else {
            $this->updateAttributes(['active' => 0]);
        }

    }

    public function search($query){
        return $query;
    }
} 