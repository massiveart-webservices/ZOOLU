<?php
/**
 * ZOOLU - Community Management System
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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * CustomerController
 *
 * Version History (please keep backward compatible):
 * 1.0, 2012-10-08: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class CustomerController extends WebControllerAction
{
    const STORAGE_NAME = 'customer';

    /**
     * @var Zend_Auth
     */
    protected $objAuth;

    /**
     * @var Zend_Auth_Adapter_DbTable
     */
    protected $objAuthAdapter;

    /**
     * @var Model_Customers
     */
    protected $objModelCustomers;

    /**
     * @var Model_Users
     */
    protected $objModelUsers;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * init
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function init()
    {
        parent::init();

        //Initialize Authentication
        $this->objAuth = Zend_Auth::getInstance();
        $this->objAuth->setStorage(new Zend_Auth_Storage_Session(self::STORAGE_NAME));

        //Initialize Authentication Adapter
        if (ClientHelper::get('Authentication')->isActive()) {
            $this->objAuthAdapter = ClientHelper::get('Authentication')->getAdapter();
        } else {
            $this->objAuthAdapter = new Zend_Auth_Adapter_DbTable($this->core->dbh);
            $this->objAuthAdapter->setTableName('customers');
            $this->objAuthAdapter->setIdentityColumn('username');
            $this->objAuthAdapter->setCredentialColumn('password');
            $this->objAuthAdapter->getDbSelect()->where('customers.idCustomerStatus = ?', $this->core->sysConfig->customerstatus->active);
        }

        $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/scripts');
    }

    /**
     * loginAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loginAction()
    {
        $this->core->logger->debug('website->controllers->customerController->loginAction()');

        //Redirect to given URL if already signed in
        $strRedirectUrl = $this->getRedirectUrl();
        $this->view->redirectUrl = $strRedirectUrl;

        if (!$this->objAuth->hasIdentity()) {
            $this->initPageView();

            $strUsername = $this->getRequest()->getParam('username');
            $strPassword = md5($this->getRequest()->getParam('password'));

            $objCustomerHelper = Zend_Registry::get('CustomerHelper');
            $objCustomerHelper->setMetaTitle('Login');

            if ($strUsername != '' && $strUsername != null) {
                $this->objAuthAdapter->setIdentity($strUsername);
                $this->objAuthAdapter->setCredential($strPassword);
                $objResult = $this->objAuth->authenticate($this->objAuthAdapter);

                $this->view->username = $strUsername;
                $this->view->redirectUrl = $strRedirectUrl;

                switch ($objResult->getCode()) {
                    case Zend_Auth_Result::SUCCESS:
                        if (ClientHelper::get('Authentication')->isActive()) {
                            $objUserData = ClientHelper::get('Authentication')->getUserData();
                            $objCustomerRoleProvider = ClientHelper::get('Authentication')->getUserRoleProvider();
                        } else {
                            $objUserData = $this->objAuthAdapter->getResultRowObject();

                            $objCustomerRoleProvider = new RoleProvider();
                            $arrCustomerGroups = $this->getModelCustomers()->loadGroups($objUserData->id);
                            if (count($arrCustomerGroups) > 0) {
                                foreach ($arrCustomerGroups as $objCustomerGroup) {
                                    $objCustomerRoleProvider->addRole(new Zend_Acl_Role($objCustomerGroup->key), $objCustomerGroup->key);
                                }
                            }
                        }

                        //Set Security
                        $objSecurity = new Security();
                        $objSecurity->setRoleProvider($objCustomerRoleProvider);
                        $objSecurity->buildAcl($this->getModelUsers());
                        Security::save($objSecurity);

                        //Write to session and redirect
                        $this->objAuth->getStorage()->write($objUserData);
                        $this->redirect($strRedirectUrl);
                        break;
                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                        $this->view->errUsername = $this->core->translate->_('Username_not_found');
                        break;
                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        $this->view->errPassword = $this->core->translate->_('Wrong_password');
                        break;
                }
            }
        } else {
            $this->redirect($strRedirectUrl);
        }
    }

    /**
     * Action for resetting the customer password
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function resetAction()
    {
        $this->core->logger->debug('website->controllers->customerController->resetAction()');

        $this->initPageView();
        $strEmail = $this->getRequest()->getParam('email', '');
        $strKey = $this->getRequest()->getParam('key', '');
        $strPassword = $this->getRequest()->getParam('password', '');

        $objCustomerHelper = Zend_Registry::get('CustomerHelper');
        $objCustomerHelper->setMetaTitle('Reset Password');

        if ($strKey == '') {
            if ($strEmail != '') {
                //send email and show confirmation
                $objCustomers = $this->getModelCustomers()->loadByEmail($strEmail);
                if (count($objCustomers) > 0) {
                    $objCustomer = $objCustomers->current();
                    $strKey = uniqid('', true);

                    $this->getModelCustomers()->edit(array('resetPasswordKey' => $strKey), $objCustomer->id);

                    $this->view->display = 'confirmation';
                }
            }
        } else {
            if ($strPassword != '') {
                //set the new password, if the key matches the key in the database
                $objCustomers = $this->getModelCustomers()->loadByResetPasswordKey($strKey);
                if (count($objCustomers) > 0) {
                    $objCustomer = $objCustomers->current();
                    $this->getModelCustomers()->edit(array('resetPasswordKey' => null, 'password' => md5($strPassword)), $objCustomer->id);
                }
                $this->view->display = 'changeConfirmation';
            } else {
                //Show change password formular
                $this->view->key = $strKey;
                $this->view->display = 'changePassword';
            }
        }
    }

    /**
     * getRedirectUrl
     * @return string
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    private function getRedirectUrl()
    {
        $strRedirectUrl = '/';
        if ($this->getRequest()->getParam('re')) {
            $strRedirectUrl = $this->getRequest()->getParam('re');
            return $strRedirectUrl;
        }
        return $strRedirectUrl;
    }

    /**
     * logoutAction
     * @author Daniel Rotter <daniel.rotter@massvieat.com>
     * @version 1.0
     */
    public function logoutAction()
    {
        $this->core->logger->debug('website->controllers->customerController->logoutAction()');

        $strRedirectUrl = $this->getRedirectUrl();

        $this->objAuth->clearIdentity();
        $this->redirect($strRedirectUrl);
    }

    /**
     * registerAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function registerAction()
    {
        $this->core->logger->debug('website->controllers->customerController->registerAction()');

        $this->initPageView();

        //Redirect to given URL if already signed in
        $strRedirectUrl = $this->getRedirectUrl();
        $this->view->redirectUrl = $strRedirectUrl;

        if ($this->getRequest()->isPost() || $this->getRequest()->getParam('key', '') != '') {
            $strUsername = $this->getRequest()->getParam('username', '');

            //Validate the data
            $objMailValidator = new Zend_Validate_EmailAddress();
            $objCustomers = $this->getModelCustomers()->loadByUsername($strUsername);

            $blnRequiredEmail = $this->getRequest()->getParam('email', '') != '';
            $blnRequiredUsername = $strUsername != '';
            $blnRequiredPassword = $this->getRequest()->getParam('password', '') != '';
            $blnValidPassword = $this->getRequest()->getParam('password') == $this->getRequest()->getParam('passwordConfirm');
            $blnValidEmail = $objMailValidator->isValid($this->getRequest()->getParam('email'));
            $blnUniqueUsername = count($objCustomers) == 0;
            $blnKeySet = $this->getRequest()->getParam('key', '') != '';
            $blnValid = (
                $blnRequiredEmail && $blnRequiredUsername && $blnRequiredPassword && $blnValidPassword && $blnValidEmail && $blnUniqueUsername
            ) || $blnKeySet;
            if ($blnValid) {
                $objRootLevel = $this->getModelRootLevels()->loadRootLevelById($this->objTheme->idRootLevels)->current();
                $strRegisterStrategy = 'RegistrationStrategy' . $objRootLevel->registrationStrategy;

                //Load correct strategy and start the registration process
                if (file_exists(GLOBAL_ROOT_PATH . 'client/website/customer/' . $strRegisterStrategy . '.php')) {
                    require_once(GLOBAL_ROOT_PATH . 'client/website/customer/' . $strRegisterStrategy . '.php');
                } elseif (file_exists(GLOBAL_ROOT_PATH . 'library/massiveart/website/customer/' . $strRegisterStrategy . '.php')) {
                    require_once(GLOBAL_ROOT_PATH . 'library/massiveart/website/customer/' . $strRegisterStrategy . '.php');
                } else {
                    throw new Exception('RegisterStrategy with name "' . $strRegisterStrategy . '" not found!');
                }
                $objRegisterStrategy = new $strRegisterStrategy($this->getRequest(), $this->objTheme);
                $this->view->display = $objRegisterStrategy->register($this->getUrl($this->getRequest()->getParam('re')));
                $this->view->redirect = $this->getRequest()->getParam('re', '/');
            } else {
                //Reassign field values
                $this->view->fname = $this->getRequest()->getParam('fname');
                $this->view->sname = $this->getRequest()->getParam('sname');
                $this->view->email = $this->getRequest()->getParam('email');
                $this->view->username = $strUsername;

                //Show validation errors
                if (!$blnRequiredEmail) {
                    $this->view->errEmail = $this->core->translate->_('Email_mandatory');
                }
                if (!$blnRequiredUsername) {
                    $this->view->errUsername = $this->core->translate->_('Username_mandatory');
                }
                if (!$blnUniqueUsername) {
                    $this->view->errUsername = $this->core->translate->_('Username_already_exists');
                }
                if (!$blnRequiredPassword) {
                    $this->view->errPassword = $this->core->translate->_('Password_mandatory');
                }
                if (!$blnValidPassword) {
                    $this->view->errPasswordConfirm = $this->core->translate->_('Password_confirm_wrong');
                }
                if (!$blnValidEmail && $blnRequiredEmail) {
                    $this->view->errEmail = $this->core->translate->_('Email_invalid');
                }
            }
        }
    }

    /**
     * initPageView
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    private function initPageView()
    {
        Zend_Layout::startMvc(array(
            'layout' => 'master',
            'layoutPath' => GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path
        ));
        Zend_Layout::getMvcInstance()->setViewSuffix('php');

        $this->setTranslate();

        $this->initNavigation();

        // Initialize CommunityHelper
        if (file_exists(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/CustomerHelper.php')) {
            require_once(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/CustomerHelper.php');
            $strCommunityHelper = ucfirst($this->objTheme->path) . '_CustomerHelper';
            $objCustomerHelper = new $strCommunityHelper();
        } else {
            require_once(dirname(__FILE__) . '/../helpers/CustomerHelper.php');
            $objCustomerHelper = new CustomerHelper();
        }

        Zend_Registry::set('CustomerHelper', $objCustomerHelper);

        Zend_Registry::set('TemplateCss', '');
        Zend_Registry::set('TemplateJs', '');
    }

    /**
     * getModelCustomers
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     * @return Model_Customers
     */
    protected
    function getModelCustomers()
    {
        if (null === $this->objModelCustomers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/Customers.php';
            $this->objModelCustomers = new Model_Customers();
        }

        return $this->objModelCustomers;
    }

    /**
     * getModelUsers
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public
    function getModelUsers()
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
     * getModelRootLevels
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     * @return Model_RootLevels
     */
    protected function getModelRootLevels()
    {
        if (null === $this->objModelRootLevels) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/RootLevels.php';
            $this->objModelRootLevels = new Model_RootLevels();
        }

        return $this->objModelRootLevels;
    }
}

?>