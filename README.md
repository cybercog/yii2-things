Different things for yii2
=========================
just different little things

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist insolita/yii2-things "*"
```

or add

```
"insolita/yii2-things": "*"
```

to the require section of your `composer.json` file.

AdvDateValidator - Extends for DateValidator
===================================
Allow to set formats as array, not only string - and return true if the validly of one of the formats, Default string validation worked too

Usage
-----
```php
public function rules()
    {
        return [
        //...
            ['somedate',AdvDateValidator::className(),'format'=>['Y-m-d H:i:s','Y-m-d','Y-m']],
        //...
        ];
    }

```

NotraceFileTarget - Extends for FileTarget
===================================
Not log any trace for current target, ignore general trace level (because trace level sets globally for logging);
Usage
-----
Just define class '\insolita\things\components\NotraceFileTarget'  for needed target, other options inherit from FileTarget

DirectCache - Extends for FileCache
===================================
Add some additional methods for access to cache with direct setting keyprefix and cachePath
(usable for access to cache between applications such as to frontend cache from backend or console app)

Usage
-----

 - define in applicationSettings where you want get direct cache access methods

 ```
 'cache' => [
             'class' => '\insolita\things\components\DirectCache',
              .....
         ],
```

Use where you want

```php
Yii::$app->getCache()->setDirect('@frontend/runtime/cache','front','cache_1','data_1', $duration);

Yii::$app->getCache()->getDirect('@demo/runtime/cache','demoPrefix','somecachekey');

Yii::$app->getCache()->deleteDirect('@frontend/runtime/cache','front','cache_1');

Yii::$app->getCache()->flushDirect('@demo/runtime/cache');

```
Standart cache methods (set\get\add\mset\delete  etc.) also presents