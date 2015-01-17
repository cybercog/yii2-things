<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 28.07.14
 * Time: 11:51
 */

namespace insolita\things\behaviors;

use insolita\things\helpers\Helper;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;
use yii\base\Behavior;

class MetaModelBeh extends Behavior
{
    public $source_attributes = []; //title,text or description
    public $metakey_attribute = 'metaKey';
    public $metadesc_attribute = 'metaDesc';

    private $_data = '';

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'processMeta',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'processMeta'
        ];
    }

    /**
     * @var \yii\db\ActiveRecord $owner
     * @throws \UnexpectedValueException
     **/
    public function processMeta($event)
    {
        if (empty($this->source_attributes) || !is_array($this->source_attributes)) {
            throw new \UnexpectedValueException('Неверно указаны аттрибуты источника');
        }
        foreach ($this->source_attributes as $sattr) {
            if (!empty($this->owner->$sattr)) {
                if ($this->owner->isNewRecord or $this->owner->$sattr != $this->owner->getOldAttribute($sattr)) {
                    $this->_data .= $this->owner->$sattr;
                }
            } else {
                Helper::logs('empty attribute ' . $sattr);
                Helper::logs($this->owner->$sattr);
            }
        }

        if (!empty($this->metakey_attribute)) {
            $this->owner->{$this->metakey_attribute} = (!empty($this->_data)) ? $this->generateMetaKey()
                : $this->owner->{$this->metakey_attribute};
        }
        if (!empty($this->metadesc_attribute)) {
            $this->owner->{$this->metadesc_attribute} = (!empty($this->_data)) ? $this->generateMetaDesc()
                : $this->owner->{$this->metakey_attribute};
        }

    }

    private function cleaner($data){
        $search = array(
            "'<script[^>]*?>.*?</script>'si","'<[\/\!]*?[^<>]*?>'si","'([\r\n])[\s]+'", 
            "'&quot;'i", "'&amp;'i","'&lt;'i","'&gt;'i","'&nbsp;'i","','i","'\.'i","';'i","':'i",
            "'\"'i","'\''i","'&#(\d+);'ue"
        );
        $replace = array(
            "","","\\1"," "," "," "," "," "," "," "," ", " "," "," ","chr(\\1)"
        );
        $dataz = preg_replace($search, $replace, $data);
        $search = [
            "'$'si","'\?'si","'\!'si","'%'si","'#'si","'&'si","'№'si","'\*'si","'\('si",
            "'\)'si","'\{'si","'\}'si","'\['si","'\]'si","'\^'si",
        ];
        $replace = [" ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " "];
        $dataz = preg_replace($search, $replace, $dataz);
        return $dataz;
    }
    private function generateMetaKey()
    {
        $p = new HtmlPurifier();
        $data = $p->process($this->_data);
        $dataz=$this->cleaner($data);
        if ($dataz) {
            $narr = $this->remStopwords($dataz);
            if (count($narr)) {
                $arr2 = array_count_values($narr);
                arsort($arr2);
                $res = implode(',', array_keys($arr2));
                $res = mb_substr($res, 0, 255, 'UTF-8');
                return mb_strtolower($res, 'UTF-8');
            } else {
                return '';
            }
        } else {
            return '';
        }


    }

    private function generateMetaDesc()
    {
        $p = new HtmlPurifier();
        $data = $p->process($this->_data);
        $data = str_replace('?', " ", $data);
        $data = str_replace('!', " ", $data);
        $dataz=$this->cleaner($data);
        if ($dataz) {
            $res = mb_substr($dataz, 0, 255, 'UTF-8');
            return $res;
        } else {
            return '';
        }
    }


    /**
     * @param string $text;
     *
     * @return array;
     **/
    public function remStopwords($text)
    {
        $shorts_exclude = ['СЕО', 'ЧПУ', '1C'];
        $fw = $this->getFiltwords();
        $text = mb_strtolower($text, 'UTF-8');
        $text = explode(' ', $text);
        foreach ($text as $i => &$w) {
            $w = trim($w);
            if (in_array($w, $fw) or (mb_strlen($w, 'UTF-8') < 4 AND !in_array($w, $shorts_exclude))) {
                unset($text[$i]);
            }
        }
        if (!empty($text)) {
            $text = $this->morphizate($text);
        }
        return $text;
    }

    /**
     * @param array $words;
     *
     * @return array;
     **/
    public function morphizate($words)
    {
        $fw = $this->getFiltwords();
        $morphy = new \phpMorphy(\Yii::getAlias(
            '@vendor/makhov/phpmorphy/dicts'
        ), 'ru_RU', ['storage' => PHPMORPHY_STORAGE_FILE]);
        $result = array();
        foreach ($words as $word) {
            $ml = $morphy->lemmatize(mb_strtoupper($word, 'UTF-8'));
            $ml = !(empty($ml)) ? mb_strtolower($ml[0], 'UTF-8') : '';
            if (!empty($ml) && !in_array($ml, $fw)) {
                $result[$word] = $ml;
            }
        }
        return $result;
    }

    protected function getFiltwords()
    {
        $filtwords
            = 'а-ля,ай,ба,без,без ведома,безо,весь,вид,видеть,вижу,смотреть,смотрю,смотря,несмотря,смотрел,смотрели,смотрят,смотришь,смотрим,смотрела,видела,видел,сказал,сказала,сказать,скажет,скажут,говорят,говорить,говорил,говорил,говорила,говорит,говоришь,скажем,поговорит,поговорить,скажу,вся,все,всё,всех,всем,всим,усим,сих,пор,средний,похожий,несколько,кстати,называть,название,назвать,назвали,назовут,назовет,назвал,названный,благодаря,близ,близи,близко от,будет,будто,будь,был,была,были,было,в,в виде,в зависимости от,в интересах,в качестве,в лице,в отличие от,в отношении,в пандан,в пользу,в преддверии,в продолжение,в результате,в роли,в связи с,в силу,в случае,в соответствии с,в течение,в целях,вблизи,ввиду,вглубь,вдогон,вдоль,вдоль по,весь,взамен,включая,вкруг,вместо,вне,внизу,внутри,внутрь,во,во имя,возле,вокруг,вопреки,вослед,впереди,вплоть до,впредь до,вразрез,вроде,все,вслед,вслед за,вследствие,всё,вы,где,где-то,даже,для,для-ради,до,его,ее,ей,её,же,з,за,за вычетом,за исключением,за счёт,заместо,и,из,из-за,из-под,изнутри,изо,или,им,исходя из,итак,их,к,ка,как,касательно,ко,когда,ком,которая,которое,которые,который,которым,которых,кроме,кругом,кто,кто-нибудь,кто-то,либо,лицом к лицу с,меж,между,мимо,мы,на,на благо,на виду у,на глазах у,на предмет,наверху,навстречу,над,надо,назад,накануне,наперекор,наперерез,наподобие,напротив,наряду с,нас,насупротив,насчёт,начиная с,не,не без,не в,не за,не считая,невзирая на,недалеко от,независимо от,ней,несмотря на,неё,ни,нибудь,ниже,них,но,о,об,обо,обок,одно,ой,около,окрест,окромя,округ,он,она,они,оно,опосля,от,от имени,от лица,относительно,ото,ох,пере,перед,передо,по,по линии,по мере,по направлению к,по отношению к,по поводу,по причине,по случаю,по сравнению с,по-за,по-над,по-под,поблизости от,поверх,под,под видом,под эгидой,подле,подо,подобно,позади,позднее,помимо,поперёк,порядка,посередине,посередь,после,посреди,посредине,посредством,потому,пред,предо,прежде,при,при помощи,применительно к,про,против,путём,ради,рядом с,с,с ведома,с помощью,с точки зрения,с целью,сверх,сверху,свыше,сзади,сквозь,скрозь,следом за,смотря по,снизу,со,согласно,спустя,среди,средь,сродни,судя по,супротив,сяк,сякие,сяких,сякой,та,так,так же,также,так же как,так как,такая,такие,таких,такой,тебе,тебя,тех,то,тобой,тогда,тот,ты,уй,ух,чем,через,чрез,что,что-нибудь,что-то,чу,чём,эй,этим,этих,это,этот,эх,я,';
        $filtwords .= 'недавно,давно,мог,мочь,могу,могли,можешь,можете,могут,сам,можно,нужно,потому,поэтому,потом,сегодня,завтра,самый,самая,самое,самые,главный,главное,главная,главные,само,однако,именно,точно,совершенно,возможно,вероятно,несомненно,причем,притом,приэтом,этом,при,первый,второй,первое,второе,среднее,средний,во-первых,во-вторых,перво,перво-наперво,много,многих,многие,многое,он,она,оно,они,мой,моя,моё,мое,мои,своего,моего,его,моими,своими,свой,своя,своё,свое,свои,его,её,ее,их,им,нам,нас,них,ихнее,эти,этот,этому,этим,этих,тем,тех,тому,теми,этими,эта,этим,это,того,этого,';
        $filtwords .= 'далекий,близкий,далеко,близко,далекая,близкая,высоко,низко,высокий,низкий,высокая,низкая,большинство,равно,ровно,меньшинство,больше,меньше,более,менее,большая,меньшая,большие,меньшие,временно,временные,временный,сейчас,потом,уже,еще,если,такое,такая,такие,такой,сякой,сякая,сякие,быть,был,была,были,будем,будут,будет,есть,было,дать,дал,дала,давал,давали,дали,давать,стал,стали,стала,стало,дало,мало,стать,становиться,станет,стану,станешь,дашь,будешь,станем,дадим,даем,становимся,бываем,иметь,имел,имела,имели,имеем,имеешь,имеют,имеет,девать,деть,дел,дела,дели,делаешь,делать,делают,делает,делали,делала,сделал,сделала,сделали,сделаешь,сделаю,делаю,сделает,сделает,сделают';
        $filtwords_arr = explode(',', $filtwords);
        return $filtwords_arr;
    }
}
