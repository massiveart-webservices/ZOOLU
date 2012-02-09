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
 * @package    library.massiveart.security
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Security
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-19: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.security
 * @subpackage Security
 */

class Security {

  const RESOURCE_FOLDER_PREFIX = 'folder_';
  const RESOURCE_ROOT_LEVEL_PREFIX = 'root_level_';

  /**
   * Privileges
   */
  const PRIVILEGE_VIEW = 'view';
  const PRIVILEGE_ADD = 'add';
  const PRIVILEGE_UPDATE = 'update';
  const PRIVILEGE_DELETE = 'delete';
  const PRIVILEGE_ARCHIVE = 'archive';
  const PRIVILEGE_LIVE = 'live';
  const PRIVILEGE_SECURITY = 'security';
  
  const ZONE_ZOOLU = 1;
  const ZONE_WEBSITE = 2;

  /**
   * @var Security
   */
  private static $objInstance;

  /**
   * @var array
   */
  private $arrZoneAcls = array();
  
  /**
   * @var array
   */
  private static $arrZones = array(
    array(
      'id'  => self::ZONE_ZOOLU,
      'key' => 'zoolu'),
    array(
      'id'  => self::ZONE_WEBSITE,
      'key' => 'website'),
  );
  
  /**
   * @var RoleProvider
   */
  private $objRoleProvider;

  /**
   * Constructor
   */
  public function __construct(){ }

  /**
   * buildAcl
   * @param Model_Users $objModelUsers
   * @return void
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function buildAcl(Model_Users $objModelUsers){
    try{
      
      foreach(self::$arrZones as $arrZone){
        $this->arrZoneAcls['id_'.$arrZone['id']] = new Acl(); 
      }
      
      /**
       * add groups
       */
      $arrGroups = $objModelUsers->getGroups();
      foreach($arrGroups as $objGroup){
        foreach(self::$arrZones as $arrZone){
          if(!$this->arrZoneAcls['id_'.$arrZone['id']]->hasRole($objGroup->key)){
            $this->arrZoneAcls['id_'.$arrZone['id']]->addRole(new Zend_Acl_Role($objGroup->key));
          }
        }        
      }

      /**
       * add resources & groups & privileges
       */
      $arrResources = $objModelUsers->getResourcesGroups();
      foreach($arrResources as $objResource){
        if(!$this->arrZoneAcls['id_'.self::ZONE_ZOOLU]->has($objResource->key)){
          $this->arrZoneAcls['id_'.self::ZONE_ZOOLU]->add(new Zend_Acl_Resource($objResource->key));
        }

        $this->arrZoneAcls['id_'.self::ZONE_ZOOLU]->allow($objResource->groupKey, $objResource->key, $objResource->permissionTitle);
      }

    }catch (Exception $exc) {
      Zend_Registry::get('Core')->logger->err($exc);
    }
  }

  /**
   * addFoldersToAcl
   * @param Model_Folders $objModelFolders
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addFoldersToAcl(Model_Folders $objModelFolders, $intZoneId = self::ZONE_ZOOLU){
    try{
      
      /**
       * add resources & groups & privileges
       */
      $arrResources = $objModelFolders->getFoldersPermissions($intZoneId);
      foreach($arrResources as $objResource){
        
        // check if acel for this zone exists
        if(!array_key_exists('id_'.$objResource->zone, $this->arrZoneAcls) || !$this->arrZoneAcls['id_'.$objResource->zone] instanceof Acl) $this->arrZoneAcls['id_'.$objResource->zone] = new Acl();
        
        $strResourceId = ($objResource->languageId == 0) ? Security::RESOURCE_FOLDER_PREFIX.$objResource->id : Security::RESOURCE_FOLDER_PREFIX.$objResource->id.'_'.$objResource->languageId;
        
        if(!$this->arrZoneAcls['id_'.$objResource->zone]->has($strResourceId)){
          $this->arrZoneAcls['id_'.$objResource->zone]->add(new Zend_Acl_Resource($strResourceId));
        }

        if(!$this->arrZoneAcls['id_'.$objResource->zone]->hasRole($objResource->groupKey)){
          $this->arrZoneAcls['id_'.$objResource->zone]->addRole(new Zend_Acl_Role($objResource->groupKey));
        }
        
        $this->arrZoneAcls['id_'.$objResource->zone]->allow($objResource->groupKey, $strResourceId, $objResource->permissionTitle);
      }

    }catch (Exception $exc) {
      Zend_Registry::get('Core')->logger->err($exc);
    }
  }
  
  /**
   * addRootLevelsToAcl
   * @param Model_Folders $objModelFolders
   * @param integer $intModuleId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addRootLevelsToAcl(Model_Folders $objModelFolders, $intModuleId, $intZoneId = self::ZONE_ZOOLU){
    try{
      
      /**
       * add resources & groups & privileges
       */
      $arrResources = $objModelFolders->getRootLevelsPermissions($intModuleId, $intZoneId);
      foreach($arrResources as $objResource){
        
        // check if acel for this zone exists
        if(!array_key_exists('id_'.$objResource->zone, $this->arrZoneAcls) || !$this->arrZoneAcls['id_'.$objResource->zone] instanceof Acl) $this->arrZoneAcls['id_'.$objResource->zone] = new Acl();
        
        $strResourceId = ($objResource->languageId == 0) ? Security::RESOURCE_ROOT_LEVEL_PREFIX.$objResource->id : Security::RESOURCE_ROOT_LEVEL_PREFIX.$objResource->id.'_'.$objResource->languageId;
          
        if(!$this->arrZoneAcls['id_'.$objResource->zone]->has($strResourceId)){
          $this->arrZoneAcls['id_'.$objResource->zone]->add(new Zend_Acl_Resource($strResourceId));
        }

        if(!$this->arrZoneAcls['id_'.$objResource->zone]->hasRole($objResource->groupKey)){
          $this->arrZoneAcls['id_'.$objResource->zone]->addRole(new Zend_Acl_Role($objResource->groupKey));
        }
        
        $this->arrZoneAcls['id_'.$objResource->zone]->allow($objResource->groupKey, $strResourceId, $objResource->permissionTitle);      
      }

    }catch (Exception $exc) {
      Zend_Registry::get('Core')->logger->err($exc);
    }
  }

  /**
   * setRoleProvider
   * @param RoleProvider $objRoleProvider
   * @return void
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function setRoleProvider(RoleProvider $objRoleProvider){
    $this->objRoleProvider = $objRoleProvider;
  }

  /**
   * isAllowed
   * @param string $strResourceKey
   * @param string $strPrivilege
   * @param boolean $blnCheckForAllLanguages
   * @param boolean $blnIfResourceNotExists
   * @see library/Zend/Zend_Acl#isAllowed()
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function isAllowed($strResourceKey, $strPrivilege = null, $blnCheckForAllLanguages = false, $blnIfResourceNotExists = true, $intZoneId = self::ZONE_ZOOLU){
    if(array_key_exists('id_'.$intZoneId, $this->arrZoneAcls) && $this->arrZoneAcls['id_'.$intZoneId] instanceof Acl){
      if($blnCheckForAllLanguages == true){        
        $arrLanguages = Zend_Registry::get('Core')->config->languages->language->toArray();
        foreach($arrLanguages as $arrLanguage){
          if($this->arrZoneAcls['id_'.$intZoneId]->has($strResourceKey.'_'.$arrLanguage['id'])){
            if($this->arrZoneAcls['id_'.$intZoneId]->isAllowed($this->objRoleProvider, $strResourceKey.'_'.$arrLanguage['id'], $strPrivilege)){
              return true;
            }
          }          
        }
        if($this->arrZoneAcls['id_'.$intZoneId]->has($strResourceKey)){
          return $this->arrZoneAcls['id_'.$intZoneId]->isAllowed($this->objRoleProvider, $strResourceKey, $strPrivilege);
        }else{
          return $blnIfResourceNotExists;
        }               
      }else{
        if($this->arrZoneAcls['id_'.$intZoneId]->has($strResourceKey)){
          return $this->arrZoneAcls['id_'.$intZoneId]->isAllowed($this->objRoleProvider, $strResourceKey, $strPrivilege);
        }else{
          return $blnIfResourceNotExists;
        }        
      }
    }else{
      return false;
    }
  }

  /**
   * save
   * @param Security $objSecurity
   * @return void
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public static function save(Security &$objSecurity){
    self::clearInstance();
    $objSecuritySesNam = new Zend_Session_Namespace('Security');
    $objSecuritySesNam->security = $objSecurity;
  }

  /**
   * get
   * @return Security
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public static function get(){
    if(self::$objInstance === null){
      $objSecuritySesNam = new Zend_Session_Namespace('Security');
      if(isset($objSecuritySesNam->security)){
        self::$objInstance = $objSecuritySesNam->security;
      }else{
        Zend_Registry::get('Core')->logger->warn('There is no security object stored in the the session namespace!');
        self::$objInstance = new self();
      }
    }
    return self::$objInstance;
  }

  /**
   * clearInstance
   * @return void
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private static function clearInstance(){
    self::$objInstance = null;
  }

}

?>