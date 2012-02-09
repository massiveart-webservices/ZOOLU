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
 * @package    application.zoolu.modules.cms.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * PageController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-06: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Cms_PageController extends AuthControllerAction {

  /**
   * @var GenericForm
   */
  protected $objForm;
  
  /**
   * @var integer
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
   * @var Model_Pages
   */
  protected $objModelPages;

  /**
   * @var Model_Folders
   */
  protected $objModelFolders;

  /**
   * @var Model_Files
   */
  protected $objModelFiles;

  /**
   * @var Model_Contacts
   */
  protected $objModelContacts;

  /**
   * @var Model_Templates
   */
  protected $objModelTemplates;

  /**
   * @var Model_GenericForms
   */
  protected $objModelGenericForm;
  
  /**
   * @var Model_Users
   */
  protected $objModelUsers;

  /**
   * init
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   * @return void
   */
  public function init(){
    parent::init();
    
    if(!Security::get()->isAllowed('portals', Security::PRIVILEGE_VIEW)){
      $blnCrossSidePrivilege = ($this->getRequest()->isXmlHttpRequest() && Security::get()->isAllowed('global', Security::PRIVILEGE_VIEW) && strpos($this->getRequest()->getActionName(), 'get') === 0) ? true : false;
      if(!$blnCrossSidePrivilege){
        $this->_redirect('/zoolu');  
      }
    }
    $this->objRequest = $this->getRequest();
  }

  /**
   * The default action
   */
  public function indexAction(){ }

  /**
   * getaddformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getaddformAction(){
    $this->core->logger->debug('cms->controllers->PageController->getaddformAction()');

    try{
	    $this->getForm($this->core->sysConfig->generic->actions->add);
	    $this->addPageSpecificFormElements();

	    /**
	     * set action
	     */
	    $this->objForm->setAction('/zoolu/cms/page/add');

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
        
        /**
         * Set if display types are shown
         */
        $this->view->showDisplayTypes = $this->core->sysConfig->display_type->enabled;

	    $this->view->form = $this->objForm;

	    $this->renderScript('page/form.phtml');
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
    $this->core->logger->debug('cms->controllers->PageController->addAction()');

    try{
	    $this->getForm($this->core->sysConfig->generic->actions->add);
	    $this->addPageSpecificFormElements();

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
	        $this->objForm->setAction('/zoolu/cms/page/edit');

	        $intPageId = $this->objForm->saveFormData();
	        $this->objForm->Setup()->setElementId($intPageId);
	        $this->objForm->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);
	        $this->objForm->getElement('id')->setValue($intPageId);

	        $this->view->assign('blnShowFormAlert', true);
	      }else{
	        /**
	         * set action
	         */
	        $this->objForm->setAction('/zoolu/cms/page/add');
	        $this->view->assign('blnShowFormAlert', false);
	      }
	    }else{

	    	/**
	       * prepare form (add fields and region to the Zend_Form)
	       */
	      $this->objForm->prepareForm();
	    }

	    /**
       * update special field values
       */
      $this->objForm->updateSpecificFieldValues();

	    /**
	     * get form title
	     */
	    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

	    /**
       * output of metainformation to hidden div
       */
      $this->setViewMetaInfos();
      
      /**
       * Set if display types are shown
       */
      $this->view->showDisplayTypes = $this->core->sysConfig->display_type->enabled;

      $this->view->form = $this->objForm;

	    $this->renderScript('page/form.phtml');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * geteditformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function geteditformAction(){
    $this->core->logger->debug('cms->controllers->PageController->geteditformAction()');

    try{
	    $this->getForm($this->core->sysConfig->generic->actions->edit);

	    /**
	     * load generic data
	     */
	    $this->objForm->loadFormData();
      $this->addPageSpecificFormElements();

	    /**
	     * set action
	     */
	    $this->objForm->setAction('/zoolu/cms/page/edit');

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
	    
	    /**
         * Set if display types are shown
         */
        $this->view->showDisplayTypes = $this->core->sysConfig->display_type->enabled;

	    $this->view->form = $this->objForm;

	    $this->renderScript('page/form.phtml');
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
    $this->core->logger->debug('cms->controllers->PageController->editAction()');

    try{
	    $this->getForm($this->core->sysConfig->generic->actions->edit);
	    $this->addPageSpecificFormElements();

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

	      if($this->objForm->isValid($arrFormData)){
	      	$this->objForm->saveFormData();
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
       * update special field values
       */
      $this->objForm->updateSpecificFieldValues();

	    /**
       * set action
       */
      $this->objForm->setAction('/zoolu/cms/page/edit');

      /**
       * output of metainformation to hidden div
       */
      $this->setViewMetaInfos();

      $this->view->form = $this->objForm;
      
      /**
       * Set if display types are shown
       */
      $this->view->showDisplayTypes = $this->core->sysConfig->display_type->enabled;

	    $this->renderScript('page/form.phtml');
	  }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * getpropertiescountAction
   * 
   * For checking if there is already an Entry in a specified language
   * 
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getpropertiescountAction(){
    $this->_helper->viewRenderer->setNoRender();
 
    $intElementId = $this->getRequest()->getParam('elementId');
    $intLanguageId = $this->getRequest()->getParam('languageId');
    
    $this->getResponse()->setHeader('Content-Type', 'text/html')->setBody(count($this->getModelPages()->loadProperties($intElementId, $intLanguageId)));
  }

  /**
   * setViewMetaInfos
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function setViewMetaInfos(){
    if(is_object($this->objForm) && $this->objForm instanceof GenericForm){
      $this->view->version = $this->objForm->Setup()->getElementVersion();
      $this->view->publisher = $this->objForm->Setup()->getPublisherName();
      $this->view->showinnavigation = $this->objForm->Setup()->getShowInNavigation();
      $this->view->changeUser = $this->objForm->Setup()->getChangeUserName();
      $this->view->publishDate = $this->objForm->Setup()->getPublishDate('d. M. Y, H:i');
      $this->view->changeDate = $this->objForm->Setup()->getChangeDate('d. M. Y, H:i');
      $this->view->statusOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT statusTitles.title AS DISPLAY FROM statusTitles WHERE statusTitles.idStatus = status.id AND statusTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().') AS DISPLAY FROM status', $this->objForm->Setup()->getStatusId());
      $this->view->creatorOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, CONCAT(fname, \' \', sname) AS DISPLAY FROM users', $this->objForm->Setup()->getCreatorId());

      if($this->objForm->Setup()->getIsStartElement(false) == true){
        $this->view->typeOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT pageTypeTitles.title AS DISPLAY FROM pageTypeTitles WHERE pageTypeTitles.idPageTypes = pageTypes.id AND pageTypeTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().') AS DISPLAY FROM pageTypes WHERE startpage = 1 AND active = 1 ORDER BY DISPLAY', $this->objForm->Setup()->getElementTypeId());
      }else{
        $this->view->typeOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT pageTypeTitles.title AS DISPLAY FROM pageTypeTitles WHERE pageTypeTitles.idPageTypes = pageTypes.id AND pageTypeTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().') AS DISPLAY FROM pageTypes WHERE page = 1 AND active = 1 ORDER BY DISPLAY', $this->objForm->Setup()->getElementTypeId());
      }
      
      $this->view->blnIsRootLevelChild = ($this->objForm->Setup()->getParentTypeId() == $this->core->sysConfig->parent_types->rootlevel) ? true : false;
      $this->view->navigationOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT navigationOptionTitles.title FROM navigationOptionTitles WHERE navigationOptionTitles.idNavigationOptions = navigationOptions.id AND navigationOptionTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().') AS DISPLAY FROM navigationOptions WHERE active = 1', $this->objForm->Setup()->getShowInNavigation());
      
      $this->view->destinationId = $this->objForm->Setup()->getDestinationId();
      $this->view->destinationOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT categories.id AS VALUE, categoryTitles.title  AS DISPLAY FROM categories INNER JOIN categoryTitles ON categoryTitles.idCategories = categories.id AND categoryTitles.idLanguages = '.$this->objForm->Setup()->getFormLanguageId().' WHERE categories.idParentCategory = 466 ORDER BY categoryTitles.title', $this->objForm->Setup()->getDestinationId());
      
      $this->view->hideInSitemap = $this->objForm->Setup()->getHideInSitemap();
      $this->view->showInWebsite = $this->objForm->Setup()->getShowInWebsite();
      $this->view->showInTablet = $this->objForm->Setup()->getShowInTablet();
      $this->view->showInMobile = $this->objForm->Setup()->getShowInMobile();
      
      $this->view->arrPublishDate = DateTimeHelper::getDateTimeArray($this->objForm->Setup()->getPublishDate());
      $this->view->monthOptions = DateTimeHelper::getOptionsMonth(false, $this->objForm->Setup()->getPublishDate('n'));

      $this->view->blnIsStartPage = $this->objForm->Setup()->getIsStartElement(false);

      if($this->objForm->Setup()->getField('url')){
        $strBaseUrl = $this->getModelFolders()->getRootLevelMainUrl($this->objForm->Setup()->getRootLevelId());  
        if(substr_count($strBaseUrl, '.') <= 1){
          $strBaseUrl = str_replace('http://', 'http://www.', $strBaseUrl); 
        }
        $this->view->pageurl = $strBaseUrl.$this->objForm->Setup()->getField('url')->getValue();
      }
     
      $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT languages.id AS VALUE, languages.languageCode AS DISPLAY FROM languages INNER JOIN rootLevelLanguages ON rootLevelLanguages.idLanguages = languages.id AND rootLevelLanguages.idRootLevels = '.$this->objForm->Setup()->getRootLevelId().' ORDER BY languages.sortOrder, languages.languageCode', $this->objForm->Setup()->getLanguageId());
            
      $this->view->authorizedDelete = ($this->objForm->Setup()->getIsStartElement(false) == true || $this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->add) ? false : Security::get()->isAllowed('portals', Security::PRIVILEGE_DELETE, false, false);
      $this->view->authorizedUpdate = Security::get()->isAllowed('portals', Security::PRIVILEGE_UPDATE, false, false);      
    }
  }

  /**
   * getfilesAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getfilesAction(){
    $this->core->logger->debug('cms->controllers->PageController->getfilesAction()');

    try{
	    $strFileIds = $this->objRequest->getParam('fileIds');
	    $strFieldName = $this->objRequest->getParam('fileFieldId');
	    $strViewType = $this->objRequest->getParam('viewtype');

	    /**
	     * get files
	     */
	    $this->getModelFiles();
	    $this->objModelFiles->setAlternativLanguageId(Zend_Auth::getInstance()->getIdentity()->languageId);
	    $objFiles = $this->objModelFiles->loadFilesById($strFileIds);

	    $this->view->assign('objFiles', $objFiles);
	    $this->view->assign('fieldname', $strFieldName);
	    $this->view->assign('viewtype', $strViewType);
	  }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * getfilteredfilesAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getfilteredfilesAction(){
    $this->core->logger->debug('cms->controllers->PageController->getfilteredfilesAction()');

    try{
	    
	    $arrTagIds = explode(',', $this->objRequest->getParam('tagIds'));
      $arrFolderIds = explode('][', trim($this->objRequest->getParam('folderIds'), '[]'));
	    $intRootLevelId = (int) $this->objRequest->getParam('rootLevelId', -1);
	    $arrCurrFileIds = explode('][', trim($this->objRequest->getParam('fileIds'), '[]'));

      $this->view->assign('fieldname', $this->objRequest->getParam('fileFieldId'));
	    $this->view->assign('viewtype', $this->objRequest->getParam('viewtype'));
	    $this->view->assign('isOverlay', (bool) $this->objRequest->getParam('isOverlay', false));
	    $this->view->assign('arrCurrFileIds', $arrCurrFileIds);

      if($intRootLevelId > 0 || $arrFolderIds[0] > 0){
        /**
         * get files
         */
        $this->getModelFiles();
        $this->objModelFiles->setAlternativLanguageId(Zend_Auth::getInstance()->getIdentity()->languageId);
        $objFiles = $this->objModelFiles->loadFilesByFilter($intRootLevelId, $arrTagIds, $arrFolderIds);
        $this->view->assign('objFiles', $objFiles);
      }else{
        $this->_helper->viewRenderer->setNoRender();
      }
	  }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * getfilteredpagesAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getfilteredpagesAction(){
    $this->core->logger->debug('cms->controllers->PageController->getfilteredfilesAction()');

    try{
      
      $arrTagIds = explode(',', $this->objRequest->getParam('tagIds'));
      $arrFolderIds = explode('][', trim($this->objRequest->getParam('folderIds'), '[]'));
      $intRootLevelId = (int) $this->objRequest->getParam('rootLevelId', -1);
      $arrCurrFileIds = explode('][', trim($this->objRequest->getParam('fileIds'), '[]'));

      $this->view->assign('fieldname', $this->objRequest->getParam('fileFieldId'));
      $this->view->assign('viewtype', $this->objRequest->getParam('viewtype'));
      $this->view->assign('isOverlay', (bool) $this->objRequest->getParam('isOverlay', false));
      $this->view->assign('pageIds', $arrCurrFileIds);
      $this->view->assign('selectOne', $this->objRequest->getParam('selectOne'));

      if($intRootLevelId > 0 || $arrFolderIds[0] > 0){
        /**
         * get files
         */
        $objPages = $this->getModelPages()->loadPagesByFilter($arrFolderIds, $arrTagIds);
        $this->view->assign('pages', $objPages);
        $this->renderScript('overlay/listpage.phtml');
      }else{
        $this->_helper->viewRenderer->setNoRender();
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }


  /**
   * getcontactsAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getcontactsAction(){
    $this->core->logger->debug('cms->controllers->PageController->getcontactsAction()');

    try{
      $strContactIds = $this->objRequest->getParam('contactIds');
      $strFieldName = $this->objRequest->getParam('fieldId');

      /**
       * get contacts
       */
      $this->getModelContacts();
      $objContacts = $this->objModelContacts->loadContactsById($strContactIds);

      $this->view->assign('elements', $objContacts);
      $this->view->assign('fieldname', $strFieldName);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }
  
  /**
   * getgroupsAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getgroupsAction(){
    $this->core->logger->debug('cms->controllers->PageController->getcontactsAction()');
  
    try{
      $strGroupIds = $this->objRequest->getParam('groupIds');
      $strFieldName = $this->objRequest->getParam('fieldId');
  
      /**
       * get groups
       */
      $objGroups = $this->getModelUsers()->loadGroupsById($strGroupIds);
  
      $this->view->assign('elements', $objGroups);
      $this->view->assign('fieldname', $strFieldName);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * deleteAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('cms->controllers->PageController->deleteAction()');

    try{
	    $this->getModelPages();
	    
	    if(Security::get()->isAllowed('portals', Security::PRIVILEGE_DELETE, false, false)){
        if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
		      $this->objModelPages->deletePage($this->objRequest->getParam("id"));
		      $this->view->blnShowFormAlert = true;
        }
	    }

	    $this->renderScript('page/form.phtml');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * dashboardAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function dashboardAction(){
    $this->core->logger->debug('cms->controllers->PageController->dashboardAction()');
    try{
      $this->getModelFolders();

      if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
        $intRootLevelId = $this->objRequest->getParam('rootLevelId');
        $intLimitNumber = 10;
        
        /**
         * check if select item in session
         */
        if(isset($this->core->objCoreSession->selectItem) && count($this->core->objCoreSession->selectItem) > 0){
          $objSelectItem = $this->core->objCoreSession->selectItem;
          
          $this->view->assign('objSelectItem', $objSelectItem);
          $this->view->assign('isSelectItem', true);
          
          unset($this->core->objCoreSession->selectItem);
        }else{
          $objPages = $this->objModelFolders->loadLimitedRootLevelChilds($intRootLevelId, $intLimitNumber);

          $this->view->assign('objPages', $objPages);
          $this->view->assign('limit', $intLimitNumber); 
          $this->view->assign('isSelectItem', false); 
        }
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }
  
  public function exportdynformentriesAction(){
    $this->core->logger->debug('cms->controllers->page->exportdynformentriesAction()');
  
    $this->_helper->viewRenderer->setNoRender();
  
    $strExport = '';
    $objEntries = $this->getModelPages()->loadDynFormEntries($this->getRequest()->getParam('pageId'), false, $this->getRequest()->getParam('from'), $this->getRequest()->getParam('to'), $this->getRequest()->getParam('startdate'), $this->getRequest()->getParam('enddate'));
    
    if($this->getRequest()->getParam('headline')){
      $objRow = json_decode($objEntries->current()->content, true);
      foreach($objRow as $key => $value){
        $strExport .= $key.';';
      }
      $strExport .= '
';
    }

    foreach($objEntries as $objRow){
      $objEntry = json_decode($objRow->content, true);
      foreach($objEntry as $value){
        if(is_array($value)){
          $value = implode(',', $value);
        }
        $strExport .= $value.';';
      }
      $strExport .= '
';
    }
  
    // fix for IE catching or PHP bug issue
    header("Pragma: public");
    header("Expires: 0"); // set expiration time
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    // browser must download file from server instead of cache
  
    // force download dialog
    header("Content-Type: application/force-download; charset=utf-8");
    header("Content-Type: application/octet-stream; charset=utf-8");
    header("Content-Type: application/csv; charset=utf-8");
  
    // Set filename
    header("Content-Disposition: attachment; filename=\"formular".date('Y-m-d').".csv\"");
  
    /**
     * The Content-transfer-encoding header should be binary, since the file will be read
     * directly from the disk and the raw bytes passed to the downloading computer.
     * The Content-length header is useful to set for downloads. The browser will be able to
     * show a progress meter as a file downloads. The content-lenght can be determines by
     * filesize function returns the size of a file.
     */
    header("Content-Transfer-Encoding: binary");
  
    echo $strExport;
  }
  
  /**
   * formentrieslistAction
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function formentrieslistAction(){
    $this->core->logger->debug('cms->controllers->PageController->formentrieslistAction()');
    
    $objEntries = $this->getModelPages()->loadDynFormEntries($this->getRequest()->getParam('id'), true);
    
    $objAdapter = new Zend_Paginator_Adapter_DbSelect($objEntries);
    $objPaginator = new Zend_Paginator($objAdapter);
    $objPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
    $objPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
    $objPaginator->setView($this->view);
    
    $objEntries = $this->getModelPages()->loadDynFormEntries($this->getRequest()->getParam('id'), false);
    
    $this->view->assign('entries', $objEntries);
    $this->view->assign('paginator', $objPaginator);
  }

  /**
   * changetemplateAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changetemplateAction(){
    $this->core->logger->debug('cms->controllers->PageController->changetemplateAction()');

    try{
      $objGenericData = new GenericData();

      $objGenericData->Setup()->setFormId($this->objRequest->getParam("formId"));
      $objGenericData->Setup()->setFormVersion($this->objRequest->getParam("formVersion"));
      $objGenericData->Setup()->setFormTypeId($this->objRequest->getParam("formTypeId"));
      $objGenericData->Setup()->setTemplateId($this->objRequest->getParam("templateId"));
      $objGenericData->Setup()->setElementId($this->objRequest->getParam("id"));
      $objGenericData->Setup()->setElementTypeId($this->objRequest->getParam("pageTypeId"));
      $objGenericData->Setup()->setParentTypeId($this->objRequest->getParam("parentTypeId"));      
      $objGenericData->Setup()->setRootLevelId($this->objRequest->getParam("rootLevelId"));
      $objGenericData->Setup()->setRootLevelGroupId($this->objRequest->getParam("rootLevelGroupId"));
      $objGenericData->Setup()->setParentId((($this->objRequest->getParam("parentFolderId") != '') ? $this->objRequest->getParam("parentFolderId") : null));
      $objGenericData->Setup()->setElementId($this->objRequest->getParam("id"));
      $objGenericData->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);
      $objGenericData->Setup()->setLanguageId($this->getItemLanguageId());
      $objGenericData->Setup()->setLanguageCode($this->getItemLanguageCode());
      $objGenericData->Setup()->setFormLanguageId($this->core->intZooluLanguageId);
      $objGenericData->Setup()->setModelSubPath('cms/models/');

      /**
       * change Template
       */
      $objGenericData->changeTemplate($this->objRequest->getParam("newTemplateId"));

      $this->objRequest->setParam("formId", $objGenericData->Setup()->getFormId());
      $this->objRequest->setParam("templateId", $objGenericData->Setup()->getTemplateId());
      $this->objRequest->setParam("formVersion", $objGenericData->Setup()->getFormVersion());

      $this->getForm($this->core->sysConfig->generic->actions->edit);

      /**
       * load generic data
       */
      $this->objForm->setGenericSetup($objGenericData->Setup());
      $this->addPageSpecificFormElements();

      /**
       * set action
       */
      if(intval($this->objRequest->getParam('id')) > 0){
        $this->objForm->setAction('/zoolu/cms/page/edit');
      }else{
        $this->objForm->setAction('/zoolu/cms/page/add');
      }

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

      $this->renderScript('page/form.phtml');

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * changelanguageAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changelanguageAction(){
    $this->core->logger->debug('cms->controllers->PageController->changelanguageAction()');

    try{
      if(intval($this->objRequest->getParam('id')) > 0){
        $objPageData = $this->getModelPages()->loadFormAndTemplateById($this->objRequest->getParam('id'));
        if(count($objPageData) == 1){
          $objPage = $objPageData->current();
          if((int) $objPage->idTemplates > 0) $this->objRequest->setParam('templateId', $objPage->idTemplates);
          if($objPage->genericFormId != '') $this->objRequest->setParam('formId', $objPage->genericFormId);          
        }
        $this->_forward('geteditform');
      }else{
        $this->_forward('getaddform');
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * changetypeAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changetypeAction(){
    $this->core->logger->debug('cms->controllers->PageController->changetypeAction()');

    try{
      //Set backlink
      $this->objRequest->setParam('backLink', $this->objRequest->getParam('backLink', false));
      if($this->objRequest->getParam('pageTypeId') != '' && $this->objRequest->getParam('pageTypeId') > 0){
      	switch($this->objRequest->getParam('pageTypeId')){
        	case $this->core->sysConfig->page_types->page->id :
            $this->objRequest->setParam('formId', '');
            if($this->objRequest->getParam('isStartPage') == 'true' && $this->objRequest->getParam('parentTypeId') == $this->core->sysConfig->parent_types->rootlevel){
              $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->page->portal_startpage_templateId);
            }else if($this->objRequest->getParam('isStartPage') == 'true'){
              $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->page->startpage_templateId);
            }else{
              $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->page->default_templateId);
            }
            break;
          case $this->core->sysConfig->page_types->link->id :
            $this->objRequest->setParam('formId', $this->core->sysConfig->page_types->link->default_formId);
            break;
          case $this->core->sysConfig->page_types->overview->id :
            $this->objRequest->setParam('formId', '');
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->overview->default_templateId);
            break;
          case $this->core->sysConfig->page_types->external->id :
            $this->objRequest->setParam('formId', $this->core->sysConfig->page_types->external->default_formId);
            break;
          case $this->core->sysConfig->page_types->process->id :
            $this->objRequest->setParam('formId', $this->core->sysConfig->page_types->process->default_formId);
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->process->default_templateId);
            break;
          case $this->core->sysConfig->page_types->collection->id :
            $this->objRequest->setParam('formId', $this->core->sysConfig->page_types->collection->default_formId);
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->collection->default_templateId);
            break;
          case $this->core->sysConfig->page_types->product_tree->id :
            $this->objRequest->setParam('formId', '');
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->product_tree->default_templateId);
            break;
          case $this->core->sysConfig->page_types->press_area->id :
            $this->objRequest->setParam('formId', '');
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->press_area->default_templateId);
            break;
          case $this->core->sysConfig->page_types->courses->id :
            $this->objRequest->setParam('formId', '');
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->courses->default_templateId);
            break;
          case $this->core->sysConfig->page_types->events->id :
            $this->objRequest->setParam('formId', '');
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->events->default_templateId);
            break;
          case $this->core->sysConfig->page_types->iframe->id :
            $this->objRequest->setParam('formId', $this->core->sysConfig->page_types->iframe->default_formId);
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->iframe->default_templateId);
            break;
          case $this->core->sysConfig->page_types->download_center->id :
            $this->objRequest->setParam('formId', $this->core->sysConfig->page_types->download_center->default_formId);
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->download_center->default_templateId);
            break;
          case $this->core->sysConfig->page_types->sitemap->id :
            $this->objRequest->setParam('formId', $this->core->sysConfig->page_types->sitemap->default_formId);
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->sitemap->default_templateId);
            break;
          case $this->core->sysConfig->page_types->service->id :
            $this->objRequest->setParam('formId', $this->core->sysConfig->page_types->service->default_formId);
            $this->objRequest->setParam('templateId', $this->core->sysConfig->page_types->service->default_templateId);
            break;
        }
      }

      $this->getForm($this->core->sysConfig->generic->actions->edit);

      /**
       * load generic data
       */
      $this->objForm->loadFormData();

      /**
       * overwrite now the page type
       */
      $this->objForm->Setup()->setElementTypeId($this->objRequest->getParam('pageTypeId'));
      $this->addPageSpecificFormElements();

      /**
       * set action
       */
      if(intval($this->objRequest->getParam('id')) > 0){
        $this->objForm->setAction('/zoolu/cms/page/edit');
      }else{
        $this->objForm->setAction('/zoolu/cms/page/add');
      }

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

      $this->view->backLink = $this->objRequest->getParam('backLink', false);
      $this->view->parentFolderId = $this->objRequest->getParam('parentFolderId');
      $this->view->form = $this->objForm;

      $this->renderScript('page/form.phtml');

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * linkedpagefieldAction
   * @author Thomas Schedler <cha@massiveart.com>
   * @version 1.0
   */
  public function linkedpagefieldAction(){
    $this->core->logger->debug('cms->controllers->PageController->linkedpagefieldAction()');

    $strFieldId = $this->objRequest->getParam('fieldId');
    $strFormId = $this->objRequest->getParam('formId');
    $intFormVersion = $this->objRequest->getParam('formVersion');
    $intPageId = $this->objRequest->getParam('pageId');

    $objFieldData = $this->getModelGenericForm()->loadFieldByName($strFieldId, $strFormId, $intFormVersion);
    if(count($objFieldData) > 0){
      $objFieldRegionData = $objFieldData->current();

      require_once(GLOBAL_ROOT_PATH.'library/massiveart/generic/elements/generic.element.field.class.php');
      $objField = new GenericElementField();
      $objField->id = $objFieldRegionData->id;
      $objField->title = $objFieldRegionData->title;
      $objField->name = $objFieldRegionData->name;
      $objField->typeId = $objFieldRegionData->idFieldTypes;
      $objField->type = $objFieldRegionData->type;
      $objField->defaultValue = $objFieldRegionData->defaultValue;
      $objField->sqlSelect = $objFieldRegionData->sqlSelect;
      $objField->columns = $objFieldRegionData->columns;
      $objField->order = $objFieldRegionData->order;
      $objField->isCoreField = $objFieldRegionData->isCoreField;
      $objField->isKeyField = $objFieldRegionData->isKeyField;
      $objField->isSaveField = $objFieldRegionData->isSaveField;
      $objField->isRegionTitle = $objFieldRegionData->isRegionTitle;
      $objField->isDependentOn = $objFieldRegionData->isDependentOn;
      $objField->copyValue = $objFieldRegionData->copyValue;
      $objField->decorator = $objFieldRegionData->decorator;
      $objField->isMultiply = $objFieldRegionData->isMultiply;

      $objGenericSetup = new GenericSetup();
      $objGenericSetup->setLanguageId($this->getItemLanguageId());

      $objField->setGenericSetup($objGenericSetup);
      $objField->loadLinkPage($intPageId);

      require_once(GLOBAL_ROOT_PATH.'library/massiveart/generic/fields/InternalLink/forms/elements/InternalLink.php');
      $objElement= new Form_Element_InternalLink($strFieldId, array(
          'value' => $objField->getValue(),
          'label' => $objField->title,
          'description' => $objField->description,
          'fieldId' => $objField->id,
          'columns' => $objField->columns,
          'class' => $objField->type,
          'height' => $objField->height,
          'isGenericSaveField' => $objField->isSaveField,
          'isCoreField' => $objField->isCoreField,
          'LanguageId' => $this->objRequest->getParam("languageId", $this->core->intZooluLanguageId),
          'isEmptyField' => 0,
          'required' => (($objField->isKeyField == 1) ? true : false)
        ));

      $objElement->addPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH.'library/massiveart/generic/forms/decorators/', 'decorator');
      $objElement->setDecorators(array($objField->decorator));

      if(count($objField->getProperties()) > 0){
        foreach($objField->getProperties() as $strProperty => $mixedPropertyValue){
          if(in_array($strProperty, GenericForm::$FIELD_PROPERTIES_TO_IMPART)){
            $objElement->$strProperty = $mixedPropertyValue;
          }
        }
      }

      $objDecorator = $objElement->getDecorator($objField->decorator);
      $objDecorator->setElement($objElement);
      echo $objDecorator->buildInput();
    }

    $this->_helper->viewRenderer->setNoRender();
  }
  
  /**
   * changeparentfolderAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function changeparentfolderAction(){
    $this->core->logger->debug('cms->controllers->PageController->changeparentfolderAction()');

    $intPageId = $this->objRequest->getParam('pageId');
    $intParentFolderId = $this->objRequest->getParam('parentFolderId');

    if($intPageId > 0 && $intParentFolderId > 0){
      $this->getModelPages();
      $this->objModelPages->changeParentFolderId($intPageId, $intParentFolderId);
    }
    $this->_helper->viewRenderer->setNoRender();
  }
  
  /**
   * changeparentrootfolderAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function changeparentrootfolderAction(){
    $this->core->logger->debug('cms->controllers->PageController->changeparentrootfolderAction()');

    $intPageId = $this->objRequest->getParam('pageId');
    $intRootFolderId = $this->objRequest->getParam('rootFolderId');

    if($intPageId > 0 && $intRootFolderId > 0){
      $this->getModelPages();
      $this->objModelPages->changeParentRootFolderId($intPageId, $intRootFolderId);
    }
    $this->_helper->viewRenderer->setNoRender();
  }

  /**
   * getForm
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function getForm($intActionType = null){
    $this->core->logger->debug('cms->controllers->PageController->getForm('.$intActionType.')');

    try{

      $strFormId = $this->objRequest->getParam("formId");
      $intTemplateId = $this->objRequest->getParam("templateId");

      /**
       * if there is now formId, try to load form template
       */
      if($strFormId == ''){
        if($intTemplateId != ''){
          /**
           * get templates
           */
          $this->getModelTemplates();
          $objTemplateData = $this->objModelTemplates->loadTemplateById($intTemplateId);

          if(count($objTemplateData) == 1){
            $objTemplate = $objTemplateData->current();

            /**
             * set form id from template
             */
            $strFormId = $objTemplate->genericFormId;
          }else{
            throw new Exception('Not able to create a form, because there is no form id!');
          }
        }else{
          throw new Exception('Not able to create a form, because there is no form id!');
        }
      }

      $intFormVersion = ($this->objRequest->getParam("formVersion") != '') ? $this->objRequest->getParam("formVersion") : null;
      $intElementId = ($this->objRequest->getParam("id") != '') ? $this->objRequest->getParam("id") : null;

      $objFormHandler = FormHandler::getInstance();
      $objFormHandler->setFormId($strFormId);
      $objFormHandler->setTemplateId($intTemplateId);
      $objFormHandler->setFormVersion($intFormVersion);
      $objFormHandler->setActionType($intActionType);
      $objFormHandler->setLanguageId($this->getItemLanguageId($intActionType));
      $objFormHandler->setLanguageCode($this->getItemLanguageCode());
      $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
      $objFormHandler->setElementId($intElementId);

      $this->objForm = $objFormHandler->getGenericForm();

      /**
       * set page default & specific form values
       */
      $this->objForm->Setup()->setCreatorId((($this->objRequest->getParam("creator") != '') ? $this->objRequest->getParam("creator") : Zend_Auth::getInstance()->getIdentity()->id));
      $this->objForm->Setup()->setStatusId((($this->objRequest->getParam("idStatus") != '') ? $this->objRequest->getParam("idStatus") : $this->core->sysConfig->form->status->default));
      $this->objForm->Setup()->setRootLevelId((($this->objRequest->getParam("rootLevelId") != '') ? $this->objRequest->getParam("rootLevelId") : null));
      $this->objForm->Setup()->setParentId((($this->objRequest->getParam("parentFolderId") != '') ? $this->objRequest->getParam("parentFolderId") : null));
      $this->objForm->Setup()->setIsStartElement((($this->objRequest->getParam("isStartPage") != '') ? $this->objRequest->getParam("isStartPage") : 0));
      $this->objForm->Setup()->setPublishDate((($this->objRequest->getParam("publishDate") != '') ? $this->objRequest->getParam("publishDate") : date('Y-m-d H:i:s')));
      $this->objForm->Setup()->setShowInNavigation((($this->objRequest->getParam("showInNavigation") != '') ? $this->objRequest->getParam("showInNavigation") : 0));
      $this->objForm->Setup()->setDestinationId((($this->objRequest->getParam("destinationId") != '') ? $this->objRequest->getParam("destinationId") : 0));
      $this->objForm->Setup()->setHideInSitemap((($this->objRequest->getParam("hideInSitemap") != '') ? $this->objRequest->getParam("hideInSitemap") : 0));
      $this->objForm->Setup()->setShowInWebsite((($this->objRequest->getParam("showInWebsite") != '') ? $this->objRequest->getParam("showInWebsite") : 1));
      $this->objForm->Setup()->setShowInTablet((($this->objRequest->getParam("showInTablet") != '') ? $this->objRequest->getParam("showInTablet") : 1));
      $this->objForm->Setup()->setShowInMobile((($this->objRequest->getParam("showInMobile") != '') ? $this->objRequest->getParam("showInMobile") : 1));
      $this->objForm->Setup()->setElementTypeId((($this->objRequest->getParam("pageTypeId") != '') ? $this->objRequest->getParam("pageTypeId") : $this->core->sysConfig->page_types->page->id));
      $this->objForm->Setup()->setParentTypeId((($this->objRequest->getParam("parentTypeId") != '') ? $this->objRequest->getParam("parentTypeId") : (($this->objRequest->getParam("parentFolderId") != '') ? $this->core->sysConfig->parent_types->folder : $this->core->sysConfig->parent_types->rootlevel)));
      $this->objForm->Setup()->setModelSubPath('cms/models/');

      /**
       * add currlevel hidden field
       */
      $this->objForm->addElement('hidden', 'currLevel', array('value' => $this->objRequest->getParam("currLevel"), 'decorators' => array('Hidden'), 'ignore' => true));

      /**
       * add elementTye hidden field (folder, page, ...)
       */
      $this->objForm->addElement('hidden', 'elementType', array('value' => $this->objRequest->getParam("elementType"), 'decorators' => array('Hidden'), 'ignore' => true));

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * addPageSpecificFormElements
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function addPageSpecificFormElements(){
    if(is_object($this->objForm) && $this->objForm instanceof GenericForm){
      /**
       * add page specific hidden fields
       */
      $this->objForm->addElement('hidden', 'creator', array('value' => $this->objForm->Setup()->getCreatorId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'idStatus', array('value' => $this->objForm->Setup()->getStatusId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objForm->Setup()->getRootLevelId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'rootLevelTypeId', array('value' => $this->objForm->Setup()->getRootLevelTypeId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'parentFolderId', array('value' => $this->objForm->Setup()->getParentId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'pageTypeId', array('value' => $this->objForm->Setup()->getElementTypeId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'isStartPage', array('value' => $this->objForm->Setup()->getIsStartElement(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'publishDate', array('value' => $this->objForm->Setup()->getPublishDate('Y-m-d H:i:s'), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'showInNavigation', array('value' => $this->objForm->Setup()->getShowInNavigation(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'destinationId', array('value' => $this->objForm->Setup()->getDestinationId(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'hideInSitemap', array('value' => $this->objForm->Setup()->getHideInSitemap(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'showInWebsite', array('value' => $this->objForm->Setup()->getShowInWebsite(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'showInTablet', array('value' => $this->objForm->Setup()->getShowInTablet(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'showInMobile', array('value' => $this->objForm->Setup()->getShowInMobile(), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'parentTypeId', array('value' => $this->objForm->Setup()->getParentTypeId(), 'decorators' => array('Hidden')));
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
        
        $intRootLevelId = $this->objRequest->getParam("rootLevelId", $this->objRequest->getParam("portalId"));
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
   * getModelPages
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelPages(){
    if (null === $this->objModelPages) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'cms/models/Pages.php';
      $this->objModelPages = new Model_Pages();
      $this->objModelPages->setLanguageId($this->getItemLanguageId());
    }

    return $this->objModelPages;
  }

  /**
   * getModelFolders
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

  /**
   * getModelFiles
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelFiles(){
    if (null === $this->objModelFiles) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Files.php';
      $this->objModelFiles = new Model_Files();
      $this->objModelFiles->setLanguageId($this->getItemLanguageId());
    }

    return $this->objModelFiles;
  }

  /**
   * getModelContacts
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelContacts(){
    if (null === $this->objModelContacts) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Contacts.php';
      $this->objModelContacts = new Model_Contacts();
      $this->objModelContacts->setLanguageId($this->getItemLanguageId());
    }

    return $this->objModelContacts;
  }

  /**
   * getModelTemplates
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelTemplates(){
    if (null === $this->objModelTemplates) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Templates.php';
      $this->objModelTemplates = new Model_Templates();
    }

    return $this->objModelTemplates;
  }
  
  /**
   * getModelUsers
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelUsers(){
    if (null === $this->objModelUsers) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'users/models/Users.php';
      $this->objModelUsers = new Model_Users();
    }
  
    return $this->objModelUsers;
  }

  /**
   * getModelGenericForm
   * @return Model_GenericForms
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelGenericForm(){
    if (null === $this->objModelGenericForm) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/GenericForms.php';
      $this->objModelGenericForm = new Model_GenericForms();
      $this->objModelGenericForm->setLanguageId($this->objRequest->getParam("languageId", $this->core->intZooluLanguageId));
    }

    return $this->objModelGenericForm;
  }
}

?>
