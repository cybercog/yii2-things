<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 01.07.14
 * Time: 19:50
 */

namespace insolita\things\models;

use yii\base\Model;


class MassActionsModel extends Model
{
    /**
     * @var string
     */
    public $ids;
    /**
     * @var string
     */
    public $act;

    /**
     * @var array
     */
    private $actlist = [];


    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'ids' => 'Id',
            'act' => 'Действие',
        ];
    }

    /**
     * @param array $list
     */
    public function setActlist($list = [])
    {
        $this->actlist = is_array($list) ? $list : [];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['ids', 'required', 'message' => 'Вы не выбрали ни одного элемента для выполнения действия'],
            ['act', 'required', 'message' => 'Указано некорректное действие'],
            ['ids', 'string', 'message' => 'Некорректный идентификатор'],
            ['act', 'in', 'range' => $this->getActs()]
        ];
    }

    /**
     * @return array
     */
    public function getIdlist()
    {
        return $this->ids ? explode(',', $this->ids) : [];
    }


    /**
     * @return array
     */
    public function getActlist()
    {
        return $this->actlist;
    }

    /**
     * @return array
     */
    public function getActs()
    {
        return array_keys($this->actlist);
    }

} 