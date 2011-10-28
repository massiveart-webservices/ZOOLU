<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 *
 * LICENSE
 *
 * This file is part of ZOOLU.
 *
 * ZOOLU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ZOOLU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ZOOLU. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * For further information visit our website www.getzoolu.org
 * or contact us at zoolu@getzoolu.org
 *
 * @category   ZOOLU
 * @package    library.massiveart
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Core Class - based on Singleton Pattern
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-09: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart
 * @subpackage Core
 */

class Core {

  /**
   * object instance
   */
  private static $instance = null;

  /**
	 * @var Zend_Db_Adapter_Abstract
	 */
  public $dbh;

  /**
	 * @var Zend_Log
	 */
  public $logger;

  /**
	 * @var Zend_Config_Xml
	 */
  public $sysConfig;
  public $zooConfig;
  public $config;

  /**
   * @var HtmlTranslate
   */
  public $translate;
  
  /**
   * @var Zend_Session_Namespace
   */
  public $objCoreSession;
  
  /**
   * @var Zend_Cache_Core
   */
  private $objTmpCache;
  
  /**
   * @var integer
   */
  public $intLanguageId;

  /**
   * @var string
   */
  public $strLanguageCode;
  
  /**
   * @var integer
   */
  public $intZooluLanguageId;

  /**
   * @var string
   */
  public $strZooluLanguageCode;  
  
  /**
   * @var boolean
   */
  public $blnIsDefaultLanguage = false;
  
  /**
   * Constructor
   */
  protected function __construct($blnWithDbh = true, Zend_Config_Xml &$sysConfig, Zend_Config_Xml &$zooConfig, Zend_Config_Xml &$config){
    /**
     * set sys config object
     */
    $this->sysConfig = $sysConfig;

    /**
     * set modules config object
     */
    $this->zooConfig = $zooConfig;

    /**
     * set website config object
     */
    $this->config = $config;

    /**
     * initialize Zend_Log
     */
    $this->logger = new Zend_Log();
    
    /**
     * initialize Zend_Session_Namespace
     */
    $this->objCoreSession = new Zend_Session_Namespace('Core');
    
    /**
     * create logfile extension for file writer
     */
    $strLogFileExtension = '';
    if($this->sysConfig->logger->priority > Zend_Log::ERR){
      if(isset($_SESSION["sesUserName"]) && isset($_SERVER['REMOTE_ADDR'])){
        $strLogFileExtension = '_'.$_SESSION["sesUserName"].'_'.$_SERVER['REMOTE_ADDR'];
      }else
      if(isset($_SERVER['REMOTE_ADDR'])){
        $strLogFileExtension = '_'.$_SERVER['REMOTE_ADDR'];
      }else{
        $strLogFileExtension = '_local';
      }
    }

    /**
     * create log file writer
     */
    $writer = new Zend_Log_Writer_Stream(GLOBAL_ROOT_PATH.$this->sysConfig->logger->path.'log_'.date('Ymd').$strLogFileExtension.'.log');
    $this->logger->addWriter($writer);

    /**
     * set log priority
     */
    $filter = new Zend_Log_Filter_Priority((int) $this->sysConfig->logger->priority);
    $this->logger->addFilter($filter);
    
    /**
     * get language and set translate object
     */
    $this->logger->info('get language from ... ');
    if(isset($_GET['language'])){
      $this->logger->info('GET');
      $this->strLanguageCode = trim($_GET['language'], '/');
      foreach($this->config->languages->language->toArray() as $arrLanguage){
        if(array_key_exists('code', $arrLanguage) && $arrLanguage['code'] == strtolower($this->strLanguageCode)){
          $this->intLanguageId = $arrLanguage['id'];
          break;
        }
      }
      if($this->intLanguageId == null){
        if(isset($this->objCoreSession->languageId)){
          $this->logger->info('SESSION');
          $this->intLanguageId = $this->objCoreSession->languageId;
          $this->strLanguageCode = $this->objCoreSession->languageCode;
        }else{
          $this->logger->info('DEFAULT');
          $this->blnIsDefaultLanguage = true;
          $this->intLanguageId = $this->sysConfig->languages->default->id;
          $this->strLanguageCode = $this->sysConfig->languages->default->code;  
        }
      }
    }else if(isset($_SERVER['REQUEST_URI']) && preg_match('/^\/[a-zA-Z\-]{2,5}\//', $_SERVER['REQUEST_URI'])){
      $this->logger->info('URI');
      preg_match('/^\/[a-zA-Z\-]{2,5}\//', $_SERVER['REQUEST_URI'], $arrMatches);
      $this->strLanguageCode = trim($arrMatches[0], '/');
      foreach($this->config->languages->language->toArray() as $arrLanguage){
        if(array_key_exists('code', $arrLanguage) && $arrLanguage['code'] == strtolower($this->strLanguageCode)){
          $this->intLanguageId = $arrLanguage['id'];
          break;
        }
      }
      if($this->intLanguageId == null){
        if(isset($this->objCoreSession->languageId)){
          $this->logger->info('SESSION');
          $this->intLanguageId = $this->objCoreSession->languageId;
          $this->strLanguageCode = $this->objCoreSession->languageCode;
        }else{
          $this->logger->info('DEFAULT');
          $this->blnIsDefaultLanguage = true;
          $this->intLanguageId = $this->sysConfig->languages->default->id;
          $this->strLanguageCode = $this->sysConfig->languages->default->code;  
        }
      }
    }else if(isset($this->objCoreSession->languageId)){
      $this->logger->info('SESSION');
      $this->intLanguageId = $this->objCoreSession->languageId;
      $this->strLanguageCode = $this->objCoreSession->languageCode;
    }else{
      $this->logger->info('DEFAULT');
      $this->blnIsDefaultLanguage = true;
      $this->intLanguageId = $this->sysConfig->languages->default->id;
      $this->strLanguageCode = $this->sysConfig->languages->default->code;
    }    
        
    /**
     * set up zoolu translate obj
     */
    $this->intZooluLanguageId = (Zend_Auth::getInstance()->hasIdentity()) ? Zend_Auth::getInstance()->getIdentity()->languageId : $this->intLanguageId;
    $this->strZooluLanguageCode = (Zend_Auth::getInstance()->hasIdentity()) ? Zend_Auth::getInstance()->getIdentity()->languageCode : $this->strLanguageCode;
    
    if(file_exists(GLOBAL_ROOT_PATH.'application/zoolu/language/zoolu-'.$this->strZooluLanguageCode.'.mo')){
      $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/zoolu/language/zoolu-'.$this->strZooluLanguageCode.'.mo');  
    }else{
      $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/zoolu/language/zoolu-'.$this->zooConfig->languages->default->code.'.mo');
    }
    
    // update session language
    $this->updateSessionLanguage();

    if($blnWithDbh == true){
      /**
       * initialize the ZEND DB Connection
       * do lazy connection binding, so db connection will be established on first use with dbh->getConnection()
       */
      try {

        $pdoParams = array(
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        );

      	$dbhParameters = array(
          'host'             => $this->sysConfig->database->params->host,
    			'username'         => $this->sysConfig->database->params->username,
    			'password'         => $this->sysConfig->database->params->password,
    			'dbname'           => $this->sysConfig->database->params->dbname,
      		'driver_options'   => $pdoParams
    		);

       	$this->dbh = Zend_Db::factory($this->sysConfig->database->adapter, $dbhParameters);

       	if($this->sysConfig->logger->priority == Zend_Log::DEBUG) $this->dbh->getProfiler()->setEnabled(true);

       	$this->dbh->getConnection();

       	$this->dbh->exec('SET CHARACTER SET '.$this->sysConfig->encoding->db);
       	
       	Zend_Db_Table::setDefaultAdapter($this->dbh);

       	/**
       	 * using a default metadata cache for all table objects
       	 *
       	 * set up the cache
       	 */
        $arrFrontendOptions = array(
          'automatic_serialization' => true
        );

        /**
         * memcache server configuration
         */
        $arrServer = array(
          'host' => Zend_Cache_Backend_Memcached::DEFAULT_HOST,
          'port' => Zend_Cache_Backend_Memcached::DEFAULT_PORT,
          'persistent' => Zend_Cache_Backend_Memcached::DEFAULT_PERSISTENT
        );

        $arrBackendOptions  = array(
          'cache_dir' => GLOBAL_ROOT_PATH.$this->sysConfig->path->cache->tables // Directory where to put the cache files
          //'server' => $arrServer
        );

        $objCache = Zend_Cache::factory('Core',
                                        'File',//Memcached
                                        $arrFrontendOptions,
                                        $arrBackendOptions);

        /**
         * set the cache to be used with all table objects
         */
        Zend_Db_Table_Abstract::setDefaultMetadataCache($objCache);

      } catch (Zend_Db_Adapter_Exception $exc) {
        $this->logger->err($exc);
        header ('Location: http://'.$this->sysConfig->hostname);
        die();
      } catch (Zend_Exception $exc) {
        $this->logger->err($exc);
        header ('Location: http://'.$this->sysConfig->hostname);
        die();
      }
    }
  }

  private function __clone(){}
  
  /**
   * updateSessionLanguage
   * @return void
   */
  public function updateSessionLanguage(){
    if($this->objCoreSession instanceof Zend_Session_Abstract){
      // update session language now
      $this->objCoreSession->languageId = $this->intLanguageId;
      $this->objCoreSession->languageCode = $this->strLanguageCode;
    }
  }
  
  /**
   * TmpCache
   * @return Zend_Cache_Core
   */
  public function TmpCache(){
    if($this->objTmpCache === null){
      $this->initTmpCache();
    }
    return $this->objTmpCache;
  }
  
  /**
   * initTmpCache
   * @return void
   */
  private function initTmpCache(){
    /**
     * set up the cache
     */
    $arrFrontendOptions = array(
      'automatic_serialization' => true,
      'lifetime' => 604860,
    );

    $arrBackendOptions  = array(
      'cache_dir' => GLOBAL_ROOT_PATH.$this->sysConfig->path->cache->tmp
    );

    $this->objTmpCache = Zend_Cache::factory('Core',
                                             'File',
                                             $arrFrontendOptions,
                                             $arrBackendOptions);  
  }

  /**
   * getInstance
   * @return object instance of the class
   */
  public static function getInstance($blnWithDbh = true, Zend_Config_Xml &$sysConfig, Zend_Config_Xml &$zooConfig, Zend_Config_Xml &$config){
    if(self::$instance == null){
      self::$instance = new Core($blnWithDbh, $sysConfig, $zooConfig, $config);
    }
    return self::$instance;
  }
}
?>