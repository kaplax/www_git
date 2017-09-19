<?php
session_start();
date_default_timezone_set('Asia/Shanghai');
define('APIMODEL','test'); //test 
if (APIMODEL == 'product') {
	error_reporting(0);
	$conf	=	dirname(__FILE__).'/config.php';
	$config	=	dirname(__FILE__).'/protected/config/main.php';
	defined('YII_DEBUG') or define('YII_DEBUG',true);
	defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
} else {
	error_reporting(E_ALL);
	$conf	=	dirname(__FILE__).'/config.test.php';
	$config	=	dirname(__FILE__).'/protected/config/main.test.php';
	defined('YII_DEBUG') or define('YII_DEBUG',true);
	defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
}
$yii	=	dirname(__FILE__).'/../yii/yii.php';
require_once ($conf);
require_once($yii);
Yii::createWebApplication($config)->run();

