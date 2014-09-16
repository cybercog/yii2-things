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
        //Helper::dump($this->controller->_gridcols);Yii::$app->end();
        return $this->controller->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

} 