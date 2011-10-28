<?php

/**
 * General Include for the project zoolu
 *
 * Is the first include in each page! (session_start,
 * load settings, class define, __autoload, ...)
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-05-28: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package zoolu
 */

//date_default_timezone_set('Europe/Vienna'); // TODO Zend_Locale ????

//session_start();

/**
 * include config
 */
require_once(dirname(__FILE__).'/config.inc.php');

/**
 * set error reporting
 */
if($sysConfig->show_errors === 'false'){
  error_reporting(0);
  ini_set('display_errors', 0);
}else{
  error_reporting(E_ALL);
  ini_set('display_errors', E_ALL && ~E_WARNING);
}

/**
 * include class autoloader
 */
require_once(dirname(__FILE__).'/classautoloader.class.php');

/**
 * Work-around for setting up a session,
 * because Flash Player doesn't send the cookies
 */
if (isset($_POST["PHPSESSID"])) {
  session_id($_POST["PHPSESSID"]);
}

Zend_Session::start(); // TODO Zend_Session ????

/**
 * set default timezone
 */
date_default_timezone_set($sysConfig->timezone);

/**
 * initialize the core class
 * (Zend_Db, Zend_Log, ...)
 */
$core = Core::getInstance(true, $sysConfig, $zooConfig, $config);
Zend_Registry::set('Core', $core);

/**
 * initialize location
 * (using for: Zend_Date, Zend_Translate, Zend_Currency, ...)
 */
$locale = new Zend_Locale($sysConfig->location);
Zend_Registry::set('Location', $locale);

?>
