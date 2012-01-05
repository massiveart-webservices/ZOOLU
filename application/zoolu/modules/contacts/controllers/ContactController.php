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
 * @package    application.zoolu.modules.global.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Contacts_ContactController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-17: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Contacts_ContactController extends AuthControllerAction {

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
   * @var Model_Contacts
   */
  public $objModelContacts;

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
   * addformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addformAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->addformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/contact/add');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }
  
  /**
   * contactAddAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->addAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/contact/add');

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
        $this->objForm->setAction('/zoolu/contacts/contact/edit');

        /**
         * set rootlevelid and parentid for contact creation
         */
        $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
        $this->objForm->Setup()->setParentId($this->objForm->getElement('parentId')->getValue());

        $intContactId = $this->objForm->saveFormData();
        $this->objForm->getElement('id')->setValue($intContactId);

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

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }
  
  /**
   * editformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function editformAction(){
    $this->core->logger->debug('contacts->controllers->ElementController->editformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * load generic data
     */
    $this->objForm->loadFormData();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/contact/edit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }
  
  /**
   * editAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function editAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->editAction()');

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
      $this->objForm->setAction('/zoolu/contacts/contact/edit');

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      if($this->objForm->isValid($arrFormData)){
        $this->objForm->saveFormData();
        $this->view->blnShowFormAlert = true;
      }
    }

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }
  
  /**
   * deleteAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->deleteAction()');

    $this->getModelContacts();

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
      $objRequest = $this->getRequest();
      $this->objModelContacts->deleteContact($objRequest->getParam("id"));
      $this->view->blnShowFormAlert = true;
    }

    $this->renderScript('form.phtml');
  }
  
  /**
   * unitAddformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function unitAddformAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->unitAddformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/contact/add-unit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }

  /**
   * addUnitAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addUnitAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->addUnitAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/contact/add-unit');

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
        $this->objForm->setAction('/zoolu/contacts/contact/edit-unit');

        /**
         * set rootlevelid and parentid for unit creation
         */
        $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
        $this->objForm->Setup()->setParentId($this->objForm->getElement('parentId')->getValue());

        $intUnitId = $this->objForm->saveFormData();
        $this->objForm->getElement('id')->setValue($intUnitId);

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

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }

  /**
   * unitEditformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function unitEditformAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->unitEditformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * load generic data
     */
    $this->objForm->loadFormData();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/contacts/contact/edit-unit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }

  /**
   * editUnitAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function editUnitAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->editUnitAction()');

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
      $this->objForm->setAction('/zoolu/contacts/contact/edit-unit');

      /**
       * prepare form (add fields and region to the Zend_Form)
       */
      $this->objForm->prepareForm();

      if($this->objForm->isValid($arrFormData)){
        $this->objForm->saveFormData();
        $this->view->blnShowFormAlert = true;
      }
    }

    $this->view->form = $this->objForm;
    $this->renderScript('form.phtml');
  }

  /**
   * deleteUnitAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteUnitAction(){
    $this->core->logger->debug('contacts->controllers->ContactController->deleteUnitAction()');

    $this->getModelContacts();

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
      $objRequest = $this->getRequest();
      $this->objModelContacts->deleteUnitNode($objRequest->getParam("id"));
      $this->view->blnShowFormAlert = true;
    }
    $this->renderScript('form.phtml');
  }
  
  /**
   * getForm
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getForm($intActionType = null){
    $this->core->logger->debug('contacts->controllers->ContactController->getForm('.$intActionType.')');

    try{
      $objRequest = $this->getRequest();

      $strFormId = $objRequest->getParam("formId");
      $intElementId = ($objRequest->getParam("id") != '') ? $objRequest->getParam("id") : null;
      
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
       * add contact & unit specific hidden fields
       */
      $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $objRequest->getParam("rootLevelId"), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'parentId', array('value' => $objRequest->getParam("parentId"), 'decorators' => array('Hidden')));
      
      /**
       * add currlevel hidden field
       */
      $this->objForm->addElement('hidden', 'currLevel', array('value' => $objRequest->getParam("currLevel"), 'decorators' => array('Hidden'), 'ignore' => true));
      
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
   * getModelContacts
   * @author Cornelius Hansjakob <cha@massiveart.com>
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
}

?>