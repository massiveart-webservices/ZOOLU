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
 * Model_FieldFilters
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-05: Daniel Rotter
 * 
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
class Model_FieldFilters {
  
  /**
   * @var Model_Table_Filters
   */
  protected $objFieldFilterTable;
  
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
   * loadFilterFieldByFieldId
   * @param number $intFieldId
   * @author Daniel Rotter
   * @version 1.0
   */
  public function loadFieldFilterByFieldId($intFieldId) {
    $this->core->logger->debug('core->model->Model_Filters->loadFieldFilterbyFieldId('.$intFieldId.')');
    
    $objSelect = $this->getFieldFilterTable()->select();
    $objSelect->setIntegrityCheck(false);
    
    $objSelect->from('fieldFilters',array('key', 'value'));
    $objSelect->where('idFields = ?', $intFieldId);
    
    return $this->getFieldFilterTable()->fetchAll($objSelect);
  }
  
  /**
   * getFolderTable
   * @return Model_Table_Folders
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getFieldFilterTable(){
  
    if($this->objFieldFilterTable === null) {
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/FieldFilters.php';
      $this->objFieldFilterTable = new Model_Table_Filters();
    }
  
    return $this->objFieldFilterTable;
  }
}
?>