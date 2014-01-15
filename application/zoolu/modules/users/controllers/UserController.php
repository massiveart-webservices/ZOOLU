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
 * @package    application.zoolu.modules.users.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Users_UserController
 *
 * Login, Logout, ...
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-03: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Users_UserController extends Zend_Controller_Action
{

    /**
     * @var Zend_Form
     */
    protected $objForm;

    /**
     * @var Core
     */
    private $core;

    /**
     * @var Model_Users
     */
    protected $objModelUsers;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    /**
     * @var Zend_Db_Table_Row
     */
    protected $objUser;

    /**
     * @var array
     */
    protected $arrGroups = array();

    /**
     * init
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function init()
    {
        $this->core = Zend_Registry::get('Core');
        $this->initView();

        if ($this->getRequest()->getActionName() != 'login' && $this->getRequest()->getActionName() != 'logout') {
            $objAuth = Zend_Auth::getInstance();

            if (!$objAuth->hasIdentity() || !isset($_SESSION['sesZooluLogin']) || $_SESSION['sesZooluLogin'] == false) {
                $this->_redirect('/zoolu/users/user/login');
            }
        }
    }

    /**
     * preDispatch
     * Called before action method.
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function preDispatch()
    {
        /**
         * set default encoding to view
         */
        $this->view->setEncoding($this->core->sysConfig->encoding->default);

        /**
         * set translate obj
         */
        $this->view->translate = $this->core->translate;
    }

    /**
     * indexAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {
        $this->_redirect('/zoolu/users/');
    }

    /**
     * listAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->core->logger->debug('users->controllers->UserController->listAction()');

        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'sname');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

        $objSelect = $this->getModelUsers()->getUserTable()->select();
        $objSelect->setIntegrityCheck(false);
        $objSelect->from($this->getModelUsers()->getUserTable(), array('id', 'fname', 'sname'));
        $objSelect->joinInner('users AS editor', 'editor.id = users.idUsers', array('CONCAT(`editor`.`fname`, \' \', `editor`.`sname`) AS editor', 'users.changed'));
        if ($strSearchValue != '') {
            $objSelect->where('users.fname LIKE ?', '%' . $strSearchValue . '%');
            $objSelect->orWhere('users.sname LIKE ?', '%' . $strSearchValue . '%');
        }
        $objSelect->order($strOrderColumn . ' ' . strtoupper($strSortOrder));

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
        $objUsersPaginator = new Zend_Paginator($objAdapter);
        $objUsersPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objUsersPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objUsersPaginator->setView($this->view);

        $this->view->assign('userPaginator', $objUsersPaginator);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);
    }

    /**
     * addformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addformAction()
    {
        $this->core->logger->debug('users->controllers->UserController->addformAction()');

        try {

            $this->initForm();
            $this->objForm->setAction('/zoolu/users/user/add');

            $this->view->form = $this->objForm;
            $this->view->formTitle = $this->core->translate->_('New_User');

            $this->renderScript('form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * addAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addAction()
    {
        $this->core->logger->debug('users->controllers->UserController->addformAction()');

        try {

            $this->initForm();

            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $arrFormData = $this->getRequest()->getPost();
                if ($this->objForm->isValid($arrFormData)) {
                    /**
                     * set action
                     */
                    $this->objForm->setAction('/zoolu/users/user/edit');

                    $arrFormData['idLanguages'] = $arrFormData['language'];
                    $arrFormData['idContentLanguages'] = $arrFormData['contentLanguage'];
                    unset($arrFormData['language']);
                    unset($arrFormData['contentLanguage']);
                    unset($arrFormData['passwordConfirmation']);
                    unset($arrFormData['_']);

                    $arrUserGroups = array();
                    if (array_key_exists('groups', $arrFormData)) {
                        $arrUserGroups = $arrFormData['groups'];
                        unset($arrFormData['groups']);
                    }

                    if (array_key_exists('idFiles', $arrFormData)) {
                        $arrFormData['idFiles'] = trim($arrFormData['idFiles'], '[]');
                    }

                    $intUserId = $this->getModelUsers()->addUser($arrFormData);

                    $this->getModelUsers()->updateUserGroups($intUserId, $arrUserGroups);

                    $this->view->assign('blnShowFormAlert', true);
                    $this->_forward('list', 'user', 'users');
                } else {
                    /**
                     * set action
                     */
                    $this->objForm->setAction('/zoolu/users/user/add');
                    $this->view->assign('blnShowFormAlert', false);

                    $this->view->form = $this->objForm;
                    $this->view->formTitle = $this->core->translate->_('New_User');

                    $this->renderScript('form.phtml');
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * editformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editformAction()
    {
        $this->core->logger->debug('users->controllers->UserController->editformAction()');

        try {

            $arrGroups = $this->getModelUsers()->getUserGroups($this->getRequest()->getParam('id'));
            if (count($arrGroups) > 0) {
                $this->arrGroups = array();
                foreach ($arrGroups as $objGroup) {
                    $this->arrGroups[] = $objGroup->idGroups;
                }
            }

            $this->initForm();
            $this->objForm->setAction('/zoolu/users/user/edit');

            $this->objUser = $this->getModelUsers()->getUserTable()->find($this->getRequest()->getParam('id'))->current();

            foreach ($this->objForm->getElements() as $objElement) {
                $name = $objElement->getName();
                if (isset($this->objUser->$name)) {
                    if ($name == 'idFiles') {
                        if ($this->objUser->$name > 0) {
                            $objElement->setValue('[' . $this->objUser->$name . ']');
                        }
                    } else {
                        $objElement->setValue($this->objUser->$name);
                    }
                }
            }

            $this->objForm->getElement('language')->setValue($this->objUser->idLanguages);
            $this->objForm->getElement('contentLanguage')->setValue($this->objUser->idContentLanguages);

            $this->view->form = $this->objForm;
            $this->view->formTitle = $this->core->translate->_('Edit_User');

            $this->renderScript('form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * editAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editAction()
    {
        $this->core->logger->debug('users->controllers->UserController->editAction()');
        try {

            $this->initForm();

            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $arrFormData = $this->getRequest()->getPost();
                if ($this->objForm->isValid($arrFormData)) {
                    /**
                     * set action
                     */
                    $this->objForm->setAction('/zoolu/users/user/edit');

                    $intUserId = $this->getRequest()->getParam('id');

                    $arrFormData['idLanguages'] = $arrFormData['language'];
                    $arrFormData['idContentLanguages'] = $arrFormData['contentLanguage'];
                    unset($arrFormData['language']);
                    unset($arrFormData['contentLanguage']);
                    unset($arrFormData['id']);
                    unset($arrFormData['passwordConfirmation']);
                    unset($arrFormData['_']);

                    $arrUserGroups = array();
                    $blnWithGroups = false;
                    if (array_key_exists('groups', $arrFormData)) {
                        $arrUserGroups = $arrFormData['groups'];
                        unset($arrFormData['groups']);
                        $blnWithGroups = true;
                    }

                    if (array_key_exists('idFiles', $arrFormData)) {
                        $arrFormData['idFiles'] = trim($arrFormData['idFiles'], '[]');
                    }

                    $this->getModelUsers()->editUser($intUserId, $arrFormData);
                    $this->getModelUsers()->updateUserGroups($intUserId, $arrUserGroups);

                    if ($blnWithGroups) {
                        $this->_forward('list', 'user', 'users');
                        $this->view->assign('blnShowFormAlert', true);
                    } else {
                        /**
                         * single edit form - no groups!
                         */
                        $this->_helper->viewRenderer->setNoRender();
                    }
                } else {
                    /**
                     * set action
                     */
                    $this->objForm->setAction('/zoolu/users/user/edit');
                    $this->view->assign('blnShowFormAlert', false);

                    $this->view->form = $this->objForm;
                    $this->view->formTitle = $this->core->translate->_('Edit_User');

                    $this->renderScript('form.phtml');
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * singleeditformAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function singleeditformAction()
    {
        $this->core->logger->debug('users->controllers->UserController->singleeditformAction()');
        try {
            $this->initForm(true);
            $this->objForm->setAction('/zoolu/users/user/edit');

            $this->objUser = $this->getModelUsers()->getUserTable()->find($this->getRequest()->getParam('id'))->current();

            foreach ($this->objForm->getElements() as $objElement) {
                $name = $objElement->getName();
                if (isset($this->objUser->$name)) {
                    if ($name == 'idFiles') {
                        if ($this->objUser->$name > 0) {
                            $objElement->setValue('[' . $this->objUser->$name . ']');
                        }
                    } else {
                        $objElement->setValue($this->objUser->$name);
                    }
                }
            }

            $this->objForm->getElement('language')->setValue($this->objUser->idLanguages);
            $this->objForm->getElement('contentLanguage')->setValue($this->objUser->idContentLanguages);

            $this->view->form = $this->objForm;
            $this->renderScript('form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * deleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteAction()
    {
        $this->core->logger->debug('users->controllers->UserController->deleteAction()');

        try {

            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $this->getModelUsers()->deleteUser($this->getRequest()->getParam('id'));
            }

            $this->_forward('list', 'user', 'users');
            $this->view->assign('blnShowFormAlert', true);

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * listdeleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function listdeleteAction()
    {
        $this->core->logger->debug('users->controllers->UserController->listdeleteAction()');

        try {

            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $strTmpUserIds = trim($this->getRequest()->getParam('values'), '[]');
                $arrUserIds = array();
                $arrUserIds = explode('][', $strTmpUserIds);

                if (count($arrUserIds) > 1) {
                    $this->getModelUsers()->deleteUsers($arrUserIds);
                } else {
                    $this->getModelUsers()->deleteUser($arrUserIds[0]);
                }

            }

            $this->_forward('list', 'user', 'users');

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * initForm
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function initForm($blnSingleEdit = false)
    {

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
        $this->objForm->addPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/', 'decorator');

        /**
         * elements prefixes
         */
        $this->objForm->addElementPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/', 'decorator');

        /**
         * regions prefixes
         */
        $this->objForm->addDisplayGroupPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/');

        $this->objForm->setAttrib('id', 'genForm');
        $this->objForm->setAttrib('onsubmit', 'return false;');
        $this->objForm->addElement('hidden', 'id', array('decorators' => array('Hidden')));

        $arrLanguageOptions = array();
        $arrLanguageOptions[''] = $this->core->translate->_('Please_choose', false);
        $sqlStmt = $this->core->dbh->query("SELECT `id`, `title` FROM `languages`")->fetchAll();
        foreach ($sqlStmt as $arrSql) {
            $arrLanguageOptions[$arrSql['id']] = $arrSql['title'];
        }

        $this->objForm->addElement('text', 'fname', array('label' => $this->core->translate->_('fname', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text keyfield', 'required' => true));
        $this->objForm->addElement('text', 'sname', array('label' => $this->core->translate->_('sname', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text keyfield', 'required' => true));
        $this->objForm->addElement('text', 'username', array('label' => $this->core->translate->_('username', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text', 'required' => true));
        $this->objForm->addElement('select', 'language', array('label' => $this->core->translate->_('system_language', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'select', 'required' => true, 'MultiOptions' => $arrLanguageOptions));
        $this->objForm->addElement('text', 'email', array('label' => $this->core->translate->_('email', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text', 'required' => true));
        $this->objForm->addElement('select', 'contentLanguage', array('label' => $this->core->translate->_('content_language', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'select', 'required' => true, 'MultiOptions' => $arrLanguageOptions));
        $this->objForm->addElement('textarea', 'description', array('label' => $this->core->translate->_('description', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'textarea', 'required' => false));
        $this->objForm->addElement('media', 'idFiles', array('label' => $this->core->translate->_('profile_image', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'media', 'required' => false, 'display_option' => ''));

        $this->objForm->addDisplayGroup(array('fname', 'sname', 'username', 'email', 'language', 'contentLanguage', 'description', 'idFiles'), 'main-group');
        $this->objForm->getDisplayGroup('main-group')->setLegend($this->core->translate->_('General_information', false));
        $this->objForm->getDisplayGroup('main-group')->setDecorators(array('FormElements', 'Region'));

        $this->objForm->addElement('password', 'password', array('label' => $this->core->translate->_('password', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'password'));
        $this->objForm->addElement('password', 'passwordConfirmation', array('label' => $this->core->translate->_('Confirm_password', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'password'));

        $this->objForm->addDisplayGroup(array('password', 'passwordConfirmation'), 'password-group');
        $this->objForm->getDisplayGroup('password-group')->setLegend($this->core->translate->_('Reset_password', false));
        $this->objForm->getDisplayGroup('password-group')->setDecorators(array('FormElements', 'Region'));
        
        $this->objForm->addElement('text', 'googlePlus', array('label' => $this->core->translate->_('googleplus_id', false), 'decorators' => array('Input'), 'columns' => 4, 'class' => 'text'));
        $this->objForm->addElement('text', 'facebook', array('label' => $this->core->translate->_('facebook_id', false), 'decorators' => array('Input'), 'columns' => 4, 'class' => 'text'));
        $this->objForm->addElement('text', 'twitter', array('label' => $this->core->translate->_('twitter_id', false), 'decorators' => array('Input'), 'columns' => 4, 'class' => 'text'));

        $this->objForm->addDisplayGroup(array('googlePlus', 'facebook', 'twitter'), 'socialmedia-group');
        $this->objForm->getDisplayGroup('socialmedia-group')->setLegend($this->core->translate->_('Social_media', false));
        $this->objForm->getDisplayGroup('socialmedia-group')->setDecorators(array('FormElements', 'Region'));

        if (!$blnSingleEdit) {
            $arrGroups = array();
            $sqlStmt = $this->core->dbh->query("SELECT `id`, `title` FROM `groups` ORDER BY `title`")->fetchAll();
            foreach ($sqlStmt as $arrSql) {
                $arrGroups[$arrSql['id']] = $arrSql['title'];
            }

            $this->objForm->addElement('multiCheckbox', 'groups', array('label' => $this->core->translate->_('groups', false), 'value' => $this->arrGroups, 'decorators' => array('Input'), 'columns' => 6, 'class' => 'multiCheckbox', 'MultiOptions' => $arrGroups));

            $this->objForm->addDisplayGroup(array('groups'), 'groups-group');
            $this->objForm->getDisplayGroup('groups-group')->setLegend($this->core->translate->_('User_groups', false));
            $this->objForm->getDisplayGroup('groups-group')->setDecorators(array('FormElements', 'Region'));
        }
    }

    /**
     * userinfoAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function userinfoAction()
    {
        $this->initView();
        $this->view->user = Zend_Auth::getInstance()->getIdentity();
        $this->view->translate = Zend_Registry::get('Core')->translate;
    }

    /**
     * loginAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loginAction()
    {

        $objAuth = Zend_Auth::getInstance();
        //$objAuth->setStorage(new Zend_Auth_Storage_Session('UserAuth'));

        if ($objAuth->hasIdentity() && isset($_SESSION['sesZooluLogin']) && $_SESSION['sesZooluLogin'] == true) {
            if ($this->core->getDashboard() !== true) {
                $this->_redirect('/zoolu/cms');
            } else {
                $this->_redirect('/zoolu');
            }
        }

        $this->view->strErrMessage = '';
        $this->view->strErrUsername = '';
        $this->view->strErrPassword = '';

        if ($this->_request->isPost()) {

            /**
             * data from the user
             * strip all HTML and PHP tags from the data
             */
            $objFilter = new Zend_Filter_StripTags();
            $username = $objFilter->filter($this->_request->getPost('username'));
            $password = md5($objFilter->filter($this->_request->getPost('password')));

            if (empty($username)) {
                $this->view->strErrUsername = $this->core->translate->_('Please_enter_username');
            } else {

                $this->core = Zend_Registry::get('Core');

                /**
                 * setup Zend_Auth for database authentication
                 */
                $objDbAuthAdapter = new Zend_Auth_Adapter_DbTable($this->core->dbh);
                $objDbAuthAdapter->setTableName('users');
                $objDbAuthAdapter->setIdentityColumn('username');
                $objDbAuthAdapter->setCredentialColumn('password');

                /**
                 * set the input credential values to authenticate against
                 */
                $objDbAuthAdapter->setIdentity($username);
                $objDbAuthAdapter->setCredential($password);

                /**
                 * do the authentication
                 */
                $result = $objAuth->authenticate($objDbAuthAdapter);

                switch ($result->getCode()) {

                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                        /**
                         * do stuff for nonexistent identity
                         */
                        $this->view->strErrUsername = $this->core->translate->_('Username_not_found');
                        break;

                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        /**
                         * do stuff for invalid credential
                         */
                        $this->view->strErrPassword = $this->core->translate->_('Wrong_password');
                        break;

                    case Zend_Auth_Result::SUCCESS:
                        /**
                         * store database row to auth's storage system but not the password
                         */
                        $objUsersData = $objDbAuthAdapter->getResultRowObject(array('id', 'idLanguages', 'idContentLanguages', 'username', 'fname', 'sname', 'email', 'idFiles'));
                        $objUsersData->languageId = $objUsersData->idLanguages;
                        $objUsersData->languageCode = null;
                        $objUsersData->contentLanguageId = $objUsersData->idContentLanguages;
                        $objUsersData->contentLanguageCode = null;


                        /**
                         * get user profile image
                         */
                        if (isset($objUsersData->idFiles) && $objUsersData->idFiles > 0) {
                            $objFiles = $this->getModelFiles()->loadFileById($objUsersData->idFiles);

                            if (count($objFiles) > 0) {
                                $objFileData = $objFiles->current();
                                $objUsersData->image = $this->core->sysConfig->media->paths->imgbase . $objFileData->path . 'icon32/' . $objFileData->filename;
                            }
                            unset($objUsersData->idFiles);
                        }

                        $arrLanguages = $this->core->zooConfig->languages->language->toArray();
                        foreach ($arrLanguages as $arrLanguage) {
                            if ($arrLanguage['id'] == $objUsersData->languageId) {
                                $objUsersData->languageCode = $arrLanguage['code'];
                            }

                            if ($arrLanguage['id'] == $objUsersData->contentLanguageId) {
                                $objUsersData->contentLanguageCode = $arrLanguage['code'];
                            }
                        }

                        if ($objUsersData->languageCode === null) {
                            $objUsersData->languageId = $this->core->zooConfig->languages->default->id;
                            $objUsersData->languageCode = $this->core->zooConfig->languages->default->code;
                        }

                        if ($objUsersData->contentLanguageCode === null) {
                            $objUsersData->contentLanguageId = $this->core->zooConfig->languages->default->id;
                            $objUsersData->contentLanguageCode = $this->core->zooConfig->languages->default->code;
                        }

                        $objUserRoleProvider = new RoleProvider();
                        $arrUserGroups = $this->getModelUsers()->getUserGroups($objUsersData->id);
                        if (count($arrUserGroups) > 0) {
                            foreach ($arrUserGroups as $objUserGroup) {
                                $objUserRoleProvider->addRole(new Zend_Acl_Role($objUserGroup->key), $objUserGroup->key);
                            }
                        }

                        $objSecurity = new Security();
                        $objSecurity->setRoleProvider($objUserRoleProvider);
                        $objSecurity->buildAcl($this->getModelUsers());
                        Security::save($objSecurity);

                        unset($objUsersData->idLanguages);
                        unset($objUsersData->idContentLanguages);
                        $objAuth->getStorage()->write($objUsersData);

                        $_SESSION['sesTestMode'] = true;
                        $_SESSION['sesZooluLogin'] = true;

                        if ($this->core->getDashboard() !== true) {
                            $this->_redirect('/zoolu/cms');
                        } else {
                            $this->_redirect('/zoolu');
                        }
                        break;

                    default:
                        /**
                         * do stuff for other failure
                         */
                        $this->view->strErrMessage = $this->core->translate->_('Login_failed');
                        break;
                }
            }
        }
    }

    /**
     * logoutAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        unset($_SESSION['sesTestMode']);
        unset($_SESSION['sesZooluLogin']);
        if ($this->core->getDashboard() !== true) {
            $this->_redirect('/zoolu/cms');
        } else {
            $this->_redirect('/zoolu');
        }
    }

    /**
     * getModelUsers
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelUsers()
    {
        if (null === $this->objModelUsers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/Users.php';
            $this->objModelUsers = new Model_Users();
        }

        return $this->objModelUsers;
    }

    /**
     * getModelFiles
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelFiles()
    {
        if (null === $this->objModelFiles) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Files.php';
            $this->objModelFiles = new Model_Files();
            $this->objModelFiles->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelFiles;
    }
}

?>