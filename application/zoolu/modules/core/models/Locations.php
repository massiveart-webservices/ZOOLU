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
 * @package    application.zoolu.modules.core.models
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Locations
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-06: Thomas Schedler
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Locations {
  
  private $intLanguageId;
  
  /**
   * @var Model_Table_Locations 
   */
  protected $objLocationsTable;
  
  /**
   * @var Model_Table_Units 
   */
  protected $objUnitTable;
  
  /**
   * @var Core
   */
  private $core;  
  
  /**
   * Constructor 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * loadNavigation
   * @param integer $intRootLevelId 
   * @param integer $intItemId
   * @param boolean $blnOnlyUnits
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadNavigation($intRootLevelId, $intItemId, $blnOnlyUnits = false){
    $this->core->logger->debug('core->models->Locations->loadNavigation('.$intRootLevelId.','.$intItemId.')');
  	
    if($blnOnlyUnits == false){
      $sqlStmt = $this->core->dbh->query("SELECT id, title, genericFormId, version, type
                                        FROM (SELECT units.id, unitTitles.title, genericForms.genericFormId, genericForms.version, 'unit' AS type
                                                FROM units
                                              LEFT JOIN unitTitles ON 
                                                unitTitles.idUnits = units.id AND 
                                                unitTitles.idLanguages = ?  
                                              INNER JOIN genericForms ON genericForms.id = units.idGenericForms
                                              WHERE units.idRootLevels = ?  AND units.idParentUnit = ?
                                            UNION
                                            SELECT locations.id, locations.name AS title, genericForms.genericFormId, genericForms.version, 'location'  AS type
                                                FROM locations  
                                              INNER JOIN units ON units.id = locations.idUnits AND units.idRootLevels = ?
                                              INNER JOIN genericForms ON genericForms.id = locations.idGenericForms
                                              WHERE locations.idUnits = ?) 
                                        AS tbl ORDER BY title", array($this->intLanguageId, $intRootLevelId, $intItemId, $intRootLevelId, $intItemId));  
    }else{
      $sqlStmt = $this->core->dbh->query("SELECT units.id, unitTitles.title, genericForms.genericFormId, genericForms.version, 'unit' AS type
                                                FROM units
                                              LEFT JOIN unitTitles ON 
                                                unitTitles.idUnits = units.id AND 
                                                unitTitles.idLanguages = ?  
                                              INNER JOIN genericForms ON genericForms.id = units.idGenericForms
                                              WHERE units.idRootLevels = ?  AND units.idParentUnit = ? ORDER BY title", array($this->intLanguageId, $intRootLevelId, $intItemId)); 
    }
    
    return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
  }
    
  /**
   * loadLocationsByUnitId
   * @param integer $intUnitId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadLocationsByUnitId($intUnitId){
    $this->core->logger->debug('core->models->Locations->loadLocationsByUnitId('.$intUnitId.')'); 

    //FIXME Subselect of `location-DEFAULT_CONTACT-1-InstanceFiles` for locationPics should be changed!
    $sqlStmt = $this->core->dbh->query("SELECT locations.id, CONCAT(locations.fname, ' ', locations.sname) AS title, genericForms.genericFormId, genericForms.version, 'location'  AS type, (SELECT files.filename FROM files INNER JOIN `location-DEFAULT_CONTACT-1-InstanceFiles` AS locationPics ON files.id = locationPics.idFiles WHERE locationPics.idLocations = locations.id LIMIT 1) AS filename
                                                FROM locations  
                                              INNER JOIN genericForms ON genericForms.id = locations.idGenericForms
                                              WHERE locations.idUnits = ?", array($intUnitId)); 
    
    return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
  }
  
  /**
   * loadLocationsByCountry
   * @param integer $intUnitId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadLocationsByCountry($strCountry, $intUnitId = 0, $intTypeId = 0, $strProvince = ''){
    $this->core->logger->debug('core->models->Locations->loadLocationsByCountry('.$strCountry.', '.$intUnitId.', '.$intTypeId.','.$strProvince.')'); 
    
    $objSelect = $this->getLocationsTable()->select();   
    $objSelect->setIntegrityCheck(false);
    
    /**
     * SELECT * FROM locations 
     * WHERE locations.country = ? AND 
     *  locations.idUnits = ? AND 
     *  locations.type = ?
     */ 
    $objSelect->from('locations');
    $objSelect->joinLeft('categoryTitles', 'categoryTitles.idCategories = locations.position AND categoryTitles.idLanguages = '.$this->intLanguageId, array('positionTitle' => 'title'));
    $objSelect->where('locations.country = (SELECT categoryCodes.idCategories FROM categoryCodes INNER JOIN categories ON categories.id = categoryCodes.idCategories AND categories.idRootCategory = 268 WHERE categoryCodes.code = ? AND categoryCodes.idLanguages = '.$this->intLanguageId.')', $strCountry);
    if($intUnitId > 0){
      $objSelect->where('locations.idUnits = ?', $intUnitId); 
    }
    if($intTypeId > 0){
      $objSelect->where('locations.type = ?', $intTypeId); 
    }
    if($strProvince != ''){
      $objSelect->where('locations.state = ?', $strProvince);
    }
    $objSelect->order('locations.name ASC');
        
    return $this->getLocationsTable()->fetchAll($objSelect);
  }
  
  /**
   * loadProvincesByCountry
   * @param string $strCountry
   * @param integer $intUnitId
   * @param integer $intTypeId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadProvincesByCountry($strCountry, $intUnitId = 0, $intTypeId = 0){
    $this->core->logger->debug('core->models->Locations->loadProvincesByCountry('.$strCountry.', '.$intUnitId.', '.$intTypeId.')'); 
    
    $objSelect = $this->getLocationsTable()->select();   
     
    $objSelect->from('locations', array('state'));
    
    $objSelect->where('locations.country = (SELECT categoryCodes.idCategories FROM categoryCodes INNER JOIN categories ON categories.id = categoryCodes.idCategories AND categories.idRootCategory = 268 WHERE categoryCodes.code = '.$this->core->dbh->quote($strCountry).' AND categoryCodes.idLanguages = '.$this->intLanguageId.')');
    if($intUnitId > 0){
      $objSelect->where('locations.idUnits = ?', $intUnitId); 
    }
    if($intTypeId > 0){
      $objSelect->where('locations.type = ?', $intTypeId); 
    }
    $objSelect->order('state ASC');
    $objSelect->group('state');
        
    return $this->getLocationsTable()->fetchAll($objSelect);
  }
  
  /**
   * loadLocation 
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function loadLocation($intElementId){
    $this->core->logger->debug('core->models->Locations->loadLocation('.$intElementId.')');
    
    $objSelect = $this->getLocationsTable()->select();   
    $objSelect->setIntegrityCheck(false);
    
    /**
     * SELECT locations.* 
     * FROM locations
     * WHERE locations.id = ?   
     */
    $objSelect->from('locations');
    $objSelect->joinLeft('categoryTitles', 'categoryTitles.idCategories = locations.country AND categoryTitles.idLanguages = '.$this->intLanguageId, array('countryTitle' => 'title'));
    $objSelect->where('locations.id = ?', $intElementId);
        
    return $this->getLocationsTable()->fetchAll($objSelect);    
  }
  
  /**
   * loadLocationsById 
   * @param string|array $mixedLocationIds
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadLocationsById($mixedLocationIds){
    $this->core->logger->debug('core->models->Locations->loadLocationsById('.$mixedLocationIds.')');
    try{
      $this->getLocationsTable();
      
      $arrLocationIds = array();
      if(is_array($mixedLocationIds)){
        $arrLocationIds = $mixedLocationIds;
      }else if(isset($mixedLocationIds) && $mixedLocationIds != ''){
	      $strTmpLocationIds = trim($mixedLocationIds, '[]');
	      $arrLocationIds = split('\]\[', $strTmpLocationIds);
      }
      
      $objSelect = $this->objLocationsTable->select();   
      $objSelect->setIntegrityCheck(false);
      
      if(count($arrLocationIds) > 0){
        $strIds = '';
        foreach($arrLocationIds as $intLocationId){
          $strIds .= $intLocationId.',';
        }
        
        //FIXME Subselect of `location-DEFAULT_CONTACT-1-InstanceFiles` for locationPics should be changed!
        $objSelect->from('locations', array('id', 'title AS acTitle', 'CONCAT(fname, \' \', sname) AS title', 'position', 'phone', 'mobile', 'fax', 'email', 'website', '(SELECT files.filename FROM files INNER JOIN `location-DEFAULT_CONTACT-1-InstanceFiles` AS locationPics ON files.id = locationPics.idFiles WHERE locationPics.idLocations = locations.id LIMIT 1) AS filename'));
        $objSelect->join('genericForms', 'genericForms.id = locations.idGenericForms', array('genericFormId', 'version'));   
        $objSelect->where('locations.id IN ('.trim($strIds, ',').')');      
        
        return $this->objLocationsTable->fetchAll($objSelect);
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  
  }
  
  /**
   * loadUnit
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function loadUnit($intElementId){
    $this->core->logger->debug('core->models->Folders->loadUnit('.$intElementId.')');
    
    $objSelect = $this->getUnitTable()->select();   
    $objSelect->setIntegrityCheck(false);
    
    /**
     * SELECT units.*, unitTitles.title 
     * FROM units 
     * INNER JOIN unitTitles ON unitTitles.idUnits = units.id AND 
     *   unitTitles.idLanguages = ?
     * WHERE units.id = ?   
     */
    $objSelect->from('units');
    $objSelect->join('unitTitles', 'unitTitles.idUnits = units.id AND unitTitles.idLanguages = '.$this->intLanguageId, array('title'));
    $objSelect->where('units.id = ?', $intElementId);
        
    return $this->getUnitTable()->fetchAll($objSelect);     
  }    
  
  /**
   * addLocation   
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function addLocation($arrData){
   try{ 
      return $this->getLocationsTable()->insert($arrData);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * editLocation
   * @param integer $intLocationId   
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function editLocation($intLocationId, $arrData){
    try{
      $this->getLocationsTable();
      $strWhere = $this->objLocationsTable->getAdapter()->quoteInto('id = ?', $intLocationId);
      return $this->objLocationsTable->update($arrData, $strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
    
  /**
   * deleteLocation 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function deleteLocation($intElementId){
    $this->core->logger->debug('core->models->Locations->deleteLocation('.$intElementId.')');
    
    $this->getLocationsTable();
    
    /**
     * delete locations
     */
    $strWhere = $this->objLocationsTable->getAdapter()->quoteInto('id = ?', $intElementId);  
    
    return $this->objLocationsTable->delete($strWhere);
  }
  
  /**
   * deleteLocations
   * @param array $arrLocationIds
   * @return integer the number of rows deleted
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteLocations($arrLocationIds){
    try{  
      $strWhere = '';
      $intCounter = 0;
      if(count($arrLocationIds) > 0){
        foreach($arrLocationIds as $intLocationId){
          if($intLocationId != ''){
            if($intCounter == 0){
              $strWhere .= $this->getLocationsTable()->getAdapter()->quoteInto('id = ?', $intLocationId);
            }else{
              $strWhere .= $this->getLocationsTable()->getAdapter()->quoteInto(' OR id = ?', $intLocationId);
            }
            $intCounter++;
          }
        }
      }   
      return $this->objLocationsTable->delete($strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * addUnitNode   
   * @param integer $intParentId
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function addUnitNode($intParentId, $arrData = array()){
   try{ 
      $intNodeId = null;
            
      $this->getUnitTable();
      
      $objNestedSet = new NestedSet($this->objUnitTable);
      $objNestedSet->setDBFParent('idParentUnit');
      $objNestedSet->setDBFRoot('idRootUnit');
      
      /**
       * if $intParentId == 0, this is a root unit node
       */
      if($intParentId == 0){
        $intNodeId = $objNestedSet->newRootNode($arrData);
      }else{
        $intNodeId = $objNestedSet->newLastChild($intParentId, $arrData);
      }
      
      return $intNodeId;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * deleteUnitNode
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function deleteUnitNode($intUnitId){
    $this->core->logger->debug('core->models->Locations->deleteUnitNode('.$intUnitId.')');
    
    $this->getUnitTable();
    
    $objNestedSet = new NestedSet($this->objUnitTable);
    $objNestedSet->setDBFParent('idParentUnit');
    $objNestedSet->setDBFRoot('idRootUnit');
      
    $objNestedSet->deleteNode($intUnitId);
    
    //FIXME:: delete locations?
  }
  
  /**
   * getLocationsTable
   * @return Model_Table_Locations $objLocationsTable
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function getLocationsTable(){
    
    if($this->objLocationsTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Locations.php';
      $this->objLocationsTable = new Model_Table_Locations();
    }
    
    return $this->objLocationsTable;
  }
  
  /**
   * getUnitTable 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getUnitTable(){
    
    if($this->objUnitTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Units.php';
      $this->objUnitTable = new Model_Table_Units();
    }
    
    return $this->objUnitTable;
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