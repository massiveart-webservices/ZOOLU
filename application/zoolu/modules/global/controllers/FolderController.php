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
 * Global_FolderController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-27: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class Global_FolderController extends AuthControllerAction
{

    /**
     * @var Model_Globals
     */
    private $objModelGlobals;

    /**
     * init
     */
    public function init()
    {
        parent::init();
        if (!Security::get()->isAllowed('global', Security::PRIVILEGE_VIEW)) {
            $this->_redirect('/zoolu');
        }
    }

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
    }

    /**
     * listAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->core->logger->debug('core->controllers->FolderController->listAction()');

        $strSearchValue = $this->getRequest()->getParam('search');
        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : '');
        $strOrderSort = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : '');

        $intPortalId = $this->getRequest()->getParam('portalId');
        $intFolderId = $this->getRequest()->getParam('folderId');
        $intCurrLevel = $this->getRequest()->getParam('currLevel');

        $strRootLevelGroupKey = $this->getRequest()->getParam('rootLevelGroupKey', 'content');

        $this->getModelGlobals();
        $objFolderSelect = $this->objModelGlobals->loadFolderContentById($intFolderId, $strSearchValue, $strOrderColumn, $strOrderSort, $strRootLevelGroupKey);

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objFolderSelect);
        $objFolderPaginator = new Zend_Paginator($objAdapter);
        $objFolderPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objFolderPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objFolderPaginator->setView($this->view);

        $this->view->assign('folderPaginator', $objFolderPaginator);
        $this->view->assign('intFolderId', $intFolderId);
        $this->view->assign('strOrderColumn', $strOrderColumn);
        $this->view->assign('strOrderSort', $strOrderSort);
        $this->view->assign('strSearchValue', $strSearchValue);
        $this->view->assign('rootLevelGroupKey', $strRootLevelGroupKey);
        $this->view->assign('currLevel', $intCurrLevel);
    }

    /**
     * getModelGenericForm
     * @return Model_GenericForms
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function getModelGlobals()
    {
        if (null === $this->objModelGlobals) {
            /**
             * autoload only handles "library" components.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/Globals.php';
            $this->objModelGlobals = new Model_Globals();
            $this->objModelGlobals->setLanguageId($this->getRequest()->getParam("languageId", $this->core->intZooluLanguageId));
        }

        return $this->objModelGlobals;
    }
}

?>