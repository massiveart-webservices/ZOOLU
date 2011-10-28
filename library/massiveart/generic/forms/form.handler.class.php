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
 * @package    library.massiveart.generic.forms
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * FormHandler Class - based on Singleton Pattern
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-24: Cornelius Hansjakob
 * 1.1, 2008-01-19: Thomas Schedler - changed structur and added generic setup object
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.1
 * @package massiveart.forms
 * @subpackage FormHandler
 */

class FormHandler {

  /**
   * @var Core
   */
  private $core;

  private $strFormId;
	private $intTemplateId;
  private $intFormVersion;
  private $intFormLanguageId;
  private $intActionType;
  private $intLanguageId;
  private $intElementId;

	/**
   * @var FormHandler object instance
   */
  private static $instance = null;

  /**
   * Constructor
   */
  protected function __construct(){
    $this->core = Zend_Registry::get('Core');
  }

  private function __clone(){}

  /**
   * getInstance
   * @return FormHandler object instance of the class
   */
  public static function getInstance(){
    if(self::$instance == null){
      self::$instance = new FormHandler();
    }
    return self::$instance;
  }

  /**
   * getGenericForm
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.1
   * @return GenericForm
   */
	public function getGenericForm(){
		try{

		  //TODO : get Form from cache or create ??

      $objForm = new GenericForm();

      $objForm->Setup()->setFormId($this->strFormId);
      $objForm->Setup()->setTemplateId($this->intTemplateId);
      $objForm->Setup()->setFormVersion($this->intFormVersion);
      $objForm->Setup()->setActionType($this->intActionType);
      $objForm->Setup()->setLanguageId($this->intLanguageId);
      $objForm->Setup()->setFormLanguageId($this->intFormLanguageId);
      $objForm->Setup()->setElementId($this->intElementId);

      /**
       * load basic generic form
       */
      $objForm->Setup()->loadGenericForm();

      /**
       * load generic form structur
       */
      $objForm->Setup()->loadGenericFormStructure();

      /**
       * init data type object
       */
      $objForm->initDataTypeObject();

      return $objForm;

		}catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
	}

  /**
   * setFormId
   * @param string $strFormId
   */
  public function setFormId($strFormId){
    $this->strFormId = $strFormId;
  }

  /**
   * getFormId
   * @param string $strFormId
   */
  public function getFormId(){
    return $this->strFormId;
  }

  /**
   * setTemplateId
   * @param integer $intTemplateId
   */
  public function setTemplateId($intTemplateId){
    $this->intTemplateId = $intTemplateId;
  }

  /**
   * getTemplateId
   * @param integer $intTemplateId
   */
  public function getTemplateId(){
    return $this->intTemplateId;
  }

  /**
   * setFormVersion
   * @param integer $intFormVersion
   */
  public function setFormVersion($intFormVersion){
    $this->intFormVersion = $intFormVersion;
  }

  /**
   * getFormVersion
   * @param integer $intFormVersion
   */
  public function getFormVersion(){
    return $this->intFormVersion;
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
   * setFormLanguageId
   * @param integer $intFormLanguageId
   */
  public function setFormLanguageId($intFormLanguageId){
    $this->intFormLanguageId = $intFormLanguageId;
  }

  /**
   * getFormLanguageId
   * @param integer $intFormLanguageId
   */
  public function getFormLanguageId(){
    return $this->intFormLanguageId;
  }

  /**
   * setActionType
   * @param integer $intActionType
   */
  public function setActionType($intActionType){
    $this->intActionType = $intActionType;
  }

  /**
   * getActionType
   * @param integer $intActionType
   */
  public function getActionType(){
    return $this->intActionType;
  }

  /**
   * setElementId
   * @param integer $intElementId
   */
  public function setElementId($intElementId){
    $this->intElementId = $intElementId;
  }

  /**
   * getElementId
   * @param integer $intElementId
   */
  public function getElementId(){
    return $this->intElementId;
  }
}

?>