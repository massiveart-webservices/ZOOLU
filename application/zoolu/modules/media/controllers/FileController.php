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
 * Media_FileController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-21: Cornelius Hansjakob
 *  *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Media_FileController extends AuthControllerAction
{

    /**
     * request object instance
     * @var Zend_Controller_Request_Abstract
     */
    protected $objRequest;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    protected $intLanguageId;

    /**
     * init
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->objRequest = $this->getRequest();
        $this->intLanguageId = ((int) $this->objRequest->getParam("languageId") > 0) ? (int) $this->objRequest->getParam("languageId") : $this->core->intZooluLanguageId;
        $this->view->assign('languageId', $this->intLanguageId);
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
     * geteditformAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function geteditformAction()
    {
        $this->core->logger->debug('media->controllers->FileController->geteditformAction()');

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $this->getModelFiles();

            $objRequest = $this->getRequest();
            $strFileIds = $objRequest->getParam('fileIds');
            $objFiles = $this->objModelFiles->loadFilesById($strFileIds);
            
            $this->view->assign('imagesSizes', $this->core->sysConfig->upload->images->default_sizes->default_size->toArray());
            $this->view->assign('strEditFormAction', '/zoolu/media/file/edit');
            $this->view->assign('strFileIds', $strFileIds);
            $this->view->assign('objFiles', $objFiles);
            
            $intFileFiltersCategoryId = (int) $this->core->zooConfig->file_filters->parent_id;
            $arrFilesFileFilters = array();
            $strTmpFileIds = trim($strFileIds, '[]');
            $arrFileIds = array();
            $arrFileIds = explode('][', $strTmpFileIds);
            foreach ($arrFileIds as $intFileId) {
                $arrFilesFileFilters[$intFileId] = $this->getModelFiles()->loadFileFilters($intFileId, $intFileFiltersCategoryId);    
            }
            $this->view->assign('arrFilesFileFilters',$arrFilesFileFilters);
            $this->view->assign('languageOptions', HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->intLanguageId));
        }

        $this->assignSecurityOptions();
        $this->renderScript('file/form.phtml');
    }

    /**
     * getaddeditformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getaddeditformAction()
    {
        $this->core->logger->debug('media->controllers->FileController->getaddeditformAction()');

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $this->getModelFiles();

            $objRequest = $this->getRequest();
            $strFileIds = $objRequest->getParam('fileIds');
            $objFiles = $this->objModelFiles->loadFilesById($strFileIds);

            $this->view->assign('imagesSizes', $this->core->sysConfig->upload->images->default_sizes->default_size->toArray());
            $this->view->assign('strEditFormAction', '/zoolu/media/file/edit');
            $this->view->assign('strFileIds', $strFileIds);
            $this->view->assign('objFiles', $objFiles);
            
            $intFileFiltersCategoryId = (int) $this->core->zooConfig->file_filters->parent_id;
            $arrFilesFileFilters = array();
            $strTmpFileIds = trim($strFileIds, '[]');
            $arrFileIds = array();
            $arrFileIds = explode('][', $strTmpFileIds);
            foreach ($arrFileIds as $intFileId) {
                $arrFilesFileFilters[$intFileId] = $this->getModelFiles()->loadEmptyFileFilters($intFileFiltersCategoryId);    
            }
            $this->view->assign('arrEmptyFileFilters',$arrFilesFileFilters);
            $this->view->assign('languageOptions', HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->intLanguageId));
        }

        $this->assignSecurityOptions();
        $this->renderScript('file/addform.phtml');
    }

    /**
     * getsingleeditformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getsingleeditformAction()
    {
        $this->core->logger->debug('media->controllers->FileController->getsingleeditformAction()');

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $this->getModelFiles();

            $objRequest = $this->getRequest();
            $intFileId = $objRequest->getParam('fileId');
            $objFile = $this->objModelFiles->loadFileById($intFileId);

            $this->view->assign('strEditFormAction', '/zoolu/media/file/edit');
            $this->view->assign('intFileId', $intFileId);
            $this->view->assign('objFile', $objFile);

            if (count($objFile) == 1 && $objFile->current()->version > 1) {
                $objFileVersions = $this->objModelFiles->loadFileVersions($intFileId);
                $this->view->assign('objFileVersions', $objFileVersions);
            }

            $this->view->assign('imagesSizes', $this->core->sysConfig->upload->images->default_sizes->default_size->toArray());
            $this->view->assign('destinationOptions', HtmlOutput::getOptionsOfSQL($this->core, 'SELECT categories.id AS VALUE, categoryTitles.title  AS DISPLAY FROM categories INNER JOIN categoryTitles ON categoryTitles.idCategories = categories.id AND categoryTitles.idLanguages = ' . $this->core->intZooluLanguageId . ' WHERE categories.idParentCategory = 466 ORDER BY categoryTitles.title', $objFile->current()->idDestination));
            $this->view->assign('groupOptions', HtmlOutput::getOptionsOfSQL($this->core, 'SELECT groups.id AS VALUE, groups.title  AS DISPLAY FROM groups LEFT JOIN groupGroupTypes ON groupGroupTypes.idGroups = groups.id WHERE groupGroupTypes.idGroupTypes = ' . $this->core->sysConfig->group_types->frontend . ' ORDER BY groups.title', $objFile->current()->idGroup));
            $this->view->assign('languageOptions', HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->intLanguageId));
            
            $intFileFiltersCategoryId = (int) $this->core->zooConfig->file_filters->parent_id;
            $arrFileFilters = $this->getModelFiles()->loadFileFilters($intFileId, $intFileFiltersCategoryId);
            $this->view->assign('arrFileFilters', $arrFileFilters);
        }

        $this->assignSecurityOptions();
        $this->renderScript('file/singleform.phtml');
    }

    /**
     * editAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function editAction()
    {
        $this->core->logger->debug('media->controllers->FileController->editAction()');

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $arrFormData = $this->getRequest()->getPost();

            $objFile = new File();
            $objFile->setLanguageId($this->intLanguageId);
            $objFile->setFileDatas($arrFormData);
            $objFile->updateFileData();
        }

        if (isset($arrFormData['IsSingleEdit']) && $arrFormData['IsSingleEdit'] == 'true') {
            echo $arrFormData['FileIds'];

            /**
             * no rendering
             */
            $this->_helper->viewRenderer->setNoRender();
        } else {
            /**
             * no rendering
             */
            $this->_helper->viewRenderer->setNoRender();
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
        $blnGeneralUpdateAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId, Security::PRIVILEGE_UPDATE, false, false);
        $this->view->authorizedUpdate = ($blnGeneralUpdateAuthorization == true) ? $blnGeneralUpdateAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId . '_' . $this->intLanguageId, Security::PRIVILEGE_UPDATE, false, false);
    }

    /**
     * deleteAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function deleteAction()
    {
        $this->core->logger->debug('media->controllers->FileController->deleteAction()');

        //FIXME where is the file delete ????

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $intRootLevelId = (int) $this->getRequest()->getParam('rootLevelId', 0);
            $blnAuthorizedToDelete = ($intRootLevelId != 0) ? Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId, Security::PRIVILEGE_DELETE, true, false) : Security::get()->isAllowed('media', Security::PRIVILEGE_DELETE, false, false);

            if ($blnAuthorizedToDelete == true) {
                $this->getModelFiles();

                $objRequest = $this->getRequest();
                $strFileIds = $objRequest->getParam('fileIds');

                $this->objModelFiles->deleteFiles($strFileIds);
            }
        }
    }

    /**
     * changeparentfolderAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function changeparentfolderAction()
    {
        $this->core->logger->debug('media->controllers->FileController->changeparentfolderAction()');

        $strFileIds = $this->objRequest->getParam('files');
        $intParentFolderId = $this->objRequest->getParam('parentFolderId');

        if ($strFileIds != '' && $intParentFolderId > 0) {
            $this->getModelFiles();
            $this->objModelFiles->changeParentFolderId($strFileIds, $intParentFolderId);
        }

        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * getModelFiles
     * @return Model_Files
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelFiles()
    {
        if (null === $this->objModelFiles) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Files.php';
            $this->objModelFiles = new Model_Files();
            $this->objModelFiles->setLanguageId($this->intLanguageId);
        }

        return $this->objModelFiles;
    }
}

?>