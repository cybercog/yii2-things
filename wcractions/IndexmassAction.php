<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 09.07.14
 * Time: 22:10
 */

namespace insolita\things\wcractions;


use insolita\things\models\MassActionsModel;
use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;

class IndexmassAction extends Action{
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
    public $massactlist=[];
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
         * @var \insolita\things\components\SActiveRecord $searchModel
         */
        $searchModel = new $this->searchClass;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        $massactmodel=new MassActionsModel();
        $massactmodel->setActlist($this->massactlist);
        return $this->controller->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'massactmodel'=>$massactmodel,
            ]);
    }

} 