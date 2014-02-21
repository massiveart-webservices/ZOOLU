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
 * Global_OverlayController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-12-17: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Global_OverlayController extends AuthControllerAction
{

    private $intRootLevelId;
    private $intFolderId;

    /**
     * @var integer
     */
    protected $intItemLanguageId;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * @var Model_Globals
     */
    protected $objModelGlobals;

    /**
     * indexAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {
    }

    /**
     * elementtreeAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function elementtreeAction()
    {
        $this->core->logger->debug('global->controllers->OverlayController->elementtreeAction()');

        $objRequest = $this->getRequest();
        $intRootLevelId = $objRequest->getParam('rootLevelId');
        $intRootLevelGroupId = $objRequest->getParam('rootLevelGroupId');
        $strItemAction = $objRequest->getParam('itemAction');

        $strElementIds = $objRequest->getParam('itemIds');

        $strTmpElementIds = trim($strElementIds, '[]');
        $arrElementIds = explode('][', $strTmpElementIds);

        $this->loadGlobalTreeForRootLevel($intRootLevelId, $intRootLevelGroupId);
        $this->view->assign('overlaytitle', $this->core->translate->_('Assign_internal_links'));
        $this->view->assign('itemAction', $strItemAction);
        $this->view->assign('elementIds', $arrElementIds);
    }

    /**
     * listglobalAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function listglobalAction()
    {
        $this->core->logger->debug('global->controllers->OverlayController->listglobalAction()');

        $intFolderId = $this->getRequest()->getParam('folderId');
        $strGlobalIds = $this->getRequest()->getParam('globalIds');

        $arrGlobalIds = explode('][', trim($strGlobalIds, '[]'));
        $objGlobals = $this->getModelGlobals()->loadGlobalByParentFolder($intFolderId);

        $this->view->assign('globals', $objGlobals);
        $this->view->assign('globalIds', $arrGlobalIds);
    }

    /**
     * internallinkAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function internallinkAction()
    {
        $this->core->logger->debug('global->controllers->OverlayController->internallinkAction()');
        $this->loadRootNavigation($this->core->sysConfig->modules->global, $this->core->sysConfig->root_level_types->global, $this->getRequest()->getParam('rootLevelId'));

        $this->view->assign('overlaytitle', $this->core->translate->_('Assign_internal_links'));
        $this->view->assign('viewtype', $this->core->sysConfig->viewtypes->list);
        $this->view->assign('contenttype', 'global');
        $this->view->assign('rootLevelId', $this->getRequest()->getParam('rootLevelId'));
        $this->renderScript('overlay/overlay.phtml');
    }

    /**
     * loadRootNavigation
     * @param integer $intRootLevelModule
     * @param integer $intRootLevelType
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function loadRootNavigation($intRootLevelModule, $intRootLevelType = -1, $intRootLevel = null)
    {
        $this->core->logger->debug('cms->controllers->OverlayController->loadRootNavigation(' . $intRootLevelModule . ', ' . $intRootLevelType . ', ' . $intRootLevel . ')');

        $this->getModelFolders();

        if ($intRootLevelType == $this->core->sysConfig->root_level_types->global) {
            $objRootLevelElements = $this->getModelFolders()->loadRootFolders($intRootLevel);
            $this->view->assign('elements', $objRootLevelElements);
        }
    }

    /**
     * loadGlobalTreeForRootLevel
     * @param integer $intRootLevelId
     * @param integer $intRootLevelGroupId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function loadGlobalTreeForRootLevel($intRootLevelId, $intRootLevelGroupId)
    {
        $this->core->logger->debug('global->controllers->OverlayController->loadGlobalTreeForRootLevel(' . $intRootLevelId . ', ' . $intRootLevelGroupId . ')');

        $this->getModelFolders();
        $objElementTree = $this->objModelFolders->loadGlobalRootLevelChilds($intRootLevelId, $intRootLevelGroupId);

        $this->view->assign('elements', $objElementTree);
        $this->view->assign('rootLevelId', $intRootLevelId);
    }

    /**
     * getItemLanguageId
     * @param integer $intActionType
     * @return integer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getItemLanguageId($intActionType = null)
    {
        if ($this->intItemLanguageId == null) {
            if (!$this->getRequest()->getParam("languageId")) {
                $this->intItemLanguageId = $this->getRequest()->getParam("rootLevelLanguageId") != '' ? $this->getRequest()->getParam("rootLevelLanguageId") : $this->core->intZooluLanguageId;

                $intRootLevelId = $this->getRequest()->getParam("rootLevelId");
                $PRIVILEGE = ($intActionType == $this->core->sysConfig->generic->actions->add) ? Security::PRIVILEGE_ADD : Security::PRIVILEGE_UPDATE;

                $arrLanguages = $this->core->config->languages->language->toArray();
                foreach ($arrLanguages as $arrLanguage) {
                    if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId . '_' . $arrLanguage['id'], $PRIVILEGE, false, false)) {
                        $this->intItemLanguageId = $arrLanguage['id'];
                        break;
                    }
                }

            } else {
                $this->intItemLanguageId = $this->getRequest()->getParam("languageId");
            }
        }

        return $this->intItemLanguageId;
    }

    /**
     * getModelFolders
     * @author Thomas Schedler <tsh@massiveart.com>
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

}

?>