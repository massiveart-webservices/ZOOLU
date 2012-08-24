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

class MediaController extends Zend_Controller_Action {

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
    public function indexAction() { }

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

        if($intMediaId > 0){
            $objFile = $this->objModelFiles->loadFileById($intMediaId);

            if(count($objFile) > 0){
                $objFileData = $objFile->current();

                $strFilePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->images->path->local->private.$objFileData->path.$objFileData->filename;

                if(file_exists($strFilePath)){
                    if(isset($objFileData->mimeType) && $objFileData->mimeType != ''){
                        $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                        header('Content-Type: '.$objFileData->mimeType);
                        readfile($strFilePath);
                    }else if(isset($objFileData->extension) && $objFileData->extension != ''){
                        $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                        header('Content-Type: image/'.$objFileData->extension);
                        readfile($strFilePath);
                    }else{
                        // no mimetype and no extension
                    }
                }else{
                    // file doesn't exist
                }
            }
        }else{
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

        if($intMediaId > 0){
            $objFile = $this->objModelFiles->loadFileById($intMediaId, $intMediaVersion);

            if(count($objFile) > 0){
                $objFileData = $objFile->current();

                if($intMediaVersion > 0 && $objFileData->version != $objFileData->archiveVersion){
                    $strFileName =  $objFileData->fileId.'.v'.$objFileData->archiveVersion.'.'.$objFileData->archiveExtension;
                    $dblFileSize = $objFileData->archiveSize;
                }else{
                    $strFileName = $objFileData->filename;
                    $dblFileSize = $objFileData->size;
                }

                $strFilePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->documents->path->local->private.$objFileData->path.$strFileName;

                if(file_exists($strFilePath)){
                    if(isset($objFileData->mimeType) && $objFileData->mimeType != ''){

                        if($objFileData->title != ''){
                            $strFileName = urlencode(str_replace('.', '-', $objFileData->title)).'.'.$objFileData->extension;
                        }else if($objFileData->fallbackTitle != ''){
                            $strFileName = urlencode(str_replace('.', '-', $objFileData->fallbackTitle)).'.'.$objFileData->extension;
                        }else{
                            $strFileName = $objFileData->filename;
                        }
                         
                        if($objFileData->idGroup != 0){
                            // if logged in as zoolu user
                            $objAuth = Zend_Auth::getInstance();
                            if($objAuth->hasIdentity()){
                                $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                                $this->setDocumentHeader($strFileName, array('MimeType' => $objFileData->mimeType, 'Size' => $dblFileSize));
                                // Datei ausgeben
                                readfile($strFilePath);
                            }

                            // if logged in as member
                            $objMemberAuth = Zend_Auth::getInstance();
                            $objMemberAuth->setStorage(new Zend_Auth_Storage_Session('Members'));

                            if($objMemberAuth->hasIdentity()){
                                $objMember = $objMemberAuth->getIdentity();
                                if(isset($objMember->groups)){
                                    foreach($objMember->groups as $arrGroup){
                                        if(array_key_exists('id', $arrGroup)){
                                            if($arrGroup['id'] == $objFileData->idGroup){
                                                $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                                                $this->setDocumentHeader($strFileName, array('MimeType' => $objFileData->mimeType, 'Size' => $dblFileSize));
                                                // Datei ausgeben
                                                readfile($strFilePath);
                                            }
                                        }
                                    }
                                }else{
                                    // user is in no group
                                }
                            }else{
                                // not logged in
                            }
                        }else{
                            $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                            $this->setDocumentHeader($strFileName, array('MimeType' => $objFileData->mimeType, 'Size' => $dblFileSize));
                            // Datei ausgeben
                            readfile($strFilePath);
                        }
                    }else{
                        // no mimetype and no extension
                    }
                }else{
                    // file doesn't exist
                }
            }
        }else{
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

        if($intMediaId > 0){
            $objFile = $this->objModelFiles->loadFileById($intMediaId, $intMediaVersion);

            if(count($objFile) > 0){
                $objFileData = $objFile->current();

                if($intMediaVersion > 0 && $objFileData->version != $objFileData->archiveVersion){
                    $strFileName =  $objFileData->fileId.'.v'.$objFileData->archiveVersion.'.'.$objFileData->archiveExtension;
                    $dblFileSize = $objFileData->archiveSize;
                }else{
                    $strFileName = $objFileData->filename;
                    $dblFileSize = $objFileData->size;
                }

                if($objFileData->isImage){
                    $strFileBase = GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->images->path->local->private.$objFileData->path; 
                    $strFilePath = $strFileBase.$strFileName;
                }else if (strpos($objFileData->mimeType, 'video/') !== false || $objFileData->mimeType == 'application/x-shockwave-flash'){
                    $strFileBase = GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->videos->path->local->private.$objFileData->path; 
                    $strFilePath = $strFileBase.$strFileName;
                }else{
                    $strFileBase = GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->documents->path->local->private.$objFileData->path;
                    $strFilePath = $strFileBase.$strFileName;
                }

                if(file_exists($strFilePath)){
                    $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                     
                    if($intMediaVersion > 0 && $objFileData->version != $objFileData->archiveVersion){
                        if($objFileData->title != ''){
                            $strFileName = urlencode(str_replace('.', '-', $objFileData->title)).'.v'.$objFileData->archiveVersion.'.'.$objFileData->archiveExtension;
                        }else if($objFileData->fallbackTitle != ''){
                            $strFileName = urlencode(str_replace('.', '-', $objFileData->fallbackTitle)).'.v'.$objFileData->archiveVersion.'.'.$objFileData->archiveExtension;
                        }else{
                            $strFileName = $objFileData->fileId.'.v'.$objFileData->archiveVersion.'.'.$objFileData->archiveExtension;
                        }
                    }else if($objFileData->title != ''){
                        $strFileName = urlencode(str_replace('.', '-', $objFileData->title)).'.'.$objFileData->extension;
                    }else if($objFileData->fallbackTitle != ''){
                        $strFileName = urlencode(str_replace('.', '-', $objFileData->fallbackTitle)).'.'.$objFileData->extension;
                    }else{
                        $strFileName = $objFileData->filename;
                    }

                    if($objFileData->idGroup != 0){
                        // if logged in as zoolu user
                        $objAuth = Zend_Auth::getInstance();
                        if($objAuth->hasIdentity()){
                            $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                            $result = $this->setDownloadHeader($strFileName, array('Filebase' => $strFileBase, 'Extension' => $objFileData->extension, 'Size' => $dblFileSize));
                            // Datei ausgeben.
                            if($result === null){
                                readfile($strFilePath);
                            }else{
                                if(file_exists($result)) readfile($result);    
                            }
                        }

                        // if logged in as member
                        $objMemberAuth = Zend_Auth::getInstance();
                        $objMemberAuth->setStorage(new Zend_Auth_Storage_Session('Members'));

                        if($objMemberAuth->hasIdentity()){
                            $objMember = $objMemberAuth->getIdentity();
                            if(isset($objMember->groups)){
                                foreach($objMember->groups as $arrGroup){
                                    if(array_key_exists('id', $arrGroup)){
                                        if($arrGroup['id'] == $objFileData->idGroup){
                                            $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                                            $result = $this->setDownloadHeader($strFileName, array('Filebase' => $strFileBase, 'Extension' => $objFileData->extension, 'Size' => $dblFileSize));
                                            // Datei ausgeben.
                                            if($result === null){
                                                readfile($strFilePath);
                                            }else{
                                                if(file_exists($result)) readfile($result);    
                                            }
                                        }
                                    }
                                }
                            }else{
                                // user is in no group
                            }
                        }else{
                            // not logged in
                        }
                    }else{
                        $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                        
                        $result = $this->setDownloadHeader($strFileName, array('Filebase' => $strFileBase, 'Extension' => $objFileData->extension, 'Size' => $dblFileSize));
                        // Datei ausgeben.
                        if($result === null){
                            readfile($strFilePath);
                        }else{
                            if(file_exists($result)) readfile($result);    
                        }
                    }
                }else{
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
        $this->core->logger->debug('website->controllers->MediaController->imageAction()');

        $this->getModelFiles();

        $intMediaId = $this->_getParam('id', 0);

        if($intMediaId > 0){
            $objFile = $this->objModelFiles->loadFileById($intMediaId);

            if(count($objFile) > 0){
                $objFileData = $objFile->current();

                $strFilePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->videos->path->local->private.$objFileData->path.$objFileData->filename;

                if(file_exists($strFilePath)){
                    if(isset($objFileData->mimeType) && $objFileData->mimeType != ''){
                        $this->objModelFiles->increaseDownloadCounter($objFileData->id);
                        header('Content-Type: '.$objFileData->mimeType);
                        readfile($strFilePath);
                    }else{
                        // no mimetype
                    }
                }else{
                    // file doesn't exist
                }
            }
        }else{
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

        if($strMedia != ''){
            $strFileBasePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->forms->path->local->private;
            $strFilePath = $strFileBasePath.$strMedia;

            if(file_exists($strFilePath)){

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
                header("Content-Disposition: attachment; filename=\"".$strMedia."\"");

                header("Content-Transfer-Encoding: binary");

                // Datei ausgeben.
                readfile($strFilePath);

            }else{
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
        header("Content-Disposition: attachment; filename=\"".$strFileName."\"");
         
        if(array_key_exists('MimeType', $arrProperties)) header('Content-Type: '.$arrProperties['MimeType']);
        if(array_key_exists('Size', $arrProperties)) header("Content-Length: ".$arrProperties['Size']);
    }

    /**
     * setDownloadHeader
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    private function setDownloadHeader($strFileName, $arrProperties = array())
    {
        $strFilePath = null;
        
        // check if file extension is for zip
        $blnZip = false;
        if(array_key_exists('Extension', $arrProperties)){
            if(!empty($this->core->sysConfig->media->download->zip_extensions)){
                $arrZipExtensions = array();
                $arrZipExtensions = $this->core->sysConfig->media->download->zip_extensions->toArray(); 
                
                if(in_array($arrProperties['Extension'], $arrZipExtensions)){
                    $blnZip = true; 
                    
                    if(array_key_exists('Filebase', $arrProperties)){
                        // create zip for file
                        $result = Zip::createZip($this->core, array($strFileName => $arrProperties['Filebase'].$strFileName), $arrProperties['Filebase'].$strFileName.'.zip', true);
                        if($result !== true){
                            $this->core->logger->debug('website->controllers->MediaController->setDownloadHeader(): Zip creation failed! '.$strFileName);  
                            return false;      
                        }else{
                            // overwrite file path and filename for return
                            $strFileName = $strFileName.'.zip';
                            $strFilePath = $arrProperties['Filebase'].$strFileName;                              
                        }    
                    }
                    
                }
            }    
        }
        
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
        header("Content-Disposition: attachment; filename=\"".$strFileName."\"");
        
        header("Content-Transfer-Encoding: binary");
        if(array_key_exists('Size', $arrProperties)) header("Content-Length: ".$arrProperties['Size']); 
        
        return $strFilePath;
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
            require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Files.php';
            $this->objModelFiles = new Model_Files();
            $this->objModelFiles->setLanguageId($this->core->intLanguageId); // TODO : get language id
        }
    
        return $this->objModelFiles;
    }
}
?>