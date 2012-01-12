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
 * Media_Service_Media
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-06: Cornelius Hansjakob

 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Service_Media {

  /**
   * @var Core
   */
  protected $core;
  
  /**
   * @var Model_Files
   */
  protected $objModelFiles;

  /**
   * Constructor
   */
  public function __construct() {
    $this->core = Zend_Registry::get('Core');
  }

  /**
   * videoToLoad
   * @param string $strDomainBase
   * @param string $strHash 
   * @return object
   */
  public function videoToLoad($strDomainBase, $strHash) {
    $this->core->logger->debug('media->services->Media->videoToLoad('.$strDomainBase.', '.$strHash.')');
    try {
      $objReturn = new stdClass();
      
      // validate hash
      if($strHash == $this->core->config->crypt->key){
        // define enviroment
        $objReturn->enviroment = APPLICATION_ENV;
        
        $objFiles = $this->getModelFiles()->loadFilesByStreamStatus(18, false, true); // 18 = rootlevel of videos
        
        if(count($objFiles) > 0){
          foreach($objFiles as $objFileData){
            $objReturn->fileLinks[] = array('id'       => $objFileData->id,
            																'filename' => $objFileData->filename, 
                                            'path'     => $objFileData->path,
            															  'url'      => 'http://'.$strDomainBase.'/zoolu-website/media/download/'.$objFileData->id.'/'.urlencode($objFileData->filename));  
          }  
        }  
      }else{
        $objReturn->message = 'Forbidden!';  
      }
      return $objReturn;
    }catch(Exception $exc){
      $this->core->logger->err($exc->getMessage());
    }
  }
  
  /**
   * videoLoadSuccessful
   * @param integer $intFileId
   * @param string $strHash
   * @return object
   */
  public function videoLoadSuccessful($intFileId, $strHash){
    $this->core->logger->debug('media->services->Media->videoLoadSuccessful('.$intFileId.' ,'.$strHash.')');
    try {
      $objReturn = new stdClass();
      
      // validate hash
      if($strHash == $this->core->config->crypt->key){
        $intEffectedRows = $this->getModelFiles()->changeFileStreamStatusById($intFileId, true);      
        if($intEffectedRows > 0){
          $objReturn->successful = true;  
        }else{
          $objReturn->successful = false;  
        }
      }else{
        $objReturn->successful = false;
        $objReturn->message = 'Forbidden!'; 
      }
      return $objReturn;
    }catch(Exception $exc){
      $this->core->logger->err($exc->getMessage());
    }  
  }
  
  /**
   * getModelFiles
   * @return Model_Files
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getModelFiles(){
    if (null === $this->objModelFiles) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Files.php';
      $this->objModelFiles = new Model_Files();
    }
    return $this->objModelFiles;
  }
}

?>