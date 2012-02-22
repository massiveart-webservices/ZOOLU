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
 * @package    application.zoolu.modules.core.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_RootLevels
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-12-15: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Model_RootLevels {

	private $intLanguageId;

  /**
   * @var Model_Table_RootLevels
   */
  protected $objRootLevelTable;
  
  /**
   * @var Model_Table_RootLevelMaintenances
   */
  protected $objRootLevelMaintenanceTable;

  /**
   * @var Model_Table_RootLevelUrls
   */
  protected $objRootLevelUrlTable;

  /**
   * @var Model_Table_RootLevelPermissions
   */
  protected $objRootLevelPermissionTable;
  
  /**
  * @var Model_Table_RootLevelFilters
  */
  protected $objRootLevelFilters;
  
  /**
   * @var Model_Table_RootLevelFilterValues
   */
  protected $objRootLevelFilterValues;
    
  /**
   * @var Core
   */
  private $core;

  /**
   * Constructor
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * getMaintenanceByDomain
   * @param string $strDomain
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getMaintenanceByDomain($strDomain){
    $this->core->logger->debug('core->models->RootLevels->getMaintenanceByDomain('.$strDomain.')');

    $objSelect = $this->getRootLevelMaintenanceTable()->select();
    $objSelect->setIntegrityCheck(false);
    
    if(strpos($strDomain, 'www.') !== false){
      $strDomain = str_replace('www.', '', $strDomain);
    }
    
    $objSelect->from('rootLevelMaintenances', array('id', 'idLanguages', 'isMaintenance', 'maintenance_startdate', 'maintenance_enddate', 'maintenance_url'));
    $objSelect->join('rootLevelUrls', 'rootLevelUrls.url = \''.$strDomain.'\'', array('idRootLevels'));
    $objSelect->where('rootLevelMaintenances.idRootLevels = rootLevelUrls.idRootLevels');
    
    return $this->getRootLevelMaintenanceTable()->fetchAll($objSelect);
  }
  
  /**
   * loadMaintenance
   * @param integer $intRootLevelId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadMaintenance($intRootLevelId, $blnCheckDate = false){
    $this->core->logger->debug('core->models->RootLevels->loadMaintenance('.$intRootLevelId.','.$blnCheckDate.')');
    
    $objSelect = $this->getRootLevelMaintenanceTable()->select();
    $objSelect->from('rootLevelMaintenances', array('id', 'idLanguages', 'isMaintenance', 'maintenance_startdate', 'maintenance_enddate', 'maintenance_url'));
    $objSelect->where('idRootLevels = ?', $intRootLevelId);
    
    $mxReturn = '';
    
    /**
     * check if maintennce is active or not if $blnCheckDate = true 
     */
    if($blnCheckDate){
      $objMaintenanceData = $this->getRootLevelMaintenanceTable()->fetchAll($objSelect);
      
      $blnIsMaintenanceActive = false;      
      if(count($objMaintenanceData) > 0){
        $objMaintenanceData = $objMaintenanceData->current();

        if($objMaintenanceData->isMaintenance == true){          
          if($objMaintenanceData->maintenance_startdate != '' && $objMaintenanceData->maintenance_enddate != ''){          
            if(time() >= strtotime($objMaintenanceData->maintenance_startdate) && time() <= strtotime($objMaintenanceData->maintenance_enddate)){
              $blnIsMaintenanceActive = true;  
            }else{
              $blnIsMaintenanceActive = false;
            } 
          }else if($objMaintenanceData->maintenance_startdate != '' && $objMaintenanceData->maintenance_enddate == ''){            
            if(time() >= strtotime($objMaintenanceData->maintenance_startdate)){
              $blnIsMaintenanceActive = true;  
            }else{
              $blnIsMaintenanceActive = false;
            }  
          }else if($objMaintenanceData->maintenance_startdate == '' && $objMaintenanceData->maintenance_enddate != ''){            
            if(time() <= strtotime($objMaintenanceData->maintenance_enddate)){
              $blnIsMaintenanceActive = true;  
            }else{
              $blnIsMaintenanceActive = false;
            }  
          }else{
            $blnIsMaintenanceActive = true;
          }
        }        
      }      
      $mxReturn = $blnIsMaintenanceActive;
    }else{
      $mxReturn = $this->getRootLevelMaintenanceTable()->fetchAll($objSelect);  
    }    
    return $mxReturn;
  }
  
  /**
   * loadActiveMaintenances
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadActiveMaintenances(){
    $this->core->logger->debug('core->models->RootLevels->loadActiveMaintenances()');
    
    $objSelect = $this->getRootLevelMaintenanceTable()->select();
    $objSelect->from('rootLevelMaintenances', array('id', 'idRootLevels', 'maintenance_startdate', 'maintenance_enddate', 'maintenance_url'));
    $objSelect->where('isMaintenance = ?', 1);

    return $this->getRootLevelMaintenanceTable()->fetchAll($objSelect);
  }
  
  /**
   * saveMaintenance
   * @param integer $intRootLevelId
   * @param array $arrFormData
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function saveMaintenance($intRootLevelId, $arrFormData){
    $this->core->logger->debug('core->models->RootLevels->saveMaintenance('.$intRootLevelId.', '.var_export($arrFormData, true).')');	  
    
    $arrData = array(//'idLanguages'					 => 'NULL',
                     'isMaintenance'         => $arrFormData['isMaintenance'],
                     'maintenance_startdate' => $arrFormData['maintenance_startdate'],
                     'maintenance_enddate'   => $arrFormData['maintenance_enddate'],
                     'maintenance_url'       => $arrFormData['maintenance_url'],
                     'changed'               => date('Y-m-d H:i:s'));
    
    $strWhere = $this->getRootLevelMaintenanceTable()->getAdapter()->quoteInto('idRootLevels = ?', $intRootLevelId);
    //$strWhere .= $this->getRootLevelMaintenanceTable()->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);
  
    $intNumOfEffectedRows = $this->getRootLevelMaintenanceTable()->update($arrData, $strWhere);
    
    if($intNumOfEffectedRows == 0){
      $arrData = array('idRootLevels'          => $intRootLevelId,
                       //'idLanguages'				 => 'NULL',
                       'isMaintenance'         => $arrFormData['isMaintenance'],
                       'maintenance_startdate' => $arrFormData['maintenance_startdate'],
                       'maintenance_enddate'   => $arrFormData['maintenance_enddate'],
                       'maintenance_url'       => $arrFormData['maintenance_url'],
                       'changed'               => date('Y-m-d H:i:s'));
      
      $intNumOfEffectedRows = $this->getRootLevelMaintenanceTable()->insert($arrData);  
    }
    
    return $intNumOfEffectedRows;
  }
  
  /**
  * addRootLevelTypeFilter
  * @param array $arrFilterInformation
  * @param array $arrFilters
  * @author Daniel Rotter
  * @version 1.0
  */
  public function addRootLevelFilter($arrFilterInformation, $arrFilters){
    $this->core->logger->debug('core->models->Model_RootLevels->addRootLevelTypeFilter('.$arrFilterInformation.', '.$arrFilters.')');
  
    $intRootLevelFilterId = $this->getRootLevelFiltersTable()->insert($arrFilterInformation);
  
    foreach($arrFilters as $arrFilter){
      $arrFilter['value'] = implode(',', $arrFilter['value']);
      $arrInsert = array_merge(array('idRootLevelFilters' => $intRootLevelFilterId), $arrFilter);
      $this->getRootLevelFilterValuesTable()->insert($arrInsert);
    }
  }
  
  /**
   * deleteRootLevelFilter
   * @param number $intRootLevelFilterId
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function deleteRootLevelFilter($intRootLevelFilterId){
    $this->core->logger->debug('core->models->Model_RootLevels->deleteRootLevelFilter('.$intRootLevelFilterId.')');
    
    $strWhere = $this->getRootLevelFiltersTable()->getAdapter()->quoteInto('id = ?', $intRootLevelFilterId);
    $this->getRootLevelFiltersTable()->delete($strWhere);
  }
  
  /**
   * loadRootLevelFilters
   * @param number $intRootLevelId
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function loadRootLevelFilters($intRootLevelId){
    $this->core->logger->debug('core->models->Model_RootLevels->loadRootLevelFilters('.$intRootLevelId.')');
    
    $objSelect = $this->getRootLevelFiltersTable()->select()->setIntegrityCheck(false);
    $objSelect->from($this->getRootLevelFiltersTable()->info(Zend_Db_Table::NAME))
              ->join('rootLevels', 'rootLevelFilters.idRootLevels = rootLevels.id', array('idRootLevelTypes', 'idRootLevelGroups'))
              ->where('rootLevelFilters.idRootLevels = ?', $intRootLevelId)
              ->order('filtertitle');
    
    return $this->getRootLevelFiltersTable()->fetchAll($objSelect);
  }
  
  /**
   * loadRootLevelFilter
   * @param number $intRootLevelFilterId
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function loadRootLevelFilter($intRootLevelFilterId){
    $this->core->logger->debug('core->models->Model_RootLevels->loadRootLevelFilters('.$intRootLevelFilterId.')');
    
    $objSelect = $this->getRootLevelFiltersTable()->select()->setIntegrityCheck(false);
    $objSelect->from($this->getRootLevelFiltersTable()->info(Zend_Db_Table::NAME))
              ->where('id = ?', $intRootLevelFilterId);
    
    return $this->getRootLevelFiltersTable()->fetchAll($objSelect);
  }
  
  /**
   * loadRootLevelFilterValues
   * @param integer $intRootLevelFilterId
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function loadRootLevelFilterValues($intRootLevelFilterId){
    $this->core->logger->debug('core->models->Model_RootLevels->loadRootLevelFilterValues('.$intRootLevelFilterId.')');
    
    $objSelect = $this->getRootLevelFilterValuesTable()->select()->where('idRootLevelFilters = ?', $intRootLevelFilterId);
    
    return $this->getRootLevelFiltersTable()->fetchAll($objSelect);
  }
  
  /**
   * loadRootLevelTitle
   * @param integer $intRootLevelId
   * @param integer $intLanguageId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadRootLevelTitle($intRootLevelId, $intLanguageId = null){
    $this->core->logger->debug('core->models->Model_RootLevels->loadRootLevelTitle('.$intRootLevelId.', '.$intLanguageId.')');
    
    if($intLanguageId == null){
      $intLanguageId = $this->core->intLanguageId;
    }
    
    $objSelect = $this->getRootLevelTable()->select()->setIntegrityCheck(false);
    
    $objSelect->from('rootLevels')
              ->join('rootLevelTitles', 'rootLevels.id = rootLevelTitles.idRootLevels')
              ->where('rootLevelTitles.idLanguages = ?', $intLanguageId)
              ->where('rootLevels.id = ?', $intRootLevelId);
    
    return $this->getRootLevelTable()->fetchAll($objSelect);
  }
  
  /**
   * loadRootLevelById
   * @param number $intRootLevelId
   * @return Zend_Db_Table_Rowset
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.o
   */
  public function loadRootLevelById($intRootLevelId){
    $this->core->logger->debug('core->models->Model_RootLevels->loadRootLevelById('.$intRootLevelId.')');
    
    $objSelect = $this->getRootLevelTable()->select()->setIntegrityCheck(false);
    $objSelect->from('rootLevels', array('id', 'idRootLevelGroups', 'idRootLevelTypes'))
              ->where('id = ?', $intRootLevelId);
              
    return $this->getRootLevelTable()->fetchAll($objSelect);
  }
  
  /**
   * loadRootLevelsByModuleId
   * @param integer $intModuleId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadRootLevelsByModuleId($intModuleId, $intEnvironment = null){
    $this->core->logger->debug('core->models->Model_RootLevels->loadRootLevelsByModuleId('.$intModuleId.')');

    $objSelect = $this->getRootLevelTable()->select()->setIntegrityCheck(false);
    
    $strAppEnv = APPLICATION_ENV;
    $intEnvironment = ($intEnvironment == null) ? $this->core->sysConfig->environments->$strAppEnv : $intEnvironment;
    
    $objSelect->from('rootLevels', array('id', 'idRootLevelTypes', 'idRootLevelGroups', 'isSecure', 'idThemes'))
              ->join('rootLevelTitles', 'rootLevels.id = rootLevelTitles.idRootLevels AND rootLevelTitles.idLanguages = '.$this->core->intLanguageId, array('title'))
              ->joinLeft('rootLevelUrls', 'rootLevelUrls.idRootLevels = rootLevels.id AND rootLevelUrls.isMain = 1 AND rootLevelUrls.idEnvironments = '.$intEnvironment, array('rootLevelLanguageId' => 'idLanguages', 'url'))
              ->where('rootLevels.active = 1')
              ->where('rootLevels.idModules = ?', $intModuleId)
              ->order(array('rootLevels.order ASC'));
    
    return $this->getRootLevelTable()->fetchAll($objSelect);
  }
  
  /**
   * Loads the URL from a given rootlevel with the given language
   * @param integer $intRootLevelId
   * @param integer $intLanguageId
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function loadRootLevelUrl($intRootLevelId, $intLanguageId){
      $this->core->logger->debug('core->models->Model_RootLevels->loadRootLevelUrl('.$intRootLevelId.')');
      
      $objSelect = $this->getRootLevelTable()->select()->setIntegrityCheck(false);
    
      $strAppEnv = APPLICATION_ENV;
      $intEnvironment = $this->core->sysConfig->environments->$strAppEnv;
      
      $objSelect->from('rootLevelUrls', array('url'))
                ->where('rootLevelUrls.isMain = 1')
                ->where('rootLevelUrls.idEnvironments = ?', $intEnvironment)
                ->where('rootLevelUrls.idRootLevels = ?', $intRootLevelId)
                ->where('rootLevelUrls.idLanguages = ?', $intLanguageId);
                
      return $this->getRootLevelTable()->fetchRow($objSelect);
  }

  /**
   * getRootLevelTable
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @return Model_Table_RootLevels
   * @version 1.0
   */
  public function getRootLevelTable(){
    if($this->objRootLevelTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/RootLevels.php';
      $this->objRootLevelTable = new Model_Table_RootLevels();
    }
    return $this->objRootLevelTable;
  }
  
  /**
   * getRootLevelMaintenanceTable
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getRootLevelMaintenanceTable(){
    if($this->objRootLevelMaintenanceTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/RootLevelMaintenances.php';
      $this->objRootLevelMaintenanceTable = new Model_Table_RootLevelMaintenances();
    }
    return $this->objRootLevelMaintenanceTable;
  }

  /**
   * getRootLevelUrlTable
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getRootLevelUrlTable(){
    if($this->objRootLevelUrlTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/RootLevelUrls.php';
      $this->objRootLevelUrlTable = new Model_Table_RootLevelUrls();
    }
    return $this->objRootLevelUrlTable;
  }
  
  /**
   * getRootLevelPermissionTable
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getRootLevelPermissionTable(){
    if($this->objRootLevelPermissionTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/RootLevelPermissions.php';
      $this->objRootLevelPermissionTable = new Model_Table_RootLevelPermissions();
    }
    return $this->objRootLevelPermissionTable;
  }
  
  /**
  * getRootLevelPermissionTable
  * @author Daniel Rotter <daniel.rotter@massiveart.com>
  * @version 1.0
  */
  public function getRootLevelFiltersTable(){
    if($this->objRootLevelFilters === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/RootLevelFilters.php';
      $this->objRootLevelFilters = new Model_Table_RootLevelFilters();
    }
    return $this->objRootLevelFilters;
  }
  
  /**
   * getRootLevelPermissionTable
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getRootLevelFilterValuesTable(){
    if($this->objRootLevelFilterValues === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/RootLevelFilterValues.php';
      $this->objRootLevelFilterValues = new Model_Table_RootLevelFilterValues();
    }
    return $this->objRootLevelFilterValues;
  }

  /**
   * setLanguageId
   * @param integer $intLanguageId
   */
  public function setLanguageId($intLanguageId){
    $this->intLanguageId = $intLanguageId;
  }

  /**
   * getLanguageId
   * @param integer $intLanguageId
   */
  public function getLanguageId(){
    return $this->intLanguageId;
  }
}

?>