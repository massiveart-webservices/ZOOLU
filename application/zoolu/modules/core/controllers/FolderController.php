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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * FolderController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-14: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Core_FolderController extends AuthControllerAction {

  /**
   * @var GenericForm
   */
  protected $objForm;
	
	/**
   * @var inter
   */
  protected $intItemLanguageId;
  
  /**
   * @var string
   */
  protected $strItemLanguageCode;

	/**
   * request object instance
   * @var Zend_Controller_Request_Abstract
   */
  protected $objRequest;

	/**
   * @var Model_Folders
   */
  protected $objModelFolders;

  /**
   * @var CommandChain
   */
  protected $objCommandChain;

  /**
   * init
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   * @return void
   */
  public function init(){
    parent::init();
    $this->objRequest = $this->getRequest();
    $this->initCommandChain();
  }

  /**
   * initCommandChain
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   * @return void
   */
  private function initCommandChain(){
    $this->core->logger->debug('core->controllers->FolderController->initCommandChain()');
    $this->objCommandChain = new CommandChain();

    if($this->objRequest->getParam('rootLevelTypeId') == $this->core->sysConfig->root_level_types->portals){
      $this->core->logger->debug('add page command');
      $this->objCommandChain->addCommand(new PageCommand());
    }

    if($this->objRequest->getParam('rootLevelTypeId') == $this->core->sysConfig->root_level_types->global){
      $this->core->logger->debug('add global command!');
      $this->objCommandChain->addCommand(new GlobalCommand((int) $this->objRequest->getParam('rootLevelGroupId'), $this->objRequest->getParam('rootLevelGroupKey')));
    }
  }

  /**
   * indexAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){ }

  /**
   * getaddformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getaddformAction(){
    $this->core->logger->debug('core->controllers->FolderController->getaddformAction()');

    try{
      $this->getForm($this->core->sysConfig->generic->actions->add);
      $this->addFolderSpecificFormElements();

      /**
       * set action
       */
      $this->objForm->setAction('/zoolu/core/folder/add');

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      /**
       * get form title
       */
      $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

      /**
       * output of metainformation to hidden div
       */
      $this->setViewMetaInfos();

      $this->view->form = $this->objForm;

      $this->renderScript('folder/form.phtml');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * addAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addAction(){
    $this->core->logger->debug('core->controllers->FolderController->addAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);
    $this->addFolderSpecificFormElements();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/core/folder/add');

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

      $arrFormData = $this->objRequest->getPost();
      $this->objForm->Setup()->setFieldValues($arrFormData);

      /**
	     * prepare form (add fields and region to the Zend_Form)
	     */
	    $this->objForm->prepareForm();

      if($this->objForm->isValid($arrFormData)){
        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/core/folder/edit');

        $intFolderId = $this->objForm->saveFormData();

        $this->objForm->Setup()->setElementId($intFolderId);
        $this->objForm->getElement('id')->setValue($intFolderId);
        $this->objForm->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);

        $this->view->assign('blnShowFormAlert', true);

        $arrArgs = array('ParentId'         => $intFolderId,
                         'LanguageId'       => $this->objRequest->getParam('languageId', $this->core->intZooluLanguageId),
                         'LanguageCode'     => $this->objRequest->getParam('languageCode', $this->core->strZooluLanguageCode),
                         'GenericSetup'     => $this->objForm->Setup());
        if($this->objCommandChain->runCommand('addFolderStartElement', $arrArgs)){
          $this->view->assign('selectNavigationItemNow', true);
          $this->view->assign('itemId', 'folder'.$intFolderId);
        }

      }else{
        $this->view->assign('blnShowFormAlert', false);
      }
    }else{
    	/**
	     * prepare form (add fields and region to the Zend_Form)
	     */
	    $this->objForm->prepareForm();
    }

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    /**
     * output of metainformation to hidden div
     */
    $this->setViewMetaInfos();

    $this->view->form = $this->objForm;
    $this->renderScript('folder/form.phtml');
  }

  /**
   * geteditformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function geteditformAction(){
    $this->core->logger->debug('core->controllers->FolderController->geteditformAction()');

    try{
    	$this->getForm($this->core->sysConfig->generic->actions->edit);

      /**
       * load generic data
       */
      $this->objForm->loadFormData();
      $this->addFolderSpecificFormElements();

      /**
       * set action
       */
      $this->objForm->setAction('/zoolu/core/folder/edit');

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      /**
       * get form title
       */
      $this->view->formtitle = $this->objForm->Setup()->getFormTitle();
			/**
       * Backlink?
       */
      $this->view->backLink = $this->getRequest()->getParam("backLink", false);
      $this->view->currLevel = $this->getRequest()->getParam('currLevel', null);
      $this->view->parentFolderId = $this->getRequest()->getParam('parentFolderId', null);

      /**
       * output of metainformation to hidden div
       */
      $this->setViewMetaInfos();

      $this->view->form = $this->objForm;

      $this->renderScript('folder/form.phtml');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * editAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function editAction(){
    $this->core->logger->debug('core->controllers->FolderController->editAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);
    $this->addFolderSpecificFormElements();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

      $arrFormData = $this->objRequest->getPost();
      $this->objForm->Setup()->setFieldValues($arrFormData);

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      /**
       * set action
       */
      $this->objForm->setAction('/zoolu/core/folder/edit');

      if($this->objForm->isValid($arrFormData)){
        $this->objForm->saveFormData();

        /**
         * update the folder start element
         */
        $arrArgs = array('LanguageId'       => $this->objRequest->getParam("languageId", $this->core->intZooluLanguageId),
                         'LanguageCode'     => $this->objRequest->getParam("languageCode", $this->core->strZooluLanguageCode),
                         'GenericSetup'     => $this->objForm->Setup());
        $this->objCommandChain->runCommand('editFolderStartElement', $arrArgs);
        
        $this->view->assign('blnShowFormAlert', true);
      }else{
      	$this->view->assign('blnShowFormAlert', false);
      }
    }else{

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();
    }

    /**
     * output of metainformation to hidden div
     */
    $this->setViewMetaInfos();

    $this->view->form = $this->objForm;

    $this->renderScript('folder/form.phtml');
  }

  /**
   * changelanguageAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changelanguageAction(){
    $this->core->logger->debug('core->controllers->FolderController->changelanguageAction()');

    try{
      
     if(intval($this->objRequest->getParam('id')) > 0){
        $this->_forward('geteditform');
      }else{
        $this->_forward('getaddform');
      }      
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * setViewMetaInfos
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function setViewMetaInfos(){
    if(is_object($this->objForm) && $this->objForm instanceof GenericForm){
      $this->view->isurlfolder = $this->objForm->Setup()->getUrlFolder();
      $this->view->showinnavigation = $this->objForm->Setup()->getShowInNavigation();
      $this->view->hideInSitemap = $this->objForm->Setup()->getHideInSitemap();
      $this->view->showInWebsite = $this->objForm->Setup()->getShowInWebsite();
      $this->view->showInTablet = $this->objForm->Setup()->getShowInTablet();
      $this->view->showInMobile = $this->objForm->Setup()->getShowInMobile();
      $this->view->folderId = $this->objForm->Setup()->getElementId();
      $this->view->version = $this->objForm->Setup()->getElementVersion();
      $this->view->publisher = $this->objForm->Setup()->getPublisherName();
      $this->view->changeUser = $this->objForm->Setup()->getChangeUserName();
      $this->view->publishDate = $this->objForm->Setup()->getPublishDate('d. M. Y');
      $this->view->changeDate = $this->objForm->Setup()->getChangeDate('d. M. Y, H:i');
      $this->view->statusOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT statusTitles.title AS DISPLAY FROM statusTitles WHERE statusTitles.idStatus = status.id AND statusTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().') AS DISPLAY FROM status', $this->objForm->Setup()->getStatusId());
      $this->view->creatorOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, CONCAT(fname, \' \', sname) AS DISPLAY FROM users', $this->objForm->Setup()->getCreatorId());

      $this->view->blnIsRootLevelChild = ($this->objForm->Setup()->getParentId() == 0) ? true : false;
      $this->view->navigationOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT navigationOptionTitles.title FROM navigationOptionTitles WHERE navigationOptionTitles.idNavigationOptions = navigationOptions.id AND navigationOptionTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().') AS DISPLAY FROM navigationOptions WHERE active = 1', $this->objForm->Setup()->getShowInNavigation());
       
      $arrSecurityCheck = array();
      if(!Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_VIEW, false, false)){
          $arrSecurityCheck = array('ResourceKey'           => Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId().'_%d', 
                                    'Privilege'             => Security::PRIVILEGE_VIEW, 
                                    'CheckForAllLanguages'  => false,
                                    'IfResourceNotExists'   => false);  
      }
      
      if($this->objRequest->getParam('zoolu_module') == 1) { //portals
        
        if(Security::get()->isAllowed('portals', Security::PRIVILEGE_VIEW, false, false)){
          $arrSecurityCheck = array();
        }
      
        $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT languages.id AS VALUE, languages.languageCode AS DISPLAY FROM languages INNER JOIN rootLevelLanguages ON rootLevelLanguages.idLanguages = languages.id AND rootLevelLanguages.idRootLevels = '.$this->objForm->Setup()->getRootLevelId().' ORDER BY languages.sortOrder, languages.languageCode', $this->objForm->Setup()->getLanguageId(), $arrSecurityCheck);
        $blnGeneralDeleteAuthorization = ($this->objForm->Setup()->getIsStartElement(false) == true) ? false : Security::get()->isAllowed('portals', Security::PRIVILEGE_DELETE, false, false);
        $blnGeneralUpdateAuthorization = Security::get()->isAllowed('portals', Security::PRIVILEGE_UPDATE, false, false);
        $blnGeneralSecurityAuthorization = Security::get()->isAllowed('portals', Security::PRIVILEGE_SECURITY, false, false);
      }else if($this->objRequest->getParam('zoolu_module') == 2) { //media
        $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageId(), $arrSecurityCheck);
        $blnGeneralDeleteAuthorization = Security::get()->isAllowed('media', Security::PRIVILEGE_DELETE, false, false);
        $blnGeneralUpdateAuthorization = Security::get()->isAllowed('media', Security::PRIVILEGE_UPDATE, false, false);
        $blnGeneralSecurityAuthorization = Security::get()->isAllowed('media', Security::PRIVILEGE_SECURITY, false, false);
      }else if($this->objRequest->getParam('zoolu_module') == 5) { //global
        $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageId(), $arrSecurityCheck);
        $blnGeneralDeleteAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_DELETE, false, false);
        $blnGeneralUpdateAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_UPDATE, false, false);
        $blnGeneralSecurityAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_SECURITY, false, false);
      }else{
        $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageId());
        $blnGeneralDeleteAuthorization = true;
        $blnGeneralUpdateAuthorization = true;
        $blnGeneralSecurityAuthorization = false;
      }
            
      $this->view->authorizedDelete = ($this->objForm->Setup()->getIsStartElement(false) == true || $this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->add) ? false : (($blnGeneralDeleteAuthorization == true) ? $blnGeneralDeleteAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId().'_'.$this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_DELETE, false, false));
      $this->view->authorizedUpdate = ($blnGeneralUpdateAuthorization == true) ? $blnGeneralUpdateAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId().'_'.$this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_UPDATE, false, false);
      $this->view->authorizedSecurityManager = ($blnGeneralSecurityAuthorization == true) ? $blnGeneralSecurityAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId().'_'.$this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_SECURITY, false, false);
    }
  }

  /**
   * deleteAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('core->controllers->FolderController->deleteAction()');

    $this->getModelFolders();

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
	    $this->objModelFolders->deleteFolderNode($this->objRequest->getParam("id"));

	    $this->view->blnShowFormAlert = true;
    }

    $this->renderScript('folder/form.phtml');
  }

  /**
   * securityAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function securityAction(){
    $this->core->logger->debug('core->controllers->FolderController->securityAction()');
    try{
      $intFolderId = $this->objRequest->getParam('folderId');
      $this->view->folderSecurity = $this->getModelFolders()->getFolderSecurity($intFolderId);
      $this->view->folderId = $intFolderId;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * securityupdateAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function securityupdateAction(){
    $this->core->logger->debug('core->controllers->FolderController->securityupdateAction()');
    try{
      $intFolderId = $this->objRequest->getParam('folderId');

      $arrZooluSecurity = $this->objRequest->getParam('ZooluSecurity', array());
      $this->getModelFolders()->updateFolderSecurity($intFolderId, $arrZooluSecurity, $this->core->sysConfig->zone->zoolu);

      $arrWebsiteSecurity = $this->objRequest->getParam('WebsiteSecurity', array());
      $this->getModelFolders()->updateFolderSecurity($intFolderId, $arrWebsiteSecurity, $this->core->sysConfig->zone->website);

      $this->_forward('security');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * foldertreeAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function foldertreeAction(){
    $this->core->logger->debug('core->controllers->FolderController->foldertreeAction()');

    $intPortalId = $this->objRequest->getParam('portalId');
    $intFolderId = $this->objRequest->getParam('folderId');
    $strActionKey = $this->objRequest->getParam('key');

    $this->loadFolderTreeForPortal($intPortalId, $intFolderId);
    $this->view->assign('key', $strActionKey);
    $this->view->assign('overlaytitle', $this->core->translate->_('Select_folder'));
  }

  /**
   * listAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function listAction(){
    $this->core->logger->debug('core->controllers->FolderController->listAction()');
    
    $strSearchValue = $this->getRequest()->getParam('search');
    $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : '');
    $strOrderSort = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : '');

    $intPortalId = $this->objRequest->getParam('portalId');
    $intFolderId = $this->objRequest->getParam('folderId');
    $intCurrLevel = $this->objRequest->getParam('currLevel');

    $this->getModelFolders();
    $objFolderSelect = $this->objModelFolders->loadFolderContentById($intFolderId, $strSearchValue, $strOrderColumn, $strOrderSort);
    
    $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objFolderSelect);
    $objFolderPaginator = new Zend_Paginator($objAdapter);
    $objFolderPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
    $objFolderPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
    $objFolderPaginator->setView($this->view);
    
    $this->view->assign('folderPaginator', $objFolderPaginator);
    $this->view->assign('intFolderId', $intFolderId);
    $this->view->assign('strOrderColumn', $strOrderColumn);
    $this->view->assign('strOrderSort', $strOrderSort);
    $this->view->assign('strSearchValue', $strSearchValue);
    $this->view->assign('currLevel', $intCurrLevel);
    //$this->view->assign('listTitle', $objFolderContent[0]->folderTitle);
  }

  /**
   * documenttreeAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function documentcheckboxtreeAction(){
    $this->core->logger->debug('core->controllers->FolderController->documentcheckboxtreeAction()');

    $this->getModelFolders();
    $objMediaRootLevels = $this->objModelFolders->loadAllRootLevels($this->core->sysConfig->modules->media, $this->core->sysConfig->root_level_types->documents);

    if(count($objMediaRootLevels) > 0){
      $objMediaRootLevel = $objMediaRootLevels->current();
      $this->intRootLevelId = $objMediaRootLevel->id;
      $objRootelements = $this->objModelFolders->loadRootLevelFolders($this->intRootLevelId);

      $this->view->assign('elements', $objRootelements);
      $this->view->assign('rootLevelId', $this->intRootLevelId);
      $this->view->assign('overlaytitle', $this->core->translate->_('Select_folder'));

      $this->view->assign('selectedRootLevelId', $this->objRequest->getParam('rootLevelId', -1));
      $this->view->assign('selectedFolderIds', $this->objRequest->getParam('folderIds', '[]'));
    }
  }

  /**
   * changeparentfolderAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changeparentfolderAction(){
    $this->core->logger->debug('core->controllers->FolderController->changeparentfolderAction()');

    $intFolderId = $this->objRequest->getParam('folderId');
    $intParentFolderId = $this->objRequest->getParam('parentFolderId');

    if($intFolderId > 0 && $intParentFolderId > 0){
      $this->getModelFolders();
      $this->objModelFolders->moveFolderToLastChildOf($intFolderId, $intParentFolderId);
    }

    $this->_helper->viewRenderer->setNoRender();
  }

  /**
   * changeparentrootfolderAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changeparentrootfolderAction(){
    $this->core->logger->debug('core->controllers->FolderController->changeparentrootfolderAction()');

    $intFolderId = $this->objRequest->getParam('folderId');
    $intRootFolderId = $this->objRequest->getParam('rootFolderId');

    if($intFolderId > 0 && $intRootFolderId > 0){
      $this->getModelFolders();
      $this->objModelFolders->moveFolderToLastChildOfRootFolder($intFolderId, $intRootFolderId);
    }

    $this->_helper->viewRenderer->setNoRender();
  }

  /**
   * loadFolderTreeForPortal
   * @param integer $intPortalId
   * @param integer $intFolderId
   * @param string $strJsAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function loadFolderTreeForPortal($intPortalId, $intFolderId){
    $this->core->logger->debug('core->controllers->FolderController->loadFolderTreeForPortal('.$intPortalId.','.$intFolderId.')');

    $this->getModelFolders();
    $objFolderTree = $this->objModelFolders->loadRootLevelFolders($intPortalId, $this->core->intZooluLanguageId);

    $this->view->assign('elements', $objFolderTree);
    $this->view->assign('portalId', $intPortalId);
    $this->view->assign('folderId', $intFolderId);
  }

  /**
   * createForm
   * @param integer $intActionType
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getForm($intActionType = null){
  	$this->core->logger->debug('core->controllers->FolderController->createForm('.$intActionType.')');

  	$this->view->module = ($this->objRequest->getParam('zoolu_module') != '') ? $this->objRequest->getParam('zoolu_module') : 0;
  	$this->view->core = $this->core;

  	$strFormId = $this->objRequest->getParam('formId');
    $intFormVersion = ($this->objRequest->getParam('formVersion') != '') ? $this->objRequest->getParam('formVersion') : null;
    $intElementId = ($this->objRequest->getParam('id') != '') ? $this->objRequest->getParam('id') : null;

    $objFormHandler = FormHandler::getInstance();
    $objFormHandler->setFormId($strFormId);
    $objFormHandler->setFormVersion($intFormVersion);
    $objFormHandler->setActionType($intActionType);
    $objFormHandler->setLanguageId($this->getItemLanguageId());
    $objFormHandler->setLanguageCode($this->getItemLanguageCode());
    $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
    $objFormHandler->setElementId($intElementId);

    $this->objForm = $objFormHandler->getGenericForm();

    /**
     * set page default & specific form values
     */
    $this->objForm->Setup()->setCreatorId((($this->objRequest->getParam('creator') != '') ? $this->objRequest->getParam('creator') : Zend_Auth::getInstance()->getIdentity()->id));
    $this->objForm->Setup()->setStatusId((($this->objRequest->getParam('idStatus') != '') ? $this->objRequest->getParam('idStatus') : $this->core->sysConfig->form->status->default));
    $this->objForm->Setup()->setRootLevelId((($this->objRequest->getParam('rootLevelId') != '') ? $this->objRequest->getParam('rootLevelId') : null));
    $this->objForm->Setup()->setRootLevelTypeId((($this->objRequest->getParam('rootLevelTypeId') != '') ? $this->objRequest->getParam('rootLevelTypeId') : null));
    $this->objForm->Setup()->setRootLevelGroupId((($this->objRequest->getParam('rootLevelGroupId') != '') ? $this->objRequest->getParam('rootLevelGroupId') : null));
    $this->objForm->Setup()->setParentId((($this->objRequest->getParam('parentFolderId') != '') ? $this->objRequest->getParam('parentFolderId') : null));
    $this->objForm->Setup()->setUrlFolder((($this->objRequest->getParam('isUrlFolder') != '') ? $this->objRequest->getParam('isUrlFolder') : 1));
    $this->objForm->Setup()->setShowInNavigation((($this->objRequest->getParam('showInNavigation') != '') ? $this->objRequest->getParam('showInNavigation') : 0));
    $this->objForm->Setup()->setHideInSitemap((($this->objRequest->getParam('hideInSitemap') != '') ? $this->objRequest->getParam('hideInSitemap') : 0));
    $this->objForm->Setup()->setShowInWebsite((($this->objRequest->getParam("showInWebsite") != '') ? $this->objRequest->getParam("showInWebsite") : 1));
    $this->objForm->Setup()->setShowInTablet((($this->objRequest->getParam("showInTablet") != '') ? $this->objRequest->getParam("showInTablet") : 1));
    $this->objForm->Setup()->setShowInMobile((($this->objRequest->getParam("showInMobile") != '') ? $this->objRequest->getParam("showInMobile") : 1));
    
    /**
     * add currlevel hidden field
     */
    $this->objForm->addElement('hidden', 'currLevel', array('value' => $this->objRequest->getParam('currLevel'), 'decorators' => array('Hidden'), 'ignore' => true));

    /**
     * add elementTye hidden field (folder, page, ...)
     */
    $this->objForm->addElement('hidden', 'elementType', array('value' => $this->objRequest->getParam('elementType'), 'decorators' => array('Hidden'), 'ignore' => true));

    /**
     * add zoolu_module hidden field
     */
    $this->objForm->addElement('hidden', 'zoolu_module', array('value' => $this->view->module, 'decorators' => array('Hidden'), 'ignore' => true));
  }

  /**
   * addFolderSpecificFormElements
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function addFolderSpecificFormElements(){
    if(is_object($this->objForm) && $this->objForm instanceof GenericForm){
      /**
       * add folder specific hidden fields
       */
      $this->objForm->addElement('hidden', 'creator', array('value' => $this->objForm->Setup()->getCreatorId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'idStatus', array('value' => $this->objForm->Setup()->getStatusId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objForm->Setup()->getRootLevelId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'rootLevelTypeId', array('value' => $this->objForm->Setup()->getRootLevelTypeId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'rootLevelGroupId', array('value' => $this->objForm->Setup()->getRootLevelGroupId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'rootLevelGroupKey', array('value' => $this->objRequest->getParam('rootLevelGroupKey', 'content'), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'parentFolderId', array('value' => $this->objForm->Setup()->getParentId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'isUrlFolder', array('value' => $this->objForm->Setup()->getUrlFolder(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'hideInSitemap', array('value' => $this->objForm->Setup()->getHideInSitemap(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'showInNavigation', array('value' => $this->objForm->Setup()->getShowInNavigation(), 'decorators' => array('Hidden'), 'ignore' => true));
      $this->objForm->addElement('hidden', 'showInWebsite', array('value' => $this->objForm->Setup()->getShowInWebsite(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'showInTablet', array('value' => $this->objForm->Setup()->getShowInTablet(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'showInMobile', array('value' => $this->objForm->Setup()->getShowInMobile(), 'decorators' => array('Hidden')));
    }
  }
  
  /**
   * getItemLanguageId
   * @param integer $intActionType
   * @return integer
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  protected function getItemLanguageId($intActionType = null){
    if($this->intItemLanguageId == null){
      if(!$this->objRequest->getParam("languageId")){
        $this->intItemLanguageId = $this->objRequest->getParam("rootLevelLanguageId") != '' ? $this->objRequest->getParam("rootLevelLanguageId") : $this->core->intZooluLanguageId;
        
        $intRootLevelId = $this->objRequest->getParam("rootLevelId");
        $PRIVILEGE = ($intActionType == $this->core->sysConfig->generic->actions->add) ? Security::PRIVILEGE_ADD : Security::PRIVILEGE_UPDATE;
        
        $arrLanguages = $this->core->config->languages->language->toArray();      
        foreach($arrLanguages as $arrLanguage){
          if(Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$intRootLevelId.'_'.$arrLanguage['id'], $PRIVILEGE, false, false)){
            $this->intItemLanguageId = $arrLanguage['id']; 
            break;
          }          
        }
                
      }else{
        $this->intItemLanguageId = $this->objRequest->getParam("languageId");
      }
    }
    
    return $this->intItemLanguageId;
  }
  
  /**
   * getItemLanguageCode
   * @return string
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0 
   */
  protected function getItemLanguageCode(){
    if($this->strItemLanguageCode == null){
      if(!$this->objRequest->getParam("languageCode")){
        $arrLanguages = $this->core->config->languages->language->toArray();      
        foreach($arrLanguages as $arrLanguage){     
          if($arrLanguage['id'] == $this->getItemLanguageId()){
            $this->strItemLanguageCode = $arrLanguage['code'];
            break;
          }        
        }
      }else{
        $this->strItemLanguageCode = $this->objRequest->getParam("languageCode");
      }
    }
    
    return $this->strItemLanguageCode;
  }

  /**
   * getModelFolders
   * @return Model_Folders
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelFolders(){
    if (null === $this->objModelFolders) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Folders.php';
      $this->objModelFolders = new Model_Folders();
      $this->objModelFolders->setLanguageId($this->getItemLanguageId());
    }

    return $this->objModelFolders;
  }
}

?>
