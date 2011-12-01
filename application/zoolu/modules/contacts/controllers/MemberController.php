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
 * @package    application.zoolu.modules.core.properties.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Contacts_MemberController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-01-19: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Contacts_MemberController extends AuthControllerAction {

  /**
   * @var GenericForm
   */
  protected $objForm;
  
  /**
   * @var inter
   */
  protected $intItemLanguageId;
  
  /**
   * request object instance
   * @var Zend_Controller_Request_Abstract
   */
  protected $objRequest;

  /**
   * @var Model_Members
   */
  public $objModelMembers;

  /**
   * @var Model_Units
   */
  public $objModelUnits;
  
  /**
   * init
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   * @return void
   */
  public function init(){
    parent::init();
    if(!Security::get()->isAllowed('contact', Security::PRIVILEGE_VIEW)){
      $this->_redirect('/zoolu');
    }
    $this->objRequest = $this->getRequest();
  }
  
  /**
   * The default action
   */
  public function indexAction(){
    $this->_helper->viewRenderer->setNoRender();
  }
    
  /**
   * listAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function listAction(){
    $this->core->logger->debug('contacts->controllers->MemberController->listAction()');
    
    $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'sname');
    $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
    $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

    $objSelect = $this->getModelMembers()->getMembersTable()->select();
    $objSelect->setIntegrityCheck(false);
    
    $objSelect->from($this->getModelMembers()->getMembersTable(), array('id', 'fname', 'sname', 'username', 'status' => 'contactStatus.title', 'country' => 'categoryTitles.title', 'company' => 'companies.name', 'companyStatus' => 'cpStatus.title', 'type' => new Zend_Db_Expr("'member'")));
    $objSelect->joinInner('genericForms', 'genericForms.id = members.idGenericForms', array('genericForms.genericFormId', 'genericForms.version'));
    $objSelect->joinLeft('users', 'users.id = members.idUsers', array('members.lastLogin', 'members.changed')); 
    $objSelect->joinLeft('companies', 'companies.id = members.company', array());
    $objSelect->joinLeft('contactStatus', 'contactStatus.id = members.status', array());
    $objSelect->joinLeft('contactStatus AS cpStatus', 'cpStatus.id = companies.status', array());
    $objSelect->joinLeft('categoryTitles', 'categoryTitles.idCategories = members.country AND categoryTitles.idLanguages = '.$this->core->intZooluLanguageId, array());
    $objSelect->where('members.idRootLevels = ?', $this->getRequest()->getParam('rootLevelId'));
    if($strSearchValue != ''){
      $objSelect->where('members.fname LIKE ?', '%'.$strSearchValue.'%'); 
      $objSelect->orWhere('members.sname LIKE ?', '%'.$strSearchValue.'%'); 
      $objSelect->orWhere('companies.name LIKE ?', '%'.$strSearchValue.'%');
      $objSelect->orWhere('contactStatus.title = ?', $strSearchValue);
    }
    $objSelect->order($strOrderColumn.' '.strtoupper($strSortOrder));
    
    $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
    $objGroupsPaginator = new Zend_Paginator($objAdapter);
    $objGroupsPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
    $objGroupsPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
    $objGroupsPaginator->setView($this->view);

    $this->view->assign('paginator', $objGroupsPaginator);
    $this->view->assign('orderColumn', $strOrderColumn);
    $this->view->assign('sortOrder', $strSortOrder);
    $this->view->assign('searchValue', $strSearchValue);
  }
  
  /**
   * sendDataAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function sendDataAction(){
    $this->core->logger->debug('contacts->controllers->MemberController->sendDataAction()');
    $this->_helper->viewRenderer->setNoRender();
    
    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

      $intRootLevelId = $this->getRequest()->getParam('rootLevelId', null);
      $intMemberId = $this->getRequest()->getParam('id', null);
      
      if($intRootLevelId != null && $intMemberId != null){
        $objMember = $this->getModelMembers()->loadMember($intMemberId);
        if(count($objMember) > 0) $objMember = $objMember->current();
        
        try {
          ClientHelper::get('Mails')->sendMail($objMember, $intRootLevelId);
        }catch (Exception $exc) {
          $this->core->logger->err($exc);
        }
      }
    }
  }

  /**
   * addformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addformAction(){
    $this->core->logger->debug('contacts->controllers->MemberController->addformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/member/add');

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
    //$this->setViewMetaInfos();

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }

  /**
   * addAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addAction(){
    $this->core->logger->debug('contacts->controllers->MemberController->addAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/member/add');

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

      $arrFormData = $this->getRequest()->getPost();
      $this->objForm->Setup()->setFieldValues($arrFormData);

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      if($this->objForm->isValid($arrFormData)){

        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/contacts/member/edit');

        /**
         * set rootlevelid and parentid for member creation
         */
        $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
        //$this->objForm->Setup()->setParentId($this->objForm->getElement('parentId')->getValue());

        $intMemberId = $this->objForm->saveFormData();
        $this->objForm->getElement('id')->setValue($intMemberId);
        
        $this->view->intId = $intMemberId; 
        $this->view->blnShowFormAlert = true;
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
    //$this->setViewMetaInfos();

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }

  /**
   * editformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function editformAction(){
    $this->core->logger->debug('contacts->controllers->MemberController->editformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * load generic data
     */
    $this->objForm->loadFormData();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/member/edit');

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
    //$this->setViewMetaInfos();

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }

  /**
   * editAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function editAction(){
    $this->core->logger->debug('contacts->controllers->MemberController->editAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

      $arrFormData = $this->getRequest()->getPost();
      $this->objForm->Setup()->setFieldValues($arrFormData);

      /**
       * set action
       */
      $this->objForm->setAction('/zoolu/contacts/member/edit');

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      if($this->objForm->isValid($arrFormData)){
        $this->objForm->saveFormData();
        $this->view->blnShowFormAlert = true;
      }
    }
    
    /**
     * output of metainformation to hidden div
     */
    //$this->setViewMetaInfos();

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }

  /**
   * deleteAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('contacts->controllers->MemberController->deleteAction()');

    $this->getModelMembers();

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
      $this->objModelMembers->deleteMember($this->objRequest->getParam("id"));
      $this->view->blnShowFormAlert = true;
    }
    $this->renderScript('form.phtml');
  }
  
  /**
   * listdeleteAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function listdeleteAction(){
    $this->core->logger->debug('contacts->controllers->MemberController->listdeleteAction()');

    try{
      if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
        $strTmpUserIds = trim($this->objRequest->getParam('values'), '[]');
        $arrMemberIds = array();
        $arrMemberIds = explode('][', $strTmpUserIds);
        
        if(count($arrMemberIds) > 1){         
          $this->getModelMembers()->deleteMembers($arrMemberIds); 
        }else{
          $this->getModelMembers()->deleteMember($arrMemberIds[0]); 
        }
        
      }
      $this->_forward('list', 'member', 'contacts');

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * getForm
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getForm($intActionType = null){
    $this->core->logger->debug('contacts->controllers->MemberController->getForm('.$intActionType.')');

    try{
      $strFormId = $this->objRequest->getParam("formId", $this->core->sysConfig->form->ids->members->default);
      $intElementId = ($this->objRequest->getParam("id") != '') ? $this->objRequest->getParam("id") : null;
      
    	/**
       * if there is no formId
       */
      if($strFormId == ''){
        throw new Exception('Not able to create a form, because there is no form id!');
      }

      $objFormHandler = FormHandler::getInstance();
      $objFormHandler->setFormId($strFormId);
      $objFormHandler->setActionType($intActionType);
      $objFormHandler->setLanguageId($this->getItemLanguageId($intActionType));
      $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
      $objFormHandler->setElementId($intElementId);

      $this->objForm = $objFormHandler->getGenericForm();

      /**
       * add location & unit specific hidden fields
       */
      $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objRequest->getParam("rootLevelId"), 'decorators' => array('Hidden')));
      //$this->objForm->addElement('hidden', 'parentId', array('value' => $this->objRequest->getParam("parentId"), 'decorators' => array('Hidden')));

      /**
       * add currlevel hidden field
       */
      $this->objForm->addElement('hidden', 'currLevel', array('value' => $this->objRequest->getParam("currLevel"), 'decorators' => array('Hidden'), 'ignore' => true));
      
      /**
       * add elementTye hidden field (folder, element, ...)
       */
      $this->objForm->addElement('hidden', 'elementType', array('value' => $this->objRequest->getParam("elementType"), 'decorators' => array('Hidden'), 'ignore' => true));
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }
  
  /**
   * setViewMetaInfos
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function setViewMetaInfos(){
    if(is_object($this->objForm) && $this->objForm instanceof GenericForm){      
      $arrSecurityCheck = array();
      if(!Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_VIEW, false, false)){
        $arrSecurityCheck = array('ResourceKey'           => Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId().'_%d', 
                                  'Privilege'             => Security::PRIVILEGE_VIEW, 
                                  'CheckForAllLanguages'  => false,
                                  'IfResourceNotExists'   => false);  
      }

      $blnGeneralDeleteAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_DELETE, false, false);
      $blnGeneralUpdateAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_UPDATE, false, false);
      
      $this->view->authorizedDelete = ($this->objForm->Setup()->getIsStartElement(false) == true || $this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->add) ? false : (($blnGeneralDeleteAuthorization == true) ? $blnGeneralDeleteAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId().'_'.$this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_DELETE, false, false));
      $this->view->authorizedUpdate = ($blnGeneralUpdateAuthorization == true) ? $blnGeneralUpdateAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->objForm->Setup()->getRootLevelId().'_'.$this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_UPDATE, false, false);
    }
  }
  
  /**
   * getItemLanguageId
   * @param integer $intActionType
   * @return integer
   * @author Cornelius Hansjakob <cha@massiveart.com>
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
   * getModelMembers
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelMembers(){
    if (null === $this->objModelMembers) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Members.php';
      $this->objModelMembers = new Model_Members();
      $this->objModelMembers->setLanguageId($this->getItemLanguageId());
    }

    return $this->objModelMembers;
  }
}
