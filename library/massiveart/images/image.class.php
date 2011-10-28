<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
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
 * @package    library.massiveart.images
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Image Class extends File
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-11: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.images
 * @subpackage Image
 */

class Image extends File {
  
  /**
   * dispaly option positions
   */
  const POSITION_LEFT_TOP = 'LEFT_TOP'; 
  const POSITION_LEFT_MIDDLE = 'LEFT_MIDDLE';
  const POSITION_LEFT_BOTTOM = 'LEFT_BOTTOM';
  const POSITION_CENTER_TOP = 'CENTER_TOP';
  const POSITION_CENTER_MIDDLE = 'CENTER_MIDDLE';
  const POSITION_CENTER_BOTTOM = 'CENTER_BOTTOM';
  const POSITION_RIGHT_TOP = 'RIGHT_TOP';
  const POSITION_RIGHT_MIDDLE = 'RIGHT_MIDDLE';
  const POSITION_RIGHT_BOTTOM = 'RIGHT_BOTTOM';
  
  protected $arrDefaultImageSizes = array();

  public function __construct(){
    parent::__construct();
    $this->blnIsImage = true;
  }

  /**
   * upload
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function upload($blnIsNewVersion = false){
    $this->core->logger->debug('massiveart.images.Image->upload()');

    try{
     if($this->objUpload != null && $this->objUpload instanceof Zend_File_Transfer_Adapter_Abstract){
        /**
         * first check upload path
         */
        $this->checkUploadPath();

        $arrFileInfos = pathinfo($this->objUpload->getFileName($this->_FILE_NAME));
        $this->strExtension = strtolower($arrFileInfos['extension']);
        $this->strTitle = $arrFileInfos['filename'];
        $this->dblSize = $this->objUpload->getFileSize($this->_FILE_NAME);
        $this->strMimeType = $this->objUpload->getMimeType($this->_FILE_NAME);
        
        if($blnIsNewVersion == true && $this->objFileData instanceof Zend_Db_Table_Row_Abstract){
          $this->strFileId = $this->objFileData->fileId;          
        }else{
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
        $this->objUpload->addFilter('Rename', array('target' => $this->getUploadPath().$this->strFileId.'.'.$this->strExtension,
                                                    'overwrite' => true), $this->_FILE_NAME);
        $this->objUpload->receive($this->_FILE_NAME);

        /**
         * check public file path
         */
        $this->checkPublicFilePath();

        $srcFile = $this->getUploadPath().$this->strFileId.'.'.$this->strExtension;

        $arrImgInfo = getimagesize($srcFile);
	      $this->intXDim = $arrImgInfo[0];
	      $this->intYDim = $arrImgInfo[1];
	      $this->strMimeType = $arrImgInfo['mime'];

        if(count($this->arrDefaultImageSizes) > 0){
          $objImageManipulation = new ImageManipulation();
          $objImageManipulation->setAdapterType($this->core->sysConfig->upload->images->adapter);

          /**
           * get image manipulation adapter
           */
          $objImageAdapter = $objImageManipulation->getAdapter();
          $objImageAdapter->setRawWidth($this->intXDim);
          $objImageAdapter->setRawHeight($this->intYDim);
          $objImageAdapter->setRenderTmpDir($this->getTmpFilePath());

          /**
           * render default image sizes
           */
          foreach($this->arrDefaultImageSizes as $arrImageSize){
            $objImageAdapter->setSource($srcFile);
            $objImageAdapter->setDestination($this->getPublicFilePath().$arrImageSize['folder'].'/'.$this->strFileId.'.'.$this->strExtension);

            $this->checkPublicFilePath($arrImageSize['folder'].'/');

            if(array_key_exists('actions', $arrImageSize) && is_array($arrImageSize['actions'])){
              if(array_key_exists('method', $arrImageSize['actions']['action'])){
                $arrAction = $arrImageSize['actions']['action'] ;
                $strMethode = $arrAction['method'];
                $arrParams = (array_key_exists('params', $arrAction)) ? explode(',', $arrAction['params']) : array();
                if(method_exists($objImageAdapter, $strMethode)){
                  call_user_func_array(array($objImageAdapter, $strMethode), $arrParams);
                }
              }else{
                foreach($arrImageSize['actions']['action']  as $arrAction){
                  if(array_key_exists('method', $arrAction)){
                    $strMethode = $arrAction['method'];
                    $arrParams = (array_key_exists('params', $arrAction)) ? explode(',', $arrAction['params']) : array();
                    if(method_exists($objImageAdapter, $strMethode)){
                      call_user_func_array(array($objImageAdapter, $strMethode), $arrParams);
                    }
                  }
                }
              }
            }
          }
        }
      }
    }catch(Exception $exc){
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * renderAllImages
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function renderAllImages(){
  	$this->core->logger->debug('massiveart.images.Image->renderAllImages()');
  	try{
  	  $this->blnSetOwnerAndGroup = true;
  	    	  
      $this->getModelFile();
      
  		/**
       * check public file path
       */
      $this->checkPublicFilePath();
  		
  		$objImageFiles = $this->objModelFile->getAllImageFiles();
  		
  		if(count($objImageFiles) > 0){
  			foreach($objImageFiles as $objImageFile){
		      
  			  $this->setSegmentPath($objImageFile->path);
  			  $srcFile = $this->getUploadPath().$objImageFile->filename;
      
		      if(count($this->arrDefaultImageSizes) > 0){
		        $objImageManipulation = new ImageManipulation();
		        $objImageManipulation->setAdapterType($this->core->sysConfig->upload->images->adapter);
		
		        /**
		         * get image manipulation adapter
		         */
		        $objImageAdapter = $objImageManipulation->getAdapter();
		        $objImageAdapter->setRawWidth($objImageFile->xDim);
		        $objImageAdapter->setRawHeight($objImageFile->yDim);
		
		        /**
		         * render default image sizes
		         */
		        foreach($this->arrDefaultImageSizes as $arrImageSize){
		          $objImageAdapter->setSource($srcFile);
		          $objImageAdapter->setDestination($this->getPublicFilePath().$arrImageSize['folder'].'/'.$objImageFile->filename);
		
		          $this->checkPublicFilePath($arrImageSize['folder'].'/');
		
		          if(array_key_exists('actions', $arrImageSize) && is_array($arrImageSize['actions'])){
		            if(array_key_exists('method', $arrImageSize['actions']['action'])){
		              $arrAction = $arrImageSize['actions']['action'] ;
		              $strMethode = $arrAction['method'];
		              $arrParams = (array_key_exists('params', $arrAction)) ? explode(',', $arrAction['params']) : array();
		              if(method_exists($objImageAdapter, $strMethode)){
		                call_user_func_array(array($objImageAdapter, $strMethode), $arrParams);
		              }
		            }else{
		              foreach($arrImageSize['actions']['action']  as $arrAction){
		                if(array_key_exists('method', $arrAction)){
		                  $strMethode = $arrAction['method'];
		                  $arrParams = (array_key_exists('params', $arrAction)) ? explode(',', $arrAction['params']) : array();
		                  if(method_exists($objImageAdapter, $strMethode)){
		                    call_user_func_array(array($objImageAdapter, $strMethode), $arrParams);
		                  }
		                }
		              }
		            }
		          }
		        }
		      }
  			}
  		}
  	}catch(Exception $exc){
      $this->core->logger->err($exc);
    }	
  }
  
  /**
   * archive
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  protected function archive(){
    try{
      if($this->objFileData instanceof Zend_Db_Table_Row_Abstract && file_exists($this->getUploadPath().$this->objFileData->filename)){
        rename($this->getUploadPath().$this->objFileData->filename, $this->getUploadPath().$this->objFileData->fileId.'.v'.$this->objFileData->version.'.'.$this->objFileData->extension);
        
        if(count($this->arrDefaultImageSizes) > 0){
          /**
           * rename all image sizes
           */
          foreach($this->arrDefaultImageSizes as $arrImageSize){
            rename($this->getPublicFilePath().$arrImageSize['folder'].'/'.$this->objFileData->filename, $this->getPublicFilePath().$arrImageSize['folder'].'/'.$this->objFileData->fileId.'.v'.$this->objFileData->version.'.'.$this->objFileData->extension);
          }
        }
      }
    }catch(Exception $exc){
      $this->core->logger->err($exc);
    }
  }

  /**
   * setDefaultImageSizes
   * @param array $arrDefaultImageSizes
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function setDefaultImageSizes($arrDefaultImageSizes){
    $this->arrDefaultImageSizes = $arrDefaultImageSizes;
  }

  /**
   * getDefaultImageSizes
   * @return array $arrDefaultImageSizes
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getDefaultImageSizes(){
    return $this->arrDefaultImageSizes;
  }
}

?>