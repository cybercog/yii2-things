<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 18.09.14
 * Time: 19:09
 */

namespace insolita\things\behaviors;


use insolita\widgets\Panels\CustControlPanel;
use yii\base\Behavior;
use yii\helpers\Json;
use yii\web\Controller;

class CustomizeBehavior extends Behavior{
    /**@var \yii\web\Controller $owner * */
    public $owner;

    /**
     * Число записей на страницу
     *
     * @var int $grid_pp
     **/
    public $grid_pp = 10;
    /**
     * Тип отображения форм- варианты - 'modal','newpage'
     *
     * @var string $lookmod
     **/
    public $lookmod = 'modal';

    /**
     * Выбранные юзером столбцы грида
     *
     * @var array|bool $gridcols
     **/
    public $gridcols = false;

    /**
     * Actions для которых выполнять поведение
     *
     * @var array|bool $gridcols
     **/
    public $actions = ['index'];

    /**
     * Модель для получения конфига колонок грида
     *
     * @var \yii\db\ActiveRecord $model
     **/
    public $model;

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'getCustomizedSettings'
        ];
    }

    public function getCustomizedSettings($event)
    {
        $uid = (\Yii::$app->user->isGuest) ? 'guest' : \Yii::$app->user->id;
        $pageSize = isset($_COOKIE[$uid . CustControlPanel::PP_COOKIE]) ? $_COOKIE[$uid . CustControlPanel::PP_COOKIE]
            : \Yii::$app->params['grid_pp'];
        $showtype = isset($_COOKIE[$uid . CustControlPanel::LOOK_COOKIE]) ? $_COOKIE[$uid . CustControlPanel::LOOK_COOKIE]
            : \Yii::$app->params['showtype'];
        $gridcols = isset($_COOKIE[$uid . CustControlPanel::COL_COOKIE]) ? Json::decode(
            $_COOKIE[$uid . CustControlPanel::COL_COOKIE]
        ) : false;
        $this->gridcols = $gridcols;
        $this->grid_pp = (in_array((int)$pageSize, [5, 10, 15, 20, 30, 50, 100])) ? $pageSize : 10;
        $this->lookmod = (in_array($showtype, ['modal', 'newpage'])) ? $showtype : 'modal';
    }
} 