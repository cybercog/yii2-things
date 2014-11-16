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
use yii\helpers\Url;
use yii\web\Response;

class CreateAction extends Action
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
    public function run()
    {
        /**
         * @var \insolita\things\components\SActiveRecord $model
         */
        $model = new $this->modelClass;
        $model->scenario = 'create';
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = (Yii::$app->request->isAjax) ? Response::FORMAT_JSON : Response::FORMAT_HTML;
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return (Yii::$app->request->isAjax)
                    ? ['state' => true, 'error' => '']
                    : $this->controller->redirect(
                        Url::to(['index'])
                    );
            } else {
                return (Yii::$app->request->isAjax) ? ['state' => false, 'error' => Helper::errorSummary($model)]
                    : $this->controller->render('create', ['model' => $model]);
            }
        } elseif (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $this->controller->renderAjax('_form', ['model' => $model]);
        } else {
            return $this->controller->render('create', ['model' => $model]);
        }
    }
} 