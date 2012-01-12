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
 * Model_Languages
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2011-09-14: Daniel Rotter
 * 
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class Model_Languages {
    
  /**
   * @var Core
   */
  private $core;
  
  /**
   * @var Model_Table_Languages
   */
  protected $objLanguagesTable;
  
  /**
   * Constructor 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * loadLanguages
   * @return Zend_Db_Table_Rowset
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function loadLanguages($intRootLevelId = null, array $arrLanguagesExclude = array()){
    $objSelect = $this->getLanguagesTable()->select()->setIntegrityCheck(false);
    
    $objSelect->from('languages', array('id', 'languageCode', 'title'));
    if($intRootLevelId != null){
      $objSelect->join('rootLevelLanguages', 'rootLevelLanguages.idLanguages = languages.id', array())
                ->where('rootLevelLanguages.idRootLevels = ?', $intRootLevelId);
    }
    if(count($arrLanguagesExclude) > 0){
      $objSelect->where('languages.id NOT IN (?)', $arrLanguagesExclude);
    }
    $objSelect->order('title');
    
    return $this->getLanguagesTable()->fetchAll($objSelect);
  }
  
  /**
   * loadLanguageById
   * @param number $intLanguageId
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function loadLanguageById($intLanguageId){
    $objSelect = $this->getLanguagesTable()->select()->setIntegrityCheck(false);
    
    $objSelect->from('languages', array('id', 'languageCode', 'title'));
    $objSelect->where('id = ?', $intLanguageId);
    
    return $this->getLanguagesTable()->fetchAll($objSelect);
  }
  
  /**
   * getActivityCommentsTable
   * @return Zend_Db_Table_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getLanguagesTable(){
    if($this->objLanguagesTable === null) {
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Languages.php';
      $this->objLanguagesTable = new Model_Table_Languages();
    }
    return $this->objLanguagesTable;
  }
}

?>