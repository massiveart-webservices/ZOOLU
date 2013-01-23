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
        $this->validateLanguage();

        $this->setTranslate();

        
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
            $objCustomerHelper->setMetaTitle($this->translate->_('Login',false));

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
                        $this->_redirect($strRedirectUrl);
                        break;
                    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                        $this->view->errUsername = $this->translate->_('Username_not_found', false);
                        break;
                    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                        $this->view->errPassword = $this->translate->_('Wrong_password', false);
                        break;
                }
            }
        } else {
            $this->_redirect($strRedirectUrl);
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

        $objMailValidator = new Zend_Validate_EmailAddress();

        $this->initPageView();
        $strEmail = $this->getRequest()->getParam('email', '');
        $strKey = $this->getRequest()->getParam('key', '');
        $strPassword = $this->getRequest()->getParam('password', null);
        $strPasswordConfirmation = $this->getRequest()->getParam('passwordConfirmation', null);

        $blnValidEmail = $objMailValidator->isValid($strEmail);
        $blnPasswordMatch = $strPassword == $strPasswordConfirmation;

        $objCustomerHelper = Zend_Registry::get('CustomerHelper');
        $objCustomerHelper->setMetaTitle($this->translate->_('Reset_password', false));

        if ($strKey == '') {
            if ($blnValidEmail) {
                //send email and show confirmation
                $objCustomers = $this->getModelCustomers()->loadByEmail($strEmail);
                if (count($objCustomers) > 0) {
                    $objCustomer = $objCustomers->current();
                    $strKey = uniqid('', true);

                    //insert generated key and inform user via email
                    $this->getModelCustomers()->edit(array('resetPasswordKey' => $strKey), $objCustomer->id);
                    $this->sendResetPasswordMail($objCustomer, $strKey);

                    $this->view->display = 'confirmation';
                }
            } elseif($strEmail != '') {
                $this->view->errEmail = $this->translate->_('Email_invalid', false);
            }
        } else {
            if ($strPassword != null && $blnPasswordMatch) {
                //set the new password, if the key matches the key in the database
                $objCustomers = $this->getModelCustomers()->loadByResetPasswordKey($strKey);
                if (count($objCustomers) > 0) {
                    $objCustomer = $objCustomers->current();
                    $this->getModelCustomers()->edit(array('resetPasswordKey' => null, 'password' => md5($strPassword)), $objCustomer->id);
                }
                $this->view->display = 'changeConfirmation';
            } elseif (!$blnPasswordMatch) {
                $this->view->key = $strKey;
                $this->view->errPassword = $this->translate->_('Password_confirm_wrong', false);
                $this->view->display = 'changePassword';
            } else {
                //Show change password formular
                $this->view->key = $strKey;
                $this->view->display = 'changePassword';
            }
        }
    }

    /**
     * Sends the email with the key for resetting the password
     * @param $objCustomer
     * @param $strKey
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    private function sendResetPasswordMail($objCustomer, $strKey)
    {
        $objMail = new Zend_Mail('utf-8');

        $objTransport = null;
        if (!empty($this->core->config->mail->params->host)) {
            // config for SMTP with auth
            $arrConfig = array('auth' => 'login',
                'username' => $this->core->config->mail->params->username,
                'password' => $this->core->config->mail->params->password);

            // smtp
            $objTransport = new Zend_Mail_Transport_Smtp($this->core->config->mail->params->host, $arrConfig);
        }

        // set mail subject
        $objMail->setSubject($this->translate->_('Registration'), false);

        $strUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/reset?key=' . $strKey;

        $objView = new Zend_View();
        $objView->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->getTheme()->path . '/scripts/');
        $objView->url = $strUrl;
        $strBody = $objView->render('customer/passwordChangeMail.phtml');

        // set body
        $objMail->setBodyHtml($strBody);

        // set mail from address
        $objMail->setFrom($this->core->config->mail->from->address, $this->core->config->mail->from->name);

        // add to address
        $objMail->addTo($objCustomer->email, $objCustomer->fname . ' ' . $objCustomer->sname);

        //set header for sending mail
        $objMail->addHeader('Sender', $this->core->config->mail->params->username);

        // send mail now
        if ($this->core->config->mail->transport == 'smtp') {
            $objMail->send($objTransport);
        } else {
            $objMail->send();
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
        $this->_redirect($strRedirectUrl);
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

        $objCustomerHelper = Zend_Registry::get('CustomerHelper');
        $objCustomerHelper->setMetaTitle($this->translate->_('Registration'), false);

        if ($this->getRequest()->isPost() || $this->getRequest()->getParam('key', '') != '') {
            $strUsername = $this->getRequest()->getParam('username', '');
            $strEmail = $this->getRequest()->getParam('email', '');

            //Validate the data
            $objMailValidator = new Zend_Validate_EmailAddress();
            $objCustomersUsername = $this->getModelCustomers()->loadByUsername($strUsername);
            $objCustomersEmail = $this->getModelCustomers()->loadByEmail($strEmail);
            $blnRequiredSalutation = $this->getRequest()->getParam('customerSalutation', '') != '';
            $blnRequiredFName = $this->getRequest()->getParam('fname', '') != '';
            $blnRequiredSName = $this->getRequest()->getParam('sname', '') != '';
            
            $blnRequiredEmail = $this->getRequest()->getParam('email', '') != '';
            $blnRequiredUsername = $strUsername != '';
            $blnRequiredPassword = $this->getRequest()->getParam('password', '') != '';
            $blnValidPassword = $this->getRequest()->getParam('password') == $this->getRequest()->getParam('passwordConfirm');
            $blnValidEmail = $objMailValidator->isValid($strEmail);
            $blnUniqueUsername = count($objCustomersUsername) == 0;
            $blnUniqueEmail = count($objCustomersEmail) == 0;
            $blnKeySet = $this->getRequest()->getParam('key', '') != '';
            $blnValid = (
                $blnRequiredSalutation && $blnRequiredFName && $blnRequiredSName && $blnRequiredEmail && $blnRequiredUsername && $blnRequiredPassword && $blnValidPassword && $blnValidEmail && $blnUniqueUsername && $blnUniqueEmail
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
                $this->view->street = $this->getRequest()->getParam('street');
                $this->view->zip = $this->getRequest()->getParam('zip');
                $this->view->city = $this->getRequest()->getParam('city');
                $this->view->country = $this->getRequest()->getParam('country');
                $this->view->email = $this->getRequest()->getParam('email');
                $this->view->username = $strUsername;

                //Show validation errors
                 if (!$blnRequiredFName) {
                    $this->view->errFname = $this->translate->_('Fname_mandatory', false);
                }
                if (!$blnRequiredSName) {
                    $this->view->errSname = $this->translate->_('Sname_mandatory', false);
                }
                if (!$blnRequiredSalutation) {
                    $this->view->errcustomerSalutation = $this->translate->_('Salutation_mandatory', false);
                }
                if (!$blnRequiredEmail) {
                    $this->view->errEmail = $this->translate->_('Email_mandatory', false);
                }
                if (!$blnRequiredUsername) {
                    $this->view->errUsername = $this->translate->_('Username_mandatory', false);
                }
                if (!$blnUniqueUsername) {
                    $this->view->errUsername = $this->translate->_('Username_already_exists', false);
                }
                if (!$blnRequiredPassword) {
                    $this->view->errPassword = $this->translate->_('Password_mandatory', false);
                }
                if (!$blnValidPassword) {
                    $this->view->errPasswordConfirm = $this->translate->_('Password_confirm_wrong', false);
                }
                if (!$blnValidEmail && $blnRequiredEmail) {
                    $this->view->errEmail = $this->translate->_('Email_invalid', false);
                }
                if (!$blnUniqueEmail) {
                    $this->view->errEmail = $this->translate->_('Email_not_unique', false);
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
    protected function getModelCustomers()
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