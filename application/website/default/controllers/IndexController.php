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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * IndexController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-15: Cornelius Hansjakob
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class IndexController extends WebControllerAction
{

    /**
     * @var Model_Pages
     */
    private $objModelPages;

    /**
     * @var Model_Users
     */
    protected $objModelUsers;

    /**
     * @var Page
     */
    private $objPage;

    private $blnSearch = false;

    private $blnPostDispatch = true;

    private $blnIsRss = false;

    /**
     * default render script
     * @var string
     */
    protected $strRenderScript = 'master.php';

    /**
     * @var string
     */
    private $strClientAction;

    /**
     * @var boolean
     */
    private $blnIsLandingPage = false;
    
    /**
     * init index controller and get core obj
     */
    public function init()
    {
        parent::init();

        /**
         * reset action
         */
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/logout') {
            $this->getRequest()->setActionName('logout');
        } elseif (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/login') {
            $this->getRequest()->setActionName('login');
        } // sitemap
        elseif (preg_match('/\/sitemap\.xml$/', $_SERVER['REQUEST_URI'])) {
            $this->getRequest()->setActionName('sitemap');
        }

        if ($this->core->sysConfig->helpers->client->init === 'enabled') ClientHelper::get('Init')->init($this);
    }

    /**
     * preDispatch
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function preDispatch()
    {
        // trigger client specific dispatch helper
        if ($this->core->sysConfig->helpers->client->dispatcher === 'enabled') ClientHelper::get('Dispatcher')->preDispatch($this);
        parent::preDispatch();
    }

    /**
     * postDispatch
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function postDispatch()
    {
        if ($this->blnPostDispatch == true) {

            if (function_exists('tidy_parse_string') && $this->blnCachingOutput == false && $this->getResponse()->getBody() != '') {
                /**
                 * Tidy is a binding for the Tidy HTML clean and repair utility which allows
                 * you to not only clean and otherwise manipulate HTML documents,
                 * but also traverse the document tree.
                 */
                $arrConfig = array(          
  					'tidy-mark'           => false,
                  	'indent'              => true,
                  	'indent-spaces'       => 4,
                  	'new-blocklevel-tags' => 'article,aside,details,figcaption,figure,footer,header,hgroup,nav,section',
                  	'new-inline-tags'     => 'video,audio,canvas',
                  	'doctype'             => '<!doctype html>',
                  	'sort-attributes'     => 'alpha',
                  	'vertical-space'      => false,
                  	'output-xhtml'        => true,
                  	'wrap'                => 200,
                  	'wrap-attributes'     => false,
                  	'break-before-br'     => false,
                );
                
                $objTidy = tidy_parse_string($this->getResponse()->getBody(), $arrConfig, $this->core->sysConfig->encoding->db);    
                $objTidy->cleanRepair();
                // tidy bugfix - doctype for html5
                $objTidy = str_replace('<!--%TIDY_DOCTYPE%-->', '<!doctype html>', $objTidy);
                
                $this->getResponse()->setBody($objTidy);
            }

            if (isset($this->objCache) && $this->objCache instanceof Zend_Cache_Frontend_Output) {
                if ($this->blnCachingStart === true) {
                    $response = $this->getResponse()->getBody();
                    $this->getResponse()->setBody(str_replace("<head>", "<head>
      <!-- This is a ZOOLU cached page (" . date('d.m.Y H:i:s') . ") -->", $response));
                    $this->getResponse()->outputBody();

                    $arrTags = array();

                    if ($this->objPage->getIsStartElement(false) == true)
                        $arrTags[] = 'Start' . ucfirst($this->objPage->getType());

                    $arrTags[] = ucfirst($this->objPage->getType()) . 'Type_' . $this->objPage->getTypeId();
                    $arrTags[] = ucfirst($this->objPage->getType()) . 'Id_' . $this->objPage->getPageId() . '_' . $this->objPage->getLanguageId();

                    $this->core->logger->debug(var_export($arrTags, true));
                    $this->objCache->end($arrTags, false, null, false);
                    $this->core->logger->debug('... end caching!');
                }
            }
            // trigger client specific dispatch helper
            if ($this->core->sysConfig->helpers->client->dispatcher === 'enabled') ClientHelper::get('Dispatcher')->postDispatch($this);
            
            parent::postDispatch();
        }
    }

    /**
     * indexAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {
        // load theme
        $this->loadTheme();
        
        try {
            // get cleaned url
            $strUrl = $this->getUrl($_SERVER['REQUEST_URI']);

            // check portal security
            $this->checkPortalSecuirty();

            // validate language
            $this->validateLanguage();


            // validate root level segment
            $this->validateSegment();        

            // check if rootlevel has portal gate
            if ($this->isPortalGate()) {
                $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/themes/' . $this->objTheme->path.'/');
                $this->renderScript('portalgate.php');
            } else {        

                //validate the url check if landingpage     
                $objUrl = $this->getValidatedUrlObject($strUrl);

                $this->checkforLanguageRedirects($strUrl);

                // set translate
                $this->setTranslate();

                // init page cache
                $this->initPageCache($strUrl);

                /*
                 * check if "q" param is in the url for the search
                 */
                if (strpos($strUrl, '?q=') !== false) {
                    $this->blnSearch = true;
                    $strUrl = '';
                }

                // check, if cached page exists
                if ($this->core->sysConfig->cache->page == 'false' ||
                    ($this->core->sysConfig->cache->page == 'true' && $this->objCache->test($this->strCacheId) == false) ||
                    ($this->core->sysConfig->cache->page == 'true' && isset($_SESSION['sesTestMode']))
                ) {

                    $this->getModelUrls(true);
                    $this->getModelPages();

                    $objNavigation = $this->initNavigation();

                    $this->core->logger->debug('loadUrls 1st Approach');

                    if (isset($objUrl->url) && count($objUrl->url) > 0) {
                        $objUrlData = $objUrl->url->current();

                        /*
                         * validate the entrypoint
                         */
                        if ($objUrlData->idUrlTypes == $this->core->sysConfig->url_types->global) {
                            $this->validateEntrypoint($objUrl);
                        }

                        $strRelationId = $objUrlData->relationId;
                        if ($objUrlData->linkId != NULL) {
                            $strRelationId = $objUrlData->linkId; 
                        }            
                        // check if url is main
                        if (!$objUrlData->isMain) {
                            $objMainUrl = $this->objModelUrls->loadUrl($strRelationId, $objUrlData->version, $objUrlData->idUrlTypes);
                            if (count($objMainUrl) > 0) {
                                $objMainUrl = $objMainUrl->current();
                                $this->getResponse()->setHeader('HTTP/1.1', '301 Moved Permanently');
                                $this->getResponse()->setHeader('Status', '301 Moved Permanently');
                                $this->getResponse()->setHttpResponseCode(301);
                                $uriLanguageFolder = '';
                                if ($this->intLanguageDefinitionType == $this->core->config->language_definition->folder) {
                                     $uriLanguageFolder = strtolower($objMainUrl->languageCode) . '/';
                                }
                                if (!$objUrlData->isLandingPage) {
                                    $baseUrl = '';
                                    if (isset($objUrl->baseUrl)) {
                                        $baseUrl = $objUrl->baseUrl->url;   
                                    }
                                    $this->_redirect($this->getPrefix() . $uriLanguageFolder . $baseUrl.$objMainUrl->url);
                                } else {
                                    $this->_redirect($this->getRedirectDomain(strtolower($objMainUrl->languageCode)) . $this->getPrefix() . $uriLanguageFolder . $objMainUrl->url);
                                }
                            }
                        }

                        if ($this->core->sysConfig->cache->page == 'true' && !isset($_SESSION['sesTestMode']) && $this->blnSearch == false && (!isset($_POST) || count($_POST) == 0)) {
                            if ($this->blnCachingStart !== false) {
                                $this->objCache->start($this->strCacheId);
                            }
                        } else {
                            $this->blnCachingStart = false;
                        }

                        if (file_exists(GLOBAL_ROOT_PATH . 'client/website/page.class.php')) {
                            require_once(GLOBAL_ROOT_PATH . 'client/website/page.class.php');
                            $this->objPage = new Client_Page();
                        } else {
                            $this->objPage = new Page();
                        }
                        $this->objPage->setRootLevelId($this->objTheme->idRootLevels);
                        $this->objPage->setRootLevelTitle(($this->core->blnIsDefaultLanguage === true ? $this->objTheme->defaultTitle : $this->objTheme->title));
                        $this->objPage->setRootLevelAlternativeTitle(((isset($this->core->config->languages->alternative->id)) ? $this->objTheme->alternativeTitle : ''));
                        $this->objPage->setRootLevelGroupId($this->objTheme->idRootLevelGroups);
                        $this->objPage->setPageId($objUrlData->relationId);
                        $this->objPage->setPageVersion($objUrlData->version);
                        $this->objPage->setLanguageId($objUrlData->idLanguages);

                        // set url prefix properties
                        $this->objPage->setHasUrlPrefix((($this->strUrlPrefix != '') ? true : false));
                        $this->objPage->setUrlPrefix($this->strUrlPrefix);
                        $this->objPage->setLanguageDefinitionType($this->intLanguageDefinitionType);

                        // set navigation segmentation properties
                        $this->objPage->setHasSegments($this->objTheme->hasSegments);
                        $this->objPage->setSegmentId($this->intSegmentId);
                        $this->objPage->setSegmentCode($this->strSegmentCode);

                        switch ($objUrlData->idUrlTypes) {
                            case $this->core->sysConfig->url_types->page:
                                $this->objPage->setType('page');
                                $this->objPage->setModelSubPath('cms/models/');
                                break;
                            case $this->core->sysConfig->url_types->global:
                                $this->objPage->setType('global');
                                $this->objPage->setModelSubPath('global/models/');
                                $this->objPage->setElementLinkId($objUrlData->idLink);
                                $this->objPage->setNavParentId($objUrlData->idLinkParent);
                                $this->objPage->setPageLinkId($objUrlData->linkId);
                                break;
                        }

                        /*
                         * preset navigation parent properties
                         * e.g. is a collection page
                         */
                        if ($objUrlData->idParent !== null) {
                            $this->objPage->setNavParentId($objUrlData->idParent);
                            $this->objPage->setNavParentTypeId($objUrlData->idParentTypes);
                        }

                        /*
                         * has base url object
                         * e.g. prduct tree
                         */
                        if (isset($objUrl->baseUrl)) {
                            $objNavigation->setBaseUrl($objUrl->baseUrl);
                            $this->objPage->setBaseUrl($objUrl->baseUrl);
                            $this->objPage->setNavParentId($objUrlData->idLinkParent);

                        }

                        $this->objPage->loadPage();

                        /*
                         * check status
                         */
                        if ($this->objPage->getStatus() != $this->core->sysConfig->status->live && (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false))) {
                            $this->_redirect($this->getPrefix() . '/');
                        }

                        if ($this->objPage->ParentPage() instanceof Page) {
                            $objNavigation->setPage($this->objPage->ParentPage());
                        } else {
                            $objNavigation->setPage($this->objPage);
                        }

                        // update default cache lifetime
                        if ($this->objPage->getTemplateCacheLifetime() > 0) {
                            $this->objCache->setLifetime($this->objPage->getTemplateCacheLifetime());
                        } else {
                            // deactivate caching
                            $this->blnCachingStart = false;
                        }

                        // update default render script
                        if ($this->objPage->getTemplateRenderScript() != '') {
                            $this->strRenderScript = $this->objPage->getTemplateRenderScript();
                        }

                        /*
                         * check page security
                         */
                        if ($objNavigation->secuirtyZoneCheck()) {
                            // deactivate caching
                            $this->blnCachingStart = false;

                            list($blnHasIdentity, $blnHasIdentityCustomer) = $this->isZooluOrCustomerIdentity();

                            if ($blnHasIdentity == false && $blnHasIdentityCustomer == false) {
                                $this->_redirect($this->getPrefix() . '/login?re=' . urlencode($_SERVER['REQUEST_URI']));
                            } else {
                                if (!$objNavigation->checkZonePrivileges()) {
                                    $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
                                    $this->getResponse()->setHeader('Status', '403 Forbidden');
                                    $this->getResponse()->setHttpResponseCode(403);
                                    $this->strRenderScript = 'error-403.php';
                                    $this->blnCachingStart = false;
                                }
                            }
                        }

                        if (file_exists(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/PageHelper.php')) {
                            require_once(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/PageHelper.php');
                            $strPageHelper = ucfirst($this->objTheme->path) . '_PageHelper';
                            $objPageHelper = new $strPageHelper();
                        } else {
                            require_once(dirname(__FILE__) . '/../helpers/PageHelper.php');
                            $objPageHelper = new PageHelper();
                        }
                        $objPageHelper->setTheme($this->objTheme->path);

                        $objPageHelper->setPage($this->objPage);
                        $objPageHelper->setTranslate($this->translate);
                        Zend_Registry::set('PageHelper', $objPageHelper);

                        // forward to SearchController
                        if ($this->blnSearch == true) {
                            $this->_forward('index', 'Search', null, array(
                                                                          'rootLevelId'  => $this->objPage->getRootLevelId(),
                                                                          'theme'        => $this->objTheme->path,
                                                                          'urlPrefix'    => $this->strUrlPrefix,
                                                                          'segmentation' => array(
                                                                              'id'           => $this->intSegmentId,
                                                                              'code'         => $this->strSegmentCode,
                                                                              'hasSegments'  => ($this->objTheme->hasSegments == 1 ? true : false)
                                                                          )
                                                                     ));
                        }
                        // forward to RssController
                        elseif ($this->blnIsRss == true) {
                            $this->_forward('index', 'Rss', null, array(
                                                                       'page'  => $this->objPage,
                                                                       'theme' => $this->objTheme
                                                                  ));
                        } else {
                            /*
                             * get page template filename
                             */
                            $this->view->template = $this->objPage->getTemplateFile();
                            $this->view->publisher = $this->objPage->getPublisherName();
                            $this->view->publishdate = $this->objPage->getPublishDate();

                            $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/');
                            $this->renderScript($this->strRenderScript);
                        }
                    } else {
                        // try url with/without slash redirect or error output
                        $this->urlRetryRedirectAndError($strUrl);
                    }
                } else {
                    if ($this->objCache->test($this->strCacheId)) {
                        $this->blnCachingStart = false;
                    }
                    $this->blnCachingOutput = true;
                    $this->getResponse()->setBody($this->objCache->load($this->strCacheId));
                    $this->_helper->viewRenderer->setNoRender();
                }
            }
        } catch (NotFoundException $e) {
            $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/');
            $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
            $this->getResponse()->setHeader('Status', '404 Not Found');
            $this->getResponse()->setHttpResponseCode(404);
            $this->renderScript('error-404.php');
            $this->blnCachingStart = false;
            $this->core->logger->warn($e->getMessage());
        }
    }

    protected function isZooluOrCustomerIdentity()
    {
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

        $blnHasIdentity = $blnHasIdentity || $blnHasIdentityCustomer;
        return array($blnHasIdentity, $blnHasIdentityCustomer);
    }

    /**
     * sitemapAction
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function sitemapAction()
    {
        $this->loadTheme();
        $this->validateLanguage();

        $strMainUrl = $this->getModelFolders()->getRootLevelMainUrl($this->objTheme->idRootLevels, $this->core->sysConfig->environments->production);
        $strMainUrl = str_replace('http://', '', $strMainUrl);
        if ($strMainUrl != '') {
            $strMainUrl = str_replace('http://', '', $strMainUrl);
            if ($this->intLanguageDefinitionType == $this->core->config->language_definition->subdomain) {
                if (strtolower($this->objTheme->languageCode) == $this->strLanguageCode && $this->objTheme->hostPrefix != '') {
                    $strMainUrl = $this->objTheme->hostPrefix . '.' . $strMainUrl;
                } else {
                    $strMainUrl = $this->strLanguageCode . '.' . $strMainUrl;
                }
            } else {
                  $arrUrlParts = explode('.', $strMainUrl);
                  if(count($arrUrlParts) == 2){
                    $strMainUrl = 'www.' . $strMainUrl;
                  }   
            }
            
            if (file_exists(GLOBAL_ROOT_PATH . 'public/sitemaps/' . $strMainUrl . '/sitemap.xml')) {
                $this->_helper->viewRenderer->setNoRender();
                $this->blnPostDispatch = false;
                $this->getResponse()->setHeader('Content-Type', 'text/xml')
                     ->setBody(file_get_contents(GLOBAL_ROOT_PATH . 'public/sitemaps/' . $strMainUrl . '/sitemap.xml'));
            } else {
                $this->_redirect($this->getPrefix() . '/');
            }
        } else {
            $this->_redirect($this->getPrefix() . '/');
        }
    }

    /**
     * loginAction
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function loginAction()
    {
        $this->loadTheme();
        $this->setTranslate();

        $objAuth = Zend_Auth::getInstance();
        if ($objAuth->hasIdentity()) {
            $this->_redirect($this->getRequest()->getParam('re', '/'));
        } else {

            $this->view->strErrMessage = '';
            $this->view->strErrUsername = '';
            $this->view->strErrPassword = '';

            if ($this->_request->isPost()) {

                /**
                 * data from the user
                 * strip all HTML and PHP tags from the data
                 */
                $objFilter = new Zend_Filter_StripTags();
                $username = $objFilter->filter($this->_request->getPost('username'));
                $password = md5($objFilter->filter($this->_request->getPost('password')));

                if (empty($username)) {
                    $this->view->strErrUsername = $this->core->translate->_('Please_enter_username');
                } else {
                    /**
                     * setup Zend_Auth for authentication
                     */
                    if (ClientHelper::get('Authentication')->isActive() == true) {
                        $objAuthAdapter = ClientHelper::get('Authentication')->getAdapter();
                    } else {
                        $objAuthAdapter = new Zend_Auth_Adapter_DbTable($this->core->dbh);
                        $objAuthAdapter->setTableName('users');
                        $objAuthAdapter->setIdentityColumn('username');
                        $objAuthAdapter->setCredentialColumn('password');
                    }

                    /**
                     * set the input credential values to authenticate against
                     */
                    $objAuthAdapter->setIdentity($username);
                    $objAuthAdapter->setCredential($password);

                    /**
                     * do the authentication
                     */
                    $result = $objAuth->authenticate($objAuthAdapter);

                    switch ($result->getCode()) {

                        case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                            /**
                             * do stuff for nonexistent identity
                             */
                            $this->view->strErrUsername = $this->core->translate->_('Username_not_found');
                            break;

                        case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                            /**
                             * do stuff for invalid credential
                             */
                            $this->view->strErrPassword = $this->core->translate->_('Wrong_password');
                            break;

                        case Zend_Auth_Result::SUCCESS:

                            if (ClientHelper::get('Authentication')->isActive() == true) {
                                $objUserData = ClientHelper::get('Authentication')->getUserData();
                                $objUserRoleProvider = ClientHelper::get('Authentication')->getUserRoleProvider();
                            } else {
                                /**
                                 * store database row to auth's storage system but not the password
                                 */
                                $objUserData = $objAuthAdapter->getResultRowObject(array('id', 'idLanguages', 'username', 'fname', 'sname'));
                                $objUserData->languageId = $objUserData->idLanguages;
                                unset($objUserData->idLanguages);

                                $objUserRoleProvider = new RoleProvider();
                                $arrUserGroups = $this->getModelUsers()->getUserGroups($objUserData->id);
                                if (count($arrUserGroups) > 0) {
                                    foreach ($arrUserGroups as $objUserGroup) {
                                        $objUserRoleProvider->addRole(new Zend_Acl_Role($objUserGroup->key), $objUserGroup->key);
                                    }
                                }
                            }

                            $objSecurity = new Security();
                            $objSecurity->setRoleProvider($objUserRoleProvider);
                            $objSecurity->buildAcl($this->getModelUsers());
                            Security::save($objSecurity);

                            $objUserData->languageCode = null;
                            $arrLanguages = $this->core->zooConfig->languages->language->toArray();
                            foreach ($arrLanguages as $arrLanguage) {
                                if ($arrLanguage['id'] == $objUserData->languageId) {
                                    $objUserData->languageCode = $arrLanguage['code'];
                                    break;
                                }
                            }

                            if ($objUserData->languageCode === null) {
                                $objUserData->languageId = $this->core->zooConfig->languages->default->id;
                                $objUserData->languageCode = $this->core->zooConfig->languages->default->code;
                            }

                            $objAuth->getStorage()->write($objUserData);
                            $this->_redirect($this->getRequest()->getParam('re', '/'));
                            break;

                        default:
                            /**
                             * do stuff for other failure
                             */
                            $this->view->strErrMessage = $this->core->translate->_('Login_failed');
                            break;
                    }
                }
            }
        }

        $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/');
        $this->renderScript('login.php');
    }

    /**
     * logoutAction
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function logoutAction()
    {
        $objAuth = Zend_Auth::getInstance();
        $objAuth->clearIdentity();
        $this->_redirect($this->getPrefix() . '/');
    }

    /**
     * clientSpecificAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     */
    public function clientSpecificAction()
    {
        if ($this->strClientAction != '') {
            $action = $this->strClientAction;
            ClientHelper::get('Actions')->$action($this);
        }
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * setClientAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     */
    public function setClientAction($strClientAction)
    {
        $this->strClientAction = $strClientAction;
    }
    
   /**
    * getValidatedUrlObject
    * @param string $strUrl
    */
private function getValidatedUrlObject($strUrl) {
        $objUrl = null;
        
        //Load URL now, because language is alredy needed if more then one language is defined
        if($this->blnUrlWithLanguage){
            //Load stadard url if there is a language
            $objUrl = $this->getModelUrls()->loadByUrl($this->objTheme->idRootLevels, (parse_url($strUrl, PHP_URL_PATH) === null) ? '' : parse_url($strUrl, PHP_URL_PATH));
        }else{
            //Load landingpage if there is no language in the url
            $objUrl = $this->getModelUrls()->loadByUrl($this->objTheme->idRootLevels, (parse_url($strUrl, PHP_URL_PATH) === null) ? '' : parse_url($strUrl, PHP_URL_PATH), null, true, false);
            if (!isset($objUrl->url) || count($objUrl->url) == 0) {
                //If there is no landingpage, try normal page with default language 
                $objUrl = $this->getModelUrls()->loadByUrl($this->objTheme->idRootLevels, (parse_url($strUrl, PHP_URL_PATH) === null) ? '' : parse_url($strUrl, PHP_URL_PATH));
            } else {
                $this->blnIsLandingPage = true;
                if(isset($objUrl->url->current()->external) && $objUrl->url->current()->external != ''){                     
                    if((bool) $objUrl->url->current()->isMain === true){
                        $ch = curl_init();
                        $timeout = 5;
                        curl_setopt($ch, CURLOPT_URL, $objUrl->url->current()->external);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                        $data = curl_exec($ch);
                        curl_close($ch);
                        // displa content of linked landingpage but do not redirect
                        echo $data;
                        exit();
                    }else{
                        $this->_redirect($objUrl->url->current()->external);  
                    }        
                }
            }
            if (isset($objUrl->url) && count($objUrl->url) > 0) {
                //Needed for landingpage: change language for redirect
                $this->setLanguage($objUrl->url->current()->idLanguages);
            }
        }
        return $objUrl;
    }
    
    /**
     * checkForLanguageRedirects
     * @param string $strUrl
     */
    public function checkForLanguageRedirects($strUrl) {
        $strUri = $_SERVER['REQUEST_URI'];
        $strDomain = $_SERVER['HTTP_HOST'];
        $strRedirectUrl = '';
        // Case: language should be defined in subdomain, but is defined in subfolder
        if ($this->intLanguageDefinitionType == $this->core->config->language_definition->subdomain) {
            //check if uri contains language code
            if(preg_match('/^\/([a-zA-Z]{2}|[a-zA-Z]{2}\-[a-zA-Z]{2})\//', $strUri, $arrMatches)){
                $strMatch = trim($arrMatches[0], '/');
                foreach($this->core->config->languages->language->toArray() as $arrLanguage){
                    //check if language exists in config 
                    if(array_key_exists('code', $arrLanguage) && $arrLanguage['code'] == strtolower($strMatch)){
                        $strRedirectUrl = '/'.preg_replace('/^\/[a-zA-Z\-]{2,5}\//', '', $strUri);
                        //check if language is not default language of rootLevel
                        if (strtolower($strMatch) != strtolower($this->objTheme->languageCode)) {
                            if(strpos($strDomain, 'www.') === 0) {
                                $strDomain = str_replace('www.', '', $strDomain);
                            }
                            //if language allready in subdomain
                            if(strpos($strDomain, ($strMatch.'.')) !== 0){
                                $strRedirectUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$strMatch.'.'.$strDomain.$strRedirectUrl;
                            }
                        } else {
                            //use host prefix for default language if is set. www e.g.
                            if ($this->objTheme->hostPrefix != '') {
                                $strRedirectUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$this->objTheme->hostPrefix.'.'.$strDomain.$strRedirectUrl;
                            } else {
                                $strRedirectUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$strMatch.'.'.$strDomain.$strRedirectUrl;
                            }
                        }
                    }                        
                }
            }                
        }
        
        if ($this->intLanguageDefinitionType == $this->core->config->language_definition->folder && !$this->blnIsLandingPage) {
            $strRedirectUri = $strUri;
            $strRedirectDomain = $strDomain;
            $strRedirectLanguage = $this->strLanguageCode;
            //check if language comatined in subdomain
            if ($this->core->config->enable_short_subdomains == 'false' && 2 === strlen(substr($strDomain, 0, strpos($strDomain, '.')))) {
                $strMatchCode = '/^[a-zA-Z\-]{2,5}/';
                preg_match($strMatchCode, $strDomain, $arrMatches);
                $strRedirectLanguage = trim($arrMatches[0], '/');
                $strRedirectDomain = $this->core->getMainDomain($strDomain);
            }
            //check if language not contained in uri
            if (!preg_match('/^\/[a-zA-Z\-]{2,5}\//', $strUri, $arrMatches)) {
                //add langauge if not in uri
                $strRedirectUri = $this->getPrefix() . '/' . $strRedirectLanguage . '/' . $strUrl;         
            }
            if ($strRedirectUri != $strUri || $strRedirectDomain != $strDomain || $strRedirectLanguage != $this->strLanguageCode) {
                $strRedirectUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $this->core->getMainDomain($strDomain) . $strRedirectUri;
            }
        }
        
        if ($this->intLanguageDefinitionType == $this->core->config->language_definition->none) {
            //check if language contained in uri, then remove it
            if (preg_match('/^\/([a-zA-Z]{2}|[a-zA-Z]{2}\-[a-zA-Z]{2})\//', $strUri, $arrMatches)) {
                 $strMatch = $arrMatches[0];
                 $strRedirectUri = str_replace($strMatch, '', $strUri);
                 $strRedirectUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $strDomain . '/' . $strRedirectUri;         
            } 
        }
        
        if ($strRedirectUrl != '') {
            $this->getResponse()->setHeader('HTTP/1.1', '301 Moved Permanently');
            $this->getResponse()->setHeader('Status', '301 Moved Permanently');
            $this->getResponse()->setHttpResponseCode(301);
            $this->_redirect($strRedirectUrl);    
        }
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
     * isPortalGate
     * @return boolean
     */
    private function isPortalGate() 
    {
      // cut off url prefix path
      $strUrl = $this->cutUrlPrefix($_SERVER['REQUEST_URI']);
      if (isset($this->objTheme->hasPortalGate) && (bool) $this->objTheme->hasPortalGate == true && parse_url($strUrl, PHP_URL_PATH) == '/') {
          // load portal gate of rootlevel
          return true;  
      } else if (parse_url($strUrl, PHP_URL_PATH) == '/' && $this->objTheme->languageDefinitionType == $this->core->config->language_definition->folder) {
          // redirect to language
          $this->getResponse()->setHeader('HTTP/1.1', '301 Moved Permanently');
          $this->getResponse()->setHeader('Status', '301 Moved Permanently');
          $this->getResponse()->setHttpResponseCode(301);
          $this->_redirect($this->getPrefix() . '/' . $this->strLanguageCode . '/');
      } else if (parse_url($strUrl, PHP_URL_PATH) == '/'.$this->strLanguageCode.'/' && $this->objTheme->languageDefinitionType == $this->core->config->language_definition->none) {
          // redirect url without language
          $this->getResponse()->setHeader('HTTP/1.1', '301 Moved Permanently');
          $this->getResponse()->setHeader('Status', '301 Moved Permanently');
          $this->getResponse()->setHttpResponseCode(301);
          $this->_redirect($this->getPrefix());
      } else {
          // rootlevel has no portal gate
          return false;
      }
    }

    /**
     * fontsizeAction
     * @author Michael Trawetzky <mtr@massiveart.com>
     * @version 1.0
     */
    public function fontsizeAction()
    {
        $request = $this->getRequest();
        $strFontSize = $request->getParam('fontsize');

        $objWebSession = new Zend_Session_Namespace('Website');
        $objWebSession->fontSize = $strFontSize;

        $this->_helper->viewRenderer->setNoRender();
    }
    
    /**
     * validateEntrypoint
     * @param stdClass $objUrl
     * @param stdClass $objBaseUrl
     * @return boolean $ret
     */
    public function validateEntrypoint($objUrl) {
        $valid = false;
        $objAuth = Zend_Auth::getInstance();
        $objAuth->setStorage(new Zend_Auth_Storage_Session());
        if ($objAuth->hasIdentity()) {
            $valid = true;
        } else {
            if (isset($objUrl->baseUrl)) {
                $objBaseUrl = $objUrl->baseUrl;
                $entryPoint = $this->getModelPages()->loadEntryPoint($objBaseUrl->relationId, $objBaseUrl->version, $objBaseUrl->genericFormId);
                if ($entryPoint) {
                    $parentFolders = $this->getModelFolders()->loadGlobalParentFolders($objUrl->url->current()->idLinkParent);
                    foreach ($parentFolders as $parentFolder) {
                        if ($entryPoint->entry_point == $parentFolder->id) {
                            $valid = true;
                        }
                    }
                }
            }
        }
        if (!$valid) {
            throw new NotFoundException('Invalid entry point for global page ' . $_SERVER['REQUEST_URI']);
        }
    }

    /**
     * getModelPages
     * @return Model_Pages
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelPages()
    {
        if (null === $this->objModelPages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/Pages.php';
            $this->objModelPages = new Model_Pages();
            $this->objModelPages->setLanguageId($this->intLanguageId);
        }

        return $this->objModelPages;
    }

    /**
     * getModelUsers
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getModelUsers()
    {
        if (null === $this->objModelUsers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/Users.php';
            $this->objModelUsers = new Model_Users();
        }

        return $this->objModelUsers;
    }
}

?>
