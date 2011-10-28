<?php

/**
 * Constants & Settings for the project zoolu
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-05-28: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package
 */

// Define root path
define('GLOBAL_ROOT_PATH', realpath(dirname(__FILE__).'/..').'/');

// Define path to application directory
defined('APPLICATION_PATH')
  || define('APPLICATION_PATH', realpath(GLOBAL_ROOT_PATH.'/application'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR,
  array(realpath(GLOBAL_ROOT_PATH.'/library'),
    get_include_path()
  )
));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
    
/**
 * include class Zend_Config_Xml
 */
require_once('Zend/Config/Xml.php');
    
$sysConfig = new Zend_Config_Xml(GLOBAL_ROOT_PATH.'/sys_config/config.xml', APPLICATION_ENV);
$zooConfig = new Zend_Config_Xml(APPLICATION_PATH.'/zoolu/app_config/config.xml', APPLICATION_ENV);
$config = new Zend_Config_Xml(APPLICATION_PATH.'/website/app_config/config.xml', APPLICATION_ENV);

/**
 * include class Zend_Registry
 */
require_once('Zend/Registry.php');

Zend_Registry::set('SysConfig', $sysConfig);
Zend_Registry::set('ZooConfig', $zooConfig);
Zend_Registry::set('Config', $config);

/**
 * define MAGIC for finfo (install php-pecl-Fileinfo)
 */
define('MAGIC', '/usr/share/misc/magic.mgc');

?>