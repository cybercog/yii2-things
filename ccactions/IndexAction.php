<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 09.07.14
 * Time: 22:10
 */

namespace insolita\things\ccactions;


use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;

/**
 * @var /common/SActiveRecord $modelClass
 * @var /common/SActiveRecord $searchClass
 **/
class IndexAction extends Action
{
    public $modelClass;
    public $searchClass;

    public function init()
    {
        if ($this->modelClass === null) {
            throw new InvalidConfigException('"modelClass" cannot be empty.');
        }
        parent::init();
    }

    /**
     * @inheritdoc
     * @var /common/SActiveRecord $searchModel
     * @var \yii\web\Controller $controller
     * @throws \yii\web\BadRequestHttpException
     */
    public function run()
    {
        $searchModel = new $this->searchClass;
        $beh=array_keys($this->controller->behaviors());
        if(in_array('customizer',$beh)){
            $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->controller->grid_pp);
        }else{
            $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        }

        return $this->controller->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

} 