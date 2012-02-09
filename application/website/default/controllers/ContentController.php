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
 * ContentController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2010-04-15: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

// IF LOCATION BASED CONTENT IS NEEDED - www.ip2location.com
//require_once(GLOBAL_ROOT_PATH.'/library/IP2Location/ip2location.class.php');

class ContentController extends Zend_Controller_Action {

  /**
   * @var Core
   */
  protected $core; 
  
  /**
   * @var Core
   */
  protected $objAuth;
  
  /**
   * request object instacne
   * @var Zend_Controller_Request_Abstract
   */
  protected $request; 
    
  /**
   * @var Model_Categories
   */
  protected $objModelCategories;
  
  /**
   * @var Model_Members
   */
  protected $objModelMembers;
  
  /**
   * @var Model_Files
   */
  protected $objModelFiles;
  
  /**
   * @var array
   */
  private $arrDesinationCountryCodes = array();
  
  /**
   * @var string
   */
  private $strCountryCode;
  
  /**
   * @var object
   */
  private $objMember;
  
  /**
   * preDispatch
   * Called before action method.
   * 
   * @return void  
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function preDispatch(){
    $this->core = Zend_Registry::get('Core');    
    $this->request = $this->getRequest();
  }
  
  /**
   * indexAction
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function indexAction(){
    $this->core->logger->debug('website->controllers->ContentController->indexAction()');
    $this->_helper->viewRenderer->setNoRender();
  }
  
  /**
   * fileFilterAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function fileFilterAction(){
    $this->core->logger->debug('website->controllers->ContentController->fileFilterAction()');
    $this->_helper->viewRenderer->setNoRender();
    
    $strTmpCacheId = $this->getRequest()->getParam('tmpId');    
        
    if($strTmpCacheId != '' && $this->core->TmpCache()->test($strTmpCacheId)){
      $arrFileFilters = $this->core->TmpCache()->load($strTmpCacheId); 
      foreach($arrFileFilters as $objEntry){
        switch($objEntry->filterType){
          case 'destination':
            echo $this->destinationFilter($objEntry);
            break;
          case 'member-group':
            echo $this->memberGroupFilter($objEntry);
            break;
        }  
      }
      
      if($this->core->sysConfig->cache->page == 'false') $this->core->TmpCache()->remove($strTmpCacheId);
    }
  }
  
  /**
   * destinationFilter
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function destinationFilter($objPageEntry){
    $this->core->logger->debug('website->controllers->ContentController->destinationFilter()');
    
    if(!array_key_exists($objPageEntry->destinationId, $this->arrDesinationCountryCodes)){
      $this->arrDesinationCountryCodes[$objPageEntry->destinationId] = $this->getDesinationCountryCodes($objPageEntry->destinationId);
    }
    
    if(is_array($this->arrDesinationCountryCodes[$objPageEntry->destinationId]) && array_search($this->getCountryCode(), $this->arrDesinationCountryCodes[$objPageEntry->destinationId]) !== false){
      return $objPageEntry->output; 
    } 
  }
  
  /**
   * getCountryCode
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  private function getCountryCode() {
    if($this->strCountryCode === null) {
      $this->strCountryCode = ((isset($this->core->objCoreSession->countryshort)) ? $this->core->objCoreSession->countryshort : '');
        
      if($this->strCountryCode == ''){
        $this->strCountryCode = $this->getCountryShortByIP($this->_getParam('ip'));
      }
    }
    return $this->strCountryCode;
  }
  
  /**
   * memberGroupFilter
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  private function memberGroupFilter($objPageEntry){
    $this->core->logger->debug('website->controllers->ContentController->memberGroupFilter()');
    
    if($this->getMember() !== false) {
      if(count($this->objMember->groups) > 0){
        foreach($this->objMember->groups as $arrGroup){
          if(array_key_exists('id', $arrGroup)){
            if($arrGroup['id'] == $objPageEntry->groupId){
              return $objPageEntry->output;  
            }  
          }  
        }  
      }
    }
  }
  
  /**
   * getMember
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  private function getMember() {
    if($this->objMember === null) {
      $this->objAuth = Zend_Auth::getInstance();
      $this->objAuth->setStorage(new Zend_Auth_Storage_Session('Members'));
  
      if($this->objAuth->hasIdentity()){
        
        $this->objMember = $this->objAuth->getIdentity(); 
        if(!isset($this->objMember->groups)){
          $objMemberGroupData = $this->getModelMembers()->loadMemberGroupById($this->objMember->id);        
          $arrGroups = array();
          if(count($objMemberGroupData) > 0){
            foreach($objMemberGroupData as $objData){
              $arrGroups[] = $objData->toArray();
            }  
          }
          $this->objMember->groups = $arrGroups; 
          $this->objAuth->getStorage()->write($this->objMember);
        }        
      }else{
        $this->objMember = false; 
      } 
    }
    return $this->objMember;
  }
  
  /**
   * memberUserInfoAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function memberUserInfoAction(){
    $this->core->logger->debug('website->controllers->ContentController->memberUserInfoAction()'); 
    
    $this->objAuth = Zend_Auth::getInstance();
    $this->objAuth->setStorage(new Zend_Auth_Storage_Session('Members'));
    
    if($this->objAuth->hasIdentity()){
      $this->view->user = $this->objAuth->getIdentity();
      $this->view->languageCode = $this->core->strLanguageCode;
            
      $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->getRequest()->getParam('theme', 'default').'/scripts/');
      $this->renderScript('member-user-info.php');
    }else{
      $this->_helper->viewRenderer->setNoRender();
    }
  }
  
  /**
   * latestDocumentsAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function latestDocumentsAction(){
    $this->core->logger->debug('website->controllers->ContentController->latestDocumentsAction()');
      
    $this->objAuth = Zend_Auth::getInstance();
    $this->objAuth->setStorage(new Zend_Auth_Storage_Session('Members'));
    
    if($this->objAuth->hasIdentity()){
      $objUser = $this->objAuth->getIdentity();
      
      $blnFilter = (bool) $this->getRequest()->getParam('filter');
      $intLimit = (($this->getRequest()->getParam('limit') > 0) ? $this->getRequest()->getParam('limit') : 0); 
      
      $arrGroups = array();
      if($blnFilter && isset($objUser->groups)){
        if(is_array($objUser->groups)){
          foreach($objUser->groups as $arrGroup){
            $arrGroups[] = $arrGroup['id'];   
          }  
        }  
      }      
      
      $objDocuments = $this->getModelFiles()->loadLatestFiles(3, $arrGroups, array('limit' => $intLimit)); // 3 = rootlevelId for "Documents"
      $this->view->objDocuments = $objDocuments;
      
      /**
       * set up zoolu translate obj
       */      
      if(file_exists(GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->core->strLanguageCode.'.mo')){
         $objTranslate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->core->strLanguageCode.'.mo');  
      }else{
         $objTranslate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->core->sysConfig->languages->default->code.'.mo');
      }      
      $this->view->translate = $objTranslate;
      
      $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->getRequest()->getParam('theme', 'default').'/scripts/');
      $this->renderScript('latest-documents.php');
    }else{
      $this->_helper->viewRenderer->setNoRender();
    }
  }
  
  /**
   * getDesinationCountryCodes
   * @param integer $intDestinationId
   * @return array
   */
  private function getDesinationCountryCodes($intDestinationId){
    $arrCountryCodes = array();
    $objCategories = $this->getModelCategories()->loadCategoryTree($intDestinationId, true);
    if(count($objCategories) > 0){
      foreach($objCategories as $objCategory){
        $arrCountryCodes[] = $objCategory->code;
      }
    }
    return $arrCountryCodes;
  }
  
  /**
   * getCountryShortByIP
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getCountryShortByIP($strIPAddress = ''){
    if(file_exists(GLOBAL_ROOT_PATH.'library/IP2Location/IP-COUNTRY-REGION-CITY-LATITUDE-LONGITUDE.BIN')){      
      
      $ip = new ip2location();
      $ip->open(GLOBAL_ROOT_PATH.'library/IP2Location/IP-COUNTRY-REGION-CITY-LATITUDE-LONGITUDE.BIN');
      
      $ipAddress = ((strpos($_SERVER['HTTP_HOST'], 'area51') === false) ? $_SERVER['REMOTE_ADDR'] : '84.72.245.26');
      if($strIPAddress != ''){
        $ipAddress = $strIPAddress;
      }
      $countryShort = $ip->getCountryShort($ipAddress);
      
      return $countryShort;
    }
  }
  
  /**
   * reloaded content 
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function reloadedAction(){    
    $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->getRequest()->getParam('theme', 'default').'/reloaded/');
    $this->renderScript($this->getRequest()->getParam('key', 'empty').'.php');
  }
  
  /**
   * reloaded content 
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function expireCacheAction(){
    $this->core->logger->debug('website->controllers->ContentController->expireCacheAction()');
    $this->_helper->viewRenderer->setNoRender();
    
    $objAuth = Zend_Auth::getInstance();
    
    if($objAuth->hasIdentity()){
      $objWebsite = new Website();
      $objWebsite->expireCache();
    }
  }
  
  /**
   * getModelCategories
   * @return Model_Categories
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getModelCategories(){
    if (null === $this->objModelCategories) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Categories.php';
      $this->objModelCategories = new Model_Categories();
      $this->objModelCategories->setLanguageId($this->core->sysConfig->languages->default->id);
    }

    return $this->objModelCategories;
  }
  
  /**
   * getModelMembers
   * @return Model_Members
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getModelMembers(){
    if (null === $this->objModelMembers) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Members.php';
      $this->objModelMembers = new Model_Members();
    }

    return $this->objModelMembers;
  }
  
  /**
   * getModelFiles
   * @return Model_Files
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getModelFiles(){
    if (null === $this->objModelFiles) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Files.php';
      $this->objModelFiles = new Model_Files();
      $this->objModelFiles->setLanguageId($this->core->intLanguageId);
    }

    return $this->objModelFiles;
  }
}
?>