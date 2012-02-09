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
 * @package    library.massiveart.generic.data.types
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericDataTypeMember extends GenericDataTypeAbstract 
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-19: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericDataTypeMember
 */

require_once(dirname(__FILE__).'/generic.data.type.abstract.class.php');

class GenericDataTypeMember extends GenericDataTypeAbstract {
  
  /**
   * @var Model_Members
   */
  protected $objModelMembers;
  
  /**
   * save
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function save(){
    $this->core->logger->debug('massiveart->generic->data->GenericDataTypeMember->save()');
    try{

      $this->getModelMembers()->setLanguageId($this->setup->getLanguageId());

      $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
      
      /**
       * add|edit|newVersion core and instance data
       */
      switch($this->setup->getActionType()){
        case $this->core->sysConfig->generic->actions->add:
                
          $arrCoreData = array('idRootLevels'     => $this->setup->getRootLevelId(),
                               'idGenericForms'   => $this->setup->getGenFormId(),
                               'idUsers'          => $intUserId,
                               'creator'          => $intUserId, 
                               'created'          => date('Y-m-d H:i:s'));
          
          if(count($this->setup->CoreFields()) > 0){
            foreach($this->setup->CoreFields() as $strField => $obField){
              if($strField == 'password'){
                if($obField->getValue() != '') $arrCoreData[$strField] = Crypt::encrypt($this->core, $this->core->config->crypt->key, $obField->getValue());
              }else{
                $arrCoreData[$strField] = $obField->getValue();    
              }
            }
          }
          
          /**
           * add member 
           */
          $this->setup->setElementId($this->objModelMembers->addMember($arrCoreData));
          $this->insertMultiFieldData('member', array('Id' => $this->setup->getElementId()));
          break;
        case $this->core->sysConfig->generic->actions->edit :

          $arrCoreData = array('idUsers' => $intUserId);
          
          if(count($this->setup->CoreFields()) > 0){
            foreach($this->setup->CoreFields() as $strField => $obField){
              if($strField == 'password'){
                if($obField->getValue() != '') $arrCoreData[$strField] = Crypt::encrypt($this->core, $this->core->config->crypt->key, $obField->getValue());
              }else{
                $arrCoreData[$strField] = $obField->getValue();    
              }
            }
          }
          
          /**
           * edit member 
           */
          $this->objModelMembers->editMember($this->setup->getElementId(), $arrCoreData); 
          $this->updateMultiFieldData('member', array('Id' => $this->setup->getElementId()));        
          break;
      }

      return $this->setup->getElementId();
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * load
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function load(){
    $this->core->logger->debug('massiveart->generic->data->GenericDataTypeMember->load()');
    try {
      
      $this->getModelMembers()->setLanguageId($this->setup->getLanguageId());
      $objMembersData = $this->getModelMembers()->loadMember($this->setup->getElementId());
      
      if(count($objMembersData) > 0){
        $objMemberData = $objMembersData->current();

        if(count($this->setup->CoreFields()) > 0){
          /**
           * for each core field set field data
           */
          foreach($this->setup->CoreFields() as $strField => $objField){
            if(isset($objMemberData->$strField)){
              $objField->setValue($objMemberData->$strField);
            }
          }
        }
        
        $this->loadMultiFieldData('member', array('Id' => $this->setup->getElementId()));
      }      
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }  
  
  /**
   * getModelMembers
   * @return Model_Members
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelMembers(){
    if (null === $this->objModelMembers) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Members.php';
      $this->objModelMembers = new Model_Members();
    }
    return $this->objModelMembers;
  }
}
?>