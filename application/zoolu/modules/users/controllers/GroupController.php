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
 * Users_GroupController
 *
 * Login, Logout, ...
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-03: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Users_GroupController extends AuthControllerAction {

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
  protected $objGroup;

  /**
   * @var array
   */
  protected $arrPermissions = array();
  
  /**
   * @var array
   */
  protected $arrGroupTypes = array();

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
    $this->core->logger->debug('users->controllers->GroupController->listAction()');

    $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'title');
    $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
    $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

    $objSelect = $this->getModelUsers()->getGroupTable()->select();
    $objSelect->setIntegrityCheck(false);
    $objSelect->from($this->getModelUsers()->getGroupTable(), array('id', 'title', 'key'));
    $objSelect->joinLeft('users', 'users.id = groups.idUsers', array('CONCAT(`users`.`fname`, \' \', `users`.`sname`) AS editor', 'groups.changed'));
    if($strSearchValue != ''){
      $objSelect->where('groups.title LIKE ?', '%'.$strSearchValue.'%');
      $objSelect->orWhere('groups.key LIKE ?', '%'.$strSearchValue.'%');  
    }
    $objSelect->order($strOrderColumn.' '.strtoupper($strSortOrder));

    $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
    $objGroupsPaginator = new Zend_Paginator($objAdapter);
    $objGroupsPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
    $objGroupsPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
    $objGroupsPaginator->setView($this->view);

    $this->view->assign('groupPaginator', $objGroupsPaginator);
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
    $this->core->logger->debug('users->controllers->GroupController->addformAction()');

    try{

      $this->arrPermissions = array(array('language' => '', 'permissions' => ''));
      $this->arrGroupTypes = array();

      $this->initForm();
      $this->objForm->setAction('/zoolu/users/group/add');

      $this->view->form = $this->objForm;
      $this->view->formTitle = $this->core->translate->_('New_Group');

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
    $this->core->logger->debug('users->controllers->GroupController->addAction()');

    try{

      $this->prepareData();

      $this->initForm();

      if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
        $arrFormData = $this->getRequest()->getPost();
        if($this->objForm->isValid($arrFormData)){
          /**
           * set action
           */
          $this->objForm->setAction('/zoolu/users/group/edit');
          
          /**
           * add group data
           */
          $arrGroupData['title'] = $this->getRequest()->getParam('title');
          $arrGroupData['key'] = $this->getRequest()->getParam('key');
          $arrGroupData['description'] = $this->getRequest()->getParam('description');
          $intGroupId = $this->getModelUsers()->addGroup($arrGroupData);
          
          /**
           * add groupGroupTypes 
           */
          $this->getModelUsers()->updateGroupGroupTypes($intGroupId, $this->getRequest()->getParam('groupTypes')); 
          
          /**
           * add groupPermissions
           */
          $this->getModelUsers()->updateGroupPermissions($intGroupId, $this->arrPermissions);
          
          $this->_forward('list', 'group', 'users');
          $this->view->assign('blnShowFormAlert', true);
        }else{
          /**
           * set action
           */
          $this->objForm->setAction('/zoolu/users/group/add');
          $this->view->assign('blnShowFormAlert', false);

          $this->view->form = $this->objForm;
          $this->view->formTitle = $this->core->translate->_('New_Group');

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
    $this->core->logger->debug('users->controllers->GroupController->editformAction()');

    try{
      
      /**
       * get permissions
       */
      $arrPermissions = $this->getModelUsers()->getGroupPermissions($this->getRequest()->getParam('id'));
      if(count($arrPermissions) > 0){
        $this->arrPermissions = array();
        foreach($arrPermissions as $objPermission){
          if(!array_key_exists('lang_'.$objPermission->idLanguages, $this->arrPermissions)){
            $this->arrPermissions['lang_'.$objPermission->idLanguages] = array('language' => $objPermission->idLanguages, 'permissions' => array($objPermission->idPermissions));
          }else{
            $this->arrPermissions['lang_'.$objPermission->idLanguages]['permissions'][] = $objPermission->idPermissions;
          }
        }
      }else{
        $this->arrPermissions = array(array('language' => '', 'permissions' => ''));
      }
      
      /**
       * get groupType
       */
      $arrGroupTypes = $this->getModelUsers()->getGroupGroupTypes($this->getRequest()->getParam('id'));
      if(count($arrGroupTypes) > 0){
        $this->arrGroupTypes = array();
        foreach($arrGroupTypes as $objGroupType){
          $this->arrGroupTypes[] = $objGroupType->idGroupTypes;  
        }  
      }else{
        $this->arrGroupTypes = array();  
      }

      $this->initForm();
      $this->objForm->setAction('/zoolu/users/group/edit');

      $this->objGroup = $this->getModelUsers()->getGroupTable()->find($this->getRequest()->getParam('id'))->current();

      foreach($this->objForm->getElements() as $objElement){
        $name = $objElement->getName();
        if(isset($this->objGroup->$name)){
          $objElement->setValue($this->objGroup->$name);
        }
      }

      $this->view->form = $this->objForm;
      $this->view->formTitle = $this->core->translate->_('Edit_Group');

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
    $this->core->logger->debug('users->controllers->GroupController->editAction()');

    try{

      $this->prepareData();

      $this->initForm();

      if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
        $arrFormData = $this->getRequest()->getPost();
        if($this->objForm->isValid($arrFormData)){
          /**
           * set action
           */
          $this->objForm->setAction('/zoolu/users/group/edit');

          $intGroupId = $this->getRequest()->getParam('id');

          $arrGroupData['title'] = $this->getRequest()->getParam('title');
          $arrGroupData['key'] = $this->getRequest()->getParam('key');
          $arrGroupData['description'] = $this->getRequest()->getParam('description');
          $this->getModelUsers()->editGroup($intGroupId, $arrGroupData);          
          
          $this->getModelUsers()->updateGroupGroupTypes($intGroupId, $this->getRequest()->getParam('groupTypes'));
          $this->getModelUsers()->updateGroupPermissions($intGroupId, $this->arrPermissions);

          $this->_forward('list', 'group', 'users');
          $this->view->assign('blnShowFormAlert', true);
        }else{
          /**
           * set action
           */
          $this->objForm->setAction('/zoolu/users/group/edit');
          $this->view->assign('blnShowFormAlert', false);

          $this->view->form = $this->objForm;
          $this->view->formTitle = $this->core->translate->_('Edit_Group');

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
    $this->core->logger->debug('users->controllers->GroupController->deleteAction()');

    try{

      if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
        $this->getModelUsers()->deleteGroup($this->getRequest()->getParam("id"));
      }

      $this->_forward('list', 'group', 'users');
      $this->view->assign('blnShowFormAlert', true);

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * prepareData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function prepareData(){
    try{
      $this->arrPermission = array();
      
      if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
        $arrFormData = $this->getRequest()->getPost();
        
        /**
         * permissions
         */
        if(isset($arrFormData['Region_Permission_Instances'])){
          $strRegionInstanceIds = trim($arrFormData['Region_Permission_Instances'], '[]');
          $arrRegionInstanceIds = array();
          $arrRegionInstanceIds = split('\]\[', $strRegionInstanceIds);

          /**
           * go through permissions
           */
          foreach($arrRegionInstanceIds as $intRegionInstanceId){
            if(isset($arrFormData['language_'.$intRegionInstanceId]) && isset($arrFormData['permissions_'.$intRegionInstanceId])){
              $this->arrPermissions[$intRegionInstanceId] = array('language'    => $arrFormData['language_'.$intRegionInstanceId],
                                                                  'permissions' => $arrFormData['permissions_'.$intRegionInstanceId]);
            }
          }
        }
      }

      if(count($this->arrPermissions) == 0){
        $this->arrPermissions = array(array('language' => '', 'permissions' => ''));
      }
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
    
    $arrGroupTypeOptions = array();
    $sqlStmt = $this->core->dbh->query("SELECT `id`, `title` FROM `groupTypes`")->fetchAll();
    foreach($sqlStmt as $arrSql){
      $arrGroupTypeOptions[$arrSql['id']] = ucfirst($arrSql['title']);
    }

    $this->objForm->setAttrib('id', 'genForm');
    $this->objForm->setAttrib('onsubmit', 'return false;');
    $this->objForm->addElement('hidden', 'id', array('decorators' => array('Hidden')));

    $this->objForm->addElement('text', 'title', array('label' => $this->core->translate->_('title', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'text keyfield', 'required' => true));
    $this->objForm->addElement('text', 'key', array('label' => $this->core->translate->_('key', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'text', 'required' => true));

    $this->objForm->addElement('textarea', 'description', array('label' => $this->core->translate->_('description', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'text'));
    
    $this->objForm->addElement('multiCheckbox', 'groupTypes', array('label' => $this->core->translate->_('groupTypes', false),
                                                                    'value' => $this->arrGroupTypes,
                                                                    'decorators' => array('Input'),
                                                                    'columns' => 6, 'class' => 'multiCheckbox',
                                                                    'MultiOptions' => $arrGroupTypeOptions));
    
    $this->objForm->addDisplayGroup(array('title', 'key', 'description', 'groupTypes'), 'main-group', array('columns' => 9));
    $this->objForm->getDisplayGroup('main-group')->setLegend($this->core->translate->_('General_information', false));
    $this->objForm->getDisplayGroup('main-group')->setDecorators(array('FormElements', 'Region'));

    $arrPermissionOptions = array();
    $sqlStmt = $this->core->dbh->query("SELECT `id`, UCASE(`title`) AS title FROM `permissions`")->fetchAll();
    foreach($sqlStmt as $arrSql){
      $arrPermissionOptions[$arrSql['id']] = $arrSql['title'];
    }

    $arrLanguageOptions = array();
    $arrLanguageOptions['0'] = $this->core->translate->_('All_languages', false);
    $sqlStmt = $this->core->dbh->query("SELECT `id`, `title` FROM `languages`")->fetchAll();
    foreach($sqlStmt as $arrSql){
      $arrLanguageOptions[$arrSql['id']] = $arrSql['title'];
    }

    $strRegionInstances = '';
    $intRegionCounter = 0;

    /**
     * create group permisson regions
     */
    foreach($this->arrPermissions as $arrPermission){
      $intRegionCounter++;
      $this->objForm->addElement('radio', 'language_'.$intRegionCounter, array('label' => $this->core->translate->_('language', false),
                                                                               'value' => $arrPermission['language'],
                                                                               'decorators' => array('Input'),
                                                                               'columns' => 6,
                                                                               'class' => 'radio',
                                                                               'MultiOptions' => $arrLanguageOptions));
      $this->objForm->addElement('multiCheckbox', 'permissions_'.$intRegionCounter, array('label' => $this->core->translate->_('permissions', false),
                                                                                          'value' => $arrPermission['permissions'],
                                                                                          'decorators' => array('Input'),
                                                                                          'columns' => 6, 'class' =>
                                                                                          'multiCheckbox',
                                                                                          'MultiOptions' => $arrPermissionOptions));

      $this->objForm->addDisplayGroup(array('language_'.$intRegionCounter, 'permissions_'.$intRegionCounter), 'Permission_'.$intRegionCounter, array(
        'columns' =>  9,
        'regionTypeId' => 1,
        'collapsable' => 0,
        'regionCounter' => $intRegionCounter,
        'regionId' => 'Permission',
        'regionExt' => $intRegionCounter,
        'isMultiply' => true,
        'regionTitle' => $this->core->translate->_('Language_specific', false)
      ));

      $this->objForm->getDisplayGroup('Permission_'.$intRegionCounter)->setLegend($this->core->translate->_('Permissions', false));
      $this->objForm->getDisplayGroup('Permission_'.$intRegionCounter)->setDecorators(array('FormElements','Region'));

      $strRegionInstances .= '['.$intRegionCounter.']';
    }

    $this->objForm->addElement('radio', 'language_REPLACE_n', array('label' => $this->core->translate->_('language', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'radio', 'MultiOptions' => $arrLanguageOptions));
    $this->objForm->addElement('multiCheckbox', 'permissions_REPLACE_n', array('label' => $this->core->translate->_('permissions', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'multiCheckbox', 'MultiOptions' => $arrPermissionOptions));

    $this->objForm->addDisplayGroup(array('language_REPLACE_n', 'permissions_REPLACE_n'), 'Permission_REPLACE_n', array(
      'columns' =>  9,
      'regionTypeId' => 1,
      'collapsable' => 0,
      'regionId' => 'Permission',
      'regionExt' => 'REPLACE_n',
      'isMultiply' => true,
      'isEmptyWidget' => true,
      'regionTitle' => $this->core->translate->_('Language_specific', false)
    ));

    $this->objForm->getDisplayGroup('Permission_REPLACE_n')->setLegend('Rechte');
    $this->objForm->getDisplayGroup('Permission_REPLACE_n')->setDecorators(array('FormElements','Region'));

    $this->objForm->addElement('hidden', 'Region_Permission_Instances', array('value' => $strRegionInstances, 'decorators' => array('Hidden')));
    $this->objForm->addElement('hidden', 'Region_Permission_Order', array('value' => '', 'decorators' => array('Hidden')));

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