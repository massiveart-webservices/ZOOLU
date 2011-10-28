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
 * Model_GenericForms
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-24: Cornelius Hansjakob
 * 1.1, 2008-11-04: Thomas Schedler : change structure - add Fields and Regions to the general Model_GenericForms class
 * 1.2, 2009-07-29: Florian Mathis : Added FieldTypeGroup Column to loadFieldsAndRegionsByFormId()
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Model_GenericForms {

	private $intLanguageId;

  /**
   * @var Model_Table_GenericForms
   */
  protected $objGenericFormTable;

  /**
   * @var Model_Table_Regions
   */
  protected $objRegionTable;

  /**
   * @var Model_Table_Fields
   */
  protected $objFieldTable;

  /**
   * @var Model_Table_FieldProperties
   */
  protected $objFieldPropertyTable;

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
   * loadForm
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @param string $strGenFormId, integer $intActionType, integer $intGenFormVersion
   * @version 1.0
   */
  public function loadForm($strGenFormId, $intActionType, $intGenFormVersion = null){
    $this->core->logger->debug('core->models->GenericForms->loadForm('.$strGenFormId.','.$intActionType.','.$intGenFormVersion.')');

    $objSelect = $this->getGenericFormTable()->select();
    $objSelect->setIntegrityCheck(false);

    /**
     * SELECT genericForms.id, genericForms.version, genericForms.idGenericFormTypes, genericFormTitles.title
     * FROM genericForms
     * LEFT JOIN genericFormTitles ON genericFormTitles.idGenericForms = genericForms.id
     *  AND genericFormTitles.idAction = ?
     *  AND genericFormTitles.idLanguages = ?
     * WHERE genericForms.version = ?
     * ORDER BY genericForms.version DESC
     * LIMIT 1
     */

    $objSelect->from('genericForms', array('id', 'version', 'idGenericFormTypes'));
    $objSelect->join('genericFormTypes', 'genericFormTypes.id = genericForms.idGenericFormTypes', array('title AS typeTitle'));
    $objSelect->joinLeft('genericFormTitles', 'genericFormTitles.idGenericForms = genericForms.id AND genericFormTitles.idAction = '.$intActionType.' AND genericFormTitles.idLanguages = '.$this->intLanguageId, array('title'));
    $objSelect->where('genericForms.genericFormId = ?', $strGenFormId);
    if($intGenFormVersion != null && $intGenFormVersion > 0){
      $objSelect->where('genericForms.version = ?', $intGenFormVersion);
    }
    $objSelect->order(array('genericForms.version DESC'));
    $objSelect->limit(1);
$this->core->logger->debug(strval($objSelect));
    return $this->getGenericFormTable()->fetchAll($objSelect);
  }

  /**
   * loadRegions
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @param integer $intGenFormId
   * @version 1.0
   */
  public function loadRegions($intGenFormId){
    $this->core->logger->debug('core->models->GenericForms->loadRegions('.$intGenFormId.')');

    $objSelect = $this->getRegionTable()->select();
    $objSelect->setIntegrityCheck(false);

    $objSelect->from('regions', array('id', 'columns', 'order', 'collapsable', 'isCollapsed', 'position'));
    $objSelect->join('regionTitles', 'regionTitles.idRegions = regions.id AND regionTitles.idLanguages = '.$this->intLanguageId, array('title'));
    $objSelect->join('tabRegions','tabRegions.idRegions = regions.id', array());
    $objSelect->join('tabs','tabs.id = tabRegions.idTabs',  array());
    $objSelect->join('genericFormTabs','genericFormTabs.idTabs = tabs.id AND genericFormTabs.idGenericForms = '.$intGenFormId,  array());
    $objSelect->order(array('tabRegions.order'));

    return $this->getRegionTable()->fetchAll($objSelect);
  }

  /**
   * loadFields
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @param integer $intRegionId
   * @version 1.0
   */
  public function loadFields($intRegionId){
    $this->core->logger->debug('core->models->GenericForms->loadFields('.$intRegionId.')');

    /**
     * @var Zend_Db_Table_Select
     */
    $objSelect = $this->getFieldTable()->select();

    $objSelect->setIntegrityCheck(false);

    /**
     * SELECT fields.*, fieldTitles.title, fieldTitles.description, fieldTypes.title AS type, decorators.title AS decorator
     * FROM fields
     * INNER JOIN fieldTypes ON fieldTypes.id = fields.idFieldTypes
     * INNER JOIN decorators ON decorators.id = fieldTypes.id
     * LEFT JOIN fieldTitles ON fieldTitles.idFields = fields.id
     *   AND fieldTitles.idLanguages = ?
     * WHERE fields.idRegions = ?
     */

    $objSelect->from('fields');
    $objSelect->join('fieldTypes', 'fieldTypes.id = fields.idFieldTypes', array('title AS type', 'defaultValue'));
    $objSelect->join('decorators', 'decorators.id = fieldTypes.idDecorator', array('title AS decorator'));
    $objSelect->joinLeft('fieldTitles', 'fieldTitles.idFields = fields.id AND fieldTitles.idLanguages = '.$this->intLanguageId, array('title', 'description'));
    $objSelect->where('fields.idRegions = ?', $intRegionId);

    return $this->getFieldTable()->fetchAll($objSelect);
  }

  /**
   * loadTemplates
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @param integer $intGenFormType
   * @version 1.0
   */
  public function loadTemplates($intGenFormType){
    $this->core->logger->debug('core->models->GenericForms->loadTemplates('.$intGenFormType.')');

    $objSelect = $this->getGenericFormTable()->select();
    $objSelect->setIntegrityCheck(false);


    return $this->getGenericFormTable()->fetchAll($objSelect);
  }

  /**
   * loadFieldsWithPropery
   * @param integer $intPropertyId
   * @param integer $intGenFormId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadFieldsWithPropery($intPropertyId, $intGenFormId){
    $this->core->logger->debug('core->models->GenericForms->loadFieldsWithPropery('.$intPropertyId.', '.$intGenFormId.')');

    $objSelect = $this->getFieldPropertyTable()->select();
    $objSelect->setIntegrityCheck(false);

    $objSelect->from('fieldProperties', array('value'));
    $objSelect->join('fields', 'fields.id = fieldProperties.idFields', array('id', 'name'));
    $objSelect->join('regionFields','regionFields.idFields = fields.id', array('order'));
    $objSelect->join('regions', 'regions.id = regionFields.idRegions', array('id AS regionId'));
    $objSelect->join('tabRegions','tabRegions.idRegions = regions.id', array());
    $objSelect->join('tabs','tabs.id = tabRegions.idTabs',  array());
    $objSelect->join('genericFormTabs','genericFormTabs.idTabs = tabs.id',  array());
    $objSelect->where('genericFormTabs.idGenericForms = ?', $intGenFormId)
              ->where('fieldProperties.idProperties = ?', $intPropertyId);
    $objSelect->order(array('fieldProperties.value'));

    return $this->objFieldPropertyTable->fetchAll($objSelect);
  }

  /**
   * loadFieldsAndRegionsByFormId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param integer $intGenFormId
   * @version 1.0
   */
  public function loadFieldsAndRegionsByFormId($intGenFormId){
    $this->core->logger->debug('core->models->GenericForms->loadFieldsAndRegionsByFormId('.$intGenFormId.')');

    /**
     * @var Zend_Db_Table_Select
     */
    $objSelect = $this->getFieldTable()->select();

    $objSelect->setIntegrityCheck(false);

    /**
     * SELECT `fields`.`id`, `fields`.`idFieldTypes`, `fields`.`name`, `fields`.`idSearchFieldTypes`, `fields`.`idRelationPage`, `fields`.`idCategory`,
     *        `fields`.`sqlSelect`, `fields`.`columns`, `fields`.`isCoreField`, `fields`.`isKeyField`, `fields`.`isSaveField`, `fields`.`isRegionTitle`,
     *        `fields`.`isDependentOn`, `fields`.`copyValue`, `fieldTypes`.`title` AS `type`, `fieldTypes`.`defaultValue`, `decorators`.`title` AS `decorator`,
     *        `fieldTitles`.`title`, `regionFields`.`order`, `regions`.`id` AS `regionId`, `regions`.`idRegionTypes`, `regions`.`columns` AS `regionColumns`,
     *        `regions`.`collapsable`, `regions`.`isCollapsed`, `regions`.`position`, `regions`.`isMultiply`, `regions`.`multiplyRegion`,
     *        `regionTitles`.`title` AS `regionTitle`, `tabRegions`.`order` AS `regionOrder`, `tabs`.`id` AS `tabId`, `tabs`.`color` AS `tabColor`, `tabTitles`.`title` AS `tabTitle`
     *  FROM `fields`
     *    INNER JOIN `fieldTypes` ON
     *      fieldTypes.id = fields.idFieldTypes
     *    INNER JOIN `decorators` ON
     *      decorators.id = fieldTypes.idDecorator
     *    LEFT JOIN `fieldTitles` ON
     *      fieldTitles.idFields = fields.id AND
     *      fieldTitles.idLanguages = 1
     *    INNER JOIN `regionFields` ON
     *      regionFields.idFields = fields.id
     *    INNER JOIN `regions` ON
     *      regions.id = regionFields.idRegions
     *    LEFT JOIN `regionTitles` ON
     *      regionTitles.idRegions = regions.id AND
     *      regionTitles.idLanguages = 1
     *    INNER JOIN `tabRegions` ON
     *      tabRegions.idRegions = regions.id
     *    INNER JOIN `tabs` ON
     *      tabs.id = tabRegions.idTabs
     *    LEFT JOIN `tabTitles` ON
     *      tabTitles.idTabs = tabs.id AND
     *      tabTitles.idLanguages = 1
     *    INNER JOIN `genericFormTabs` ON
     *      genericFormTabs.idTabs = tabs.id
     *  WHERE (genericFormTabs.idGenericForms = 2)
     *    ORDER BY `genericFormTabs`.`order` ASC,
     *             `tabRegions`.`order` ASC,
     *             `regionFields`.`order` ASC
     */
    
    $objSelect->from('genericFormTabs', array('tabOrder' => 'order'))
              ->join('tabs', 'genericFormTabs.idTabs = tabs.id', array('tabId' => 'id', 'tabColor' => 'color', 'tabAction' => 'action'))
              ->joinLeft('tabTitles', 'tabTitles.idTabs = tabs.id AND tabTitles.idLanguages = '.$this->intLanguageId, array('tabTitle' => 'title'))
              ->joinLeft('tabRegions', 'tabRegions.idTabs = tabs.id', array('regionOrder' => 'order'))
              ->joinLeft('regions', 'regions.id = tabRegions.idRegions', array('regionId' => 'id', 'idRegionTypes', 'regionColumns' => 'columns', 'collapsable', 'isCollapsed', 'position', 'isMultiply', 'multiplyRegion'))
              ->joinLeft('regionTitles', 'regionTitles.idRegions = regions.id AND regionTitles.idLanguages = '.$this->intLanguageId, array('regionTitle' => 'title'))
              ->joinLeft('regionFields', 'regionFields.idRegions = regions.id', array('order'))
              ->joinLeft('fields', 'fields.id = regionFields.idFields', array('id', 'idFieldTypes', 'name', 'idSearchFieldTypes', 'idRelationPage', 'idCategory', 'sqlSelect', 'columns', 'isCoreField', 'isKeyField', 'isSaveField', 'isRegionTitle', 'isDependentOn', 'showDisplayOptions', 'copyValue', 'validators'))
              ->joinLeft('fieldTitles', 'fieldTitles.idFields = fields.id AND fieldTitles.idLanguages = '.$this->intLanguageId, array('title'))
              ->joinLeft('fieldTypes', 'fieldTypes.id = fields.idFieldTypes', array('type' => 'title', 'defaultValue', 'idFieldTypeGroup'))
              ->joinLeft('decorators', 'decorators.id = fieldTypes.idDecorator', array('decorator' => 'title'))
              ->where('genericFormTabs.idGenericForms = ?', $intGenFormId)
              ->order(array('genericFormTabs.order', 'tabRegions.order', 'regionFields.order'));
              
    //$objSelect->from('fields', array('id', 'idFieldTypes', 'name', 'idSearchFieldTypes', 'idRelationPage', 'idCategory', 'sqlSelect', 'columns', 'isCoreField', 'isKeyField', 'isSaveField', 'isRegionTitle', 'isDependentOn', 'showDisplayOptions', 'copyValue'));
    //$objSelect->join('fieldTypes', 'fieldTypes.id = fields.idFieldTypes', array('title AS type', 'defaultValue', 'idFieldTypeGroup'));
    //$objSelect->join('decorators', 'decorators.id = fieldTypes.idDecorator', array('title AS decorator'));
    //$objSelect->joinLeft('fieldTitles','fieldTitles.idFields = fields.id AND fieldTitles.idLanguages = '.$this->intLanguageId, array('title'));
    //$objSelect->join('regionFields','regionFields.idFields = fields.id', array('order'));
    //$objSelect->join('regions','regions.id = regionFields.idRegions',  array('id AS regionId', 'idRegionTypes', 'columns AS regionColumns', 'collapsable', 'isCollapsed', 'position', 'isMultiply', 'multiplyRegion'));
    //$objSelect->joinLeft('regionTitles', 'regionTitles.idRegions = regions.id AND regionTitles.idLanguages = '.$this->intLanguageId, array('title AS regionTitle'));
    //$objSelect->join('tabRegions','tabRegions.idRegions = regions.id', array('order AS regionOrder'));
    //$objSelect->join('tabs','tabs.id = tabRegions.idTabs',  array('id AS tabId', 'color AS tabColor'));
    //$objSelect->joinLeft('tabTitles', 'tabTitles.idTabs = tabs.id AND tabTitles.idLanguages = '.$this->intLanguageId, array('title AS tabTitle'));
    //$objSelect->join('genericFormTabs','genericFormTabs.idTabs = tabs.id', array('order AS tabOrder'));
    //$objSelect->where('genericFormTabs.idGenericForms = ?', $intGenFormId);
    //$objSelect->order(array('genericFormTabs.order', 'tabRegions.order', 'regionFields.order'));

    return $this->getFieldTable()->fetchAll($objSelect);
  }

  /**
   * loadFieldByName
   * @param integer $strFieldName
   * @param string $strFormId
   * @param integer $intFormVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadFieldByName($strFieldName, $strFormId, $intFormVersion){
    $this->core->logger->debug('core->models->GenericForms->loadFieldsAndRegionsByFormId('.$strFieldName.', '.$strFormId.', '.$intFormVersion.')');

    /**
     * @var Zend_Db_Table_Select
     */
    $objSelect = $this->getFieldTable()->select();
    $objSelect->setIntegrityCheck(false);

    $objSelect->from('fields', array('id', 'idFieldTypes', 'name', 'idRelationPage', 'idCategory', 'sqlSelect', 'columns', 'isCoreField', 'isKeyField', 'isSaveField', 'isRegionTitle', 'isDependentOn', 'copyValue'));
    $objSelect->join('fieldTypes', 'fieldTypes.id = fields.idFieldTypes', array('title AS type', 'defaultValue'));
    $objSelect->join('decorators', 'decorators.id = fieldTypes.idDecorator', array('title AS decorator'));
    $objSelect->joinLeft('fieldTitles','fieldTitles.idFields = fields.id AND fieldTitles.idLanguages = '.$this->intLanguageId, array('title'));
    $objSelect->join('regionFields','regionFields.idFields = fields.id', array('order'));

    $objSelect->join('regions','regions.id = regionFields.idRegions',  array('id AS regionId', 'columns AS regionColumns', 'collapsable', 'isCollapsed', 'position', 'isMultiply', 'multiplyRegion'));
    $objSelect->joinLeft('regionTitles', 'regionTitles.idRegions = regions.id AND regionTitles.idLanguages = '.$this->intLanguageId, array('title AS regionTitle'));
    $objSelect->join('tabRegions','tabRegions.idRegions = regions.id', array('order AS regionOrder'));
    $objSelect->join('tabs','tabs.id = tabRegions.idTabs',  array());
    $objSelect->join('genericFormTabs','genericFormTabs.idTabs = tabs.id', array(''));
    $objSelect->join('genericForms', 'genericForms.id = genericFormTabs.idGenericForms', array());
    $objSelect->where('genericForms.genericFormId = ?', $strFormId)
              ->where('genericForms.version = ?', $intFormVersion)
              ->where('fields.name = ?', $strFieldName);

    return $this->getFieldTable()->fetchAll($objSelect);
  }

  /**
   * getGenericFormTable
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getGenericFormTable(){

    if($this->objGenericFormTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/GenericForms.php';
      $this->objGenericFormTable = new Model_Table_GenericForms();
    }

    return $this->objGenericFormTable;
  }

  /**
   * getRegionTable
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getRegionTable(){

    if($this->objRegionTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Regions.php';
      $this->objRegionTable = new Model_Table_Regions();
    }

    return $this->objRegionTable;
  }

  /**
   * getFieldTable
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getFieldTable(){

    if($this->objFieldTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Fields.php';
      $this->objFieldTable = new Model_Table_Fields();
    }

    return $this->objFieldTable;
  }

  /**
   * getFieldPropertyTable
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getFieldPropertyTable(){

    if($this->objFieldPropertyTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/FieldProperties.php';
      $this->objFieldPropertyTable = new Model_Table_FieldProperties();
    }

    return $this->objFieldPropertyTable;
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