<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 07.09.14
 * Time: 2:51
 */

namespace insolita\things\validators;


use yii\validators\DateValidator;

class AdvDateValidator extends DateValidator{
    /**
     * @var mixed(array|string) the date format that the value being validated should follow.
     * Please refer to <http://www.php.net/manual/en/datetime.createfromformat.php> on
     * supported formats.
     */
    public $format = 'Y-m-d';
    /**
     * @var string the name of the attribute to receive the parsing result.
     * When this property is not null and the validation is successful, the named attribute will
     * receive the parsing result.
       IF THE FORMAT SPECIFIED AS ARRAY - will be used the first value!!!
     */
    public $timestampAttribute;

    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        $result = $this->validateValue($value);
        if (!empty($result)) {
            $this->addError($object, $attribute, $result[0], $result[1]);
        } elseif ($this->timestampAttribute !== null) {
            $format=is_array($this->format)?$this->format[0]:$this->format;
            $date = \DateTime::createFromFormat($format, $value);
            $object->{$this->timestampAttribute} = $date->getTimestamp();
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (is_array($value)) {
            return [$this->message, []];
        }
        if(is_array($this->format)){
            $valid=false;
            foreach($this->format as $format){
                $date = \DateTime::createFromFormat($format, $value);
                $errors = \DateTime::getLastErrors();
                $invalid = $date === false || $errors['error_count'] || $errors['warning_count'];
                if(!$invalid){
                    $valid=true;
                    break;
                }
            }
            return $valid ? null : [$this->message, []];

        }else{
            $date = \DateTime::createFromFormat($this->format, $value);
            $errors = \DateTime::getLastErrors();
            $invalid = $date === false || $errors['error_count'] || $errors['warning_count'];
        }

        return $invalid ? [$this->message, []] : null;
    }
} 