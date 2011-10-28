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
 * GenericDataTypeGlobal
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-11-02: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package generic.data.type.interface.php
 * @subpackage GenericFormTypeGlobal
 */

require_once(dirname(__FILE__).'/generic.data.type.abstract.class.php');

class GenericDataTypeGlobal extends GenericDataTypeAbstract {

  /**
   * @var Model_Globals
   */
  protected $objModelGlobals;

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
    $this->core->logger->debug('massiveart->generic->data->GenericDataTypeGlobal->save()');
    try{

      $this->getModelGlobals()->setLanguageId($this->setup->getLanguageId());

      $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

      /**
       * add|edit|newVersion core and instance data
       */
      switch($this->setup->getActionType()){
        case $this->core->sysConfig->generic->actions->add :

          $objGlobal = $this->objModelGlobals->add($this->setup);

          $this->setup->setElementId($objGlobal->id);
          if(isset($objGlobal->linkId)) $this->setup->setElementLinkId($objGlobal->linkId);

          $this->insertCoreData('global', $objGlobal->globalId, $objGlobal->version);
          $this->insertFileData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
          $this->insertMultiFieldData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
          $this->insertInstanceData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
          $this->insertMultiplyRegionData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
          break;

        case $this->core->sysConfig->generic->actions->edit :

          $objGlobal = $this->objModelGlobals->load($this->setup->getElementId());
          
          if(count($objGlobal) > 0){
            $objGlobal = $objGlobal->current();

            $this->objModelGlobals->update($this->setup, $objGlobal);

            $this->updateCoreData('global', $objGlobal->globalId, $objGlobal->version);
            $this->updateFileData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            $this->updateMultiFieldData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            $this->updateInstanceData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            $this->updateMultiplyRegionData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
          }
          break;

        case $this->core->sysConfig->generic->actions->change_template :

          $objGlobal = $this->objModelGlobals->load($this->setup->getElementId());
          
          if(count($objGlobal) > 0){
            $objGlobal = $objGlobal->current();

            $this->objModelGlobals->update($this->setup, $objGlobal);
            
            $this->insertCoreData('global', $objGlobal->globalId, $objGlobal->version);

            if($this->blnHasLoadedFileData){
              $this->updateFileData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            }else{
              $this->insertFileData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            }

            if($this->blnHasLoadedMultiFieldData){
              $this->updateMultiFieldData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            }else{
              $this->insertMultiFieldData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            }

            if($this->blnHasLoadedInstanceData){
              $this->updateInstanceData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            }else{
              $this->insertInstanceData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            }
            
            if($this->blnHasLoadedMultiplyRegionData){
              $this->updateMultiplyRegionData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
            }else{
              $this->insertMultiplyRegionData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version)); 
            }
          }          
          break;

        case $this->core->sysConfig->generic->actions->change_template_id :

          $objGlobal = $this->objModelGlobals->load($this->setup->getElementId());
          
          if(count($objGlobal) > 0){
            $objGlobal = $objGlobal->current();

            $this->objModelGlobals->update($this->setup, $objGlobal);                        
          }          
          break;
      }

      /**
       * now save all the special fields
       */
      if(count($this->setup->SpecialFields()) > 0){
        foreach($this->setup->SpecialFields() as $objField){
          $objField->setGenericSetup($this->setup);
          if($objField->type == GenericSetup::FIELD_TYPE_URL && (int) $this->setup->getElementLinkId() > 0){
          	$objField->save($this->setup->getElementLinkId(), 'global', $objGlobal->globalId, $objGlobal->version);
          }else{
            $objField->save($this->setup->getElementId(), 'global', $objGlobal->globalId, $objGlobal->version);	
          }
        }
      }
      
      //cache expiring
      if($this->Setup()->getField('url')){
        $strUrl = $this->Setup()->getField('url')->url;
        $strUrlLanguageCode = $this->Setup()->getField('url')->languageCode;
        
        $arrFrontendOptions = array(
          'lifetime' => null, // cache lifetime (in seconds), if set to null, the cache is valid forever.
          'automatic_serialization' => true
        );

        $arrBackendOptions = array(
          'cache_dir' => GLOBAL_ROOT_PATH.$this->core->sysConfig->path->cache->pages // Directory where to put the cache files
        );

        // getting a Zend_Cache_Core object
        $objCache = Zend_Cache::factory('Output',
                                        'File',
                                        $arrFrontendOptions,
                                        $arrBackendOptions);

        $strCacheId = 'page_'.$this->Setup()->getRootLevelId().'_'.strtolower(str_replace('-', '_', $strUrlLanguageCode)).'_'.preg_replace('/[^a-zA-Z0-9_]/', '_', $strUrl);

        $objCache->remove($strCacheId);

        $objCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('StartGlobal', 
                                                                           'GlobalType_'.$this->core->sysConfig->global_types->product_overview->id,
                                                                           'GlobalType_'.$this->core->sysConfig->global_types->content_overview->id,
                                                                           'GlobalType_'.$this->core->sysConfig->global_types->press_overview->id,        
                                                                           'GlobalType_'.$this->core->sysConfig->global_types->course_overview->id,
                                                                           'GlobalType_'.$this->core->sysConfig->global_types->event_overview->id,
                                                                           'GlobalId_'.$objGlobal->globalId.'_'.$this->setup->getLanguageId()));        
      }

      //global index      
      if($this->setup->getStatusId() == $this->core->sysConfig->status->live){
        if(substr(PHP_OS, 0, 3) === 'WIN') {
          $this->core->logger->warning('Now indexing implemented at the moment!');
          //TODO:FIXME $this->updateIndex(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->global, $objGlobal->globalId.'_'.$this->setup->getLanguageId());
        }else{
          $strIndexGlobalFilePath = GLOBAL_ROOT_PATH.'cli/IndexGlobal.php';
          //run global index in background
          exec("php ".$strIndexGlobalFilePath." --globalId='".$objGlobal->globalId."' --linkId='".$this->setup->getElementLinkId()."' --version=".$objGlobal->version." --languageId=".$this->setup->getLanguageId()." --rootLevelId=".$this->setup->getRootLevelId()." > /dev/null &#038;");
        }
      }else{
        //$this->removeFromIndex(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->global, $objGlobal->globalId.'_'.$this->setup->getLanguageId().'_r*');
        $strIndexGlobalFilePath = GLOBAL_ROOT_PATH.'cli/IndexRemoveGlobal.php';
        //run remove global from index in background
        exec("php ".$strIndexGlobalFilePath." --key='".$objGlobal->globalId."_".$this->setup->getLanguageId()."_r*' > /dev/null &#038;");
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
    $this->core->logger->debug('massiveart->generic->data->GenericDataTypeGlobal->load()');
    try {

      $objGlobal = $this->getModelGlobals()->load($this->setup->getElementId());

      if(count($objGlobal) > 0){
        $objGlobal = $objGlobal->current();

        /**
         * set some metainformations of current global to get them in the output
         */
        $this->setup->setMetaInformation($objGlobal);
        if($objGlobal->idGlobalTypes > 0) $this->setup->setElementTypeId($objGlobal->idGlobalTypes);
        if($objGlobal->isStartGlobal != null) $this->setup->setIsStartElement($objGlobal->isStartGlobal);
        if($objGlobal->idParentTypes != null) $this->setup->setParentTypeId($objGlobal->idParentTypes);

        parent::loadGenericData('global', array('Id' => $objGlobal->globalId, 'Version' => $objGlobal->version));
        
        /**
		     * now laod all data from the special fields
		     */
		    if(count($this->setup->SpecialFields()) > 0){
		      foreach($this->setup->SpecialFields() as $objField){
		        $objField->setGenericSetup($this->setup);
		        if($objField->type == GenericSetup::FIELD_TYPE_URL && (int) $this->setup->getElementLinkId() > 0){
		        	$objField->load($this->setup->getElementLinkId(), 'global', $objGlobal->globalId, $objGlobal->version);
	          }else{
	            $objField->load($this->setup->getElementId(), 'global', $objGlobal->globalId, $objGlobal->version); 
	          }		        
		      }
		    }

      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * getModelGlobals
   * @return Model_Globals
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelGlobals(){
    if (null === $this->objModelGlobals) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'global/models/Globals.php';
      $this->objModelGlobals = new Model_Globals();
      $this->objModelGlobals->setLanguageId($this->setup->getLanguageId());
    }

    return $this->objModelGlobals;
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