<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 30.05.14
 * Time: 4:08
 */
namespace insolita\things\helpers;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/**
 * Class Helper
 *
 * @package insolita\things\helpers
 */
class Helper
{

    /**
     * Highlighted by default VarDumper::dump
     *
     * @param      $var
     * @param bool $highlight
     */
    public static function dump($var, $highlight = true)
    {
        VarDumper::dump($var, 10, $highlight);
    }

    /**
     * Highlighted by default VarDumper::dumpAsString
     *
     * @param      $var
     * @param bool $highlight
     *
     * @return string
     */
    public static function dumps($var, $highlight = true)
    {
        return VarDumper::dumpAsString($var, 10, $highlight);
    }

    /**
     * Font-Awesome icon Helper
     *
     * @param      $icon
     * @param int  $size
     * @param bool $spin
     *
     * @return string
     */
    public static function Fa($icon, $size = 0, $spin = false)
    {
        if (!$size) {
            return '<i class="fa fa-' . $icon . ($spin ? ' fa-spin' : '') . '"></i> ';
        }
        return ($size == 'lg')
            ? '<i class="fa fa-' . $icon . ' fa-lg' . ($spin ? ' fa-spin' : '') . '"></i> '
            :
            '<i class="fa fa-' . $icon . ' fa-' . $size . 'x' . ($spin ? ' fa-spin' : '') . '"></i> ';
    }

    /**
     * Bootstrap Glyphicon helper
     *
     * @param $icon
     *
     * @return string
     */
    public static function Glyf($icon)
    {
        return '<span class="glyphicon glyphicon-' . $icon . '"></span> ';
    }

    public static function Ion($icon, $size = 'lg')
    {
        if (!$size) {
            return '<i class="icon ion-' . $icon . '"></i> ';
        } else {
            return ($size == 'lg') ? '<i class="icon ion-' . $icon . ' fa-lg"></i> '
                : '<i class="icon ion-' . $icon . ' fa-' . $size . 'x' . '"></i> ';
        }
    }

    /**
     * customized error summary
     *
     * @param       $models
     * @param array $options
     * @param bool  $pure if true - return errors without any html
     *
     * @return string
     */
    public static function errorSummary($models, $options = [], $pure = false)
    {
        $lines = [];
        if (!is_array($models)) {
            $models = [$models];
        }
        foreach ($models as $model) {
            foreach ($model->getFirstErrors() as $error) {
                $lines[] = Html::encode($error);
            }
        }
        if ($pure) {
            return implode("\n", $lines);
        }
        $header = isset($options['header']) ? $options['header']
            : '<p>' . \Yii::t('yii', 'Please fix the following errors:') . '</p>';
        $footer = isset($options['footer']) ? $options['footer'] : '';
        unset($options['header'], $options['footer']);

        if (empty($lines)) {
            // still render the placeholder for client-side validation use
            $content = "<ul></ul>";
            $options['style'] = isset($options['style']) ? rtrim($options['style'], ';') . '; display:none'
                : 'display:none';
        } else {
            $content = "<ul><li>" . implode("</li>\n<li>", $lines) . "</li></ul>";
        }
        return Html::tag('div', $header . $content . $footer, $options);
    }


    /**
     * Аналог ArrayHelper::map склеивающий значения нескольких аттрибутов
     *
     * @param        $array
     * @param        $id
     * @param array  $concattrs
     * @param string $separator
     *
     * @return array
     */
    public static function cmap($array, $id, $concattrs = [], $separator = ' ')
    {
        $result = [];
        foreach ($array as $element) {
            $key = ArrayHelper::getValue($element, $id);
            $value = [];
            foreach ($concattrs as $el) {
                $value[] = ArrayHelper::getValue($element, $el);
            }
            $result[$key] = implode($separator, $value);
        }

        return $result;

    }

    /**
     * Random string generator
     *
     * @param int  $len
     * @param bool $isdigit
     *
     * @return string
     */
    public static function randomString($len = 10, $isdigit = false)
    {
        $key_chars = ($isdigit) ? '12345678987654321'
            : 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $num_chars = strlen($key_chars);
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= substr($key_chars, rand(1, $num_chars) - 1, 1);
        }

        return $key;
    }

    /**
     * ucwords for utf-8
     *
     * @param $str
     *
     * @return string
     */
    public static function mb_ucwords($str)
    {
        $str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
        return ($str);
    }

    /**
     * @param $str
     *
     * @return string
     */
    public static function smart_ucwords($str)
    {
        $strs = explode(' ', strtolower($str));
        $res = [];
        foreach ($strs as $s) {
            if (!preg_match('/[аоуэыияёюе]/iu', $s) or preg_match('/[0-9\-_\.]/iu', $s)) {
                $res[] = mb_strtolower(trim($s), 'UTF-8');
            } else {
                $res[] = self::mb_ucwords(trim($s));
            }
        }
        return implode(' ', $res);
    }


    /**
     * php function stripos for array of string, return true if one of needle in haystack
     *
     * @param                     $haystack
     * @param mixed(array|string) $needle
     * @param int                 $offset
     *
     * @return bool
     */
    public static function striposa($haystack, $needle, $offset = 0)
    {
        if (!is_array($needle)) {
            $needle = array($needle);
        }
        foreach ($needle as $query) {
            if (stripos($haystack, $query, $offset) !== false) {
                return true;
            } // stop on first true result
        }
        return false;
    }


    /**
     * Quick logs with special category
     *
     * @param string $mess
     */
    public static function logs($mess)
    {
        \Yii::info(Helper::dumps($mess, false), 'spec');
    }

    /**
     * Check if in_array all elements from first array in second array
     */
    public static function is_subarray(array $arr1, array $arr2)
    {

        $arr2 = array_flip($arr2);
        foreach ($arr1 as $item) {
            if (!isset($arr2[$item])) {
                return false;
            }
        }
        return true;

        /*$diff=array_diff($arr1,$arr2);
        return !empty($diff)?false:true;*/
    }

    /**
     * faster in_array implementation ( really it strange)
     */
    public static function in_array($val, array $arr)
    {
        $arr = array_flip($arr);
        return isset($arr[$val]);
    }

    public static function num_words($string)
    {
        preg_match_all("/\S+/", $string, $matches);
        return count($matches[0]);
    }

    public static function str2num($str){
        return preg_replace("/[^0-9]/", '', $str);
    }

    public static function str2float($str){
        $str=str_replace(',','.',$str);
        return ($str+1)-1;
    }
}