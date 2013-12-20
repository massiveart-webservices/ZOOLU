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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * SubscriberController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2010-04-15: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
class SubscriberController extends WebControllerAction {

    const INTEREST_GROUPS_ID = 615;
    
    /**
     * @var Core
     */
    protected $core;
    
    /**
     * @var Model_Subscribers
     */
    private $objModelSubscribers;
    
    /**
     * @var Model_Categories
     */
    public $objModelCategories;
    
    /**
     * preDispatch
     * Called before action method.
     * 
     * @return void  
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function preDispatch() {
        $this->core = Zend_Registry::get('Core');
        $this->request = $this->getRequest();
    }
    
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

        $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/scripts');
    }
    
    /**
     * initPageView
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
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
        if (file_exists(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/SubscriberHelper.php')) {
            require_once(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/SubscriberHelper.php');
            $strSubscriberHelper = ucfirst($this->objTheme->path) . '_SubscriberHelper';
            $objSubscriberHelper = new $strSubscriberHelper();
        } else {
            require_once(dirname(__FILE__) . '/../helpers/SubscriberHelper.php');
            $objSubscriberHelper = new SubscriberHelper();
        }

        Zend_Registry::set('SubscriberHelper', $objSubscriberHelper);

        Zend_Registry::set('TemplateCss', '');
        Zend_Registry::set('TemplateJs', '');
    }

    /**
     * indexAction
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     */
    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender();
        echo 'index';
    }
    
    /**
     * subscribeAction
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     */
    public function subscribeAction() {
        $this->loadTheme();
        $this->initPageView();
        $objSubscriberHelper = Zend_Registry::get('SubscriberHelper');
        $objSubscriberHelper->setMetaTitle($this->translate->_('Newsletter_subscribe'), false);
        $this->getModelCategories()->setLanguageId($this->core->intLanguageId);
        $interestGroupsData = $this->getModelCategories()->loadCategoryTree(self::INTEREST_GROUPS_ID);
        $objSubscriberHelper->setInterestGroup($interestGroupsData);
    }
    
    /**
     * unsubscribeAction
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     */
    public function unsubscribeAction() {
        $this->loadTheme();
        $this->initPageView();
        
        $objCustomerHelper = Zend_Registry::get('SubscriberHelper');
        $objCustomerHelper->setMetaTitle($this->translate->_('Newsletter_unsubscribe'), false);
    }
    
    /**
     * getModelSubscribers
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelSubscribers()
    {
        if (null === $this->objModelSubscribers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/Subscribers.php';
            $this->objModelSubscribers = new Model_Subscribers();
            $this->objModelSubscribers->setLanguageId($this->core->intZooluLanguageId);
        }
        return $this->objModelSubscribers;
    }
    
    /**
     * getModelCategories
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelCategories()
    {
        if (null === $this->objModelCategories) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Categories.php';
            $this->objModelCategories = new Model_Categories();
        }

        return $this->objModelCategories;
    }
}
?>