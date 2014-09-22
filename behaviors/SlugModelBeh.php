<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 28.07.14
 * Time: 11:50
 */

namespace insolita\things\behaviors;

use yii\db\ActiveRecord;
use yii\base\Behavior;
use yii\validators\UniqueValidator;

class SlugModelBeh extends Behavior
{
    public $source_attribute = 'name';
    public $slug_attribute = 'slug';


    public $lowercase = true;
    public $unique = true;
    public $refresh = true;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'processSlug',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'processSlug',
        ];
    }

    public function processSlug($event)
    {
        if (empty($this->owner->{$this->slug_attribute})) {
            $this->generateSlug($this->owner->{$this->source_attribute});
        } else {
            //echo 'direct slug change';
        }

    }

    private function generateSlug($slug)
    {
        $slug = $this->slugify($slug);
        $this->owner->{$this->slug_attribute} = $slug;
        if ($this->unique) {
            $suffix = 1;
            while (!$this->checkUniqueSlug()) {
                $this->owner->{$this->slug_attribute} = $slug . ++$suffix;
            }
        }
    }

    private function slugify($slug)
    {
        return $this->tr($slug, $this->lowercase);
    }

    public function tr($st, $islower = false)
    {
        $st = trim((string)$st);

        $st = preg_replace("#\s#siu", "_", $st);
        $st = str_replace('"', '', $st);
        $st = str_replace("'", '', $st);
        $st = preg_replace("#[^A-Za-zА-Яа-яЁё0-9_\-]#siu", "", $st);
        $st = urldecode($st);
        $replace_chars = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'з' => 'z',
            'и' => 'i',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'ц' => 'c',
            'ы' => 'y',
            'і' => 'i',
            'й' => 'jj',
            'ё' => 'e',
            'ж' => 'zh',
            'х' => 'kh',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'tsh',
            'э' => 'e',
            'ю' => 'u',
            'я' => 'ya',
            'ъ' => '',
            'ь' => '',
            'ї' => 'yi',
            'є' => 'ye',
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'З' => 'Z',
            'И' => 'I',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Ц' => 'C',
            'Ы' => 'Y',
            'І' => 'I',
            'Й' => 'Y',
            'Ё' => 'E',
            'Ж' => 'Zh',
            'Х' => 'H',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Tsh',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya',
            'Ъ' => '',
            'Ь' => '',
            'Ї' => 'YI',
            'Є' => 'YE'
        );

        foreach ($replace_chars As $key => $val) {
            $st = mb_ereg_replace($key, $val, $st);

        }
        $st = trim($st);

        return ($islower) ? strtolower($st) : $st;
    }

    private function checkUniqueSlug()
    {
        $model = clone $this->owner;
        $uniqueValidator = new UniqueValidator;
        $uniqueValidator->validateAttribute($model, $this->slug_attribute);

        return !$model->hasErrors($this->slug_attribute);
    }
} 