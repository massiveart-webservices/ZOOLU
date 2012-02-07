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
 * GenericDataTypeFolder extends GenericDataTypeAbstract
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-16: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericDataTypeFolder
 */

require_once(dirname(__FILE__).'/generic.data.type.abstract.class.php');

class GenericDataTypeFolder extends GenericDataTypeAbstract {

  /**
   * @var Model_Folders
   */
  protected $objModelFolders;

	/**
	 * save
	 * @author Thomas Schedler <tsh@massiveart.com>
	 * @version 1.0
	 */
	public function save(){
		$this->core->logger->debug('massiveart->generic->data->GenericDataTypeFolder->save()');
		try{

		  $this->getModelFolders()->setLanguageId($this->setup->getLanguageId());

			$intUserId = Zend_Auth::getInstance()->getIdentity()->id;

			/**
			 * add|edit|... core and instance data
			 */
      switch($this->setup->getActionType()){
        case $this->core->sysConfig->generic->actions->add :
				  
          $objFolder = $this->objModelFolders->add($this->setup);

          $this->setup->setElementId($objFolder->id);
         
          $this->insertCoreData('folder', $objFolder->folderId, $objFolder->version);
          $this->insertFileData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));
          $this->insertMultiFieldData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));
          $this->insertInstanceData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));
          $this->insertMultiplyRegionData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));
          break;
          
            case $this->core->sysConfig->generic->actions->edit :
      
          $objFolder = $this->objModelFolders->load($this->setup->getElementId());
          
          if(count($objFolder) > 0){
            $objFolder = $objFolder->current();
            
            $this->objModelFolders->update($this->setup, $objFolder);
            
            $this->updateCoreData('folder', $objFolder->folderId, $objFolder->version);
            $this->updateFileData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));
            $this->updateMultiFieldData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));
            $this->updateInstanceData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));
            $this->updateMultiplyRegionData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));
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
	 * @author Thomas Schedler <tsh@massiveart.com>
	 * @version 1.0
	 */
	public function load(){
		$this->core->logger->debug('massiveart->generic->data->GenericDataTypeFolder->load()');
		try {
		  $objFolder = $this->getModelFolders()->load($this->setup->getElementId());

			if(count($objFolder) > 0){
				$objFolder = $objFolder->current();

				/**
				 * set some metainformations of current folder to get them in the output
				 */
				$this->setup->setMetaInformation($objFolder);
				$this->setup->setUrlFolder($objFolder->isUrlFolder);

        parent::loadGenericData('folder', array('Id' => $objFolder->folderId, 'Version' => $objFolder->version));

			 /**
         * now laod all data from the special fields
         */
        if(count($this->setup->SpecialFields()) > 0){
          foreach($this->setup->SpecialFields() as $objField){
            $objField->setGenericSetup($this->setup);
            $objField->load($this->setup->getElementId(), 'folder', $objFolder->folderId, $objFolder->version);
          }
        }
			}
		}catch (Exception $exc) {
			$this->core->logger->err($exc);
		}
	}

  /**
   * getModelFolders
   * @return Model_Folders
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelFolders(){
    if (null === $this->objModelFolders) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Folders.php';
      $this->objModelFolders = new Model_Folders();
      $this->objModelFolders->setLanguageId($this->setup->getLanguageId());
    }

    return $this->objModelFolders;
  }
}

?>