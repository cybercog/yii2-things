<?php
/**
 * Created by PhpStorm.
 * User: Insolita
 * Date: 15.08.14
 * Time: 13:02
 */

namespace insolita\things\components;

use yii\helpers\VarDumper;
use yii\log\FileTarget;
use yii\log\Logger;

class NotraceFileTarget extends FileTarget
{
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);
        if (!is_string($text)) {
            $text = VarDumper::export($text);
        }
        $traces = [];
        $prefix = $this->getMessagePrefix($message);
        return date('Y-m-d H:i:s', $timestamp) . " {$prefix}[$level][$category] $text";
    }

} 