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
 * @package    library.massiveart.files
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * File Class
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-11: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.files
 * @subpackage File
 */

class File
{

    /**
     * @var Core core object (dbh, logger, ...)
     */
    protected $core;

    /**
     * save paths local or s3
     */
    const DESTINATION_LOCAL = 'local';
    const DESTINATION_S3 = 's3';

    /**
     * image adapter
     */
    const ADAPTER_GD2 = 'Gd2';
    const ADAPTER_IMAGICK = 'Imagick';

    /**
     * direcory owner and group
     */
    private static $DIRECTORY_OWNER;
    private static $DIRECTORY_GROUP;

    /**
     * @var Model_Files
     */
    protected $objModelFile;

    /**
     * @var Model_Utilities
     */
    private $objModelUtilities;

    /**
     * @var Zend_Db_Table_Rowset_Abstract
     */
    private $objPathReplacers;

    /**
     * @var Model_Tags
     */
    private $objModelTags;

    /**
     * @var Zend_File_Transfer_Adapter_Abstract
     */
    protected $objUpload;

    protected $intId;
    protected $strFileId;
    protected $strUploadPath;
    protected $strPublicFilePath;
    protected $strTmpFilePath;
    protected $strExtension;
    protected $intVersion;
    protected $intUserId;
    protected $strTitle;
    protected $dblSize;

    protected $intXDim;
    protected $intYDim;
    protected $strMimeType;

    protected $_FILE_NAME;

    protected $blnIsImage = false;

    protected $intParentId;
    protected $intParentTypeId;

    protected $arrFileDatas = array();

    protected $intLanguageId;

    /**
     * if render all images from cli, set owner and group of new directories
     * to the web server executing user and group, else you will get privilege errors
     */
    protected $blnSetOwnerAndGroup = false;

    /**
     * Zend_Db_Table_Row_Abstract
     */
    protected $objFileData;

    /**
     * file system segmenting
     */
    protected $blnSegmenting = false;
    protected $intNumberOfSegments = 10;
    protected $strSegmentPath = '';

    /**
     * for tags
     */
    protected $arrNewTagIds = array();
    protected $arrNewTags = array();

    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
        self::$DIRECTORY_OWNER = $this->core->sysConfig->server->executing->user;
        self::$DIRECTORY_GROUP = $this->core->sysConfig->server->executing->group;
    }

    /**
     * add
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function add($_FILE_NAME)
    {
        $this->core->logger->debug('massiveart.files.File->add()');
        try {
            $this->_FILE_NAME = $_FILE_NAME;

            $this->getModelFile();
            $this->intUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $this->intVersion = 1;

            /**
             * insert file data
             */
            $arrInsertData = array(
                'idUsers'        => $this->intUserId,
                'idParent'       => $this->intParentId,
                'idParentTypes'  => $this->intParentTypeId,
                'isS3Stored'     => 0,
                'creator'        => $this->intUserId,
                'created'        => date('Y-m-d H:i:s'),
                'changed'        => date('Y-m-d H:i:s'),
                'version'        => $this->intVersion
            );
            $this->intId = $this->objModelFile->getFileTable()->insert($arrInsertData);

            if ($this->blnSegmenting === true) {
                $this->strSegmentPath = sprintf('%02d', ($this->intId % $this->intNumberOfSegments + 1)) . '/';
            }

            $this->upload();

            /**
             * update file data
             */
            $arrUpdateData = array(
                'fileId'         => $this->strFileId,
                'path'           => $this->strSegmentPath,
                'isImage'        => $this->getIsImage(),
                'filename'       => $this->strFileId . '.' . $this->strExtension,
                'size'           => $this->dblSize,
                'extension'      => $this->strExtension,
                'mimeType'       => $this->strMimeType,
                'version'        => $this->intVersion
            );
            $strWhere = $this->objModelFile->getFileTable()->getAdapter()->quoteInto('id = ?', $this->intId);
            $this->objModelFile->getFileTable()->update($arrUpdateData, $strWhere);

            /**
             * insert version data
             */
            $arrInsertData = array(
                'idFiles'        => $this->intId,
                'fileId'         => $this->strFileId,
                'idUsers'        => $this->intUserId,
                'path'           => $this->strSegmentPath,
                'idParent'       => $this->intParentId,
                'idParentTypes'  => $this->intParentTypeId,
                'isS3Stored'     => 0,
                'isImage'        => $this->getIsImage(),
                'filename'       => $this->strFileId . '.' . $this->strExtension,
                'creator'        => $this->intUserId,
                'created'        => date('Y-m-d H:i:s'),
                'changed'        => date('Y-m-d H:i:s'),
                'size'           => $this->dblSize,
                'extension'      => $this->strExtension,
                'mimeType'       => $this->strMimeType,
                'version'        => $this->intVersion
            );
            $this->objModelFile->getFileVersionTable()->insert($arrInsertData);

            if ($this->getIsImage()) {
                $arrInsertAttributeData = array(
                    'idFiles'  => $this->intId,
                    'xDim'     => $this->intXDim,
                    'yDim'     => $this->intYDim
                );

                $this->objModelFile->getFileAttributeTable()->insert($arrInsertAttributeData);
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * addVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function addVersion($_FILE_NAME)
    {
        $this->core->logger->debug('massiveart.files.File->addVersion()');
        try {
            $this->_FILE_NAME = $_FILE_NAME;
            $this->intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            $objFileData = $this->getModelFile()->getFileTable()->find($this->intId);

            if (count($objFileData) == 1) {
                $this->objFileData = $objFileData->current();

                $this->strSegmentPath = $this->objFileData->path;

                /**
                 * archive file now
                 */
                $this->archive();

                /**
                 * update file version data
                 */
                $arrUpdateData = array(
                    'archiver'                   => $this->intUserId,
                    'archived'                   => date('Y-m-d H:i:s'),
                    'changed'                    => date('Y-m-d H:i:s'),
                    'downloadCounter'            => $this->objFileData->downloadCounter
                );

                $strWhere = $this->objModelFile->getFileVersionTable()->getAdapter()->quoteInto('idFiles = ?', $this->objFileData->id);
                $strWhere .= $this->objModelFile->getFileVersionTable()->getAdapter()->quoteInto(' AND version = ?', $this->objFileData->version);
                $this->objModelFile->getFileVersionTable()->update($arrUpdateData, $strWhere);

                /**
                 * upload new version
                 */
                $this->upload(true);

                $this->objFileData->isImage = $this->getIsImage();
                $this->objFileData->filename = $this->objFileData->fileId . '.' . $this->strExtension;
                $this->objFileData->size = $this->dblSize;
                $this->objFileData->extension = $this->strExtension;
                $this->objFileData->mimeType = $this->strMimeType;
                $this->objFileData->version++;
                $this->objFileData->stream = false; // reset stream status to 0 for new version of file

                /**
                 * update file data
                 */
                $this->objFileData->save();

                /**
                 * insert new version data
                 */
                $arrInsertData = array(
                    'idFiles'        => $this->objFileData->id,
                    'fileId'         => $this->objFileData->fileId,
                    'idUsers'        => $this->intUserId,
                    'path'           => $this->objFileData->path,
                    'idParent'       => $this->objFileData->idParent,
                    'idParentTypes'  => $this->objFileData->idParentTypes,
                    'isS3Stored'     => 0,
                    'isImage'        => $this->objFileData->isImage,
                    'filename'       => $this->objFileData->filename,
                    'creator'        => $this->intUserId,
                    'created'        => date('Y-m-d H:i:s'),
                    'changed'        => date('Y-m-d H:i:s'),
                    'size'           => $this->objFileData->size,
                    'extension'      => $this->objFileData->extension,
                    'mimeType'       => $this->objFileData->mimeType,
                    'version'        => $this->objFileData->version
                );
                $this->objModelFile->getFileVersionTable()->insert($arrInsertData);

                /**
                 * upate image file attributes
                 */
                if ($this->getIsImage()) {
                    $arrUpdateAttributeData = array(
                        'xDim'     => $this->intXDim,
                        'yDim'     => $this->intYDim
                    );
                    $strWhere = $this->objModelFile->getFileAttributeTable()->getAdapter()->quoteInto('idFiles = ?', $this->objFileData->id);
                    $this->objModelFile->getFileAttributeTable()->update($arrUpdateAttributeData, $strWhere);
                }

            } else {
                throw new Exception('Not able to add new version, because there is no file with given ID!');
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * saveFileData
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function saveFileData($arrFileData = array())
    {
        $this->core->logger->debug('massiveart.files.File->saveFileData()');
        try {

            $arrMetaData = array();

            $this->getModelFile();
            $this->getModelTags();

            $arrUploadedFileIds = array();
            if (count($arrFileData) > 0) {
                $arrUploadedFileIds = $arrFileData;
            } else {
                $strTmpUploadedFileIds = trim($this->arrFileDatas['FileIds'], '[]');
                $arrUploadedFileIds = explode('][', $strTmpUploadedFileIds);
            }

            if (count($arrUploadedFileIds) > 0) {
                foreach ($arrUploadedFileIds as $intUploadedFileId) {
                    if ($intUploadedFileId != '') {

                        $strFileTitle = $this->arrFileDatas['FileTitle' . $intUploadedFileId];
                        $strFileDescription = $this->arrFileDatas['FileDescription' . $intUploadedFileId];
                        $intFileIsLanguageSpecific = (int) (isset($this->arrFileDatas['FileIsLanguageSpecific' . $intUploadedFileId])) ? $this->arrFileDatas['FileIsLanguageSpecific' . $intUploadedFileId] : 0;
                        $intFileDestinationId = (int) (isset($this->arrFileDatas['FileDestinationId' . $intUploadedFileId])) ? $this->arrFileDatas['FileDestinationId' . $intUploadedFileId] : 0;
                        $intFileGroupId = (int) (isset($this->arrFileDatas['FileGroupId' . $intUploadedFileId])) ? $this->arrFileDatas['FileGroupId' . $intUploadedFileId] : 0;

                        $strWhere = $this->objModelFile->getFileTable()->getAdapter()->quoteInto('id = ?', $intUploadedFileId);
                        $this->objModelFile->getFileTable()->update(array('isLanguageSpecific' => $intFileIsLanguageSpecific, 'idDestination' => $intFileDestinationId, 'idGroup' => $intFileGroupId), $strWhere);

                        $arrInsertData = array(
                            'idFiles'       => $intUploadedFileId,
                            'idLanguages'   => $this->intLanguageId,
                            'title'         => $strFileTitle,
                            'description'   => $strFileDescription
                        );

                        if ($this->objModelFile->hasDisplayTitle($intUploadedFileId) == false) {
                            $arrInsertData['isDisplayTitle'] = 1;
                        }

                        $this->objModelFile->getFileTitleTable()->insert($arrInsertData);

                        /**
                         * save tags (quick&dirty solution)
                         */
                        $this->arrNewTags = array();
                        $this->arrNewTags = explode(',', trim($this->arrFileDatas['FileTags' . $intUploadedFileId]));

                        $this->validateTags();

                        $this->objModelTags->deletTypeTags('file', $intUploadedFileId, 1); // TODO : version
                        $this->objModelTags->addTypeTags('file', $this->arrNewTagIds, $intUploadedFileId, 1); // TODO : version
                        
                        // saveFileFilters
                        $this->updateFileFilters($intUploadedFileId);
                    }
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * updateFileData
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function updateFileData()
    {
        $this->core->logger->debug('massiveart.files.File->updateFileData()');
        try {

            $arrMetaData = array();

            $this->getModelFile();
            $this->getModelTags();

            $strTmpEditFileIds = trim($this->arrFileDatas['FileIds'], '[]');
            $arrEditFileIds = array();
            $arrEditFileIds = explode('][', $strTmpEditFileIds);

            if (count($arrEditFileIds) > 0) {
                foreach ($arrEditFileIds as $intEditFileId) {
                    if ($intEditFileId != '') {

                        $strFileTitle = $this->arrFileDatas['FileTitle' . $intEditFileId];
                        $strFileDescription = $this->arrFileDatas['FileDescription' . $intEditFileId];

                        if ($strFileTitle != '' || $strFileDescription != '') {

                            $arrData = array(
                                'title'       => $strFileTitle,
                                'description' => $strFileDescription,
                                'changed'     => date('Y-m-d H:i:s')
                            );

                            if ($this->objModelFile->hasDisplayTitle($intEditFileId) == false) {
                                $arrData['isDisplayTitle'] = 1;
                            }

                            $strWhere = $this->objModelFile->getFileTitleTable()->getAdapter()->quoteInto('idFiles = ?', $intEditFileId);
                            $strWhere .= $this->objModelFile->getFileTitleTable()->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);
                            $intNumOfEffectedRows = $this->objModelFile->getFileTitleTable()->update($arrData, $strWhere);

                            if ($intNumOfEffectedRows == 0) {
                                $this->saveFileData(array($intEditFileId));
                            } else {
                                /**
                                 * update is language specific file
                                 */
                                $intFilePreviewId = 0;
                                if (array_key_exists('documentpic', $this->arrFileDatas)) {
                                    $intFilePreviewId = trim($this->arrFileDatas['documentpic'], '[]'); //FIXME: Make a single-select media-field
                                } else if (array_key_exists('videopic', $this->arrFileDatas)) {
                                    $intFilePreviewId = trim($this->arrFileDatas['videopic'], '[]');
                                }
                                $intFileIsLanguageSpecific = (int) (isset($this->arrFileDatas['FileIsLanguageSpecific' . $intEditFileId])) ? $this->arrFileDatas['FileIsLanguageSpecific' . $intEditFileId] : 0;
                                $intFileDestinationId = (int) (isset($this->arrFileDatas['FileDestinationId' . $intEditFileId])) ? $this->arrFileDatas['FileDestinationId' . $intEditFileId] : 0;
                                $intFileGroupId = (int) (isset($this->arrFileDatas['FileGroupId' . $intEditFileId])) ? $this->arrFileDatas['FileGroupId' . $intEditFileId] : 0;

                                $strWhere = $this->objModelFile->getFileTitleTable()->getAdapter()->quoteInto('id = ?', $intEditFileId);
                                $this->objModelFile->getFileTable()->update(array('isLanguageSpecific' => $intFileIsLanguageSpecific, 'idDestination' => $intFileDestinationId, 'idGroup' => $intFileGroupId, 'changed' => date('Y-m-d H:i:s'), 'idFiles' => (($intFilePreviewId > 0) ? $intFilePreviewId : 'NULL')), $strWhere);

                                // save tags (quick&dirty solution)
                                $this->arrNewTags = array();
                                $this->arrNewTags = explode(',', trim($this->arrFileDatas['FileTags' . $intEditFileId]));

                                $this->validateTags();

                                $this->objModelTags->deletTypeTags('file', $intEditFileId, 1); // TODO : version
                                $this->objModelTags->addTypeTags('file', $this->arrNewTagIds, $intEditFileId, 1); // TODO : version
                                
                                // saveFileFilters
                                $this->updateFileFilters($intEditFileId);
                            }
                        } else {
                            $strWhere = $this->objModelFile->getFileTitleTable()->getAdapter()->quoteInto('idFiles = ?', $intEditFileId);
                            $strWhere .= $this->objModelFile->getFileTitleTable()->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);
                            $this->objModelFile->getFileTitleTable()->delete($strWhere);
                        }
                    }
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }
    
    /**
     * updateFileFilters
     * @author Raphael Stocker <rst@massiveart.com>
     * @param integer $intEditFileId
     * @return void
     */
    private function updateFileFilters($intEditFileId) {
        $arrFileFiltersData = array();
        foreach ($this->arrFileDatas as $key => $val) {
            if (strpos($key, 'fileFilter' . $intEditFileId . '_') === 0) {
                foreach ($val as $entry) {
                    if ($entry != '') {
                        $lenght = strlen('fileFilter' . $intEditFileId . '_');
                        $intCategoryId = substr($key, $lenght); 
                        $arrFileFiltersData[] = array('idFiles' => $intEditFileId, 'idCategories' => $intCategoryId, 'value' => $entry);
                    }    
                }
                
            }
        }
        $this->objModelFile->deleteFileFilters($intEditFileId);
        $this->objModelFile->updateFileFilters($arrFileFiltersData) ;
    }

    /**
     * upload
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    protected function upload($blnIsNewVersion = false)
    {
        $this->core->logger->debug('massiveart.files.File->upload()');

        try {
            if ($this->objUpload != null && $this->objUpload instanceof Zend_File_Transfer_Adapter_Abstract) {
                /**
                 * first check upload path
                 */
                $this->checkUploadPath();

                $arrFileInfos = pathinfo($this->objUpload->getFileName($this->_FILE_NAME));
                $this->strExtension = strtolower($arrFileInfos['extension']);
                $this->strTitle = $arrFileInfos['filename'];
                $this->dblSize = $this->objUpload->getFileSize($this->_FILE_NAME);
                $this->strMimeType = $this->objUpload->getMimeType($this->_FILE_NAME);

                if ($blnIsNewVersion == true && $this->objFileData instanceof Zend_Db_Table_Row_Abstract) {
                    $this->strFileId = $this->objFileData->fileId;
                } else {
                    /**
                     * make fileId conform
                     */
                    $this->strFileId = $this->makeFileIdConform($this->strTitle);

                    /**
                     * check uniqueness of fileId
                     */
                    $this->strFileId = $this->checkFileIdUniqueness($this->strFileId);
                }

                /**
                 * receive file
                 */
                $this->objUpload->addFilter('Rename', array(
                                                           'target'    => $this->getUploadPath() . $this->strFileId . '.' . $this->strExtension,
                                                           'overwrite' => true
                                                      ), $this->_FILE_NAME);
                $this->objUpload->receive($this->_FILE_NAME);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * archive
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    protected function archive()
    {
        try {
            if ($this->objFileData instanceof Zend_Db_Table_Row_Abstract && file_exists($this->getUploadPath() . $this->objFileData->filename)) {
                rename($this->getUploadPath() . $this->objFileData->filename, $this->getUploadPath() . $this->objFileData->fileId . '.v' . $this->objFileData->version . '.' . $this->objFileData->extension);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * makeFileIdConform
     * @param string $strFileId
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function makeFileIdConform($strFileId)
    {
        $this->core->logger->debug('massiveart.files.File->makeFileIdConform(' . $strFileId . ')');

        $this->getPathReplacers();

        $strFileId = strtolower($strFileId);

        if (count($this->objPathReplacers) > 0) {
            foreach ($this->objPathReplacers as $objPathReplacer) {
                $strFileId = str_replace($objPathReplacer->from, $objPathReplacer->to, $strFileId);
            }
        }
        $strFileId = strtolower($strFileId);
        $strFileId = urlencode(preg_replace('/([^A-za-z0-9\s-_])/', '-', $strFileId));
        $strFileId = str_replace('+', '-', $strFileId);

        return $strFileId;
    }

    /**
     * getPathReplacers
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    private function getPathReplacers()
    {
        if ($this->objPathReplacers === null) {
            $this->objPathReplacers = $this->getModelUtilities()->loadPathReplacers();
        }
    }

    /**
     * checkFileIdUniqueness
     * @param string $strFileId
     * @param integer $intFileNameAddon = 0
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function checkFileIdUniqueness($strFileId, $intFileIdAddon = 0)
    {
        $this->core->logger->debug('massiveart.files.File->checkFileIdUniqueness(' . $strFileId . ',' . $intFileIdAddon . ')');

        $this->getModelFile();

        $strNewFileId = ($intFileIdAddon > 0) ? $strFileId . '-' . $intFileIdAddon : $strFileId;
        $objFileData = $this->objModelFile->loadFileByFileId($strNewFileId);

        if (count($objFileData) > 0) {
            return $this->checkFileIdUniqueness($strFileId, $intFileIdAddon + 1);
        } else {
            return $strNewFileId;
        }
    }

    /**
     * checkUploadPath
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function checkUploadPath()
    {
        $this->core->logger->debug('massiveart.files.File->checkUploadPath(' . $this->getUploadPath() . ')');

        if (!is_dir($this->getUploadPath())) {
            mkdir($this->getUploadPath(), 0775, true);
            if ($this->blnSetOwnerAndGroup === true && self::$DIRECTORY_OWNER !== null && self::$DIRECTORY_GROUP !== null) {
                chown($this->getUploadPath(), self::$DIRECTORY_OWNER);
                chgrp($this->getUploadPath(), self::$DIRECTORY_GROUP);
            }
        }
    }

    /**
     * checkPublicFilePath
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function checkPublicFilePath($strPathAddon = '')
    {
        $this->core->logger->debug('massiveart.documents.Document->checkPublicFilePath(' . $this->getPublicFilePath() . ')');

        if (!is_dir($this->getPublicFilePath() . $strPathAddon)) {
            mkdir($this->getPublicFilePath() . $strPathAddon, 0775, true);
            if ($this->blnSetOwnerAndGroup === true && self::$DIRECTORY_OWNER !== null && self::$DIRECTORY_GROUP !== null) {
                chown($this->getPublicFilePath() . $strPathAddon, self::$DIRECTORY_OWNER);
                chgrp($this->getPublicFilePath() . $strPathAddon, self::$DIRECTORY_GROUP);
            }
        }
    }

    /**
     * validateTags
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function validateTags()
    {

        $this->getModelTags();
        $this->arrNewTagIds = array();

        /**
         * get tag ids
         */
        foreach ($this->arrNewTags as $mixedTag) {
            $mixedTag = trim($mixedTag);
            if ($mixedTag != '') {
                try {
                    if (is_numeric($mixedTag)) {
                        $objTagData = $this->objModelTags->loadTag($mixedTag);
                    } else {
                        $objTagData = $this->objModelTags->loadTagByName($mixedTag);
                    }

                    /**
                     * if the tag exists
                     */
                    if (count($objTagData) > 0) {
                        $objTag = $objTagData->current();

                        /**
                         * fill in tagIds array
                         */
                        if (!in_array($objTag->id, $this->arrNewTagIds)) {
                            $this->arrNewTagIds[] = $objTag->id;
                        }

                    } else {
                        /**
                         * else, insert new tag
                         */
                        $this->arrNewTagIds[] = $this->objModelTags->addTag($mixedTag);
                    }
                } catch (PDOException $exc) {
                    $this->core->logger->logException($exc);
                }
            }
        }
    }

    /**
     * getModelFile
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelFile()
    {
        if (null === $this->objModelFile) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Files.php';
            $this->objModelFile = new Model_Files();
            $this->objModelFile->setLanguageId($this->intLanguageId);
        }

        return $this->objModelFile;
    }

    /**
     * getModelUtilities
     * @return Model_Utilities
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelUtilities()
    {
        if (null === $this->objModelUtilities) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Utilities.php';
            $this->objModelUtilities = new Model_Utilities();
            $this->objModelUtilities->setLanguageId($this->intLanguageId);
        }

        return $this->objModelUtilities;
    }

    /**
     * getModelTags
     * @return Model_Tags
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelTags()
    {
        if (null === $this->objModelTags) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Tags.php';
            $this->objModelTags = new Model_Tags();
            $this->objModelTags->setLanguageId($this->intLanguageId);
        }

        return $this->objModelTags;
    }

    /**
     * setUpload
     * @param Zend_File_Transfer_Adapter_Abstract $objUpload
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setUpload(Zend_File_Transfer_Adapter_Abstract $objUpload)
    {
        $this->objUpload = $objUpload;
    }

    /**
     * setId
     * @param integer $intId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setId($intId)
    {
        $this->intId = $intId;
    }

    /**
     * getId
     * @return integer $intId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getId()
    {
        return $this->intId;
    }

    /**
     * setFileId
     * @param string $strFileId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setFileId($strFileId)
    {
        $this->strFileId = $strFileId;
    }

    /**
     * getFileId
     * @return string $strFileId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getFileId()
    {
        return $this->strFileId;
    }

    /**
     * setUploadPath
     * @param string $strUploadPath
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setUploadPath($strUploadPath)
    {
        $this->strUploadPath = $strUploadPath;
    }

    /**
     * getUploadPath
     * @param boolean $blnWithSegmentPath
     * @return string $strUploadPath
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getUploadPath($blnWithSegmentPath = true)
    {
        if ($blnWithSegmentPath === true) {
            return $this->strUploadPath . $this->strSegmentPath;
        } else {
            return $this->strUploadPath;
        }
    }

    /**
     * setPublicFilePath
     * @param string $strPublicFilePath
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setPublicFilePath($strPublicFilePath)
    {
        $this->strPublicFilePath = $strPublicFilePath;
    }

    /**
     * getPublicFilePath
     * @param boolean $blnWithSegmentPath
     * @return string $strPublicFilePath
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPublicFilePath($blnWithSegmentPath = true)
    {
        if ($blnWithSegmentPath === true) {
            return $this->strPublicFilePath . $this->strSegmentPath;
        } else {
            return $this->strPublicFilePath;
        }
    }

    /**
     * setTmpFilePath
     * @param string $strTmpFilePath
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setTmpFilePath($strTmpFilePath)
    {
        $this->core->logger->debug('setTmpFilePath: ' . $strTmpFilePath);
        $this->strTmpFilePath = $strTmpFilePath;
    }

    /**
     * getTmpFilePath
     * @return string $strTmpFilePath
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getTmpFilePath()
    {
        return $this->strTmpFilePath;
    }

    /**
     * setExtension
     * @param string $strExtension
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setExtension($strExtension)
    {
        $this->strExtension = $strExtension;
    }

    /**
     * getExtension
     * @return string $strExtension
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getExtension()
    {
        return $this->strExtension;
    }

    /**
     * setVersion
     * @param integer $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setVersion($intVersion)
    {
        $this->intVersion = $intVersion;
    }

    /**
     * getVersion
     * @return integer $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getVersion()
    {
        return $this->intVersion;
    }

    /**
     * setUserId
     * @param integer $intUserId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setUserId($intUserId)
    {
        $this->intUserId = $intUserId;
    }

    /**
     * getUserId
     * @return integer $intUserId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getUserId()
    {
        return $this->intUserId;
    }

    /**
     * setTitle
     * @param string $strTitle
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setTitle($strTitle)
    {
        $this->strTitle = $strTitle;
    }

    /**
     * getTitle
     * @return string $strTitle
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getTitle()
    {
        return $this->strTitle;
    }

    /**
     * setSize
     * @param double $dblSize
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setSize($dblSize)
    {
        $this->dblSize = $dblSize;
    }

    /**
     * getSize
     * @return double $dblSize
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getSize()
    {
        return $this->dblSize;
    }

    /**
     * setParentId
     * @param integer $intParentId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setParentId($intParentId)
    {
        $this->intParentId = $intParentId;
    }

    /**
     * getParentId
     * @return integer $intParentId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getParentId()
    {
        return $this->intParentId;
    }

    /**
     * setParentTypeId
     * @param integer $intParentTypeId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setParentTypeId($intParentTypeId)
    {
        $this->intParentTypeId = $intParentTypeId;
    }

    /**
     * getParentTypeId
     * @return integer $intParentTypeId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getParentTypeId()
    {
        return $this->intParentTypeId;
    }

    /**
     * getIsImage
     * @return boolean $blnIsImage
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getIsImage($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnIsImage == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnIsImage;
        }
    }

    /**
     * getFileDatas
     * @return array $arrFileDatas
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getFileDatas()
    {
        return $this->arrFileDatas;
    }

    /**
     * setFileDatas
     * @param array $arrFileDatas
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setFileDatas($arrFileDatas)
    {
        $this->arrFileDatas = $arrFileDatas;
    }

    /**
     * getXDim
     * @return integer $intXDim
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getXDim()
    {
        return $this->intXDim;
    }

    /**
     * setXDim
     * @param integer $intXDim
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setXDim($intXDim)
    {
        $this->intXDim = $intXDim;
    }

    /**
     * getYDim
     * @return integer $intYDim
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getYDim()
    {
        return $this->intYDim;
    }

    /**
     * setYDim
     * @param integer $intYDim
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setYDim($intYDim)
    {
        $this->intYDim = $intYDim;
    }

    /**
     * getMimeType
     * @return string $strMimeType
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getMimeType()
    {
        return $this->strMimeType;
    }

    /**
     * setMimeType
     * @param string $strMimeType
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setMimeType($strMimeType)
    {
        $this->strMimeType = $strMimeType;
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

    /**
     * setSegmenting
     * @param boolean $blnSegmenting
     */
    public function setSegmenting($blnSegmenting)
    {
        $this->blnSegmenting = $blnSegmenting;
    }

    /**
     * getSegmenting
     * @param boolean $blnSegmenting
     */
    public function getSegmenting()
    {
        return $this->blnSegmenting;
    }

    /**
     * setSegmentPath
     * @param string $strSegmentPath
     */
    public function setSegmentPath($strSegmentPath)
    {
        $this->strSegmentPath = $strSegmentPath;
    }

    /**
     * getSegmentPath
     * @return string $strSegmentPath
     */
    public function getSegmentPath()
    {
        return $this->strSegmentPath;
    }

    /**
     * setNumberOfSegments
     * @param integer $intNumberOfSegments
     */
    public function setNumberOfSegments($intNumberOfSegments)
    {
        $this->intNumberOfSegments = $intNumberOfSegments;
    }

    /**
     * getNumberOfSegments
     * @param integer $intNumberOfSegments
     */
    public function getNumberOfSegments()
    {
        return $this->intNumberOfSegments;
    }
}

?>