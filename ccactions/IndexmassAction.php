<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 09.07.14
 * Time: 22:10
 */

namespace insolita\things\ccactions;


use backend\models\MassActionsModel;
use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;

class IndexmassAction extends Action
{
    public $modelClass;
    public $searchClass;
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
        $searchModel = new $this->searchClass;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), $this->controller->_grid_pp);
        $allowedcols = array_keys($searchModel::getGridedAttributes());
        $showedcols = (!empty($this->controller->_gridcols) && is_array($this->controller->_gridcols))
            ? $this->controller->_gridcols : $searchModel::$gridDefaults;
        foreach ($showedcols as $i => $col) {
            if (!in_array($col, $allowedcols)) {
                unset($showedcols[$i]);
            }
        }
        $this->controller->_gridcols = !empty($showedcols) ? $showedcols : $searchModel::$gridDefaults;
        $massactmodel = new MassActionsModel();
        $massactmodel->setActlist($this->massactlist);
        return $this->controller->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'massactmodel' => $massactmodel,
            ]
        );
    }

} 