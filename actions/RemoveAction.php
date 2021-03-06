<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 09.07.14
 * Time: 22:10
 */

namespace insolita\things\actions;

use yii\base\Action;
 use Yii;
use yii\base\InvalidConfigException;
 use yii\web\Response;


class RemoveAction extends Action
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
    public function run($id)
    {
        $class = $this->modelClass;
        $model = $class::findOne($id);
        if ($model) {
            $model->delete();
            return (Yii::$app->request->isAjax) ? true : $this->controller->redirect(['index']);
        } else {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['state' => false, 'error' => 'Запрашиваемой страниц не существует'];
            } else {
                return $this->controller->redirect(['index']);
            }
        }

    }
}
