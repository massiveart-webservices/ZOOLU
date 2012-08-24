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
 * @package    application.zoolu.modules.core.media.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Media_ViewController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-10: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Media_ViewController extends AuthControllerAction
{

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    /**
     * request object instance
     * @var Zend_Controller_Request_Abstract
     */
    protected $objRequest;

    /**
     * init
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->objRequest = $this->getRequest();
    }

    /**
     * indexAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {
    }

    /**
     * thumbAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function thumbAction()
    {
        $this->core->logger->debug('media->controllers->ViewController->thumbAction()');

        $objRequest = $this->getRequest();
        $intFolderId = $objRequest->getParam('folderId');
        $intSliderValue = $objRequest->getParam('sliderValue');

        /**
         * get files
         */
        $this->getModelFiles();
        $objFiles = $this->objModelFiles->loadFiles($intFolderId, -1, false);

        $this->view->assign('objFiles', $objFiles);
        $this->view->assign('sliderValue', $intSliderValue);
        $this->assignSecurityOptions();
    }

    /**
     * listAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->core->logger->debug('media->controllers->ViewController->listAction()');

        $strSearchValue = $this->getRequest()->getParam('search');
        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : '');
        $strOrderSort = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : '');

        $objRequest = $this->getRequest();
        $intFolderId = $objRequest->getParam('folderId');

        /**
         * get files
         */
        $this->getModelFiles();
        $objFilesSelect = $this->objModelFiles->loadFiles($intFolderId, -1, false, true, $strSearchValue, $strOrderColumn, $strOrderSort);

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objFilesSelect);
        $objFilePaginator = new Zend_Paginator($objAdapter);
        $objFilePaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objFilePaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objFilePaginator->setView($this->view);

        $this->view->assign('filePaginator', $objFilePaginator);
        $this->view->assign('intFolderId', $intFolderId);
        $this->view->assign('strOrderColumn', $strOrderColumn);
        $this->view->assign('strOrderSort', $strOrderSort);
        $this->view->assign('strSearchValue', $strSearchValue);
        $this->assignSecurityOptions();
    }

    /**
     * dashboardAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function dashboardAction()
    {
        $this->core->logger->debug('media->controllers->ViewController->dashboardAction()');
        try {
            $this->getModelFiles();

            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intRootLevelId = $this->objRequest->getParam('rootLevelId');
                $intLimitNumber = 10;

                /**
                 * check if select item in session
                 */
                if (isset($this->core->objCoreSession->selectItem) && count($this->core->objCoreSession->selectItem) > 0) {
                    $objSelectItem = $this->core->objCoreSession->selectItem;

                    $this->view->assign('objSelectItem', $objSelectItem);
                    $this->view->assign('isSelectItem', true);

                    unset($this->core->objCoreSession->selectItem);
                } else {
                    // FIXME : load dashboard file data
                    //$objFiles = $this->objModelFiles->loadFiles('', $intLimitNumber, false);

                    //$this->view->assign('objFiles', $objFiles);
                    //$this->view->assign('limit', $intLimitNumber);
                    $this->view->assign('isSelectItem', false);
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * assignSecurityOptions
     * @author Thomas Schedler <cha@massiveart.com>
     * @version 1.0
     */
    protected function assignSecurityOptions()
    {
        $intRootLevelId = (int) $this->getRequest()->getParam('rootLevelId', 0);
        if ($intRootLevelId != 0) {
            $this->view->authorizedAdd = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId, Security::PRIVILEGE_ADD, true, false);
            $this->view->authorizedDelete = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId, Security::PRIVILEGE_DELETE, true, false);
            $this->view->authorizedUpdate = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId, Security::PRIVILEGE_UPDATE, true, false);
        } else {
            $this->view->authorizedAdd = Security::get()->isAllowed('media', Security::PRIVILEGE_ADD, false, false);
            $this->view->authorizedDelete = Security::get()->isAllowed('media', Security::PRIVILEGE_DELETE, false, false);
            $this->view->authorizedUpdate = Security::get()->isAllowed('media', Security::PRIVILEGE_UPDATE, false, false);
        }
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
            $this->objModelFiles->setLanguageId($this->getRequest()->getParam("languageId", $this->core->intZooluLanguageId));
            $this->objModelFiles->setAlternativLanguageId(Zend_Auth::getInstance()->getIdentity()->languageId);
        }

        return $this->objModelFiles;
    }

}

?>