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
 * @package    library.massiveart.generic.fields.Imagemap.data.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericDataHelper_Imagemap
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-08-10:  Raphael Stocker
 *
 * @author Raphael Stocker <rst@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.fields.Imagemap.data.helpers
 * @subpackage GenericDataHelper_Imagemap
 */

require_once(dirname(__FILE__) . '/../../../../data/helpers/Abstract.php');

class GenericDataHelper_Imagemap extends GenericDataHelperAbstract
{
    
    /**
     * @var Model_GenericData
     */
    protected $objModelGenericData;
    
    /**
     * @var Model_Files
     */
    protected $objModelFiles;
    
    /**
     * load()
     * @param integer $intElementId
     * @param string $strType
     * @param string $strElementId
     * @param integet $intVersion
     * @author Raphael Stocker <rst@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
    try {
            $this->strType = $strType;

            $strGenTableName = $strType . 'Imagemaps';
            $objGenTable = $this->getModelGenericData()->getGenericTable($strType . 'Imagemaps');
            $objSelect = $objGenTable->select();
            $objSelect->setIntegrityCheck(false);
            $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('id', 'idFiles', 'size', 'idTargetRegion', 'markers'));
            $objSelect->where($strGenTableName . '.' . $strType . 'Id = ?', $strElementId);
            $objSelect->where($strGenTableName . '.version = ?', $intVersion);
            $objSelect->where($strGenTableName . '.idLanguages = ?', $this->objElement->Setup()->getLanguageId());
            $objSelect->where($strGenTableName . '.idFields = ?', $this->objElement->id);
            $objRawDatas = $objGenTable->fetchAll($objSelect);
            
            if (count($objRawDatas) > 0) {
                $objRawData = $objRawDatas->current();
                $value = new stdClass();
                $value->size = $objRawData->size;
                $value->idTargetRegion = $objRawData->idTargetRegion;
                if (isset($objRawData->idFiles) && $objRawData->idFiles > 0) {
                    $objFiles = $this->getModelFiles()->loadFileById($objRawData->idFiles);
                    if (count($objFiles) > 0 ) {
                        $objFile = $objFiles->current();
                        $value->file = $objRawData->idFiles;
                        $value->path = $objFile->path;
                        $value->filename = $objFile->filename;
                        $dimensions = getimagesize(GLOBAL_ROOT_PATH.'public/website/uploads/images/' . $value->path . $value->size . '/' . $value->filename);
                        $value->dimensions = $dimensions;
                    } 
                }
                $value->markers = $objRawData->markers;
                $this->objElement->setValue($value);
            }
            

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }


    /**
     * loadInstanceData
     * @param string $strType
     * @param string $strElementId
     * @param GenericElementRegion $objRegion
     * @param number $intVersion
     * @author Raphael Stocker <rst@massiveart.com>
     * @version 1.0
     */
    public function loadInstanceData($strType, $strElementId, $objRegion, $intVersion)
    {
        try {
             // TODO
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }
    
    /**
     * getModelGenericData
     * @return Model_GenericData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelGenericData()
    {
        if (null === $this->objModelGenericData) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/GenericData.php';
            $this->objModelGenericData = new Model_GenericData();
        }

        return $this->objModelGenericData;
    }
    
  /**
   * getModelFiles
   * @return Model_Files
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelFiles(){
    if (null === $this->objModelFiles) {
      /**
       * autoload only handles "library" components.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Files.php';
      $this->objModelFiles = new Model_Files();
      $this->objModelFiles->setLanguageId($this->objElement->Setup()->getLanguageId());
    }
  
    return $this->objModelFiles;
  }
}