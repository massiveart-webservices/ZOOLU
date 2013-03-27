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
 * @package    application.zoolu.modules.global.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Contacts_NavigationController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-05: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Contacts_NavigationController extends AuthControllerAction
{

    private $intRootLevelId;
    private $intFolderId;

    private $intParentId;
    private $intParentTypeId;

    private $intLanguageId;

    /**
     * @var Model_Contacts
     */
    protected $objModelContacts;

    /**
     * @var Model_Members
     */
    protected $objModelMembers;

    /**
     * @var Model_Locations
     */
    protected $objModelLocations;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * init
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        Security::get()->addFoldersToAcl($this->getModelFolders());
        Security::get()->addRootLevelsToAcl($this->getModelFolders(), $this->core->sysConfig->modules->contacts);
    }

    /**
     * indexAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {
        $this->getModelFolders();
        $objRootLevels = $this->objModelFolders->loadAllRootLevelsWithGroups($this->core->sysConfig->modules->contacts);

        $objRootLevelNavigation = new NavigationTree();
        if (count($objRootLevels) > 0) {
            $intOrder = 0;
            foreach ($objRootLevels as $objRootLevel) {
                $intOrder++;

                if (!$objRootLevelNavigation->hasSubTree($objRootLevel->name)) {
                    $objNavGroup = new NavigationTree();
                    $objNavGroup->setId($objRootLevel->idRootLevelGroups);
                    $objNavGroup->setItemId($objRootLevel->name);
                    $objNavGroup->setTypeId($objRootLevel->idRootLevelGroups);
                    $objNavGroup->setTitle($objRootLevel->rootLevelGroupTitle);
                    $objNavGroup->setUrl($objRootLevel->href);
                    $objNavGroup->setLanguageId(((int) $objRootLevel->rootLevelGuiLanguageId > 0 ? $objRootLevel->rootLevelGuiLanguageId : $objRootLevel->rootLevelLanguageId));

                    $objRootLevelNavigation->addTree($objNavGroup, $objRootLevel->name);
                }

                $objNavItem = new NavigationItem();
                $objNavItem->setId($objRootLevel->id);
                $objNavItem->setItemId($objRootLevel->name);
                $objNavItem->setTypeId($objRootLevel->idRootLevelTypes);
                $objNavItem->setTitle($objRootLevel->title);
                $objNavItem->setUrl($objRootLevel->href);
                $objNavItem->setOrder($intOrder);
                $objNavItem->setParentId($objRootLevel->idRootLevelGroups);
                $objNavItem->setLanguageId(((int) $objRootLevel->rootLevelGuiLanguageId > 0 ? $objRootLevel->rootLevelGuiLanguageId : $objRootLevel->rootLevelLanguageId));

                $objRootLevelNavigation->addToParentTree($objNavItem, $objRootLevel->name . '_' . $objRootLevel->id);
            }
        }

        $this->view->assign('rootLevelNavigation', $objRootLevelNavigation);
        $this->view->assign('rootLevelId', $this->getRequest()->getParam('rootLevelId'));
        $this->view->assign('rootLevelGroupId', $this->getRequest()->getParam('rootLevelGroupId'));
        $this->view->assign('rootLevelGroupKey', $this->getRequest()->getParam('rootLevelGroupKey'));
        $this->view->assign('rootLevelType', $this->getRequest()->getParam('rootLevelType'));

        $strRenderScript = ($this->getRequest()->getParam('layoutType') == 'list') ? 'list.phtml' : 'tree.phtml';
        $this->renderScript('navigation/' . $strRenderScript);
    }

    /**
     * rootnavigationAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function rootnavigationAction()
    {
        $this->core->logger->debug('contacts->controllers->NavigationController->rootnavigationAction()');

        $objRequest = $this->getRequest();
        $intCurrLevel = $objRequest->getParam('currLevel');
        $this->setRootLevelId($objRequest->getParam('rootLevelId'));
        $intRootLevelTypeId = $objRequest->getParam('rootLevelTypeId');
        $intRootLevelGroupId = $objRequest->getParam('rootLevelGroupId');
        $strRootLevelGroupKey = $objRequest->getParam('rootLevelGroupKey');

        $strRenderScript = '';

        if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->intRootLevelId, Security::PRIVILEGE_VIEW, true, false)) {

            $objRootelements = new stdClass();
            if ($intRootLevelTypeId != '' && $intRootLevelTypeId > 0) {

                switch ($intRootLevelTypeId) {
                    case $this->core->sysConfig->root_level_types->contacts:
                    case $this->core->sysConfig->root_level_types->contactcompanies:
                        /**
                         * get contacts root navigation
                         */
                        $this->getModelContacts();
                        $objRootelements = $this->objModelContacts->loadNavigation($this->intRootLevelId, 0);
                        $strRenderScript = 'contactnavigation.phtml';
                        break;
                    case $this->core->sysConfig->root_level_types->locations:
                        /**
                         * get locations root navigation
                         */
                        $this->getModelLocations();
                        $objRootelements = $this->objModelLocations->loadNavigation($this->intRootLevelId, 0, true);
                        $strRenderScript = 'locationnavigation.phtml';
                        break;
                    case $this->core->sysConfig->root_level_types->members:
                        /**
                         * get members root navigation
                         */
                        $this->getModelMembers();
                        $objRootelements = $this->objModelMembers->loadNavigation($this->intRootLevelId, 0);
                        $strRenderScript = 'membernavigation.phtml';
                        break;
                }
            }

            $this->view->assign('rootelements', $objRootelements);
            $this->view->assign('currLevel', $intCurrLevel);
            $this->view->assign('rootLevelId', $this->intRootLevelId);
            $this->view->assign('rootLevelTypeId', $intRootLevelTypeId);
            $this->view->assign('unitFormDefaultId', $this->core->sysConfig->form->ids->units->default);
            $this->view->assign('contactFormDefaultId', $this->core->sysConfig->form->ids->contacts->default);
            $this->view->assign('return', true);

            if ($strRenderScript != '') {
                $this->renderScript('navigation/' . $strRenderScript);
            }
        } else {
            $this->view->assign('return', false);
        }
    }

    /**
     * contactnavigationAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function contactnavigationAction()
    {
        $this->core->logger->debug('contacts->controllers->NavigationController->contactnavigationAction()');

        $objRequest = $this->getRequest();
        $intCurrLevel = $objRequest->getParam('currLevel');
        $intRootLevelId = $objRequest->getParam('rootLevelId');

        if ($intCurrLevel == 1) {
            $intItemId = 0;
        } else {
            $intItemId = $objRequest->getParam('itemId');
        }

        /**
         * get navigation
         */
        $this->getModelContacts();
        $objContactNavElements = $this->objModelContacts->loadNavigation($intRootLevelId, $intItemId);

        $this->view->assign('elements', $objContactNavElements);
        $this->view->assign('currLevel', $intCurrLevel);
        $this->view->assign('rootLevelId', $intRootLevelId);
        $this->view->assign('rootLevelTypeId', $this->core->sysConfig->root_level_types->contacts);
        $this->view->assign('unitFormDefaultId', $this->core->sysConfig->form->ids->units->default);
        $this->view->assign('contactFormDefaultId', $this->core->sysConfig->form->ids->contacts->default);
    }

    /**
     * locationnavigationAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function locationnavigationAction()
    {
        $this->core->logger->debug('contacts->controllers->NavigationController->locationnavigationAction()');

        $objRequest = $this->getRequest();
        $intCurrLevel = $objRequest->getParam('currLevel');
        $intRootLevelId = $objRequest->getParam('rootLevelId');

        if ($intCurrLevel == 1) {
            $intItemId = 0;
        } else {
            $intItemId = $objRequest->getParam('itemId');
        }

        /**
         * get navigation
         */
        $this->getModelLocations();
        $objLocationNavElements = $this->objModelLocations->loadNavigation($intRootLevelId, $intItemId, true);

        $this->view->assign('elements', $objLocationNavElements);
        $this->view->assign('currLevel', $intCurrLevel);
        $this->view->assign('rootLevelId', $intRootLevelId);
        $this->view->assign('rootLevelTypeId', $this->core->sysConfig->root_level_types->locations);
        $this->view->assign('unitFormDefaultId', $this->core->sysConfig->form->ids->units->default);
        $this->view->assign('locationFormDefaultId', $this->core->sysConfig->form->ids->locations->default);
        $this->view->assign('itemId', $intItemId);
    }

    /**
     * getModelContacts
     * @return Model_Contacts
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelContacts()
    {
        if (null === $this->objModelContacts) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Contacts.php';
            $this->objModelContacts = new Model_Contacts();
            $this->objModelContacts->setLanguageId(1);
        }

        return $this->objModelContacts;
    }

    /**
     * getModelMembers
     * @return Model_Members
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelMembers()
    {
        if (null === $this->objModelMembers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Members.php';
            $this->objModelMembers = new Model_Members();
            $this->objModelMembers->setLanguageId(1);
        }

        return $this->objModelMembers;
    }

    /**
     * getModelLocations
     * @return Model_Locations
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelLocations()
    {
        if (null === $this->objModelLocations) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Locations.php';
            $this->objModelLocations = new Model_Locations();
            $this->objModelLocations->setLanguageId(1);
        }

        return $this->objModelLocations;
    }

    /**
     * getModelFolders
     * @return Model_Folders
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelFolders()
    {
        if (null === $this->objModelFolders) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Folders.php';
            $this->objModelFolders = new Model_Folders();
            $this->objModelFolders->setLanguageId($this->core->intZooluLanguageId);
            $this->objModelFolders->setContentLanguageId(Zend_Auth::getInstance()->getIdentity()->contentLanguageId);
        }

        return $this->objModelFolders;
    }

    /**
     * setRootLevelId
     * @param integer $intRootLevelId
     */
    public function setRootLevelId($intRootLevelId)
    {
        $this->intRootLevelId = $intRootLevelId;
    }

    /**
     * getRootLevelId
     * @param integer $intRootLevelId
     */
    public function getRootLevelId()
    {
        return $this->intRootLevelId;
    }

    /**
     * setFolderId
     * @param integer $intFolderId
     */
    public function setFolderId($intFolderId)
    {
        $this->intFolderId = $intFolderId;
    }

    /**
     * getFolderId
     * @param integer $intFolderId
     */
    public function getFolderId()
    {
        return $this->intFolderId;
    }

    /**
     * setParentId
     * @param integer $intParentId
     */
    public function setParentId($intParentId)
    {
        $this->intParentId = $intParentId;
    }

    /**
     * getParentId
     * @param integer $intParentId
     */
    public function getParentId()
    {
        return $this->intParentId;
    }

    /**
     * setParentTypeId
     * @param integer $intParentTypeId
     */
    public function setParentTypeId($intParentTypeId)
    {
        $this->intParentTypeId = $intParentTypeId;
    }

    /**
     * getParentTypeId
     * @param integer $intParentTypeId
     */
    public function getParentTypeId()
    {
        return $this->intParentTypeId;
    }

    /**
     * setLanguageId
     * @param integer $intLanguageId
     */
    public function setLanguageId($intLanguageId)
    {
        $this->intLanguageId = $intLanguageId;
    }

    /**
     * getLanguageId
     * @param integer $intLanguageId
     */
    public function getLanguageId()
    {
        return $this->intLanguageId;
    }
}

?>
