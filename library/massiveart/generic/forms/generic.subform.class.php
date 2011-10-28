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
 * GenericSubForm
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-07-22: Florian Mathis
 * 1.1, 2009-07-23: Thomas Schedler
 * 1.2, 2009-07-28: Daniel Rotter - changed the used plugin loader to our own
 *
 * @author Florian Mathis <flo@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.forms
 * @subpackage GenericSubForm
 */

class GenericSubForm extends Zend_Form_SubForm {

  /**
   * @var Core
   */
  protected $core;

  /**
   * @var GenericForm Object
   */
  protected $objGenericForm;

  protected $intId;
  protected $strTitle;
  protected $strAction;
  protected $blnHide;

  /**
   * set generic form object
   * @param GenericForm &$objGenericForm
   */
  public function setGenericForm(GenericForm &$objGenericForm) {
    $this->objGenericForm = $objGenericForm;
  }

  public static $FIELD_PROPERTIES_TO_IMPART = array('tagIds',
                                                    'isRegionTitle',
                                                    'showDisplayOptions',
                                                    'display_option',
                                                    'strLinkedPageId',
                                                    'intLinkedPageVersion',
                                                    'strLinkedPageTitle',
                                                    'strLinkedPageUrl',
                                                    'intLinkedPageId',
                                                    'strLinkedPageBreadcrumb',
                                                    'intVideoTypeId',
                                                    'strVideoUserId',
                                                    'strVideoThumb',
                                                    'strVideoTitle',
                                                    'intParentId',
                                                    'blnIsStartElement',                                                                                                   
                                                    'objItemInternalLinks',
                                                    'objInstanceInternalLinks',
                                                    'objPageCollection');

  /**
   * Constructor
   */
  public function __construct($options = null){
    $this->core = Zend_Registry::get('Core');

    /**
     * Zend_Form_SubForm
     */
    parent::__construct($options);

    /**
     * Use our own PluginLoader
     */
    $objLoader = new PluginLoader();
    $objLoader->setPluginLoader($this->getPluginLoader(PluginLoader::TYPE_FORM_ELEMENT));
    $objLoader->setPluginType(PluginLoader::TYPE_FORM_ELEMENT);
    $this->setPluginLoader($objLoader, PluginLoader::TYPE_FORM_ELEMENT);
    
    /**
     * clear all decorators
     */
    $this->clearDecorators();

    /**
     * add standard decorators
     */
    $this->addDecorator('FormElements')
         ->addDecorator('Tab');

    /**
     * add prefix path to own elements
     */
    //$this->addPrefixPath('Form_Element', '', 'element');

    /**
     * elements prefixes
     */
    $this->addElementPrefixPath('Form_Decorator', dirname(__FILE__).'/decorators/', 'decorator');

    /**
     * regions prefixes
     */
    $this->addDisplayGroupPrefixPath('Form_Decorator', dirname(__FILE__).'/decorators/');
  }

  /**
   * addField
   * @param GenericElementField $objField
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.2
   */
  public function addField(GenericElementField &$objField, $intRegionId, $strNameExtension = '', $intRegionInstanceId = null, $blnEmpty = false){
    try{
      $sqlStmt = array();
      $arrOptions = array();

      /**
       * get array options for select output if sqlSelect is in database
       */
      if($objField->sqlSelect != '' && $objField->sqlSelect){
        $objReplacer = new Replacer();
        $sqlSelect = $objReplacer->sqlReplacer($objField->sqlSelect, array('LANGUAGE_ID' => $this->objGenericForm->Setup()->getFormLanguageId(), 'ROOTLEVEL_LANGUAGE_ID' => $this->objGenericForm->Setup()->getLanguageId()), $this->objGenericForm->Setup()->getRootLevelId());
        $sqlStmt = $this->core->dbh->query($sqlSelect)->fetchAll();
        if($objField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_SELECT_ID) {
          $arrOptions[''] = $this->core->translate->_('Please_choose', false);
        }
        foreach($sqlStmt as $arrSql){
          if(array_key_exists('depth', $arrSql)){
            $arrOptions[$arrSql['id']] = array('title' => $arrSql['title'],
                                               'depth' => $arrSql['depth']);
          }else{
            $arrOptions[$arrSql['id']] = $arrSql['title'];
          }
        }
      }

      if($objField->type == GenericSetup::FIELD_TYPE_TEMPLATE){
        $objField->defaultValue = $this->objGenericForm->Setup()->getTemplateId();
      }

      if(!is_null($intRegionInstanceId)){
        $mixedValue = $objField->getInstanceValue($intRegionInstanceId);
      }else{
        $mixedValue = $objField->getValue();
      }

      if($blnEmpty == true){
        $mixedValue = null;
      }

      $strCssClass = '';
      if($objField->isKeyField){
        $strCssClass = ' keyfield';
      }

      /**
       * add field to form
       */
      $this->addElement($objField->type, $objField->name.$strNameExtension, array(
        'value' => $mixedValue,
        'label' => $objField->title,
        'description' => $objField->description,
        'decorators' => array($objField->decorator),
        'fieldId' => $objField->id,
        'columns' => $objField->columns,
        'class' => $objField->type.$strCssClass,
        'height' => $objField->height,
        'isGenericSaveField' => $objField->isSaveField,
        'isCoreField' => $objField->isCoreField,
        'MultiOptions' => $arrOptions,
        'LanguageId' => $this->objGenericForm->Setup()->getLanguageId(),
        'FormLanguageId' => $this->objGenericForm->Setup()->getFormLanguageId(),
        'isEmptyField' => (($blnEmpty == true) ? 1 : 0),
        'required' =>  (($objField->isKeyField == 1) ? true : false),
        'RegisterInArrayValidator' => false,
      ));
      //Add validators
      $this->getElement($objField->name.$strNameExtension)->addPrefixPath('Form_Validator', GLOBAL_ROOT_PATH.'/library/massiveart/generic/forms/validators', 'validate');
      $this->getElement($objField->name.$strNameExtension)->addValidators($objField->validators);
      foreach($this->getElement($objField->name.$strNameExtension)->getValidators() as $objValidator){
        if($objValidator instanceof Form_Validator_Abstract){
          $objValidator->setGenericSetup($this->objGenericForm->Setup());
        }
      }

      $this->getElement($objField->name.$strNameExtension)->regionId = $intRegionId;
      $this->getElement($objField->name.$strNameExtension)->regionExtension = $strNameExtension;
      $this->getElement($objField->name.$strNameExtension)->formTypeId = $this->objGenericForm->Setup()->getFormTypeId();

      if(count($objField->getProperties()) > 0){
        foreach($objField->getProperties() as $strProperty => $mixedPropertyValue){
          if(in_array($strProperty, self::$FIELD_PROPERTIES_TO_IMPART)){
            $this->getElement($objField->name.$strNameExtension)->$strProperty = $mixedPropertyValue;
          }
        }
      }
      
      if(!is_null($intRegionInstanceId)){
        if(count($objField->getInstanceProperties($intRegionInstanceId)) > 0){
          foreach($objField->getInstanceProperties($intRegionInstanceId) as $strProperty => $mixedPropertyValue){
            if(in_array($strProperty, self::$FIELD_PROPERTIES_TO_IMPART)){
              $this->getElement($objField->name.$strNameExtension)->$strProperty = $mixedPropertyValue;
            }
          }
        }
      }

      /**
       * template specific addons
       */
      if($objField->type == GenericSetup::FIELD_TYPE_TEMPLATE){
        $this->getElement($objField->name.$strNameExtension)->isStartElement = $this->objGenericForm->Setup()->getIsStartElement(false);
        $this->getElement($objField->name.$strNameExtension)->intFormTypeId = $this->objGenericForm->Setup()->getFormTypeId();
        $this->getElement($objField->name.$strNameExtension)->intElementTypeId = $this->objGenericForm->Setup()->getElementTypeId();
        $this->getElement($objField->name.$strNameExtension)->intParentTypeId = $this->objGenericForm->Setup()->getParentTypeId();        
      }

      $this->objGenericForm->fieldAddedToSubform($this->intId, $objField->name);

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * setId
   * @param integer $intId
   */
  public function setId($intId){
    $this->intId = $intId;
  }

  /**
   * getId
   * @return integer $intId
   */
  public function getId(){
    return $this->intId;
  }

  /**
   * setTitle
   * @param string $strTitle
   */
  public function setTitle($strTitle){
    $this->strTitle = $strTitle;
  }

  /**
   * getTitle
   * @return string $strTitle
   */
  public function getTitle(){
    return $this->strTitle;
  }

  /**
   * setHide
   * @param boolean $blnHide
   */
  public function setHide($blnHide){
    $this->blnHide = $blnHide;
  }

  /**
   * getHide
   * @return boolean $blnHide
   */
  public function getHide(){
    return $this->blnHide;
  }
  
  /**
   * setAction
   * @param string $strAction
   */
  public function setAction($strAction){
    $this->strAction = $strAction;
  }
  
  /**
   * getAction
   * @return string
   */
  public function getAction(){
    return $this->strAction;
  }
}

?>