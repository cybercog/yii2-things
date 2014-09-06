<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 03.09.14
 * Time: 18:50
 */

namespace insolita\things\components;


use yii\caching\FileCache;
use Yii;

/**
 * Class DirectCache
 *
 * @package insolita\things
 */
class DirectCache extends FileCache{
    private $_olds=[];

    /**
     * @param $path
     * @param $prefix
     */
    private  function softChange($path,$prefix){
        $this->_olds=['path'=>$this->cachePath,'prefix'=>$this->keyPrefix];
        $this->cachePath = Yii::getAlias($path);
        $this->keyPrefix=$prefix;
    }

    /**
     *
     */
    private function retriveOlds(){
        $this->cachePath = $this->_olds['path'];
        $this->keyPrefix=$this->_olds['prefix'];
    }

    /**
     * @param $path
     * @param $prefix
     * @param $key
     *
     * @return mixed
     */
    public function getDirect($path,$prefix,$key){
        $this->softChange($path,$prefix);
        $res= $this->get($key);
        $this->retriveOlds();
        return $res;
    }

    /**
     * @param $path
     * @param $prefix
     * @param $key
     * @param $value
     * @param $duration
     * @param $dependency
     *
     * @return bool
     */
    public function setDirect($path,$prefix,$key, $value, $duration = 0, $dependency = null){
        $this->softChange($path,$prefix);
        $res= $this->set($key,$value,$duration,$dependency);
        $this->retriveOlds();
        return $res;
    }

    /**
     * @param $path
     * @param $prefix
     * @param $key
     *
     * @return bool
     */
    public function deleteDirect($path,$prefix,$key){
        $this->softChange($path,$prefix);
        $res= $this->delete($key);
        $this->retriveOlds();
        return $res;
    }

    /**
     * @param $path
     * @param $prefix
     *
     * @return bool
     */
    public function flushDirect($path,$prefix){
        $this->softChange($path,$prefix);
        $res= $this->flush();
        $this->retriveOlds();
        return $res;
    }

    /**
     * @param $path
     * @param $prefix
     * @param $key
     *
     * @return bool
     */
    public function existsDirect($path,$prefix,$key)
    {
        $this->softChange($path,$prefix);
        $res= $this->exists($key);
        $this->retriveOlds();
        return $res;
    }

    /**
     * @param      $path
     * @param      $prefix
     * @param      $key
     * @param      $value
     * @param int  $duration
     * @param null $dependency
     *
     * @return bool
     */
    public function addDirect($path,$prefix,$key, $value, $duration = 0, $dependency = null)
    {
        $this->softChange($path,$prefix);
        $res= $this->add($key, $value, $duration, $dependency);
        $this->retriveOlds();
        return $res;
    }


}