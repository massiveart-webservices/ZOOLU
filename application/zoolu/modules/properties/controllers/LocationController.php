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
 * LocationController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-07: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Properties_LocationController extends AuthControllerAction {

	/**
   * @var GenericForm
   */
  protected $objForm;

  /**
   * @var Model_Locations
   */
  public $objModelLocations;

  /**
   * @var Model_Units
   */
  public $objModelUnits;

  /**
   * The default action
   */
  public function indexAction(){
    $this->_helper->viewRenderer->setNoRender();
  }
  
  /**
   * listAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function listAction(){
    $this->core->logger->debug('properties->controllers->LocationController->listAction()');
    
    $intUnitId = $this->getRequest()->getParam('folderId', 0);
    $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'name');
    $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
    $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

    $objSelect = $this->getModelLocations()->getLocationsTable()->select();
    $objSelect->setIntegrityCheck(false);
    $objSelect->from($this->getModelLocations()->getLocationsTable(), array('id', 'name', 'type' => new Zend_Db_Expr("'location'")));
    $objSelect->joinInner('genericForms', 'genericForms.id = locations.idGenericForms', array('genericForms.genericFormId', 'genericForms.version'));
    $objSelect->joinLeft('users', 'users.id = locations.idUsers', array('CONCAT(`users`.`fname`, \' \', `users`.`sname`) AS editor', 'locations.changed')); 
    $objSelect->where('locations.idUnits = ?', $intUnitId);
    if($strSearchValue != ''){
      $objSelect->where('locations.name LIKE ?', '%'.$strSearchValue.'%');  
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
   * getaddformAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getaddformAction(){
    $this->core->logger->debug('properties->controllers->LocationController->getaddformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/location/add');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('location/form.phtml');
  }

  /**
   * addAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addAction(){
    $this->core->logger->debug('properties->controllers->LocationController->addAction()');

    $this->getForm($this->core->sysConfig->generic->actions->add);

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/location/add');

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
        $this->objForm->setAction('/zoolu/properties/location/edit');

        /**
         * set rootlevelid and parentid for location creation
         */
        $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
        $this->objForm->Setup()->setParentId($this->objForm->getElement('parentId')->getValue());

        $intLocationId = $this->objForm->saveFormData();
        $this->objForm->getElement('id')->setValue($intLocationId);

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

    $this->renderScript('location/form.phtml');
  }

  /**
   * geteditformAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function geteditformAction(){
    $this->core->logger->debug('properties->controllers->LocationController->geteditformAction()');

    $this->getForm($this->core->sysConfig->generic->actions->edit);

    /**
     * load generic data
     */
    $this->objForm->loadFormData();

    /**
     * set action
     */
    $this->objForm->setAction('/zoolu/properties/location/edit');

    /**
     * prepare form (add fields and region to the Zend_Form)
     */
    $this->objForm->prepareForm();

    /**
     * get form title
     */
    $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

    $this->view->form = $this->objForm;
    $this->renderScript('location/form.phtml');
  }

  /**
   * editAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function editAction(){
    $this->core->logger->debug('propterties->controllers->LocationController->editAction()');

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
      $this->objForm->setAction('/zoolu/properties/location/edit');

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

    $this->renderScript('location/form.phtml');
  }

  /**
   * deleteAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('properties->controllers->LocationController->deleteAction()');

    $this->getModelLocations();

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
      $objRequest = $this->getRequest();
      $this->objModelLocations->deleteLocation($objRequest->getParam("id"));
      $this->view->blnShowFormAlert = true;
    }

    $this->renderScript('location/form.phtml');
  }
  
  /**
   * listdeleteAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function listdeleteAction(){
    $this->core->logger->debug('properties->controllers->LocationController->listdeleteAction()');

    try{

      if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
        $strTmpUserIds = trim($this->getRequest()->getParam('values'), '[]');
        $arrUserIds = array();
        $arrUserIds = split('\]\[', $strTmpUserIds);
        
        if(count($arrUserIds) > 1){         
          $this->getModelLocations()->deleteLocations($arrUserIds); 
        }else{
          $this->getModelLocations()->deleteLocation($arrUserIds[0]); 
        }
        
      }

      $this->_forward('list', 'location', 'properties');

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * getForm
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function getForm($intActionType = null){
    $this->core->logger->debug('properties->controllers->LocationController->getForm('.$intActionType.')');

    try{
      $objRequest = $this->getRequest();

      $strFormId = $objRequest->getParam("formId", $this->core->sysConfig->form->ids->locations->default);
      $intElementId = ($objRequest->getParam("id") != '') ? $objRequest->getParam("id") : null;

      $objFormHandler = FormHandler::getInstance();
	    $objFormHandler->setFormId($strFormId);
	    $objFormHandler->setActionType($intActionType);
	    $objFormHandler->setLanguageId(1); //TODO : get Language id
	    $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
	    $objFormHandler->setElementId($intElementId);

      $this->objForm = $objFormHandler->getGenericForm();

      /**
       * add location & unit specific hidden fields
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
   * getModelLocations
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelLocations(){
    if (null === $this->objModelLocations) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Locations.php';
      $this->objModelLocations = new Model_Locations();
      $this->objModelLocations->setLanguageId(1); // TODO : get language id
    }

    return $this->objModelLocations;
  }
}
