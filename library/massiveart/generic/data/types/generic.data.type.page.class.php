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
 * GenericDataTypePage
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-16: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package generic.data.type.interface.php
 * @subpackage GenericFormTypePage
 */

require_once(dirname(__FILE__).'/generic.data.type.abstract.class.php');

class GenericDataTypePage extends GenericDataTypeAbstract {

  /**
   * @var Model_Pages
   */
  protected $objModelPages;

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
    $this->core->logger->debug('massiveart->generic->data->GenericDataTypePage->save()');
    try{

      $this->getModelPages()->setLanguageId($this->setup->getLanguageId());

      $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

      /**
       * add|edit|newVersion core and instance data
       */
      switch($this->setup->getActionType()){
        case $this->core->sysConfig->generic->actions->add :

          $objPage = $this->objModelPages->add($this->setup);

          $this->setup->setElementId($objPage->id);
         
          $this->insertCoreData('page', $objPage->pageId, $objPage->version);
          $this->insertFileData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
          $this->insertMultiFieldData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
          $this->insertInstanceData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
          $this->insertMultiplyRegionData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
          break;

        case $this->core->sysConfig->generic->actions->edit :

          $objPage = $this->objModelPages->load($this->setup->getElementId());
          
          if(count($objPage) > 0){
            $objPage = $objPage->current();
            
            $this->objModelPages->update($this->setup, $objPage);
            
            $this->updateCoreData('page', $objPage->pageId, $objPage->version);
            $this->updateFileData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            $this->updateMultiFieldData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            $this->updateInstanceData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            $this->updateMultiplyRegionData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
          }
          break;

        case $this->core->sysConfig->generic->actions->change_template :

          $objPage = $this->objModelPages->load($this->setup->getElementId());
          
          if(count($objPage) > 0){
            $objPage = $objPage->current();

            $this->objModelPages->update($this->setup, $objPage);
                        
            $this->insertCoreData('page', $objPage->pageId, $objPage->version);

            if($this->blnHasLoadedFileData){
              $this->updateFileData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            }else{
              $this->insertFileData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            }

            if($this->blnHasLoadedMultiFieldData){
              $this->updateMultiFieldData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            }else{
              $this->insertMultiFieldData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            }

            if($this->blnHasLoadedInstanceData){
              $this->updateInstanceData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            }else{
              $this->insertInstanceData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            }
            
            if($this->blnHasLoadedMultiplyRegionData){
              $this->updateMultiplyRegionData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
            }else{
              $this->insertMultiplyRegionData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version)); 
            }
          }
          break;
        case $this->core->sysConfig->generic->actions->change_template_id :
         
          $objPage = $this->objModelPages->load($this->setup->getElementId());
          
          if(count($objPage) > 0){
            $objPage = $objPage->current();

            $this->objModelPages->update($this->setup, $objPage);            
          }
          break;
      }

      /**
       * now save all the special fields
       */
      if(count($this->setup->SpecialFields()) > 0){
        foreach($this->setup->SpecialFields() as $objField){
          $objField->setGenericSetup($this->setup);
          $objField->save($this->setup->getElementId(), 'page', $objPage->pageId, $objPage->version);
        }
      }

      //page index
      if($this->setup->getElementTypeId() != $this->core->sysConfig->page_types->link->id && $this->setup->getStatusId() == $this->core->sysConfig->status->live){
        if(substr(PHP_OS, 0, 3) === 'WIN') {
          $this->core->logger->warning('slow page index on windows based OS!');
          $this->updateIndex(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->page.'/'.sprintf('%02d', $this->setup->getLanguageId()), $objPage->pageId.'_'.$this->setup->getLanguageId());
        }else{
          $strIndexPageFilePath = GLOBAL_ROOT_PATH.'cli/IndexPage.php';
          //run page index in background
          exec("php $strIndexPageFilePath --pageId='".$objPage->pageId."' --version=".$objPage->version." --languageId=".$this->setup->getLanguageId()." --rootLevelId=".$this->setup->getRootLevelId()." > /dev/null &#038;");
        }
      }else{
        //$this->removeFromIndex(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->page.'/'.sprintf('%02d', $this->setup->getLanguageId()), $objPage->pageId.'_'.$this->setup->getLanguageId());
        $strIndexPageFilePath = GLOBAL_ROOT_PATH.'cli/IndexRemovePage.php';
        //run remove page from index in background
        exec("php ".$strIndexPageFilePath." --key='".$objPage->pageId."_".$this->setup->getLanguageId()."_r*' > /dev/null &#038;");
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

        $objCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('StartPage', 'PageType_'.$this->core->sysConfig->page_types->overview->id));
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
    $this->core->logger->debug('massiveart->generic->data->GenericDataTypePage->load()');
    try {

      $objPage = $this->getModelPages()->load($this->setup->getElementId());

      if(count($objPage) > 0){
        $objPage = $objPage->current();

        /**
         * set some metainformations of current page to get them in the output
         */
        $this->setup->setMetaInformation($objPage);
        if($objPage->idPageTypes > 0) $this->setup->setElementTypeId($objPage->idPageTypes);
        if($objPage->isStartPage != null) $this->setup->setIsStartElement($objPage->isStartPage);
        if($objPage->idParentTypes != null) $this->setup->setParentTypeId($objPage->idParentTypes);

        parent::loadGenericData('page', array('Id' => $objPage->pageId, 'Version' => $objPage->version));
        
        /**
		     * now laod all data from the special fields
		     */
		    if(count($this->setup->SpecialFields()) > 0){
		      foreach($this->setup->SpecialFields() as $objField){
		        $objField->setGenericSetup($this->setup);
		        $objField->load($this->setup->getElementId(), 'page', $objPage->pageId, $objPage->version);
		      }
		    }
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * getModelPages
   * @return Model_Pages
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelPages(){
    if (null === $this->objModelPages) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'cms/models/Pages.php';
      $this->objModelPages = new Model_Pages();
      $this->objModelPages->setLanguageId($this->setup->getLanguageId());
    }

    return $this->objModelPages;
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