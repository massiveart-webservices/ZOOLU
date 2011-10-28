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
 * CategoryController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-16: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Properties_CategoryController extends AuthControllerAction {

	/**
   * @var GenericForm
   */
  protected $objForm;

  /**
   * request object instance
   * @var Zend_Controller_Request_Abstract
   */
  protected $objRequest;

  /**
   * @var Model_Categories
   */
  public $objModelCategories;

  /**
   * init
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   * @return void
   */
  public function init(){
    parent::init();
    $this->objRequest = $this->getRequest();
  }

  /**
   * The default action
   */
  public function indexAction(){
    $this->_helper->viewRenderer->setNoRender();
  }

  /**
   * getaddformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getaddformAction(){
    $this->core->logger->debug('properties->controllers->CategoryController->getaddformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/category/add');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('category/form.phtml');
  }

  /**
   * addAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addAction(){
    $this->core->logger->debug('properties->controllers->CategoryController->addAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/category/add');

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
        $this->objForm->setAction('/zoolu/properties/category/edit');

        /**
         * set rootlevelid and parentid for category creation
         */
        $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
        $this->objForm->Setup()->setParentId($this->objForm->getElement('parentId')->getValue());
        $this->objForm->Setup()->setElementTypeId($this->objForm->getElement('categoryTypeId')->getValue());

        $intCategoryId = $this->objForm->saveFormData();
        $this->objForm->getElement('id')->setValue($intCategoryId);
        $this->objForm->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);

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
    if($this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->edit) $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageId());

    $this->view->form = $this->objForm;

    $this->renderScript('category/form.phtml');
  }

  /**
   * geteditformAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function geteditformAction(){
    $this->core->logger->debug('properties->controllers->CategoryController->geteditformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * load generic data
     */
    $this->objForm->loadFormData();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/category/edit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();
    $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageId());

    $this->view->form = $this->objForm;
    $this->renderScript('category/form.phtml');
  }

  /**
   * editAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function editAction(){
    $this->core->logger->debug('propterties->controllers->CategoryController->editAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();
    $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageId());

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

      $arrFormData = $this->objRequest->getPost();
      $this->objForm->Setup()->setFieldValues($arrFormData);

      /**
       * set action
       */
      $this->objForm->setAction('/zoolu/properties/category/edit');

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

    $this->renderScript('category/form.phtml');
  }

  /**
   * deleteAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('properties->controllers->CategoryController->deleteAction()');

    $this->getModelCategories();

    if($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
      $this->objModelCategories->deleteCategoryNode($this->objRequest->getParam("id"));
      $this->view->blnShowFormAlert = true;
    }

    $this->renderScript('category/form.phtml');
  }

  /**
   * changelanguageAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function changelanguageAction(){
    $this->core->logger->debug('properties->controllers->CategoryController->changelanguageAction()');

    try{
      $this->_forward('geteditform');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * getForm
   */
  private function getForm($intActionType = null){
    $this->core->logger->debug('properties->controllers->CategoryController->getForm('.$intActionType.')');

    try{

      $strFormId = $this->objRequest->getParam("formId");
      $intElementId = ($this->objRequest->getParam("id") != '') ? $this->objRequest->getParam("id") : null;

      $objFormHandler = FormHandler::getInstance();
	    $objFormHandler->setFormId($strFormId);
	    $objFormHandler->setActionType($intActionType);
	    $objFormHandler->setLanguageId($this->objRequest->getParam("languageId", $this->core->intZooluLanguageId));
	    $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
	    $objFormHandler->setElementId($intElementId);

      $this->objForm = $objFormHandler->getGenericForm();

      /**
       * add folder specific hidden fields
       */
      $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objRequest->getParam("rootLevelId"), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'parentId', array('value' => $this->objRequest->getParam("parentId"), 'decorators' => array('Hidden')));
      $this->objForm->addElement('hidden', 'categoryTypeId', array('value' => $this->objRequest->getParam("categoryTypeId"), 'decorators' => array('Hidden')));

      /**
       * add currlevel hidden field
       */
      $this->objForm->addElement('hidden', 'currLevel', array('value' => $this->objRequest->getParam("currLevel"), 'decorators' => array('Hidden'), 'ignore' => true));

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }

  /**
   * getModelCategories
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelCategories(){
    if (null === $this->objModelCategories) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Categories.php';
      $this->objModelCategories = new Model_Categories();
    }

    return $this->objModelCategories;
  }

}
