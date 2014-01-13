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
 * MediaController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-12-02: Cornelius Hansjakob
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class MediaController extends Zend_Controller_Action
{

    /**
     * @var Core
     */
    private $core;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    /**
     * preDispatch
     * Called before action method.
     *
     * @return void
     * @author Thomas Schedler <cha@massiveart.com>
     * @version 1.0
     */
    public function preDispatch()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->core = Zend_Registry::get('Core');
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
     * imageAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function imageAction()
    {
        $this->core->logger->debug('website->controllers->MediaController->imageAction()');

        $this->getModelFiles();

        $intMediaId = $this->_getParam('id', 0);

        if ($intMediaId > 0) {
            $objFile = $this->objModelFiles->loadFileById($intMediaId);

            if (count($objFile) > 0) {
                $objFileData = $objFile->current();

                $strFilePath = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->images->path->local->private . $objFileData->path . $objFileData->filename;

                if (file_exists($strFilePath)) {
                    if (isset($objFileData->mimeType) && $objFileData->mimeType != '') {
                        $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                        header('Content-Type: ' . $objFileData->mimeType);
                        readfile($strFilePath);
                    } else if (isset($objFileData->extension) && $objFileData->extension != '') {
                        $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                        header('Content-Type: image/' . $objFileData->extension);
                        readfile($strFilePath);
                    } else {
                        // no mimetype and no extension
                    }
                } else {
                    // file doesn't exist
                }
            }
        } else {
            // no file id in url
        }
    }

    /**
     * documentAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function documentAction()
    {
        $this->core->logger->debug('website->controllers->MediaController->documentAction()');

        $this->getModelFiles();

        $intMediaId = $this->_getParam('id', 0);
        $intMediaVersion = $this->_getParam('v', 0);

        if ($intMediaId > 0) {
            $objFile = $this->objModelFiles->loadFileById($intMediaId, $intMediaVersion);

            if (count($objFile) > 0) {
                $objFileData = $objFile->current();

                if ($intMediaVersion > 0 && $objFileData->version != $objFileData->archiveVersion) {
                    $strFileName = $objFileData->fileId . '.v' . $objFileData->archiveVersion . '.' . $objFileData->archiveExtension;
                    $dblFileSize = $objFileData->archiveSize;
                } else {
                    $strFileName = $objFileData->filename;
                    $dblFileSize = $objFileData->size;
                }

                $strFilePath = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->documents->path->local->private . $objFileData->path . $strFileName;

                if (file_exists($strFilePath)) {
                    if (isset($objFileData->mimeType) && $objFileData->mimeType != '') {

                        if ($objFileData->title != '') {
                            $strFileName = urlencode(str_replace('.', '-', $objFileData->title)) . '.' . $objFileData->extension;
                        } else if ($objFileData->fallbackTitle != '') {
                            $strFileName = urlencode(str_replace('.', '-', $objFileData->fallbackTitle)) . '.' . $objFileData->extension;
                        } else {
                            $strFileName = $objFileData->filename;
                        }

                        if ($objFileData->idGroup != 0) {
                            // if logged in as zoolu user
                            $objAuth = Zend_Auth::getInstance();
                            if ($objAuth->hasIdentity()) {
                                $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                                $this->setDocumentHeader($strFileName, array('MimeType' => $objFileData->mimeType, 'Size' => $dblFileSize));
                                // Datei ausgeben
                                readfile($strFilePath);
                            }

                            // if logged in as member
                            $objMemberAuth = Zend_Auth::getInstance();
                            $objMemberAuth->setStorage(new Zend_Auth_Storage_Session('Members'));

                            if ($objMemberAuth->hasIdentity()) {
                                $objMember = $objMemberAuth->getIdentity();
                                if (isset($objMember->groups)) {
                                    foreach ($objMember->groups as $arrGroup) {
                                        if (array_key_exists('id', $arrGroup)) {
                                            if ($arrGroup['id'] == $objFileData->idGroup) {
                                                $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                                                $this->setDocumentHeader($strFileName, array('MimeType' => $objFileData->mimeType, 'Size' => $dblFileSize));
                                                // Datei ausgeben
                                                readfile($strFilePath);
                                            }
                                        }
                                    }
                                } else {
                                    // user is in no group
                                }
                            } else {
                                // not logged in
                            }
                        } else {
                            $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                            $this->setDocumentHeader($strFileName, array('MimeType' => $objFileData->mimeType, 'Size' => $dblFileSize));
                            // Datei ausgeben
                            readfile($strFilePath);
                        }
                    } else {
                        // no mimetype and no extension
                    }
                } else {
                    // file doesn't exist
                }
            }
        } else {
            // no file id in url
        }
    }

    /**
     * downloadAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function downloadAction()
    {
        $this->core->logger->debug('website->controllers->MediaController->downloadAction()');

        $this->getModelFiles();

        $intMediaId = $this->_getParam('id', 0);
        $intMediaVersion = $this->_getParam('v', 0);

        if ($intMediaId > 0) {
            $objFile = $this->objModelFiles->loadFileById($intMediaId, $intMediaVersion);

            if (count($objFile) > 0) {
                $objFileData = $objFile->current();

                if ($intMediaVersion > 0 && $objFileData->version != $objFileData->archiveVersion) {
                    $strFileName = $objFileData->fileId . '.v' . $objFileData->archiveVersion . '.' . $objFileData->archiveExtension;
                    $dblFileSize = $objFileData->archiveSize;
                } else {
                    $strFileName = $objFileData->filename;
                    $dblFileSize = $objFileData->size;
                }

                if ($objFileData->isImage) {
                    $strFileBase = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->images->path->local->private . $objFileData->path;
                    $strFilePath = $strFileBase . $strFileName;
                } else if (strpos($objFileData->mimeType, 'video/') !== false || $objFileData->mimeType == 'application/x-shockwave-flash') {
                    $strFileBase = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->videos->path->local->private . $objFileData->path;
                    $strFilePath = $strFileBase . $strFileName;
                } else {
                    $strFileBase = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->documents->path->local->private . $objFileData->path;
                    $strFilePath = $strFileBase . $strFileName;
                }

                if (file_exists($strFilePath)) {

                    if ($intMediaVersion > 0 && $objFileData->version != $objFileData->archiveVersion) {
                        if ($objFileData->title != '') {
                            $strDesiredFileName = urlencode(str_replace('.', '-', $objFileData->title)) . '.v' . $objFileData->archiveVersion . '.' . $objFileData->archiveExtension;
                        } else if ($objFileData->fallbackTitle != '') {
                            $strDesiredFileName = urlencode(str_replace('.', '-', $objFileData->fallbackTitle)) . '.v' . $objFileData->archiveVersion . '.' . $objFileData->archiveExtension;
                        } else {
                            $strDesiredFileName = $objFileData->fileId . '.v' . $objFileData->archiveVersion . '.' . $objFileData->archiveExtension;
                        }
                    } else if ($objFileData->title != '') {
                        $strDesiredFileName = urlencode(str_replace('.', '-', $objFileData->title)) . '.' . $objFileData->extension;
                    } else if ($objFileData->fallbackTitle != '') {
                        $strDesiredFileName = urlencode(str_replace('.', '-', $objFileData->fallbackTitle)) . '.' . $objFileData->extension;
                    } else {
                        $strDesiredFileName = $objFileData->filename;
                    }

                    if ($objFileData->idGroup != 0) {
                        // if logged in as zoolu user
                        $objAuth = Zend_Auth::getInstance();
                        if ($objAuth->hasIdentity()) {
                            $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                            $this->sendDownloadPackage($strFileBase, $strFileName, array('DesiredFileName' => $strDesiredFileName, 'Extension' => $objFileData->extension, 'Size' => $dblFileSize));
                        }

                        // if logged in as member
                        $objMemberAuth = Zend_Auth::getInstance();
                        $objMemberAuth->setStorage(new Zend_Auth_Storage_Session('Members'));

                        if ($objMemberAuth->hasIdentity()) {
                            $objMember = $objMemberAuth->getIdentity();
                            if (isset($objMember->groups)) {
                                foreach ($objMember->groups as $arrGroup) {
                                    if (array_key_exists('id', $arrGroup)) {
                                        if ($arrGroup['id'] == $objFileData->idGroup) {
                                            $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                                            $this->sendDownloadPackage($strFileBase, $strFileName, array('DesiredFileName' => $strDesiredFileName, 'Extension' => $objFileData->extension, 'Size' => $dblFileSize));
                                        }
                                    }
                                }
                            } else {
                                // user is in no group
                            }
                        } else {
                            // not logged in
                        }
                    } else {
                        $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                        $this->sendDownloadPackage($strFileBase, $strFileName, array('DesiredFileName' => $strDesiredFileName, 'Extension' => $objFileData->extension, 'Size' => $dblFileSize));
                    }
                } else {
                    // file doesn't exist
                }
            }
        }
    }

    /**
     * videoAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function videoAction()
    {
        $this->core->logger->debug('website->controllers->MediaController->videoAction()');

        $this->getModelFiles();

        $intMediaId = $this->_getParam('id', 0);

        if ($intMediaId > 0) {
            $objFile = $this->objModelFiles->loadFileById($intMediaId);

            if (count($objFile) > 0) {
                $objFileData = $objFile->current();

                $strFilePath = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->videos->path->local->private . $objFileData->path . $objFileData->filename;

                if (file_exists($strFilePath)) {
                    if (isset($objFileData->mimeType) && $objFileData->mimeType != '') {
                        $this->objModelFiles->increaseDownloadCounter($objFileData->id);

                        if ($this->core->sysConfig->media->download->chunk_videos == 'true') {
                            $fileStream = @fopen ( $strFilePath, 'rb' );

                            $size = filesize ( $strFilePath ); // File size
                            $length = $size; // Content length
                            $start = 0; // Start byte
                            $end = $size - 1; // End byte

                            header('Content-Type: ' . $objFileData->mimeType);
                            //header("Accept-Ranges: 0-$length");
                            header("Accept-Ranges: bytes");
                            if (isset($_SERVER['HTTP_RANGE'])) {

                                $chunk_start = $start;
                                $chunk_end = $end;

                                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                                if (strpos($range, ',') !== false) {
                                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                    header("Content-Range: bytes $start-$end/$size");
                                    exit;
                                }
                                if ($range == '-') {
                                    $chunk_start = $size - substr($range, 1);
                                }else{
                                    $range = explode('-', $range);
                                    $chunk_start = $range[0];
                                    $chunk_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                                }
                                $chunk_end = ($chunk_end > $end) ? $end : $chunk_end;
                                if ($chunk_start > $chunk_end || $chunk_start > $size - 1 || $chunk_end >= $size) {
                                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                    header("Content-Range: bytes $start-$end/$size");
                                    exit;
                                }
                                $start = $chunk_start;
                                $end = $chunk_end;
                                $length = $end - $start + 1;
                                fseek($fileStream, $start);
                                header('HTTP/1.1 206 Partial Content');
                            }
                            header("Content-Range: bytes $start-$end/$size");
                            header("Content-Length: ".$length);

                            $buffer = 1024 * 8;
                            while(!feof($fileStream) && ($pointer = ftell($fileStream)) <= $end) {

                                if ($pointer + $buffer > $end) {
                                    $buffer = $end - $pointer + 1;
                                }
                                set_time_limit(0);
                                echo fread($fileStream, $buffer);
                                flush();
                            }

                            fclose($fileStream);
                            exit();
                        } else {
                            header('Content-Type: ' . $objFileData->mimeType);
                            readfile($strFilePath);
                        }
                    } else {
                        // no mimetype
                    }
                } else {
                    // file doesn't exist
                }
            }
        } else {
            // no file id in url
        }
    }

    /**
     * formAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function formAction()
    {
        $this->core->logger->debug('website->controllers->MediaController->formAction()');

        $strMedia = ((isset($_GET['file'])) ? $_GET['file'] : '');

        if ($strMedia != '') {
            $strFileBasePath = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->forms->path->local->private;
            $strFilePath = $strFileBasePath . $strMedia;

            if (file_exists($strFilePath)) {

                // fix for IE catching or PHP bug issue
                header("Pragma: public");
                header("Expires: 0"); // set expiration time
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                // browser must download file from server instead of cache

                // force download dialog
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");

                // Passenden Dateinamen im Download-Requester vorgeben,
                header("Content-Disposition: attachment; filename=\"" . $strMedia . "\"");

                header("Content-Transfer-Encoding: binary");

                // Datei ausgeben.
                readfile($strFilePath);

            } else {
                // file doesn't exist
            }
        }
    }

    /**
     * setDocumentHeader
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    private function setDocumentHeader($strFileName, $arrProperties = array())
    {
        // fix for IE catching or PHP bug issue
        header("Pragma: public");
        header("Expires: 0"); // set expiration time
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        // browser must download file from server instead of cache

        // Passenden Dateinamen im Download-Requester vorgeben,
        header("Content-Disposition: attachment; filename=\"" . $strFileName . "\"");

        if (array_key_exists('MimeType', $arrProperties)) header('Content-Type: ' . $arrProperties['MimeType']);
        if (array_key_exists('Size', $arrProperties)) header("Content-Length: " . $arrProperties['Size']);
    }

    /**
     * @param string $strFileBase
     * @param string $strFileName
     * @param array $arrProperties
     * @return string
     */
    protected function zipFileIfNecessary($strFileBase, $strFileName, $arrProperties = array())
    {
        $strExtension = '';
        if (array_key_exists('Extension', $arrProperties)) {
            if (!empty($this->core->sysConfig->media->download->zip_extensions)) {
                $arrZipExtensions = $this->core->sysConfig->media->download->zip_extensions->toArray();

                if (in_array($arrProperties['Extension'], $arrZipExtensions)) {

                    if ($strFileBase) {
                        // create zip for file
                        $result = Zip::createZip($this->core, array($strFileName => $strFileBase . $strFileName), $strFileBase . $strFileName . '.zip', true);
                        if ($result !== true) {
                            $this->core->logger->err('website->controllers->MediaController->zipFileIfNecessary(): Zip creation failed! ' . $strFileName);
                        } else {
                            $strExtension = '.zip';
                        }
                    }
                }
            }
        }
        return $strExtension;
    }

    /**
     * @param string $strFileBase
     * @param string $strFileName
     * @param array $arrProperties
     */
    protected function sendDownloadPackage($strFileBase, $strFileName, $arrProperties = array())
    {
        $this->core->logger->debug('website->controllers->MediaController->sendDownloadPackage(' . $strFileBase . ', ' . $strFileName . ')');
        $strRealPath = realpath($strFileBase . $strFileName);
        
        if (file_exists($strRealPath)) {

            // zip file if necessary
            $strZipExtension = $this->zipFileIfNecessary($strFileBase, $strFileName, $arrProperties);
            if ($strZipExtension !== '') {
                $strRealPath = realpath($strFileBase . $strFileName . $strZipExtension);
            }
            
            // overwrite file name
            if (array_key_exists('DesiredFileName', $arrProperties)) {
                $strFileName = $arrProperties['DesiredFileName'] . $strZipExtension;
            }

            // fix for IE catching or PHP bug issue
            if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false) {
                header('Pragma: public');
                header('Expires: 0'); // set expiration time
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Content-Disposition: attachment; filename="' . basename($strFileName) . '";');
            } else {
                header('Content-Disposition: attachment; filename=' . urlencode(basename($strFileName)) . ';');
            }

            // force download dialog
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');

            if ($this->hasXSendFile() && strpos($strFileName, '.zip') == false) {  // Bugfix: file is empty if is zip
                // Sending file via mod_xsendfile
                $this->core->logger->info('website->controllers->MediaController->sendDownloadPackage(): X-Sendfile');
                header('X-Sendfile: ' . $strRealPath);
            } else {
                header('Content-Transfer-Encoding: binary');

                if (array_key_exists('Size', $arrProperties)) {
                    header('Content-Length: ' . $arrProperties['Size']);
                }

                $this->core->logger->info('website->controllers->MediaController->sendDownloadPackage(): readfile');
                readfile($strRealPath);
            }
        } else {
            // file doesn't exist
        }
    }
    
    /**
     * mulitDownloadAction
     * @author Raphael Stocker <rst@massiveart.com>
     * @version 1.0
     */
    public function multiDownloadAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->core->logger->debug('website->controllers->MediaController->multiDownloadAction()');
        $this->getModelFiles();
        $strFileIds = '';
        if (isset($_GET['fileIds'])) {
           foreach ($_GET['fileIds'] as $strFileId) {
               $strFileIds .= '[' . $strFileId . ']';
           }
        }
        $arrFileObjects = $this->getModelFiles()->loadFilesByIdForMultiDownload($strFileIds);
        
        $intMediaVersion = 1; // fix
        
        if (count($arrFileObjects) > 1) {
            $arrFilesToZip = array();
            foreach ($arrFileObjects as $objFileData) {
                if ($intMediaVersion > 0 && $objFileData->version != $objFileData->archiveVersion) {
                    $strFileName = $objFileData->fileId . '.v' . $objFileData->archiveVersion . '.' . $objFileData->archiveExtension;
                } else {
                    $strFileName = $objFileData->filename;
                }
                if ($objFileData->isImage) {
                    $strFileBase = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->images->path->local->private . $objFileData->path;
                    $strFilePath = $strFileBase . $strFileName;
                } else if (strpos($objFileData->mimeType, 'video/') !== false || $objFileData->mimeType == 'application/x-shockwave-flash') {
                    $strFileBase = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->videos->path->local->private . $objFileData->path;
                    $strFilePath = $strFileBase . $strFileName;
                } else {
                    $strFileBase = GLOBAL_ROOT_PATH . $this->core->sysConfig->upload->documents->path->local->private . $objFileData->path;
                    $strFilePath = $strFileBase . $strFileName;
                }

                if (file_exists($strFilePath)) {
                    $arrFilesToZip[$strFileName] = $strFilePath;
                }
                 
            }
            $strFileSelection = '';
            foreach ($arrFilesToZip as $file) {
                $strFileSelection .= $file;
            } 
            $strFileSelectionHash = hash('md5', $strFileSelection);
            $strDestinationZipBase = GLOBAL_ROOT_PATH . 'tmp/cache/zip_downloads/';
            $strDestinationZipExt =  '.zip';
            $strDestinationZipFile = $strDestinationZipBase . $strFileSelectionHash . $strDestinationZipExt;
            $blnZipFileAvailable = false; 
            if (!file_exists($strDestinationZipFile)) {
                $blnZipFileAvailable = Zip::createZip($this->core, $arrFilesToZip, $strDestinationZipFile, false);
            } else {
                $blnZipFileAvailable = true;
            }
            if ($blnZipFileAvailable) {
                
            } else {
                throw new Exception('An error occured by downloading the ziped file: '.$strDestinationZipFile);
            }
            $this->sendDownloadPackage($strDestinationZipBase, $strFileSelectionHash.$strDestinationZipExt, array('DesiredFileName' => 'Hoval-Dokumentpackage-' . date('Ymd') . '.zip'));
                        
        } else if (count($arrFileObjects) == 1) {
            $this->_forward('download', 'media', null, array('id' => $arrFileObjects[0]->id));  
        }
        
    }

    /**
     * @return bool
     */
    protected function hasXSendFile()
    {
        // this will return false if mod_xsendfile is not loaded as a module
        return in_array('mod_xsendfile', apache_get_modules());
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
            $this->objModelFiles->setLanguageId($this->core->intLanguageId); // TODO : get language id
        }

        return $this->objModelFiles;
    }
}
