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
 * @package    library.massiveart.command
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GlobalCommand
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-11-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.command
 * @subpackage GlobalCommand
 */

require_once(dirname(__FILE__).'/command.interface.php');

class GlobalCommand implements CommandInterface {

  /**
   * @var Core
   */
  protected $core;

  /**
   * @var Model_Globals
   */
  protected $objModelGlobals;

  /**
   * @var Model_Templates
   */
  protected $objModelTemplates;
  
  protected $intRootLevelGroupId;
  protected $strRootLevelGroupKey;

  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct($intRootLevelGroupId, $strRootLevelGroupKey){
    $this->intRootLevelGroupId = $intRootLevelGroupId;
    $this->strRootLevelGroupKey = $strRootLevelGroupKey;
    $this->core = Zend_Registry::get('Core');
  }

  /**
   * onCommand
   * @param string $strName
   * @param array $arrArgs
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function onCommand($strName, $arrArgs){
    switch($strName){
      case 'addFolderStartElement':
        return $this->addFolderStartGlobal($arrArgs);
      case 'editFolderStartElement':
        return $this->editFolderStartGlobal($arrArgs);
      default:
        return true;
    }
  }

  /**
   * addFolderStartGlobal
   * @param array $arrArgs
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function addFolderStartGlobal($arrArgs){
    try{
      if(array_key_exists('GenericSetup', $arrArgs) && $arrArgs['GenericSetup'] instanceof GenericSetup){
        $objGenericSetup = $arrArgs['GenericSetup'];
        
        $strGlobalType = $this->strRootLevelGroupKey.'_overview';
        
        $intTemplateId = $this->core->sysConfig->global_types->$strGlobalType->default_templateId;
        $objTemplateData = $this->getModelTemplates()->loadTemplateById($intTemplateId);

        if(count($objTemplateData) == 1){
          $objTemplate = $objTemplateData->current();

          /**
           * set form id from template
           */
          $strFormId = $objTemplate->genericFormId;
          $intFormVersion = $objTemplate->version;
          $intFormTypeId = $objTemplate->formTypeId;
        }else{
          throw new Exception('Not able to create a generic data object, because there is no form id!');
        }

        $objGenericData = new GenericData();
        $objGenericData->Setup()->setFormId($strFormId);
        $objGenericData->Setup()->setFormVersion($intFormVersion);
        $objGenericData->Setup()->setFormTypeId($intFormTypeId);
        $objGenericData->Setup()->setTemplateId($intTemplateId);
        $objGenericData->Setup()->setActionType($this->core->sysConfig->generic->actions->add);
        $objGenericData->Setup()->setLanguageId($arrArgs['LanguageId']);
        $objGenericData->Setup()->setFormLanguageId($this->core->intZooluLanguageId);

        $objGenericData->Setup()->setParentId($arrArgs['ParentId']);
        $objGenericData->Setup()->setRootLevelId($objGenericSetup->getRootLevelId());
        $objGenericData->Setup()->setRootLevelGroupId($this->intRootLevelGroupId);
        $objGenericData->Setup()->setElementTypeId($this->core->sysConfig->global_types->$strGlobalType->id);
        $objGenericData->Setup()->setCreatorId($objGenericSetup->getCreatorId());
        $objGenericData->Setup()->setStatusId($objGenericSetup->getStatusId());
        $objGenericData->Setup()->setShowInNavigation($objGenericSetup->getShowInNavigation());
        $objGenericData->Setup()->setModelSubPath('global/models/');
        
        $objGenericData->addFolderStartElement($objGenericSetup->getCoreField('title')->getValue());

        return true;
      }else{
        throw new Exception('There ist now GenericSetup in the args array!');
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      return false;
    }
  }

  /**
   * editFolderStartGlobal
   * @param array $arrArgs
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function editFolderStartGlobal($arrArgs){
    try{
      if(array_key_exists('GenericSetup', $arrArgs) && $arrArgs['GenericSetup'] instanceof GenericSetup){
        $objGenericSetup = $arrArgs['GenericSetup'];

        $intFolderId = $objGenericSetup->getElementId();
        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;


        $arrProperties = array('idUsers'          => $intUserId,
                               'creator'          => $objGenericSetup->getCreatorId(),
                               'idStatus'         => $objGenericSetup->getStatusId(),
                               'showInNavigation' => $objGenericSetup->getShowInNavigation(),
                               'changed'          => date('Y-m-d H:i:s'));

        $strGlobalType = $this->strRootLevelGroupKey.'_overview';        
        $intDefaultTemplateId = $this->core->sysConfig->global_types->$strGlobalType->default_templateId;
        
        $arrTitle = array('idUsers'     => $intUserId,
                          'creator'     => $objGenericSetup->getCreatorId(),
                          'title'       => $objGenericSetup->getCoreField('title')->getValue(),
                          'idLanguages' => $objGenericSetup->getLanguageId(),
                          'changed'     => date('Y-m-d H:i:s'));

        $this->getModelGlobals($arrArgs)->updateFolderStartGlobal($intFolderId, $arrProperties, $arrTitle, $this->intRootLevelGroupId, $intDefaultTemplateId);
        return true;
      }else{
        throw new Exception('There ist now GenericSetup in the args array!');
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      return false;
    }
  }

  /**
   * getModelGlobals
   * @param array $arrArgs
   * @return Model_Globals
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelGlobals($arrArgs){
    if (null === $this->objModelGlobals) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'global/models/Globals.php';
      $this->objModelGlobals = new Model_Globals();
      $this->objModelGlobals->setLanguageId($arrArgs['LanguageId']);
    }

    return $this->objModelGlobals;
  }

  /**
   * getModelTemplates
   * @return Model_Templates
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelTemplates(){
    if (null === $this->objModelTemplates) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Templates.php';
      $this->objModelTemplates = new Model_Templates();
    }

    return $this->objModelTemplates;
  }
}

?>