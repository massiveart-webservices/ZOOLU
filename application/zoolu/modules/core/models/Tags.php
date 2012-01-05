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
 * Model_Tags
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-29: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Tags {
  
  /**
   * @var integer
   */
	private $intLanguageId;
	
	/**
   * @var array
   */
  private $arrTagTypeTables = array();

  /**
   * @var Model_Table_Tags
   */
  protected $objTagsTable;

  /**
   * @var Model_Table_Tag_Types
   */
  protected $objTagTypeTable;

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
   * loadTag
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function loadTag($intElementId){
    $this->core->logger->debug('core->models->Tags->loadTag('.$intElementId.')');

    $objSelect = $this->getTagsTable()->select();
    $objSelect->setIntegrityCheck(false);

    $objSelect->from('tags', array('id', 'title'));
    $objSelect->where('tags.id = ?', $intElementId);

    return $this->getTagsTable()->fetchAll($objSelect);
  }

  /**
   * loadTagByName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param string $strTag
   * @return Zend_Db_Table_Rowset
   * @version 1.0
   */
  public function loadTagByName($strTag){
    $this->core->logger->debug('core->models->Tags->loadTagByName('.$strTag.')');

    $objSelect = $this->getTagsTable()->select();
    $objSelect->setIntegrityCheck(false);

    $objSelect->from('tags', array('id'));
    $objSelect->where('tags.title = ?', $strTag);

    return $this->getTagsTable()->fetchAll($objSelect);
  }

  /**
   * addTag
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param string $strTag
   * @return integer
   * @version 1.0
   */
  public function addTag($strTag){
    $arrData = array('title' => $strTag);
    return $this->getTagsTable()->insert($arrData);
  }

  /**
   * addTypeTags
   * @param string $strTagType
   * @param array $arrTagIds
   * @param string $strElementId
   * @param integet $intVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addTypeTags($strTagType, $arrTagIds, $strElementId, $intVersion){
    $this->core->logger->debug('core->models->Tags->addTypeTags('.$strTagType.', '.$strElementId.', '.$intVersion.')');
    $this->core->logger->debug('core->models->Tags->addTypeTags: count $arrTagIds: '.count($arrTagIds));
    
    if(count($arrTagIds) > 0){
      $this->getTagTypeTable($strTagType);
      $strInstanceField = strtolower($strTagType).'Id';
      foreach ($arrTagIds as $intTagId){
        $arrData = array('idTags'           => $intTagId,
                         $strInstanceField  => $strElementId,
                         'version'          => $intVersion,
                         'idLanguages'      => $this->intLanguageId);
        $this->objTagTypeTable->insert($arrData);
      }
    }
  }
  
  
  /**
   * loadAllTags
   * @return Zend_Db_Table_Rowset
   * @author Dominik Mößlang <dmo@massiveart.com>
   * @version 1.0
   */
  public function loadAllTags(){

    $objSelect = $this->getTagsTable()->select();
    $objSelect->setIntegrityCheck(false);
    
    $objSelect->from('tags', array('id', 'title'));
    $objSelect->order(array('title ASC'));

    return $this->objTagsTable->fetchAll($objSelect);
  }

  /**
   * loadMostUsedTags
   * @param string $strTagType
   * @return Zend_Db_Table_Rowset
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadMostUsedTags($strTagType){
    $this->getTagTypeTable($strTagType);

    $objSelect = $this->getTagsTable()->select();
    $objSelect->setIntegrityCheck(false);

    $strTagTypeTableName = $this->objTagTypeTable->info(Zend_Db_Table_Abstract::NAME);

    $objSelect->from('tags', array('id', 'title', 'count(tags.id) AS counter'));
    $objSelect->join($strTagTypeTableName, $strTagTypeTableName.'.idTags = tags.id', array(''));
    $objSelect->group('tags.id');
    $objSelect->order(array('counter DESC'));

    return $this->objTagsTable->fetchAll($objSelect);
  }

  /**
   * loadTypeTags
   * @param string $strTagType
   * @param string $strElementId
   * @param integet $intVersion
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadTypeTags($strTagType, $strElementId, $intVersion){
    $this->core->logger->debug('core->models->Tags->loadTypeTags('.$strTagType.', '.$strElementId.', '.$intVersion.')');

    $objSelect = $this->getTagTypeTable($strTagType)->select();
    $objSelect->setIntegrityCheck(false);

    $strTagTypeTableName = $this->objTagTypeTable->info(Zend_Db_Table_Abstract::NAME);

    $objSelect->from($strTagTypeTableName, array());
    $objSelect->join('tags', $strTagTypeTableName.'.idTags = tags.id', array('id', 'title'));
    $objSelect->where($strTagTypeTableName.'.'.strtolower($strTagType).'Id = ?', $strElementId);
    $objSelect->where($strTagTypeTableName.'.version = ?', $intVersion);
    $objSelect->where($strTagTypeTableName.'.idLanguages = ?', $this->intLanguageId);

    return $this->objTagTypeTable->fetchAll($objSelect);
  }

  /**
   * deleteTag
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function deleteTag($intElementId){
    $this->core->logger->debug('core->models->Tags->deleteTag('.$intElementId.')');

    $this->getTagsTable();

    /**
     * delete tags
     */
    $strWhere = $this->objTagsTable->getAdapter()->quoteInto('id = ?', $intElementId);

    return $this->objTagsTable->delete($strWhere);
  }

  /**
   * deletTypeTags
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param string $strTagType
   * @param string $strElementId
   * @param integet $intVersion
   * @param integer $intInstanceId
   * @version 1.0
   */
  public function deletTypeTags($strTagType,  $strElementId, $intVersion){
    $this->core->logger->debug('core->models->Tags->deletTypeTags('.$strTagType.', '.$strElementId.', '.$intVersion.')');

    $this->getTagTypeTable($strTagType);

    /**
     * delete tags
     */
    $strWhere = $this->objTagTypeTable->getAdapter()->quoteInto(strtolower($strTagType).'Id = ?', $strElementId);
    $strWhere .= $this->objTagTypeTable->getAdapter()->quoteInto(' AND version = ?', $intVersion);
    $strWhere .= $this->objTagTypeTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

    return $this->objTagTypeTable->delete($strWhere);
  }

  /**
   * getTagsTable
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getTagsTable(){

    if($this->objTagsTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Tags.php';
      $this->objTagsTable = new Model_Table_Tags();
    }

    return $this->objTagsTable;
  }

  /**
   * getTagTypeTable
   * @author Thomas Schedler <cha@massiveart.com>
   * @version 1.0
   */
  public function getTagTypeTable($strType){
    try{

      if(!array_key_exists($strType, $this->arrTagTypeTables)){
        require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/TagTypes.php';
        $this->objTagTypeTable = new Model_Table_Tag_Types($strType);
        $this->setTagTypeTables($strType, $this->objTagTypeTable); 
      }else{
        $this->objTagTypeTable = $this->getTagTypeTables($strType); 	
      }

      return $this->objTagTypeTable;

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
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
  
  /**
   * setTagTypeTables
   * @param string $strType
   * @param object $objTagTypeTable
   */
  public function setTagTypeTables($strType, $objTagTypeTable){
    $this->arrTagTypeTables[$strType] = $objTagTypeTable;
  }

  /**
   * getTagTypeTables
   * @param string $strType
   * @return object $objTagTypeTable
   */
  public function getTagTypeTables($strType){
    return $this->arrTagTypeTables[$strType];
  }
}

?>