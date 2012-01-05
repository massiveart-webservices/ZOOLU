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
 * 1.0, 2010-07-07: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class Model_RootLevelTypes {

	private $intLanguageId;

  /**
   * @var Model_Table_RootLevelPermissions
   */
  protected $objRootLevelTypeFilterTypes;
  
  /**
   * @var Model_Table_RootLevelTypeFilters
   */
  protected $objRootLevelFilters;
  
  /**
   * @var RootLevelTypeFilterValues
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
   * loadRootLevelTypeFilterTypes
   * @param number $intRootLevelTypeId
   * @author Daniel Rotter
   * @version 1.0
   */
  public function loadRootLevelTypeFilterTypes($intRootLevelTypeId) {
    $this->core->logger->debug('core->models->Model_RootLevelTypes->loadRootLevelTypeFilterTypes('.$intRootLevelTypeId.')');
    
    $objSelect = $this->getRootLevelTypeFilterTypesTable()->select()->setIntegrityCheck(false);
    $objSelect->from($this->getRootLevelTypeFilterTypesTable()->info(Zend_Db_Table_Abstract::NAME), array('id', 'name', 'operators', 'sqlSelect'))
               ->joinLeft('rootLevelTypeFilterTypeTitles', 'rootLevelTypeFilterTypeTitles.idRootLevelTypeFilterTypes = rootLevelTypeFilterTypes.id', array('title'))
               ->where('rootLevelTypeFilterTypes.idRootLevelTypes = ?', $intRootLevelTypeId)
               ->where('rootLevelTypeFilterTypeTitles.idLanguages = ?', $this->getLanguageId());
    
    return $this->getRootLevelTypeFilterTypesTable()->fetchAll($objSelect);
  }
  
  /**
   * getRootLevelPermissionTable
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getRootLevelTypeFilterTypesTable(){
    if($this->objRootLevelTypeFilterTypes === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/RootLevelTypeFilterTypes.php';
      $this->objRootLevelTypeFilterTypes = new Model_Table_RootLevelTypeFilterTypes();
    }
    return $this->objRootLevelTypeFilterTypes;
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