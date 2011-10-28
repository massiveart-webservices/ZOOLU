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
 * @package    application.zoolu.modules.users.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Users_ResourceController
 *
 * Login, Logout, ...
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-03: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Users_ResourceController extends AuthControllerAction {

  /**
   * init
   */
  public function init(){
    parent::init();
    if(!Security::get()->isAllowed('user_administration', Security::PRIVILEGE_VIEW)){
      $this->_redirect('/zoolu');
    }
  }
  
  /**
   * @var Zend_Form
   */
  protected $objForm;

  /**
   * @var Model_Users
   */
  protected $objModelUsers;

  /**
   * @var Zend_Db_Table_Row
   */
  protected $objResource;

  /**
   * @var array
   */
  protected $arrGroups = array();

  /**
   * indexAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
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
    $this->core->logger->debug('users->controllers->ResourceController->listAction()');

    $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'title');
    $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
    $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

    $objSelect = $this->getModelUsers()->getResourceTable()->select();
    $objSelect->setIntegrityCheck(false);
    $objSelect->from($this->getModelUsers()->getResourceTable(), array('id', 'title', 'key'));
    $objSelect->joinLeft('users', 'users.id = resources.idUsers', array('CONCAT(`users`.`fname`, \' \', `users`.`sname`) AS editor', 'resources.changed'));
    if($strSearchValue != ''){
      $objSelect->where('resources.title LIKE ?', '%'.$strSearchValue.'%');
      $objSelect->orWhere('resources.key LIKE ?', '%'.$strSearchValue.'%');  
    }
    $objSelect->order($strOrderColumn.' '.strtoupper($strSortOrder));

    $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
    $objResourcesPaginator = new Zend_Paginator($objAdapter);
    $objResourcesPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
    $objResourcesPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
    $objResourcesPaginator->setView($this->view);

    $this->view->assign('resourcePaginator', $objResourcesPaginator);
    $this->view->assign('orderColumn', $strOrderColumn);
    $this->view->assign('sortOrder', $strSortOrder);
    $this->view->assign('searchValue', $strSearchValue);
  }

  /**
   * addformAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addformAction(){
    $this->core->logger->debug('users->controllers->ResourceController->addformAction()');

    try{

      $this->initForm();
      $this->objForm->setAction('/zoolu/users/resource/add');

      $this->view->form = $this->objForm;
      $this->view->formTitle = $this->core->translate->_('New_Resource');

      $this->renderScript('form.phtml');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * addAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addAction(){
    $this->core->logger->debug('users->controllers->ResourceController->addformAction()');

    try{

      $this->initForm();

      if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
        $arrFormData = $this->getRequest()->getPost();
        if($this->objForm->isValid($arrFormData)){
          /**
           * set action
           */
          $this->objForm->setAction('/zoolu/users/resource/edit');

          $arrResourceGroups = array();
          if(array_key_exists('groups', $arrFormData)){
            $arrResourceGroups = $arrFormData['groups'];
            unset($arrFormData['groups']);
          }

          $intResourceId = $this->getModelUsers()->addResource($arrFormData);

          $this->getModelUsers()->updateResourceGroups($intResourceId, $arrResourceGroups);

          $this->view->assign('blnShowFormAlert', true);
          $this->_forward('list', 'resource', 'users');
        }else{
          /**
           * set action
           */
          $this->objForm->setAction('/zoolu/users/resource/add');
          $this->view->assign('blnShowFormAlert', false);

          $this->view->form = $this->objForm;
          $this->view->formTitle = $this->core->translate->_('New_Resource');

          $this->renderScript('form.phtml');
        }
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * editformAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function editformAction(){
    $this->core->logger->debug('users->controllers->ResourceController->editformAction()');

    try{

      $arrGroups = $this->getModelUsers()->getResourceGroups($this->getRequest()->getParam('id'));
      if(count($arrGroups) > 0){
        $this->arrGroups = array();
        foreach($arrGroups as $objGroup){
          $this->arrGroups[] = $objGroup->idGroups;
        }
      }

      $this->initForm();
      $this->objForm->setAction('/zoolu/users/resource/edit');

      $this->objResource = $this->getModelUsers()->getResourceTable()->find($this->getRequest()->getParam('id'))->current();

      foreach($this->objForm->getElements() as $objElement){
        $name = $objElement->getName();
        if(isset($this->objResource->$name)){
          $objElement->setValue($this->objResource->$name);
        }
      }

      $this->view->form = $this->objForm;
      $this->view->formTitle = $this->core->translate->_('Edit_Resource');

      $this->renderScript('form.phtml');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * editAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function editAction(){
    $this->core->logger->debug('users->controllers->ResourceController->editAction()');

    try{

      $this->initForm();

      if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
        $arrFormData = $this->getRequest()->getPost();
        if($this->objForm->isValid($arrFormData)){
          /**
           * set action
           */
          $this->objForm->setAction('/zoolu/users/resource/edit');

          $intResourceId = $this->getRequest()->getParam('id');

          $arrResourceGroups = array();
          if(array_key_exists('groups', $arrFormData)){
            $arrResourceGroups = $arrFormData['groups'];
            unset($arrFormData['groups']);
          }

          unset($arrFormData['_']);
          $this->getModelUsers()->editResource($intResourceId, $arrFormData);

          $this->getModelUsers()->updateResourceGroups($intResourceId, $arrResourceGroups);

          $this->_forward('list', 'resource', 'users');
          $this->view->assign('blnShowFormAlert', true);
        }else{
          /**
           * set action
           */
          $this->objForm->setAction('/zoolu/users/resource/edit');
          $this->view->assign('blnShowFormAlert', false);

          $this->view->form = $this->objForm;
          $this->view->formTitle = $this->core->translate->_('Edit_Resource');

          $this->renderScript('form.phtml');
        }
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * deleteAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function deleteAction(){
    $this->core->logger->debug('users->controllers->ResourceController->deleteAction()');

    try{

      if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
        $this->getModelUsers()->deleteResource($this->getRequest()->getParam("id"));
      }

      $this->_forward('list', 'resource', 'users');
      $this->view->assign('blnShowFormAlert', true);

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * initForm
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function initForm(){

    $this->objForm = new Zend_Form();

    /**
     * Use our own PluginLoader
     */
    $objLoader = new PluginLoader();
    $objLoader->setPluginLoader($this->objForm->getPluginLoader(PluginLoader::TYPE_FORM_ELEMENT));
    $objLoader->setPluginType(PluginLoader::TYPE_FORM_ELEMENT);
    $this->objForm->setPluginLoader($objLoader, PluginLoader::TYPE_FORM_ELEMENT);

    /**
     * clear all decorators
     */
    $this->objForm->clearDecorators();

    /**
     * add standard decorators
     */
    $this->objForm->addDecorator('TabContainer');
    $this->objForm->addDecorator('FormElements');
    $this->objForm->addDecorator('Form');

    /**
     * add form prefix path
     */
    $this->objForm->addPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH.'library/massiveart/generic/forms/decorators/', 'decorator');

    /**
     * elements prefixes
     */
    $this->objForm->addElementPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH.'library/massiveart/generic/forms/decorators/', 'decorator');

    /**
     * regions prefixes
     */
    $this->objForm->addDisplayGroupPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH.'library/massiveart/generic/forms/decorators/');

    $this->objForm->setAttrib('id', 'genForm');
    $this->objForm->setAttrib('onsubmit', 'return false;');
    $this->objForm->addElement('hidden', 'id', array('decorators' => array('Hidden')));

    $this->objForm->addElement('text', 'title', array('label' => $this->core->translate->_('title', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'text keyfield', 'required' => true));
    $this->objForm->addElement('text', 'key', array('label' => $this->core->translate->_('key', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'text', 'required' => true));

    $this->objForm->addDisplayGroup(array('title', 'key'), 'main-resource');
    $this->objForm->getDisplayGroup('main-resource')->setLegend($this->core->translate->_('General_information', false));
    $this->objForm->getDisplayGroup('main-resource')->setDecorators(array('FormElements', 'Region'));

    $arrGroups = array();
    $sqlStmt = $this->core->dbh->query("SELECT `id`, `title` FROM `groups` ORDER BY `title`")->fetchAll();
    foreach($sqlStmt as $arrSql){
      $arrGroups[$arrSql['id']] = $arrSql['title'];
    }

    $this->objForm->addElement('multiCheckbox', 'groups', array('label' => $this->core->translate->_('groups', false), 'value' => $this->arrGroups, 'decorators' => array('Input'), 'columns' => 6, 'class' => 'multiCheckbox', 'MultiOptions' => $arrGroups));

    $this->objForm->addDisplayGroup(array('groups'), 'groups-group');
    $this->objForm->getDisplayGroup('groups-group')->setLegend($this->core->translate->_('Resource_groups', false));
    $this->objForm->getDisplayGroup('groups-group')->setDecorators(array('FormElements', 'Region'));
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
}

?>