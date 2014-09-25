<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 26.08.14
 * Time: 9:49
 */

namespace insolita\things\validators;

use yii\validators\Validator;


class JsonValidator extends Validator{
    public function init(){
        parent::init();
        if(!$this->message){
            $this->message =  '{attribute} must be a valid JSON';
        }
    }
    /**
     * @inheritdoc
     */
    public function validateValue($value)
    {
        if(!@json_decode($value)){
            return [$this->message, []];
        }
    }
} 