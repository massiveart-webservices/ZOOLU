<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
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
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
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
class Core
{

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
    public $objCoreSession = null;

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
     * @var string
     */
    public $strDisplayType;

    /**
     * Constructor
     */
    protected function __construct($blnWithDbh = true, Zend_Config_Xml $sysConfig, Zend_Config_Xml $zooConfig, Zend_Config_Xml $config)
    {
        // set sys config object
        $this->sysConfig = $sysConfig;

        // set modules config object
        $this->zooConfig = $zooConfig;

        // set website config object
        $this->config = $config;

        // initialize zf log
        $this->logger = new Zend_Log();
        
        if (isset($_SERVER['HTTP_HOST'])) {
            Zend_Session::setOptions(array('cookie_domain' => $this->getMainDomain($_SERVER['HTTP_HOST'])));
        }
        
        // initialize Zend_Session_Namespace
        $this->objCoreSession = new Zend_Session_Namespace('Core');

        // create logfile extension for file writer
        $strLogFileExtension = '';
        if ($this->sysConfig->logger->priority > Zend_Log::ERR) {
            if (isset($_SESSION["sesUserName"]) && isset($_SERVER['REMOTE_ADDR'])) {
                $strLogFileExtension = '_' . $_SESSION["sesUserName"] . '_' . $_SERVER['REMOTE_ADDR'];
            } else
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $strLogFileExtension = '_' . $_SERVER['REMOTE_ADDR'];
                } else {
                    $strLogFileExtension = '_local';
                }
        }

        // create log file writer
        $writer = new Zend_Log_Writer_Stream(GLOBAL_ROOT_PATH . $this->sysConfig->logger->path . 'log_' . date('Ymd') . $strLogFileExtension . '.log');
        $this->logger->addWriter($writer);

        // set log priority
        $filter = new Zend_Log_Filter_Priority((int) $this->sysConfig->logger->priority);
        $this->logger->addFilter($filter);

        // set the display environment
        $this->initDisplayEnvironment();
        
        // get language and set translate object
        $this->logger->info('get language from ... ');
        if (isset($_GET['language'])) {
            $this->logger->info('GET');
            $this->strLanguageCode = trim($_GET['language'], '/');
            foreach ($this->config->languages->language->toArray() as $arrLanguage) {
                if (array_key_exists('code', $arrLanguage) && $arrLanguage['code'] == strtolower($this->strLanguageCode)) {
                    $this->intLanguageId = $arrLanguage['id'];
                    break;
                }
            }
            // fallback if ther is no language id
            if ($this->intLanguageId == null) {
                $this->logger->info('DEFAULT');
                $this->blnIsDefaultLanguage = true;
                $this->intLanguageId = $this->sysConfig->languages->default->id;
                $this->strLanguageCode = $this->sysConfig->languages->default->code;
            }

        } else {
            $this->logger->info('DEFAULT');
            $this->blnIsDefaultLanguage = true;
            $this->intLanguageId = $this->sysConfig->languages->default->id;
            $this->strLanguageCode = $this->sysConfig->languages->default->code;
        }
        
        Zend_Session::setOptions(array('cookie_domain' => 'zoolu.area51.at'));
        // set up zoolu translate obj
        $this->intZooluLanguageId = (Zend_Auth::getInstance()->hasIdentity()) ? Zend_Auth::getInstance()->getIdentity()->languageId : $this->intLanguageId;
        $this->strZooluLanguageCode = (Zend_Auth::getInstance()->hasIdentity()) ? Zend_Auth::getInstance()->getIdentity()->languageCode : $this->strLanguageCode;
        if(file_exists(GLOBAL_ROOT_PATH.'application/zoolu/language/zoolu-'.$this->strZooluLanguageCode.'.mo')){
          $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/zoolu/language/zoolu-'.$this->strZooluLanguageCode.'.mo');  
        }else{
          $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/zoolu/language/zoolu-'.$this->zooConfig->languages->default->code.'.mo');
        }
            
        if ($blnWithDbh == true) {
            /**
             * initialize the ZEND DB Connection
             * do lazy connection binding, so db connection will be established on first use with dbh->getConnection()
             */
            try {
                $pdoParams = array(
                    PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
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

                if ($this->sysConfig->logger->priority == Zend_Log::DEBUG) $this->dbh->getProfiler()->setEnabled(true);

                $this->dbh->getConnection();

                $this->dbh->exec('SET CHARACTER SET ' . $this->sysConfig->encoding->db);

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
                    'host'       => Zend_Cache_Backend_Memcached::DEFAULT_HOST,
                    'port'       => Zend_Cache_Backend_Memcached::DEFAULT_PORT,
                    'persistent' => Zend_Cache_Backend_Memcached::DEFAULT_PERSISTENT
                );

                $arrBackendOptions = array(
                    'cache_dir' => GLOBAL_ROOT_PATH . $this->sysConfig->path->cache->tables // Directory where to put the cache files
                    //'server' => $arrServer
                );

                $objCache = Zend_Cache::factory('Core',
                    'File', //Memcached
                    $arrFrontendOptions,
                    $arrBackendOptions);

                /**
                 * set the cache to be used with all table objects
                 */
                Zend_Db_Table_Abstract::setDefaultMetadataCache($objCache);
            } catch (Zend_Db_Adapter_Exception $exc) {
                $this->logger->err($exc);
                header('Location: http://' . $this->sysConfig->hostname);
                die();
            } catch (Zend_Exception $exc) {
                $this->logger->err($exc);
                header('Location: http://' . $this->sysConfig->hostname);
                die();
            }
        }
    }

    /**
     * updateSessionLanguage
     * @return void
     */
    public function updateSessionLanguage()
    {
        if ($this->objCoreSession instanceof Zend_Session_Abstract) {
            // update session language now
            $this->objCoreSession->languageId = $this->intLanguageId;
            $this->objCoreSession->languageCode = $this->strLanguageCode;
        }
    }

    /**
     * initDisplayEnvironment
     */
    private function initDisplayEnvironment()
    {
        if (isset($this->objCoreSession) && isset($this->objCoreSession->strDisplayType)) {
            $this->strDisplayType = $this->objCoreSession->strDisplayType;
        } else {
            $useragent = ((isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '');
            if (preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {
                //Mobile devices
                $this->strDisplayType = $this->sysConfig->display_type->mobile;
            } elseif (preg_match('/iPad/i', $useragent)) {
                //Tablets (until now only iPad)
                $this->strDisplayType = $this->sysConfig->display_type->tablet;
            } else {
                //Otherwise website
                $this->strDisplayType = $this->sysConfig->display_type->website;
            }
        }
    }

    private function __clone()
    {
    }
    
    /**
     * TmpCache
     * @return Zend_Cache_Core
     */
    public function TmpCache()
    {
        if ($this->objTmpCache === null) {
            $this->initTmpCache();
        }
        return $this->objTmpCache;
    }

    /**
     * initTmpCache
     * @return void
     */
    private function initTmpCache()
    {
        /**
         * set up the cache
         */
        $arrFrontendOptions = array(
            'automatic_serialization' => true,
            'lifetime'                => 604860,
        );

        $arrBackendOptions = array(
            'cache_dir' => GLOBAL_ROOT_PATH . $this->sysConfig->path->cache->tmp
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
    public static function getInstance($blnWithDbh = true, Zend_Config_Xml $sysConfig, Zend_Config_Xml $zooConfig, Zend_Config_Xml $config)
    {
        if (self::$instance == null) {
            self::$instance = new Core($blnWithDbh, $sysConfig, $zooConfig, $config);
        }
        return self::$instance;
    }
    
    /**
     * getMainDomain
     * returns the domain without language from subdomain
     * @param string $strUrl
     */
    public function getMainDomain($strDomain) {
        if ($this->config->enable_short_subdomains != 'true') {    
            $arrUrlParts = explode('.', $strDomain);
            // if subdomain exists
            if (count($arrUrlParts) > 2) {
                // if first part url is 2 chars long
                if (strlen($arrUrlParts[0]) == 2) {
                    $strDomain = substr($strDomain, strpos($strDomain, '.')+1);    
                } 
            }
        }
        return $strDomain;
    }
}

?>