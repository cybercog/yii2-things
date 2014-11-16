<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 09.07.14
 * Time: 22:10
 */

namespace insolita\things\wcractions;


use insolita\things\helpers\Helper;
use insolita\things\models\MassActionsModel;
use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Response;
use yii\web\NotFoundHttpException;

class MassAction extends Action
{
    /**
     * @var \insolita\things\components\SActiveRecord $modelClass
     */
    public $modelClass;
    /**
     * @var \insolita\things\components\SActiveRecord $searchClass
     */
    public $searchClass;

    /**
     * @var \yii\web\Controller the controller that owns this action
     */
    public $controller;

    public $massactlist = [];

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
        $massactmodel = new MassActionsModel();
        $massactmodel->setActlist($this->massactlist);
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            if ($massactmodel->load(\Yii::$app->request->post()) && $massactmodel->validate()) {
                if ($massactmodel->act == 'del') {
                    $idlist = $massactmodel->getIdlist();
                    foreach ($idlist as $id) {
                        $this->findModel($id)->softdelete();
                    }
                    return ['state' => true, 'error' => $massactmodel->ids];
                }elseif($massactmodel->act=='rem'){
                    $idlist = $massactmodel->getIdlist();
                    foreach ($idlist as $id) {
                        $this->findModel($id)->delete();
                    }
                    return ['state' => true, 'error' => $massactmodel->ids];
                } else {
                    return ['state' => false, 'error' => 'Некорректный тип операции'];
                }

            } else {
                return ['state' => false, 'error' => Helper::errorSummary($massactmodel)];
            }
        } else {
            return ['state' => false, 'error' => 'Некорректный тип запроса'];
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
        /**
         * @var \insolita\things\components\SActiveRecord $class
         */
        $class = $this->modelClass;
        if (($model = $class::findOne($id)) !== null) {
            return $model;
        } else {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['state' => false, 'error' => 'Запрашиваемой страниц не существует'];
            }
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

} 