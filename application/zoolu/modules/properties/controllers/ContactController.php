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
 * ContactController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-07: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Properties_ContactController extends AuthControllerAction {

	/**
   * @var GenericForm
   */
  protected $objForm;

  /**
   * @var Model_Contacts
   */
  public $objModelContacts;

  /**
   * @var Model_Units
   */
  public $objModelUnits;

  /**
   * The default action - show the home page
   */
  public function indexAction(){
    $this->_helper->viewRenderer->setNoRender();
  }

  /**
   * getaddformAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getaddformAction(){
    $this->core->logger->debug('properties->controllers->ContactController->getaddformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/contact/addunit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('contact/form.phtml');
  }

  /**
   * addAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addAction(){
    $this->core->logger->debug('properties->controllers->ContactController->addAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/contact/add');

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
        $this->objForm->setAction('/zoolu/properties/contact/edit');

        /**
         * set rootlevelid and parentid for contact creation
         */
        $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
        $this->objForm->Setup()->setParentId($this->objForm->getElement('parentUnitId')->getValue());

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

    $this->renderScript('contact/form.phtml');
  }

  /**
   * geteditformAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function geteditformAction(){
    $this->core->logger->debug('properties->controllers->ContactController->geteditformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * load generic data
     */
    $this->objForm->loadFormData();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/contact/edit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('contact/form.phtml');
  }

  /**
   * editAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function editAction(){
    $this->core->logger->debug('propterties->controllers->ContactController->editAction()');

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
      $this->objForm->setAction('/zoolu/properties/contact/edit');

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

    $this->renderScript('contact/form.phtml');
  }

  /**
   * deleteAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('properties->controllers->ContactController->deleteAction()');

    $this->getModelContacts();

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
      $objRequest = $this->getRequest();
      $this->objModelContacts->deleteContact($objRequest->getParam("id"));
      $this->view->blnShowFormAlert = true;
    }

    $this->renderScript('contact/form.phtml');
  }


  /**
   * getunitaddformAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getunitaddformAction(){
    $this->core->logger->debug('properties->controllers->ContactController->getunitaddformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/contact/addunit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('contact/form.phtml');
  }

  /**
   * addunitAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addunitAction(){
    $this->core->logger->debug('properties->controllers->ContactController->addunitAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/contact/addunit');

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
        $this->objForm->setAction('/zoolu/properties/contact/editunit');

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

    $this->renderScript('contact/form.phtml');
  }

  /**
   * getuniteditformAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getuniteditformAction(){
    $this->core->logger->debug('properties->controllers->ContactController->getuniteditformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * load generic data
     */
    $this->objForm->loadFormData();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/contact/editunit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('contact/form.phtml');
  }

  /**
   * editunitAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function editunitAction(){
    $this->core->logger->debug('propterties->controllers->ContactController->editunitAction()');

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
      $this->objForm->setAction('/zoolu/properties/contact/editunit');

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

    $this->renderScript('contact/form.phtml');
  }

  /**
   * deleteunitAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function deleteunitAction(){
    $this->core->logger->debug('properties->controllers->ContactController->deleteunitAction()');

    $this->getModelContacts();

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
      $objRequest = $this->getRequest();
      $this->objModelContacts->deleteUnitNode($objRequest->getParam("id"));
      $this->view->blnShowFormAlert = true;
    }

    $this->renderScript('contact/form.phtml');
  }

  /**
   * getForm
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function getForm($intActionType = null){
    $this->core->logger->debug('properties->controllers->ContactController->getForm('.$intActionType.')');

    try{
      $objRequest = $this->getRequest();

      $strFormId = $objRequest->getParam("formId");
      $intElementId = ($objRequest->getParam("id") != '') ? $objRequest->getParam("id") : null;

      $objFormHandler = FormHandler::getInstance();
	    $objFormHandler->setFormId($strFormId);
	    $objFormHandler->setActionType($intActionType);
	    $objFormHandler->setLanguageId(1); //TODO : get Language id
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

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
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
      $this->objModelContacts->setLanguageId(1); // TODO : get language id
    }

    return $this->objModelContacts;
  }
}
