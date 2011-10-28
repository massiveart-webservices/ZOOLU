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
 * GenericDataTypeCategory extends GenericDataTypeAbstract
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-20: Cornelius Hansjakob
 * 1.1, 2009-02-03: Thomas Schedler: The Nested Set Model
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericDataTypeCategory
 */

require_once(dirname(__FILE__).'/generic.data.type.abstract.class.php');

class GenericDataTypeCategory extends GenericDataTypeAbstract {

	/**
   * @var Model_Categories
   */
  protected $objModelCategories;

	/**
	 * save
	 * @author Cornelius Hansjakob <cha@massiveart.com>
	 * @version 1.1
	 */
	public function save(){
		$this->core->logger->debug('massiveart->generic->data->GenericDataTypeCategory->save()');
		try{

		  $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

      $this->getModelCategories()->setLanguageId($this->setup->getLanguageId());

			/**
			 * add|edit|newVersion core and instance data
			 */
			switch($this->setup->getActionType()){
				case $this->core->sysConfig->generic->actions->add:

				  /**
				   * add category node to the "Nested Set Model"
				   */
				  $this->setup->setElementId($this->objModelCategories->addCategoryNode($this->setup->getParentId(), array('idCategoryTypes' => $this->setup->getElementTypeId())));

          if(count($this->setup->CoreFields()) > 0){
            foreach($this->setup->CoreFields() as $strField => $objField){
              $arrCoreData = array('idCategories' => $this->setup->getElementId(),
                                   'idLanguages'  => $this->setup->getLanguageId(),
                                   $strField      => $objField->getValue(),
                                   'idUsers'      => $intUserId,
                                   'changed'      => date('Y-m-d H:i:s'));

              $this->getModelGenericData()->getGenericTable('category'.((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')).'ies' : ucfirst($strField).'s'))->insert($arrCoreData);
            }
          }

          break;
				case $this->core->sysConfig->generic->actions->edit :

          if(count($this->setup->CoreFields()) > 0){
            /**
             * for each core field, try to insert into the secondary table
             */
            foreach($this->setup->CoreFields() as $strField => $objField){

              $objGenTable = $this->getModelGenericData()->getGenericTable('category'.((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')).'ies' : ucfirst($strField).'s'));

              $arrCoreData = array($strField  => $objField->getValue(),
                                   'idUsers'  => $intUserId,
                                   'changed'  => date('Y-m-d H:i:s'));

              $strWhere = $objGenTable->getAdapter()->quoteInto('idCategories = ?', $this->setup->getElementId());
              $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());
              $intNumOfEffectedRows =  $objGenTable->update($arrCoreData, $strWhere);

              if($intNumOfEffectedRows == 0){
                $arrCoreData = array('idCategories' => $this->setup->getElementId(),
                                   'idLanguages'  => $this->setup->getLanguageId(),
                                   $strField      => $objField->getValue(),
                                   'idUsers'      => $intUserId,
                                   'changed'      => date('Y-m-d H:i:s'));
                $objGenTable->insert($arrCoreData);
              }
            }
          }
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
		$this->core->logger->debug('massiveart->generic->data->GenericDataTypeCategory->load()');
		try {

			$this->getModelCategories()->setLanguageId($this->setup->getLanguageId());
			$objCategoriesData = $this->getModelCategories()->loadCategory($this->setup->getElementId());

		  if(count($objCategoriesData) > 0){
        $objCategoryData = $objCategoriesData->current();

        if(count($this->setup->CoreFields()) > 0){
          /**
           * for each core field, try to select the secondary table
           */
          foreach($this->setup->CoreFields() as $strField => $objField){
            $objGenTable = $this->getModelGenericData()->getGenericTable('category'.((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')).'ies' : ucfirst($strField).'s'));
            $objSelect = $objGenTable->select();

            $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array($strField));
            $objSelect->where('idCategories = ?', $objCategoryData->id);
            $objSelect->where('idLanguages = ?', $this->setup->getLanguageId());

            $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();

            foreach ($arrGenFormsData as $arrRowGenFormData) {
              foreach ($arrRowGenFormData as $column => $value) {
                if($column == $strField){
                  $objField->setValue($value);
                }else{
                  $objField->$column = $value;
                }
              }
            }

          }
        }
      }
		}catch (Exception $exc) {
			$this->core->logger->err($exc);
		}
	}

  /**
   * getModelCategories
   * @return Model_Categories
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelCategories(){
    if (null === $this->objModelCategories) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Categories.php';
      $this->objModelCategories = new Model_Categories();
    }

    return $this->objModelCategories;
  }
}

?>