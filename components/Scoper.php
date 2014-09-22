<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 10.07.14
 * Time: 18:49
 */

namespace insolita\things\components;

use yii\db\ActiveQuery;
use yii\db\Expression;

class Scoper extends ActiveQuery
{
    public function active($state = true, $alias = false)
    {
        if (!$alias) {
            $this->andWhere(['active' => $state]);
        } else {
            $this->andWhere([$alias . '.active' => $state]);
        }

        return $this;
    }

    public function mainer()
    {
        $this->addOrderBy(['is_main' => SORT_DESC]);
        return $this;
    }

    public function published($alias = false)
    {
        if (!$alias) {
            $this->andWhere(['active' => 1])->andWhere(new Expression('publishto<=NOW()'));
        } else {
            $this->andWhere([$alias . '.active' => 1])->andWhere(new Expression($alias . '.publishto<=NOW()'));
        }

        return $this;
    }

} 