<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 09.07.14
 * Time: 22:09
 */

namespace insolita\things\wcractions;


use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ViewAction extends Action
{
    /**
     * @var \insolita\things\components\SActiveRecord $modelClass
     */
    public $modelClass;

    /**
     * @var \yii\web\Controller the controller that owns this action
     */
    public $controller;


    public function init()
    {
        if ($this->modelClass === null) {
            throw new InvalidConfigException('"modelClass" cannot be empty.');
        }
        parent::init();
    }

    /**
     * @inheritdoc
     * @throws \yii\web\BadRequestHttpException
     */
    public function run($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isAjax) {
            return $this->controller->renderAjax('_view', ['model' => $model]);
        }else{
            return $this->controller->render('view', ['model'=>$model]);
        }
    }

    protected function findModel($id)
    {
        $class = $this->modelClass;
        if (($model = $class::findOne($id)) !== null) {
            return $model;
        } else {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['title' => 'Не найдено', 'body' => 'Запрашиваемой страницы не существует'];
            }
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
} 