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

    public function init()
    {
        parent::init();

        //Initialize Authentication
        $this->objAuth = Zend_Auth::getInstance();
        $this->objAuth->setStorage(new Zend_Auth_Storage_Session(self::STORAGE_NAME));

        //Initialize Authentication Adapter
        $this->objAuthAdapter = new Zend_Auth_Adapter_DbTable($this->core->dbh);
        $this->objAuthAdapter->setTableName('customers');
        $this->objAuthAdapter->setIdentityColumn('username');
        $this->objAuthAdapter->setCredentialColumn('password');

        $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->objTheme->path.'/scripts');
    }

    public function loginAction()
    {
        $this->core->logger->debug('website->controllers->customerController->loginAction()');

        $this->view->addFilter('PageReplacer');

        $this->initPageView();

        $strUsername = $this->getRequest()->getParam('username');
        $strPassword = $this->getRequest()->getParam('password');

        //Redirect to given URL if already signed in
        $strRedirectUrl = '/';
        if ($this->getRequest()->getParam('re')) {
            $strRedirectUrl = $this->getRequest()->getParam('re');
        }
        if ($this->objAuth->hasIdentity()) {
            $this->redirect($strRedirectUrl);
        }

        if ($strUsername != '' && $strUsername != null) {
            $this->objAuthAdapter->setIdentity($strUsername);
            $this->objAuthAdapter->setCredential($strPassword);
            $objResult = $this->objAuth->authenticate($this->objAuthAdapter);

            switch ($objResult->getCode()) {
                case Zend_Auth_Result::SUCCESS:
                    $objUserData = $this->objAuthAdapter->getResultRowObject();
                    $this->objAuth->getStorage()->write($objUserData);
                    break;
            }
        }
    }

    public function logoutAction()
    {
        //TODO Implement
    }

    public function registerAction()
    {
        //TODO Implement
    }

    private function initPageView()
    {
        Zend_Layout::startMvc(array(
            'layout' => 'master',
            'layoutPath' => GLOBAL_ROOT_PATH.'public/website/themes/'.$this->objTheme->path
        ));
        Zend_Layout::getMvcInstance()->setViewSuffix('php');

        $this->setTranslate();

        $this->initNavigation();

        // Initialize CommunityHelper
        if(file_exists(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->objTheme->path.'/helpers/CustomerHelper.php')) {
            require_once(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->objTheme->path.'/helpers/CustomerHelper.php');
            $strCommunityHelper = ucfirst($this->objTheme->path).'_CustomerHelper';
            $objCommunityHelper = new $strCommunityHelper();
        } else {
            require_once(dirname(__FILE__).'/../helpers/CustomerHelper.php');
            $objCommunityHelper = new CustomerHelper();
        }

        Zend_Registry::set('CustomerHelper', $objCommunityHelper);

        Zend_Registry::set('TemplateCss', '');
        Zend_Registry::set('TemplateJs', '');
    }
}

?>