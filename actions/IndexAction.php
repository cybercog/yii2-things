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

/**
 * @var \yii\db\ActiveRecord $modelClass
 * @var \yii\db\ActiveRecord $searchClass
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
     * @var \yii\db\ActiveRecord $searchModel
     * @throws \yii\web\BadRequestHttpException
     */
    public function run()
    {
        $searchModel = new $this->searchClass;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->controller->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

} 