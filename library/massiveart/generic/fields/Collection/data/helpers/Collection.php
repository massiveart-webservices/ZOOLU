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
 * @package    library.massiveart.generic.fields.Collection.data.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericDataHelper_Collection
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-08-28: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.fields.Collection.data.helpers
 * @subpackage GenericDataHelper_Collection
 */

require_once(dirname(__FILE__).'/../../../../data/helpers/Abstract.php');

class GenericDataHelper_Collection extends GenericDataHelperAbstract  {

  /**
   * @var Model_Pages
   */
  private $objModelPages;

  /**
   * save()
   * @param integer $intElementId
   * @param string $strType
   * @param string $strElementId
   * @param integet $intVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function save($intElementId, $strType, $strElementId = null, $intVersion = null){
    try{

      $this->getModelPages();

      $intParentId = $this->objElement->Setup()->getParentId();
      $intParentTypeId = $this->objElement->Setup()->getParentTypeId();

      $this->objModelPages->deletePageCollection($strElementId, $intVersion);
      $this->objModelPages->deletePageCollectionUrls($intParentId, $intParentTypeId);

      $this->objModelPages->addPageCollection($this->objElement->getValue(), $strElementId, $intVersion);

      $this->load($intElementId, $strType, $strElementId, $intVersion);

      $strBaseUrl = '';
      if($this->objElement->Setup()->getField('url') instanceof GenericElementField){
        $strBaseUrl = preg_replace('/^\/[a-zA-Z]{2}\//', '', $this->objElement->Setup()->getField('url')->getValue());
      }

      if(count($this->objElement->objPageCollection) > 0){

        $objUrlHelper = new GenericDataHelper_Url();
        $objUrlHelper->setElement($this->objElement);
        $objUrlHelper->setType($strType);

        foreach($this->objElement->objPageCollection as $objPageCollection){
          $objPageCollection->url = $objUrlHelper->checkUrlUniqueness($strBaseUrl.$objUrlHelper->makeUrlConform($objPageCollection->title));
        }
      }

      if($this->objElement->objPageCollection) $this->objModelPages->addPageCollectionUrls($this->objElement->objPageCollection, $intParentId, $intParentTypeId);

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * load()
   * @param integer $intElementId
   * @param string $strType
   * @param string $strElementId
   * @param integet $intVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function load($intElementId, $strType, $strElementId = null, $intVersion = null){
    try{
      $this->getModelPages();

      $intParentId = $this->objElement->Setup()->getParentId();
      $intParentTypeId = $this->objElement->Setup()->getParentTypeId();

      $objPageCollectionData = $this->objModelPages->loadPageCollection($strElementId, $intVersion, $intParentId, $intParentTypeId);

      if(count($objPageCollectionData) > 0){
        $this->objElement->objPageCollection = $objPageCollectionData;

        $strValue = '';
        foreach($objPageCollectionData as $objPageInternalLink){
          $strValue .= '['.$objPageInternalLink->pageId.']';
        }

        $this->objElement->setValue($strValue);
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
      $this->objModelPages->setLanguageId($this->objElement->Setup()->getLanguageId());
    }

    return $this->objModelPages;
  }
}
?>