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
 * @package    library.massiveart.generic.data.types
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericDataTypeContact extends GenericDataTypeAbstract 
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-20: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericDataTypeContact
 */

require_once(dirname(__FILE__).'/generic.data.type.abstract.class.php');

class GenericDataTypeContact extends GenericDataTypeAbstract {
  
	/**
   * @var Model_Contacts
   */
  protected $objModelContacts;
	
	/**
	 * save
	 * @author Thomas Schedler <tsh@massiveart.com>
	 * @version 1.1
	 */
	public function save(){
		$this->core->logger->debug('massiveart->generic->data->GenericDataTypeContact->save()');
		try{

      $this->getModelContacts()->setLanguageId($this->setup->getLanguageId());

      $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
      
			/**
			 * add|edit|newVersion core and instance data
			 */
			switch($this->setup->getActionType()){
				case $this->core->sysConfig->generic->actions->add:
				  		  
				  $arrCoreData = array('idGenericForms'   => $this->setup->getGenFormId(),
				                       'idUnits'          => $this->setup->getParentId(),                                   
                               'idUsers'          => $intUserId,
                               'creator'          => $intUserId, 
                               'created'          => date('Y-m-d H:i:s'));
				  
          if(count($this->setup->CoreFields()) > 0){
            foreach($this->setup->CoreFields() as $strField => $obField){
              $arrCoreData[$strField] = $obField->getValue();
            }
          }
          
          /**
           * add contact 
           */
          $this->setup->setElementId($this->objModelContacts->addContact($arrCoreData));
          $this->insertFileData('contact', array('Id' => $this->setup->getElementId()));
          break;
				case $this->core->sysConfig->generic->actions->edit :

          $arrCoreData = array('idUsers' => $intUserId);
          
          if(count($this->setup->CoreFields()) > 0){
            foreach($this->setup->CoreFields() as $strField => $obField){
              $arrCoreData[$strField] = $obField->getValue();
            }
          }
          
          /**
           * add contact 
           */
          $this->objModelContacts->editContact($this->setup->getElementId(), $arrCoreData);
          $this->updateFileData('contact', array('Id' => $this->setup->getElementId()));          
          break;
			}

			return $this->setup->getElementId();
		}catch (Exception $exc) {
			$this->core->logger->err($exc);
		}
	}

	/**
	 * load
	 * @author Thomas Schedler <tsh@massiveart.com>
	 * @version 1.0
	 */
	public function load(){
		$this->core->logger->debug('massiveart->generic->data->GenericDataTypeContact->load()');
		try {
      
			$this->getModelContacts()->setLanguageId($this->setup->getLanguageId());
			$objContactsData = $this->getModelContacts()->loadContact($this->setup->getElementId());
			
		  if(count($objContactsData) > 0){
        $objContactData = $objContactsData->current();

        if(count($this->setup->CoreFields()) > 0){
          /**
           * for each core field set field data
           */
          foreach($this->setup->CoreFields() as $strField => $objField){
            if(isset($objContactData->$strField)){
              $objField->setValue($objContactData->$strField);
            }
          }
        }
        
		    /**
         * generic form file fields
         */
        if(count($this->setup->FileFields()) > 0){
          
          $objGenTable = $this->getModelGenericData()->getGenericTable('contact-'.$this->setup->getFormId().'-'.$this->setup->getFormVersion().'-InstanceFiles');
          $strTableName = $objGenTable->info(Zend_Db_Table_Abstract::NAME);
          
          $objSelect = $objGenTable->select();
          $objSelect->setIntegrityCheck(false);
          
          $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('idFiles'));
          $objSelect->join('fields', 'fields.id = `'.$objGenTable->info(Zend_Db_Table_Abstract::NAME).'`.idFields', array('name'));
          $objSelect->where('idContacts = ?', $objContactData->id);
          
          $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();              
          
          if(count($arrGenFormsData) > 0){
            $this->blnHasLoadedFileData = true;
            foreach($arrGenFormsData as $arrGenRowFormsData){
              $strFileIds = $this->setup->getFileField($arrGenRowFormsData['name'])->getValue().'['.$arrGenRowFormsData['idFiles'].']';            
              $this->setup->getFileField($arrGenRowFormsData['name'])->setValue($strFileIds);
            }
          }          
        }
      }	
      
      
		}catch (Exception $exc) {
			$this->core->logger->err($exc);
		}		 
	}
	
  /**
   * getModelContacts
   * @return Model_Contacts
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelContacts(){
    if (null === $this->objModelContacts) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Contacts.php';
      $this->objModelContacts = new Model_Contacts();
    }

    return $this->objModelContacts;
  }
}

?>