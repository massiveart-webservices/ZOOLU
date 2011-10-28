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
 * Model_Contacts
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-06: Thomas Schedler
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Contacts {
  
  private $intLanguageId;
  
  /**
   * @var Model_Table_Contacts 
   */
  protected $objContactsTable;
  
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
  public function loadNavigation($intRootLevelId, $intItemId = null, $blnOnlyUnits = false){
    $this->core->logger->debug('core->models->Contacts->loadNavigation('.$intRootLevelId.','.$intItemId.')'); 
    
    $objSelect1 = $this->getContactsTable()->select();
    $objSelect1->setIntegrityCheck(false);
    
    $objSelect1->from('units', array('id', 'title' => 'unitTitles.title', 'type' => new Zend_Db_Expr("'unit'"), 'depth', 'idParentUnit', 'idRootUnit', 'lft'))              
              ->join('genericForms', 'genericForms.id = units.idGenericForms', array('genericFormId', 'version'))
              ->joinLeft('unitTitles', 'unitTitles.idUnits = units.id AND unitTitles.idLanguages = '.$this->intLanguageId, array())
              ->where('units.idRootLevels = ?', $intRootLevelId);
    if($intItemId !== null){
      $objSelect1->where('units.idParentUnit = ?', $intItemId);
    }
      
    if($blnOnlyUnits == false){
      $objSelect2 = $this->getContactsTable()->select();
      $objSelect2->setIntegrityCheck(false);
      
      $objSelect2->from('contacts', array('id', 'title' => new Zend_Db_Expr("CONCAT(contacts.fname, ' ', contacts.sname)"), 'type' => new Zend_Db_Expr("'contact'"), 'units.depth', 'units.idParentUnit', 'units.idRootUnit', 'units.lft'))
                 ->join('genericForms', 'genericForms.id = contacts.idGenericForms', array('genericFormId', 'version'))
                 ->join('units', 'units.id = contacts.idUnits AND units.idRootLevels = '.$intRootLevelId, array())
                 ->joinLeft('unitTitles', 'unitTitles.idUnits = units.id AND unitTitles.idLanguages = '.$this->intLanguageId, array());
      if($intItemId !== null){
        $objSelect2->where('contacts.idUnits = ?', $intItemId);
      }

      $objSelect = $this->objContactsTable->select()
                               ->distinct()
                               ->union(array($objSelect2, $objSelect1));
        
    }else{
      $objSelect = $objSelect1;
    }
    
    $objSelect->order('idRootUnit');
    $objSelect->order('lft');
    $objSelect->order('title');  
    
    return $this->objContactsTable->fetchAll($objSelect);    
  }
      
  /**
   * loadContactsByUnitId
   * @param integer $intUnitId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadContactsByUnitId($intUnitId){
    $this->core->logger->debug('core->models->Contacts->loadContactsByUnitId('.$intUnitId.')'); 

    $objSelect = $this->getContactsTable()->select();   
    $objSelect->setIntegrityCheck(false);
      
    //FIXME Subselect of `contact-DEFAULT_CONTACT-1-InstanceFiles` for contactPics should be changed!
    $objSelect->from('contacts', array('id', 'title AS acTitle', 'CONCAT(fname, \' \', sname) AS title', 'position', 'phone', 'mobile', 'fax', 'email', 'website', 'street', 'city', 'state', 'zip', 'country'));
    $objSelect->joinLeft(array('pics' => 'files'), 'pics.id = (SELECT contactPics.idFiles FROM `contact-DEFAULT_CONTACT-1-InstanceFiles` AS contactPics WHERE contactPics.idContacts = contacts.id AND contactPics.idFields = 84 LIMIT 1)', array('filename',  'filepath' => 'path', 'fileversion' => 'version'));
    $objSelect->join('genericForms', 'genericForms.id = contacts.idGenericForms', array('genericFormId', 'version'));
    $objSelect->where('contacts.idUnits = ?', $intUnitId);  

    return $this->objContactsTable->fetchAll($objSelect);
  }
  
  /**
   * loadContact 
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function loadContact($intElementId){
    $this->core->logger->debug('core->models->Contacts->loadContact('.$intElementId.')');
    
    $objSelect = $this->getContactsTable()->select();   
    $objSelect->setIntegrityCheck(false);
    
    /**
     * SELECT contacts.* 
     * FROM contacts
     * WHERE contacts.id = ?   
     */
    $objSelect->from('contacts');
    $objSelect->where('contacts.id = ?', $intElementId);
        
    return $this->getContactsTable()->fetchAll($objSelect);    
  }
  
  /**
   * loadContactsById 
   * @param string|array $mixedContactIds
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadContactsById($mixedContactIds){
    $this->core->logger->debug('core->models->Contacts->loadContactsById('.$mixedContactIds.')');
    try{
      $this->getContactsTable();
      
      $arrContactIds = array();
      if(is_array($mixedContactIds)){
        $arrContactIds = $mixedContactIds;
      }else if(isset($mixedContactIds) && $mixedContactIds != ''){
	      $strTmpContactIds = trim($mixedContactIds, '[]');
	      $arrContactIds = split('\]\[', $strTmpContactIds);
      }
      
      $objSelect = $this->objContactsTable->select();   
      $objSelect->setIntegrityCheck(false);
      
      if(count($arrContactIds) > 0){
        $strIds = '';
        foreach($arrContactIds as $intContactId){
          $strIds .= $intContactId.',';
        }
        
        //FIXME Subselect of `contact-DEFAULT_CONTACT-1-InstanceFiles` for contactPics should be changed!
        $objSelect->from('contacts', array('id', 'title AS acTitle', 'CONCAT(fname, \' \', sname) AS title', 'position', 'phone', 'mobile', 'fax', 'email', 'website', 'street', 'city', 'state', 'zip', 'country'));
        $objSelect->joinLeft(array('pics' => 'files'), 'pics.id = (SELECT contactPics.idFiles FROM `contact-DEFAULT_CONTACT-1-InstanceFiles` AS contactPics WHERE contactPics.idContacts = contacts.id AND contactPics.idFields = 84 LIMIT 1)', array('filename',  'filepath' => 'path', 'fileversion' => 'version'));
        $objSelect->joinLeft(array('docs' => 'files'), 'docs.id = (SELECT contactDocs.idFiles FROM `contact-DEFAULT_CONTACT-1-InstanceFiles` AS contactDocs INNER JOIN fileTitles ON fileTitles.idFiles = contactDocs.idFiles AND fileTitles.idLanguages = '.$this->intLanguageId.' WHERE contactDocs.idContacts = contacts.id AND contactDocs.idFields = 175 LIMIT 1)', array('docid' => 'id', 'docfilename' => 'filename', 'docfilepath' => 'path', 'docfileversion' => 'version'));
        $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = docs.id AND fileTitles.idLanguages = '.$this->intLanguageId, array('doctitle' => 'title'));
        $objSelect->join('genericForms', 'genericForms.id = contacts.idGenericForms', array('genericFormId', 'version'));
        $objSelect->joinLeft('categoryTitles', 'categoryTitles.idCategories = contacts.country AND categoryTitles.idLanguages = '.$this->intLanguageId, array('countryTitle' => 'title'));   
        $objSelect->where('contacts.id IN ('.trim($strIds, ',').')');   
        $objSelect->order('FIND_IN_SET(contacts.id,\''.trim($strIds, ',').'\')');   
        
        return $this->objContactsTable->fetchAll($objSelect);
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
   * addContact   
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function addContact($arrData){
   try{ 
      return $this->getContactsTable()->insert($arrData);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * editContact
   * @param integer $intContactId   
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function editContact($intContactId, $arrData){
    try{
      $this->getContactsTable();
      $strWhere = $this->objContactsTable->getAdapter()->quoteInto('id = ?', $intContactId);
      return $this->objContactsTable->update($arrData, $strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
    
  /**
   * deleteContact 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function deleteContact($intElementId){
    $this->core->logger->debug('core->models->Contacts->deleteContact('.$intElementId.')');
    
    $this->getContactsTable();
    
    /**
     * delete contacts
     */
    $strWhere = $this->objContactsTable->getAdapter()->quoteInto('id = ?', $intElementId);  
    
    return $this->objContactsTable->delete($strWhere);
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
    $this->core->logger->debug('core->models->Contacts->deleteUnitNode('.$intUnitId.')');
    
    $this->getUnitTable();
    
    $objNestedSet = new NestedSet($this->objUnitTable);
    $objNestedSet->setDBFParent('idParentUnit');
    $objNestedSet->setDBFRoot('idRootUnit');
      
    $objNestedSet->deleteNode($intUnitId);
    
    //FIXME:: delete contacts?
  }
  
  /**
   * getContactsTable
   * @return Model_Table_Contacts $objContactsTable
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function getContactsTable(){
    
    if($this->objContactsTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Contacts.php';
      $this->objContactsTable = new Model_Table_Contacts();
    }
    
    return $this->objContactsTable;
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