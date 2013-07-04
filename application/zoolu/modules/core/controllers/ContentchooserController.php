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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ContentchooserController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-21: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
class Core_ContentchooserController extends AuthControllerAction
{
    /**
     * @var Zend_Controller_Request_Abstract
     */
    private $objRequest;

    /**
     * @var Model_Modules
     */
    protected $objModelModules;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * @var Model_Pages
     */
    protected $objModelPages;

    /**
     * @var Model_Globals
     */
    protected $objModelGlobals;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    /**
     * @var integer
     */
    protected $intItemLanguageId;

    /**
     * init
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->objRequest = $this->getRequest();
    }

    /**
     * overlayModulesAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function overlayModulesAction()
    {
        $this->core->logger->debug('core->controllers->ContentchooserController->overlayModulesAction()');
        try {
            $arrSelectedIds = array();

            $strRelationIds = $this->objRequest->getParam('relationIds');
            if ($strRelationIds != '') {
                $strTmpRelationIds = trim($strRelationIds, '[]');
                $arrSelectedIds = explode('][', $strTmpRelationIds);
            }

            $objModules = $this->getModelModules()->getModules();

            $this->view->assign('elements', $objModules);
            $this->view->assign('overlaytitle', $this->core->translate->_('Choose_module'));
            $this->view->assign('translate', $this->core->translate);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * overlayRootlevelsAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function overlayRootlevelsAction()
    {
        $this->core->logger->debug('core->controllers->ContentchooserController->overlayRootlevelsAction()');
        try {
            $intModuleId = $this->objRequest->getParam('moduleId');
            $objRootLevels = $this->getModelRootLevels()->loadRootLevelsByModuleId($intModuleId);

            $this->view->assign('elements', $objRootLevels);
            $this->view->assign('overlaytitle', $this->core->translate->_('Choose_main_area'));
            $this->view->assign('translate', $this->core->translate);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * overlayContentAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function overlayContentAction()
    {
        $this->core->logger->debug('core->controllers->ContentchooserController->overlayContentAction()');
        try {
            $intModuleId = $this->objRequest->getParam('moduleId');
            $intRootLevelId = $this->objRequest->getParam('rootLevelId');
            $intRootLevelTypeId = $this->objRequest->getParam('rootLevelTypeId');
            $intRootLevelGroupId = $this->objRequest->getParam('rootLevelGroupId');

            $objRootLevelElements = $this->getModelFolders()->loadRootFolders($intRootLevelId);
            $this->view->assign('elements', $objRootLevelElements);

            $this->view->assign('viewtype', $this->core->sysConfig->viewtypes->list);
            $this->view->assign('moduleId', $intModuleId);
            $this->view->assign('rootLevelId', $intRootLevelId);
            $this->view->assign('type', $this->objRequest->getParam('type'));

            $this->view->assign('rootLevelTypeId', $intRootLevelTypeId);
            $this->view->assign('rootLevelGroupId', $intRootLevelGroupId);

            switch ($intModuleId) {
                case $this->core->sysConfig->modules->global:
                    $this->view->assign('contenttype', 'global');
                    break;
                case $this->core->sysConfig->modules->media:
                    $this->view->assign('contenttype', 'media');
                    break;
                default:
                    $this->view->assign('contenttype', 'page');
                    break;
            }
            $this->view->assign('overlaytitle', $this->core->translate->_('Choose_content'));
            $this->view->assign('translate', $this->core->translate);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * overlayChildnavigationAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function overlayChildnavigationAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->overlayChildnavigationAction()');
        try {
            $intFolderId = $this->objRequest->getParam('folderId');
            $intViewType = $this->objRequest->getParam('viewtype');
            $intLanguageId = $this->objRequest->getParam('languageId');
            $strContentType = $this->objRequest->getParam('contenttype');
            $intRootLevelTypeId = $this->objRequest->getParam('rootLevelTypeId', '');
            $intRootLevelGroupId = $this->objRequest->getParam('rootLevelGroupId', '');

            /**
             * get childfolders
             */
            $objChildelements = $this->getModelFolders()->loadChildFolders($intFolderId);

            $this->view->assign('elements', $objChildelements);
            $this->view->assign('intFolderId', $intFolderId);
            $this->view->assign('viewtype', $intViewType);
            $this->view->assign('contenttype', $strContentType);
            $this->view->assign('rootLevelTypeId', $intRootLevelTypeId);
            $this->view->assign('rootLevelGroupId', $intRootLevelGroupId);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * overlayListAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function overlayListAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->overlayListAction()');
        try {
            $intFolderId = $this->getRequest()->getParam('folderId');
            $strRelation = $this->getRequest()->getParam('relation', '');
            $strContentType = $this->getRequest()->getParam('contenttype', '');
            $intRootLevelTypeId = $this->objRequest->getParam('rootLevelTypeId', '');
            $intRootLevelGroupId = $this->objRequest->getParam('rootLevelGroupId', '');

            if ($strContentType != '') {
                $objRelation = new stdClass();
                if ($strRelation != '') {
                    $objRelation = json_decode($strRelation);
                }

                $objElements = '';
                switch ($strContentType) {
                    case 'global':
                        $objElements = $this->getModelGlobals()->loadGlobalsByFilter($intFolderId, null, $intRootLevelGroupId);
                        break;
                    case 'page':
                        $objElements = $this->getModelPages()->loadPagesByfilter($intFolderId);
                        break;
                    case 'media':
                        $objElements = $this->getModelFiles()->loadFiles($intFolderId);
                        break;
                }

                $this->view->assign('elements', $objElements);
                $this->view->assign('relation', $objRelation);
                $this->view->assign('contenttype', $strContentType);
                $this->view->assign('rootLevelTypeId', $intRootLevelTypeId);
                $this->view->assign('rootLevelGroupId', $intRootLevelGroupId);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getItemLanguageId
     * @return integer
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getItemLanguageId()
    {
        if ($this->intItemLanguageId == null) {
            if (!$this->getRequest()->getParam("languageId")) {
                $this->intItemLanguageId = $this->getRequest()->getParam("rootLevelLanguageId") != '' ? $this->getRequest()->getParam("rootLevelLanguageId") : $this->core->intZooluLanguageId;
            } else {
                $this->intItemLanguageId = $this->getRequest()->getParam("languageId");
            }
        }
        return $this->intItemLanguageId;
    }

    /**
     * getModelModules
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelModules()
    {
        if (null === $this->objModelModules) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Modules.php';
            $this->objModelModules = new Model_Modules();
        }

        return $this->objModelModules;
    }

    /**
     * getModelRootLevels
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
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
            $this->objModelRootLevels->setLanguageId(1); // TODO Language from user
        }

        return $this->objModelRootLevels;
    }

    /**
     * getModelFolders
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
            $this->objModelFolders->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelFolders;
    }


    /**
     * getModelPages
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelPages()
    {
        if (null === $this->objModelPages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/Pages.php';
            $this->objModelPages = new Model_Pages();
            $this->objModelPages->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelPages;
    }

    /**
     * getModelGlobals
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelGlobals()
    {
        if (null === $this->objModelGlobals) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/Globals.php';
            $this->objModelGlobals = new Model_Globals();
            $this->objModelGlobals->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelGlobals;
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
            $objAuth = Zend_Auth::getInstance();
            $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Files.php';
            $this->objModelFiles = new Model_Files();
            $this->objModelFiles->setLanguageId($this->getItemLanguageId());
            $this->objModelFiles->setAlternativLanguageId($objAuth->getIdentity()->languageId);
        }

        return $this->objModelFiles;
    }
}
