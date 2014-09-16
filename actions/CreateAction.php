<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 09.07.14
 * Time: 22:09
 */

namespace insolita\things\actions;

use insolita\things\helpers\Helper;
use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\Response;

class CreateAction extends Action
{
    public $modelClass;

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
        $model = new $this->modelClass;
        $model->scenario = 'create';
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
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
            return [
                'title' => Helper::Fa('plus-circle', 'lg') . ' Добавить запись',
                'body' => $this->controller->renderAjax('_form', ['model' => $model])
            ];
        } else {
            return $this->controller->render('create', ['model' => $model]);
        }
    }
} 