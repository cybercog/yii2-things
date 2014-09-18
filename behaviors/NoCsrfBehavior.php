<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 18.09.14
 * Time: 18:37
 */

namespace insolita\things\behaviors;


use yii\base\Behavior;
use yii\web\Controller;

/**
 * Disable csrf on custom actions
 *
 * @example
 * public function behaviors(){
 *   return  [
 *         'nocsrf'=>[
 *                     'class' =>NoCsrfBehavior::className(),
 *                     'actions'=>['action1','action2']
 *                   ]
 *         ...
 *    ]
 * }
**/
class NoCsrfBehavior extends Behavior{
    public $actions=[];
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'nocsrf'
        ];
    }

    public function nocsrf($event){
        if(is_array($this->actions) && !empty($this->actions) && in_array($event->action->id, $this->actions)){
            \Yii::$app->request->enableCsrfValidation = false;
        }
    }

} 