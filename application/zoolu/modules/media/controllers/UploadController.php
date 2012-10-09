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
 * Media_UploadController
 *
 * php-pecl-Fileinfo has to be installed
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-10: Thomas Schedler
 * 1.1, 2009-05-14: Cornelius Hansjakob
 *
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Media_UploadController extends AuthControllerAction
{

    protected $intParentId;
    protected $intFileId;
    protected $intLanguageId;

    /**
     * @var Zend_File_Transfer_Adapter_Http
     */
    protected $objUpload;
    
    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    const UPLOAD_FIELD = 'Filedata';

    /**
     * init
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->intLanguageId = ((int) $this->getRequest()->getParam("languageId") > 0) ? (int) $this->getRequest()->getParam("languageId") : $this->core->intZooluLanguageId;
        $this->view->assign('languageId', $this->intLanguageId);
    }

    /**
     * indexAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {
        try {
            $this->core->logger->debug('media->controllers->UploadController->indexAction()');

            $this->handleFileTransfer();

            if ($this->getRequest()->isPost()) {
                $objRequest = $this->getRequest();
                $this->intParentId = $objRequest->getParam('folderId');

                /**
                 * check if is image, video or document
                 */
                if ($this->intParentId > 0 && $this->intParentId != '') {
                    if (strpos($this->objUpload->getMimeType(self::UPLOAD_FIELD), 'image/') !== false) {
                        $this->handleImageUpload();
                    } else if (strpos($this->objUpload->getMimeType(self::UPLOAD_FIELD), 'video/') !== false || $this->objUpload->getMimeType(self::UPLOAD_FIELD) == 'application/x-shockwave-flash') {
                        $this->handleVideoUpload();
                    } else {
                        $this->handleFileUpload();
                    }
                    $intFileFiltersCategoryId = (int) $this->core->zooConfig->file_filters->parent_id;
                    $this->view->assign('arrEmptyFileFilters', $this->getModelFiles()->loadEmptyFileFilters($intFileFiltersCategoryId));
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * versionAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function versionAction()
    {
        try {
            $this->core->logger->debug('media->controllers->UploadController->versionAction()');

            $this->handleFileTransfer();

            if ($this->getRequest()->isPost()) {
                $objRequest = $this->getRequest();
                $this->intFileId = $objRequest->getParam('fileId');

                /**
                 * check if is image or else document
                 */
                if ($this->intFileId > 0 && $this->intFileId != '') {
                    if (strpos($this->objUpload->getMimeType(self::UPLOAD_FIELD), 'image/') !== false) {
                        $this->handleImageVersionUpload();
                    } else if (strpos($this->objUpload->getMimeType(self::UPLOAD_FIELD), 'video/') !== false || $this->objUpload->getMimeType(self::UPLOAD_FIELD) == 'application/x-shockwave-flash') {
                        $this->handleVideoVersionUpload();
                    } else {
                        $this->handleFileVersionUpload();
                    }
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * saveAction
     * @author Cornelius Hansjakob <cha@massiveart.at>
     * @version 1.0
     */
    public function saveAction()
    {
        $this->core->logger->debug('media->controllers->UploadController->saveAction()');

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $arrFormData = $this->getRequest()->getPost();

            $objFile = new File();
            $objFile->setFileDatas($arrFormData);
            $objFile->setLanguageId($this->intLanguageId);
            $objFile->updateFileData();
        }

        /**
         * no rendering
         */
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * handleFileTransfer
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    private function handleFileTransfer()
    {

        $this->objUpload = new Zend_File_Transfer_Adapter_Http();
        $this->objUpload->setOptions(array('useByteString' => false));

        /**
         * validators for upload of media
         */
        $arrExcludedExtensions = $this->core->sysConfig->upload->excluded_extensions->extension->toArray();

        $this->objUpload->addValidator('Size', false, array('min' => 1, 'max' => $this->core->sysConfig->upload->max_filesize));
        $this->objUpload->addValidator('ExcludeExtension', false, $arrExcludedExtensions);

        /**
         * check if medium is uploaded
         */
        if (!$this->objUpload->isUploaded(self::UPLOAD_FIELD)) {
            $this->core->logger->warn('isUploaded: ' . implode('\n', $this->objUpload->getMessages()));
            throw new Exception('File is not uploaded!');
        }

        /**
         * check if upload is valid
         */
        if (!$this->objUpload->isValid(self::UPLOAD_FIELD)) {
            $this->core->logger->warn('isValid: ' . implode('\n', $this->objUpload->getMessages()));
            throw new Exception('Uploaded file is not valid!');
        }
    }

    /**
     * handleImageUpload
     * @author Cornelius Hansjakob <cha@massiveart.at>
     * @version 1.0
     */
    private function handleImageUpload()
    {
        $this->core->logger->debug('media->controllers->UploadController->handleImageUpload()');

        $objImage = new Image();
        $objImage->setUpload($this->objUpload);
        $objImage->setParentId($this->intParentId);
        $objImage->setParentTypeId($this->core->sysConfig->parent_types->folder);
        $objImage->setSegmenting((($this->core->sysConfig->upload->images->segmenting->enabled == 'true') ? true : false));
        $objImage->setNumberOfSegments($this->core->sysConfig->upload->images->segmenting->number_of_segments);
        $objImage->setUploadPath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->images->path->local->private);
        $objImage->setPublicFilePath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->images->path->local->public);
        $objImage->setTmpFilePath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->images->path->local->tmp);
        $objImage->setDefaultImageSizes($this->core->sysConfig->upload->images->default_sizes->default_size->toArray());
        $objImage->setLanguageId($this->intLanguageId);
        $objImage->add(self::UPLOAD_FIELD);

        $this->writeViewData($objImage);
    }

    /**
     * handleVideoUpload
     * @author Cornelius Hansjakob <cha@massiveart.at>
     * @version 1.0
     */
    private function handleVideoUpload()
    {
        $this->core->logger->debug('media->controllers->UploadController->handleVideoUpload()');

        $objFile = new File();
        $objFile->setUpload($this->objUpload);
        $objFile->setParentId($this->intParentId);
        $objFile->setParentTypeId($this->core->sysConfig->parent_types->folder);
        $objFile->setSegmenting((($this->core->sysConfig->upload->documents->segmenting->enabled == 'true') ? true : false));
        $objFile->setNumberOfSegments($this->core->sysConfig->upload->documents->segmenting->number_of_segments);
        $objFile->setUploadPath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->videos->path->local->private);
        $objFile->setPublicFilePath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->videos->path->local->public);
        $objFile->setLanguageId($this->intLanguageId);
        $objFile->add(self::UPLOAD_FIELD);

        $this->writeViewData($objFile);
    }

    /**
     * handleFileUpload
     * @author Cornelius Hansjakob <cha@massiveart.at>
     * @version 1.0
     */
    private function handleFileUpload()
    {
        $this->core->logger->debug('media->controllers->UploadController->handleFileUpload()');

        $objFile = new File();
        $objFile->setUpload($this->objUpload);
        $objFile->setParentId($this->intParentId);
        $objFile->setParentTypeId($this->core->sysConfig->parent_types->folder);
        $objFile->setSegmenting((($this->core->sysConfig->upload->documents->segmenting->enabled == 'true') ? true : false));
        $objFile->setNumberOfSegments($this->core->sysConfig->upload->documents->segmenting->number_of_segments);
        $objFile->setUploadPath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->documents->path->local->private);
        $objFile->setPublicFilePath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->documents->path->local->public);
        $objFile->setLanguageId($this->intLanguageId);
        $objFile->add(self::UPLOAD_FIELD);

        $this->writeViewData($objFile);
    }

    /**
     * writeViewData
     * @param File $objFile
     * @author Cornelius Hansjakob <cha@massiveart.at>
     * @version 1.0
     */
    private function writeViewData(File $objFile)
    {
        $this->core->logger->debug('media->controllers->UploadController->writeViewData()');

        $this->view->assign('fileId', $objFile->getId());
        $this->view->assign('fileFileId', $objFile->getFileId());
        $this->view->assign('fileExtension', $objFile->getExtension());
        $this->view->assign('fileTitle', $objFile->getTitle());
        $this->view->assign('fileVersion', $objFile->getVersion());
        $this->view->assign('filePath', sprintf($this->core->sysConfig->media->paths->icon32, $objFile->getSegmentPath()));
        $this->view->assign('mimeType', $objFile->getMimeType());
        $this->view->assign('strDefaultDescription', $this->core->translate->_('Add_description_'));
        $this->view->assign('languageId', $this->intLanguageId);
    }

    /**
     * handleImageVersionUpload
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    private function handleImageVersionUpload()
    {
        $this->core->logger->debug('media->controllers->UploadController->handleImageVersionUpload()');

        $objImage = new Image();
        $objImage->setUpload($this->objUpload);
        $objImage->setId($this->intFileId);
        $objImage->setSegmenting((($this->core->sysConfig->upload->images->segmenting->enabled == 'true') ? true : false));
        $objImage->setNumberOfSegments($this->core->sysConfig->upload->images->segmenting->number_of_segments);
        $objImage->setUploadPath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->images->path->local->private);
        $objImage->setPublicFilePath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->images->path->local->public);
        $objImage->setDefaultImageSizes($this->core->sysConfig->upload->images->default_sizes->default_size->toArray());
        $objImage->setLanguageId($this->intLanguageId);
        $objImage->addVersion(self::UPLOAD_FIELD);
    }

    /**
     * handleVideoVersionUpload
     * @author Cornelius Hansjakob <cha@massiveart.com>
     */
    private function handleVideoVersionUpload()
    {
        $this->core->logger->debug('media->controllers->UploadController->handleVideoVersionUpload()');

        $objFile = new File();
        $objFile->setUpload($this->objUpload);
        $objFile->setId($this->intFileId);
        $objFile->setSegmenting((($this->core->sysConfig->upload->documents->segmenting->enabled == 'true') ? true : false));
        $objFile->setNumberOfSegments($this->core->sysConfig->upload->documents->segmenting->number_of_segments);
        $objFile->setUploadPath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->videos->path->local->private);
        $objFile->setPublicFilePath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->videos->path->local->public);
        $objFile->setLanguageId($this->intLanguageId);
        $objFile->addVersion(self::UPLOAD_FIELD);
    }

    /**
     * handleFileVersionUpload
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    private function handleFileVersionUpload()
    {
        $this->core->logger->debug('media->controllers->UploadController->handleFileVersionUpload()');

        $objFile = new File();
        $objFile->setUpload($this->objUpload);
        $objFile->setId($this->intFileId);
        $objFile->setSegmenting((($this->core->sysConfig->upload->documents->segmenting->enabled == 'true') ? true : false));
        $objFile->setNumberOfSegments($this->core->sysConfig->upload->documents->segmenting->number_of_segments);
        $objFile->setUploadPath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->documents->path->local->private);
        $objFile->setPublicFilePath(GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->documents->path->local->public);
        $objFile->setLanguageId($this->intLanguageId);
        $objFile->addVersion(self::UPLOAD_FIELD);
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