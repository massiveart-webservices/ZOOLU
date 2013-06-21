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
 * @package    library.massiveart.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * WebControllerAction
 *
 * Check authentification before starting controller actions
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-08-04: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
abstract class WebControllerAction extends Zend_Controller_Action
{
    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * @var Model_Urls
     */
    protected $objModelUrls;

    /**
     * @var Model_Languages
     */
    protected $objModelLanguages;

    /**
     * @var Zend_Db_Table_Row_Abstract
     */
    protected $objTheme;

    /**
     * @var Zend_Cache_Frontend_Output
     */
    protected $objCache;

    protected $blnCachingStart = false;
    protected $blnCachingOutput = false;

    /**
     * @var string
     */
    protected $strCacheId;

    /**
     * @var integer
     */
    protected $intLanguageId;

    /**
     * @var string
     */
    protected $strLanguageCode;

    /**
     * @var integer
     */
    protected $intLanguageDefinitionType;

    /**
     * @var integer
     */
    protected $intSegmentId;

    /**
     * @var string
     */
    protected $strSegmentCode;

    /**
     * @var string
     */
    protected $strUrlPrefix;

    /**
     * @var HtmlTranslate
     */
    protected $translate;

    /**
     * @var boolean
     */
    protected $blnUrlWithLanguage;

    /**
     * init index controller and get core obj
     */
    public function init()
    {
        $this->core = Zend_Registry::get('Core');

        $this->intLanguageId = $this->core->intLanguageId;
        $this->strLanguageCode = $this->core->strLanguageCode;

        $this->loadTheme();
    }

    /**
     * getUrl
     * @return string
     */
    public function getUrl($strUrl, $blnCutLanguage = true)
    {
        $this->blnUrlWithLanguage = true;

        // check for .rss ending
        $strUrl = $this->validateRss($strUrl);

        // cut off url prefix path
        $strUrl = $this->cutUrlPrefix($strUrl);

        // cut off language & segment prefix of url
        if (preg_match('/^\/[a-zA-Z]{1}\/^\/([a-zA-Z]{2}|[a-zA-Z]{2}\-[a-zA-Z]{2})\//', $strUrl)) {
            $strUrl = preg_replace('/^\/[a-zA-Z]{1}\/^\/([a-zA-Z]{2}|[a-zA-Z]{2}\-[a-zA-Z]{2})\//', '', $strUrl);
        } elseif (preg_match('/^\/([a-zA-Z]{2}|[a-zA-Z]{2}\-[a-zA-Z]{2})\//', $strUrl) && $blnCutLanguage) { // cut off language prefix of url
            $strUrl = preg_replace('/^\/([a-zA-Z]{2}|[a-zA-Z]{2}\-[a-zA-Z]{2})\//', '', $strUrl);
        } else {
            $strUrl = preg_replace('/^\//', '', $strUrl);
            $this->blnUrlWithLanguage = false;
        }

        return $strUrl;
    }

    /**
     * loadTheme
     * @return void
     */
    public function loadTheme()
    {
        // set domain
        $strDomain = $_SERVER['SERVER_NAME'];
        $objThemeData = $this->getModelFolders()->getThemeByDomain($this->core->getMainDomain($strDomain));

        if (count($objThemeData) > 0) {
            
            $this->validateUrlPrefix($objThemeData);

            //FIXME : for development
            if (strpos($strDomain, 'm.') === 0) {
                $this->objTheme->path = 'mobile';
            }

            $this->view->analyticsKey = $this->objTheme->analyticsKey;
            $this->view->analyticsDomain = $strDomain;
            $this->view->mapsKey = $this->objTheme->mapsKey;
            $this->view->rootLevelId = $this->objTheme->idRootLevels;
            $this->view->urlPrefix = $this->strUrlPrefix;
            $this->view->theme = $this->objTheme->path;

            if ($this->objTheme->localization != '') {
                Zend_Registry::get('Location')->setLocale($this->objTheme->localization);
            }
        } else {
            throw new Exception('Unable to load theme based on the URL "' . $strDomain . '"');
        }
    }

    /**
     * validateUrlPrefix
     * @param Zend_Db_Table_Rowset $objThemeData
     */
    protected function validateUrlPrefix($objThemeData)
    {
        if (count($objThemeData) > 1) {
            $strUrl = ltrim($_SERVER['REQUEST_URI'], '/');
            foreach ($objThemeData as $objTheme) {
                if (strpos($strUrl, $objTheme->urlPath) !== false && strpos($strUrl, $objTheme->urlPath) == 0) {
                    $this->objTheme = $objTheme;
                    break;
                }
            }

            // check if objTheme is null
            if (!isset($this->objTheme)) {
                foreach ($objThemeData as $objTheme) {
                    if ((bool) $objTheme->isMain === true) {
                        $this->objTheme = $objTheme;
                        break;
                    }
                }
            }
        } else {
            $this->objTheme = $objThemeData->current();
        }

        $this->strUrlPrefix = $this->objTheme->urlPath;
    }

    /**
     * validateRss
     * @param string $strUrl
     * @return string
     */
    protected function validateRss($strUrl)
    {
        if (strpos($strUrl, '.rss') !== false) {
            $strUrl = str_replace('.rss', '', $strUrl);
            $this->blnIsRss = true;
        }

        return $strUrl;
    }

    /**
     * cutUrlPrefix
     * @param string $strUrl
     * @return void
     */
    protected function cutUrlPrefix($strUrl)
    {
        if ($this->strUrlPrefix != '') {
            $strUrl = substr($strUrl, strlen($this->strUrlPrefix) + 1);
        }

        return ($strUrl == '') ? '/' : $strUrl;
    }

    /**
     * getTheme
     * @return void
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTheme()
    {
        return $this->objTheme;
    }

    /**
     * checkPortalSecuirty
     * @return void
     */
    protected function checkPortalSecuirty()
    {
        /**
         * check portal security
         */
        if (isset($this->objTheme) && (int) $this->objTheme->isSecure === 1) {
            // deactivate caching
            $this->blnCachingStart = false;

            $blnHasIdentity = true;
            $objAuth = Zend_Auth::getInstance();
            $objAuth->setStorage(new Zend_Auth_Storage_Session());
            if (!$objAuth->hasIdentity()) {
                $blnHasIdentity = false;
            }
            // for members
            $blnHasIdentityCustomer = true;
            $objAuth->setStorage(new Zend_Auth_Storage_Session('customer'));
            if (!$objAuth->hasIdentity()) {
                $blnHasIdentityCustomer = false;
            }

            if (!$objAuth->hasIdentity()) {
                $this->_redirect('/login?re=' . urlencode($_SERVER['REQUEST_URI']));
            } else {
                Security::get()->addRootLevelsToAcl($this->getModelFolders(), $this->core->sysConfig->modules->cms, Security::ZONE_WEBSITE);
                if (!Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objTheme->idRootLevels, Security::PRIVILEGE_VIEW, false, false, Security::ZONE_WEBSITE) && !Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objTheme->idRootLevels . '_' . $this->intLanguageId, Security::PRIVILEGE_VIEW, false, false, Security::ZONE_WEBSITE)) {
                    $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
                    $this->getResponse()->setHeader('Status', '403 Forbidden');
                    $this->getResponse()->setHttpResponseCode(403);
                    $this->strRenderScript = 'error-403.php';
                }
            }
        }
    }

    /**
     * validateLanguage
     * @return void
     */
    protected function validateLanguage()
    {
        $this->core->logger->debug('get language by: ');
        $this->intLanguageDefinitionType = $this->objTheme->languageDefinitionType;
        if ($this->core->blnIsDefaultLanguage === true || $this->core->blnIsSessionLanguage === true) {
            $this->core->logger->debug('theme');
            $this->core->intLanguageId = $this->objTheme->idLanguages;
            $this->core->strLanguageCode = strtolower($this->objTheme->languageCode);
            if ($this->intLanguageDefinitionType != $this->core->config->language_definition->none) {
                $strRequestString = '';
                $strMatchCode = '';
                $strTld = '';
                if ($this->intLanguageDefinitionType == $this->core->config->language_definition->folder) {
                    $this->core->logger->debug('folder');
                    $strRequestString = $_SERVER['REQUEST_URI'];
                    if ($this->strUrlPrefix != ''){
                        $strRequestString = $this->cutUrlPrefix($_SERVER['REQUEST_URI']);
                    }
                    $strMatchCode = '/^\/([a-zA-Z]{2}|[a-zA-Z]{2}\-[a-zA-Z]{2})\//';
                } else if ($this->intLanguageDefinitionType == $this->core->config->language_definition->subdomain || $this->core->config->language_definition->subandtld) {
                    $this->core->logger->debug('subdomain');
                    $strRequestString = $_SERVER['HTTP_HOST'];
                    $strMatchCode = '/^[a-zA-Z]{2}/';
                    $strTld = strrchr ( $_SERVER['SERVER_NAME'], "." );
                    $strTld = substr ( $strTld, 1 );
                }
                if ($strRequestString != '' && preg_match($strMatchCode, $strRequestString)) {
                    preg_match($strMatchCode, $strRequestString, $arrMatches);
                    $strCode = trim($arrMatches[0], '/');
                    if ($this->intLanguageDefinitionType == $this->core->config->language_definition->subandtld) {
                        if ($strTld != '') {
                            $strTmpCode = $strCode . '-' . $strTld;
                            foreach($this->core->config->languages->language->toArray() as $arrLanguage){
                                if(array_key_exists('code', $arrLanguage) && $arrLanguage['code'] == strtolower($strTmpCode)) {
                                    $this->core->strLanguageCode = $strTmpCode;
                                    $this->core->intLanguageId = $arrLanguage['id'];
                                    break;
                                }
                            }
                        }
                    } else {
                        foreach($this->core->config->languages->language->toArray() as $arrLanguage){
                            if(array_key_exists('code', $arrLanguage) && $arrLanguage['code'] == strtolower($strCode)) {
                                $this->core->strLanguageCode = $strCode;
                                $this->core->intLanguageId = $arrLanguage['id'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        $this->intLanguageId = $this->core->intLanguageId;
        $this->strLanguageCode = $this->core->strLanguageCode;
        $this->view->languageId = $this->intLanguageId;
        $this->view->languageCode = $this->strLanguageCode;
        
        $this->core->updateSessionLanguage();
    }

    /**
     * validateSegment
     * @return void
     */
    protected function validateSegment()
    {
        if ($this->objTheme->hasSegments == 1) {

            if (isset($_SERVER['REQUEST_URI']) && preg_match('/^\/[a-zA-Z]{1}\//', $_SERVER['REQUEST_URI'])) {
                preg_match('/^\/[a-zA-Z]{1}\//', $_SERVER['REQUEST_URI'], $arrMatches);
                $this->strSegmentCode = trim($arrMatches[0], '/');
            }

            $arrPortals = $this->core->config->portals->toArray();

            if (array_key_exists('id', $arrPortals['portal'])) {
                $arrPortals = array($arrPortals['portal']);
            } else {
                $arrPortals = $arrPortals['portal'];
            }

            foreach ($arrPortals as $arrPortal) {
                if ($arrPortal['id'] == $this->objTheme->idRootLevels) {
                    if (array_key_exists('id', $arrPortal['segment'])) {
                        $arrSegments = array($arrPortal['segment']);
                    } else {
                        $arrSegments = $arrPortal['segment'];
                    }

                    $arrDefaultSegment = null;
                    foreach ($arrSegments as $arrSegment) {
                        if (array_key_exists('code', $arrSegment) && $arrSegment['code'] == strtolower($this->strSegmentCode)) {
                            $this->intSegmentId = $arrSegment['id'];
                            break;
                        }

                        if (array_key_exists('default', $arrSegment) && $arrSegment['default'] === 'true') {
                            $arrDefaultSegment = $arrSegment;
                        }
                    }

                    if (empty($this->intSegmentId)) {
                        if (!empty($arrDefaultSegment)) {
                            $this->strSegmentCode = $arrDefaultSegment['code'];
                            $this->intSegmentId = $arrDefaultSegment['id'];
                        } else {
                            throw new Exception('No Segment found!');
                        }
                    }

                    break;
                }
            }

            $this->view->segmentId = $this->intSegmentId;
            $this->view->segmentCode = $this->strSegmentCode;
        }
    }

    /**
     * urlRetryRedirectAndError
     * @return void
     */
    protected function urlRetryRedirectAndError($strUrl)
    {
        if ($strUrl == '' && $this->getRequest()->getParam('re', 'true') == 'true') {
            // reset language

            $this->core->intLanguageId = $this->objTheme->idLanguages;
            $this->core->strLanguageCode = strtolower($this->objTheme->languageCode);

            // update session language
            $this->core->updateSessionLanguage();

            // redirct
            $this->_redirect($this->getPrefix() . '/?re=false');
        } else {
            $strTmpUrl = ((parse_url($strUrl, PHP_URL_PATH) === null) ? '' : parse_url($strUrl, PHP_URL_PATH));

            if (($strTmpUrl[strlen($strTmpUrl) - 1]) == '/') {
                $strTmpUrl = rtrim($strTmpUrl, '/');
            } else if (($strTmpUrl[strlen($strTmpUrl) - 1]) != '/') {
                $strTmpUrl = $strTmpUrl . '/';
            }

            $objUrl = $this->objModelUrls->loadByUrl($this->objTheme->idRootLevels, $strTmpUrl);

            if (isset($objUrl->url) && count($objUrl->url) > 0) {
                $this->getResponse()->setHeader('HTTP/1.1', '301 Moved Permanently');
                $this->getResponse()->setHeader('Status', '301 Moved Permanently');
                $this->getResponse()->setHttpResponseCode(301);
                $strLanguageFolder = '';
                if ($this->intLanguageDefinitionType == $this->core->config->language_definition->folder) {
                    $strLanguageFolder =  $this->strLanguageCode . '/';
                }
                $this->_redirect($this->getPrefix() . '/' . $strLanguageFolder . $strTmpUrl);
            } else {
                $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/');
                $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
                $this->getResponse()->setHeader('Status', '404 Not Found');
                $this->getResponse()->setHttpResponseCode(404);
                $this->renderScript('error-404.php');
            }
        }
    }

    /**
     * getPrefix
     * @return string
     */
    protected function getPrefix()
    {
        $strUrlPrefix = '';

        // check for url prefix
        if ($this->strUrlPrefix != '') {
            $strUrlPrefix .= '/' . $this->strUrlPrefix;
        }

        // check for segmentation
        if ($this->objTheme->hasSegments == 1) {
            $strUrlPrefix .= '/' . $this->strSegmentCode;
        }

        return $strUrlPrefix;
    }

    /**
     * getRedirectDomain
     * @return string
     */
    protected function getRedirectDomain($strDomainLanguageCode = '')
    {
        $strDomain = $_SERVER['HTTP_HOST'] . '/';
        
       
        if ($this->intLanguageDefinitionType == $this->core->config->language_definition->subdomain && $strDomainLanguageCode != '') {
            if(strpos($strDomain, 'www.') === 0) {
                $strDomain = str_replace('www.', '', $strDomain);
            }
            
            if ($this->core->config->enable_short_subdomains == 'false' && 2 === strlen(substr($strDomain, 0, strpos($strDomain, '.')))) {
                $strDomain = substr($strDomain, 3);
            }
            
            //if language allready in subdomain
            if(strpos($strDomain, ($strDomainLanguageCode.'.')) !== 0){
                $strDomain = $strDomainLanguageCode . '.' . $strDomain;
            }
        }
        return (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $strDomain;
    }
    
    /**
     * setTranslate
     * @return void
     */
    public function setTranslate()
    {
        // set up zoolu translate obj
        if (file_exists(GLOBAL_ROOT_PATH . 'application/website/default/language/website-' . $this->strLanguageCode . '.mo')) {
            $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH . 'application/website/default/language/website-' . $this->strLanguageCode . '.mo');
        } else {
            $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH . 'application/website/default/language/website-' . $this->core->sysConfig->languages->default->code . '.mo');
        }

        $this->view->translate = $this->translate;
    }

    /**
     * getTranslate
     * @return HtmlTranslate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTranslate()
    {
        return $this->translate;
    }

    /**
     * initPageCache
     * @return void
     */
    protected function initPageCache($strUrl)
    {
        $this->strCacheId = 'page_' . $this->objTheme->idRootLevels;

        // add url prefix to page cache key
        if ($this->strUrlPrefix != '') {
            $this->strCacheId .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $this->strUrlPrefix);
        }

        // add segment to page cache key
        if ($this->objTheme->hasSegments == 1) {
            $this->strCacheId .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $this->strSegmentCode);
        }

        $this->strCacheId .= '_' . strtolower(str_replace('-', '_', $this->strLanguageCode)) . '_' . strtolower(str_replace('-', '_', $this->objTheme->path)) . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $strUrl);

        $arrFrontendOptions = array(
            'lifetime'                => 604800, // cache lifetime (in seconds), if set to null, the cache is valid forever.
            'automatic_serialization' => true
        );

        $arrBackendOptions = array(
            'cache_dir' => GLOBAL_ROOT_PATH . $this->core->sysConfig->path->cache->pages // Directory where to put the cache files
        );

        // getting a Zend_Cache_Core object
        $this->objCache = Zend_Cache::factory('Output',
            'File',
            $arrFrontendOptions,
            $arrBackendOptions);
    }

    /**
     * initNavigation
     * @return Client_Navigation|Navigation
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function initNavigation()
    {
        if (file_exists(GLOBAL_ROOT_PATH . 'client/website/navigation.class.php')) {
            require_once(GLOBAL_ROOT_PATH . 'client/website/navigation.class.php');
            $objNavigation = new Client_Navigation();
        } else {
            $objNavigation = new Navigation();
        }
        $objNavigation->setRootLevelId($this->objTheme->idRootLevels);
        $objNavigation->setLanguageId($this->intLanguageId);

        // set navigation url prefix properties
        $objNavigation->setHasUrlPrefix((($this->strUrlPrefix != '') ? true : false));
        $objNavigation->setUrlPrefix($this->strUrlPrefix);
        $objNavigation->setLanguageDefinitionType($this->intLanguageDefinitionType);

        // set navigation segmentation properties
        $objNavigation->setHasSegments($this->objTheme->hasSegments);
        $objNavigation->setSegmentId($this->intSegmentId);
        $objNavigation->setSegmentCode($this->strSegmentCode);

        if (file_exists(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/NavigationHelper.php')) {
            require_once(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/NavigationHelper.php');
            $strNavigationHelper = ucfirst($this->objTheme->path) . '_NavigationHelper';
            $objNavigationHelper = new $strNavigationHelper();
        } else {
            require_once(dirname(__FILE__) . '/../helpers/NavigationHelper.php');
            $objNavigationHelper = new NavigationHelper();
        }

        $objNavigationHelper->setNavigation($objNavigation);
        $objNavigationHelper->setTranslate($this->translate);
        Zend_Registry::set('NavigationHelper', $objNavigationHelper);
        return $objNavigation;
    }

    /**
     * setLanguageId
     * @param number $intLanguageId
     * @version 1.0
     */
    public function setLanguage($intLanguageId)
    {
        $this->intLanguageId = $intLanguageId;

        $objLanguage = $this->getModelLanguages()->loadLanguageById($intLanguageId);
        if (count($objLanguage) > 0) {
            $this->strLanguageCode = strtolower($objLanguage->current()->languageCode);
        }
    }

    /**
     * getLanguageId
     * @return integer
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getLanguageId()
    {
        return $this->intLanguageId;
    }

    /**
     * getLanguageCode
     * @return string
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getLanguageCode()
    {
        return $this->strLanguageCode;
    }

    /**
     * getModelFolders
     * @return Model_Folders
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelFolders()
    {
        if (null === $this->objModelFolders) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Folders.php';
            $this->objModelFolders = new Model_Folders();
            $this->objModelFolders->setLanguageId($this->intLanguageId);
        }

        return $this->objModelFolders;
    }

    /**
     * getModelUrls
     * @param $blnForceNewInstance Forces a new instantiation of the model
     * @return Model_Urls
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelUrls($blnForceNewInstance = false)
    {
        if (null === $this->objModelUrls || $blnForceNewInstance) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Urls.php';
            $this->objModelUrls = new Model_Urls();
            $this->objModelUrls->setLanguageId($this->intLanguageId);
        }

        return $this->objModelUrls;
    }

    /**
     * getModelLanguages
     * @return Model_Languages
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelLanguages()
    {
        if (null === $this->objModelLanguages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Languages.php';
            $this->objModelLanguages = new Model_Languages();
        }

        return $this->objModelLanguages;
    }

}