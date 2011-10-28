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
 * @package    application.plugins.Gmaps.data.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericDataHelper_Gmaps
 *
 * Helper to save and load Google Maps
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-07-24: Florian Mathis
 *
 * @author Florian Mathis <flo@massiveart.com>
 * @version 1.0
 * @package application.plugins.Gmaps.data.helpers
 * @subpackage Plugin_DataHelper_Gmaps
 */

require_once(dirname(__FILE__).'/../../../../../library/massiveart/generic/data/helpers/Abstract.php');

class Plugin_DataHelper_Gmaps extends GenericDataHelperAbstract  {

  /**
   * @var Model_Pages
   */
  private $objModel;

  private $strType;

  /**
   * save()
   * @param integer $intElementId
   * @param string $strType
   * @param string $strElementId
   * @param integet $intVersion
   * @author Florian Mathis <flo@massiveart.com>
   * @version 1.0
   */
  public function save($intElementId, $strType, $strElementId = null, $intVersion = null){
    try{
    	$this->core->logger->debug('application->plugins->Gmaps->data->helpers->Plugin_DataHelperGmaps->save('.$intElementId.', '.$strType.', '.$strElementId.', '.$intVersion.')');
      $this->strType = $strType;

      $this->getModel();

      $strGmapsLatitude = '';
      if(array_key_exists($this->objElement->name.'Latitude', $_POST)){
        $strGmapsLatitude = $_POST[$this->objElement->name.'Latitude'];
      }

      $strGmapsLongitude = '';
      if(array_key_exists($this->objElement->name.'Longitude', $_POST)){
        $strGmapsLongitude = $_POST[$this->objElement->name.'Longitude'];
      }

      if($strGmapsLongitude != '' && $strGmapsLatitude != ''){
      	$arrValues = array('longitude'  =>  $strGmapsLongitude,
      	                   'latitude' =>  $strGmapsLatitude);
        //$this->objModel->addGmaps($intElementId, $strGmapsLongitude, $strGmapsLatitude);
        $this->objModel->addPlugin($intElementId, $arrValues, 'Gmaps');
        $this->load($intElementId, $strType, $strElementId, $intVersion);
      }else{
        //$this->objModel->removeGmaps($intElementId);
      }

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * load()
   * @param integer $intElementId
   * @param string $strType
   * @param string $strElementId
   * @param integet $intVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function load($intElementId, $strType, $strElementId = null, $intVersion = null){
    try{
    	$this->core->logger->debug('application->plugins->Gmaps->data->helpers->Plugin_DataHelperGmaps->load('.$intElementId.', '.$strType.', '.$strElementId.', '.$intVersion.')');
      $this->strType = $strType;
      
    	$this->getModel();
    	$elementId = $this->strType.'Id';
    	
      $objGmap = $this->objModel->loadPlugin($intElementId, array('longitude', 'latitude'), 'Gmaps');
      $this->objElement->setValue($objGmap[0]);

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * getModel
   * @return type Model
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModel(){
    if($this->objModel === null) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      $strModelFilePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.$this->objElement->Setup()->getModelSubPath().((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s').'.php';
 
      if(file_exists($strModelFilePath)){
        require_once $strModelFilePath;
        $strModel = 'Model_'.((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s');
        $this->objModel = new $strModel();
        $this->objModel->setLanguageId($this->objElement->Setup()->getLanguageId());
      }else{
        throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "'.$this->strType.'"');
      }
    }
    return $this->objModel;
  }
}
?>