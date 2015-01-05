<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 09.07.14
 * Time: 22:09
 */

namespace insolita\things\wcractions;


use insolita\things\helpers\Helper;
use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Url;

class UpdateAction extends Action
{
    /**
     * @var \insolita\things\components\SActiveRecord $modelClass
     */
    public $modelClass;

    /**
     * @var \yii\web\Controller the controller that owns this action
     */
    public $controller;

    public $scenario='update';

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
        /**
         * @var \insolita\things\components\SActiveRecord $model
         */
        $model = $this->findModel($id);
        if($this->scenario){
            $model->scenario = 'update';
        }

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format =(Yii::$app->request->isAjax)? Response::FORMAT_JSON: Response::FORMAT_HTML;
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return (Yii::$app->request->isAjax)?['state' => true, 'error' => '']:$this->controller->redirect(Url::to(['index']));

            } else {
                return (Yii::$app->request->isAjax)?['state' => false, 'error' => Helper::errorSummary($model)]:$this->controller->render('update',['model'=>$model]);
            }
        } elseif (Yii::$app->request->isAjax) {
            return $this->controller->renderAjax('_form', ['model' => $model]);
        }else{
            return $this->controller->render('update',['model'=>$model]);
        }
    }

    /**
     * @param (int|string) $id
     *
     * @throws NotFoundHttpException
     * @return \insolita\things\components\SActiveRecord
     */
    protected function findModel($id)
    {
        $class = $this->modelClass;
        if (($model = $class::findOne($id)) !== null) {
            return $model;
        } else {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['title' => 'Ошибка', 'body' => 'Запрашиваемой страниц не существует','state'=>false,'error'=>'Запрашиваемой страниц не существует'];
            }
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
} 