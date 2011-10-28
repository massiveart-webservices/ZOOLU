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
 * @package    library.massiveart.generic.elements
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericElementRegion
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-20: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.elements
 * @subpackage GenericElementRegion
 */

require_once(dirname(__FILE__).'/generic.element.abstract.class.php');

class GenericElementRegion extends GenericElementAbstract {
    
	protected $intRegionId;
	protected $strRegionTitle;
	protected $intRegionCols;
	protected $intRegionOrder;
	protected $intRegionPosition;
	protected $intRegionTypeId;
	protected $blnRegionCollapsable = true;
	protected $blnRegionIsCollapsed = false;
	protected $blnRegionIsMultiply = false;
	protected $blnRegionMultiplyRegion = false;

	protected $arrFields = array();
	
	protected $arrRegionInstanceIds = array();
  /**
   * property of the region instance id array
   * @return Array $arrRegionInstanceIds
   */
  public function RegionInstanceIds(){
    return $this->arrRegionInstanceIds;
  }
  
  protected $arrFieldNames = array();
  /**
   * property of the field name array
   * @return Array $arrFieldNames
   */
  public function FieldNames(){
    return $this->arrFieldNames;
  }
  	
	protected $arrFileFieldNames = array();
  /**
   * property of the file field names array
   * @return Array $arrFileFieldNames
   */
  public function FileFieldNames(){
    return $this->arrFileFieldNames;
  }

  protected $arrFileFilterFieldNames = array();
  /**
   * property of the file filter field names array
   * @return Array $arrFileFilterFieldNames
   */
  public function FileFilterFieldNames(){
    return $this->arrFileFilterFieldNames;
  }

	protected $arrMultiFieldNames = array();
  /**
   * property of the multi field names array
   * @return Array $arrMultiFieldNames
   */
  public function MultiFieldNames(){
    return $this->arrMultiFieldNames;
  }
  
	protected $arrInstanceFieldNames = array();
  /**
   * property of the instance field names array
   * @return Array $arrInstanceFieldNames
   */
  public function InstanceFieldNames(){
    return $this->arrInstanceFieldNames;
  }
  
  protected $arrSpecialFieldNames = array();
  /**
   * property of the special field names array
   * @return Array $arrSpecialFieldNames
   */
  public function SpecialFieldNames(){
    return $this->arrSpecialFieldNames;
  }
  
	/**
   * addField
   * @param GenericElementField $objField
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
	public function addField(GenericElementField &$objField){
	  $this->arrFields[$objField->name] = $objField;	  
	}
	
  /**
   * getField
   * @param string $strFieldName
   * @return GenericElementField|null
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getField($strFieldName){
    if(array_key_exists($strFieldName, $this->arrFields)){
      return $this->arrFields[$strFieldName];    
    }
    return null;
  }
	
  /**
   * getFields
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getFields(){
    return $this->arrFields;   
  }
  
  /**
   * addRegionInstanceId
   * @param integer $intRegionInstanceId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addRegionInstanceId($intRegionInstanceId){
    $this->arrRegionInstanceIds[] = $intRegionInstanceId;    
  }
  
  /**
   * resetRegionInstances
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function resetRegionInstances(){
    $this->arrRegionInstanceIds = array();
  }
  
  /**
   * addFieldName
   * @param string $strFieldName
   * @param integer $intFieldType
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addFieldName($strFieldName, $intFieldType){
    $this->arrFieldNames[$strFieldName] = $intFieldType;    
  }
  
  /**
   * addFileFieldName
   * @param string $strFieldName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addFileFieldName($strFieldName){
    $this->arrFileFieldNames[] = $strFieldName;    
  }

  /**
   * addFileFilterFieldName
   * @param string $strFieldName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addFileFilterFieldName($strFieldName){
    $this->arrFileFilterFieldNames[] = $strFieldName;
  }
    
  /**
   * addMultiFieldName
   * @param string $strFieldName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addMultiFieldName($strFieldName){
    $this->arrMultiFieldNames[] = $strFieldName;    
  }
  
  /**
   * addInstanceFieldName
   * @param string $strFieldName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addInstanceFieldName($strFieldName){
     $this->arrInstanceFieldNames[] = $strFieldName;     
  }
  
  /**
   * addSpecialFieldName
   * @param string $strFieldName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addSpecialFieldName($strFieldName){
     $this->arrSpecialFieldNames[] = $strFieldName;     
  }  
	
	/**
	 * setRegionId
	 * @param integer $intRegionId
	 */
	public function setRegionId($intRegionId){
		$this->intRegionId = $intRegionId;
	}

	/**
	 * getRegionId
	 * @param integer $intRegionId
	 */
	public function getRegionId(){
		return $this->intRegionId;
	}

	/**
	 * setRegionCols
	 * @param integer $intRegionCols
	 */
	public function setRegionCols($intRegionCols){
		$this->intRegionCols = $intRegionCols;
	}

	/**
	 * getRegionCols
	 * @param integer $intRegionCols
	 */
	public function getRegionCols(){
		return $this->intRegionCols;
	}
	
  /**
   * setRegionOrder
   * @param integer $intRegionOrder
   */
  public function setRegionOrder($intRegionOrder){
    $this->intRegionOrder = $intRegionOrder;
  }

  /**
   * getRegionOrder
   * @param integer $intRegionOrder
   */
  public function getRegionOrder(){
    return $this->intRegionOrder;
  }

	/**
	 * setRegionPosition
	 * @param integer $intRegionPosition
	 */
	public function setRegionPosition($intRegionPosition){
		$this->intRegionPosition = $intRegionPosition;
	}

	/**
	 * getRegionPosition
	 * @param integer $intRegionPosition
	 */
	public function getRegionPosition(){
		return $this->intRegionPosition;
	}

	/**
	 * setRegionTitle
	 * @param string $strRegionTitle
	 */
	public function setRegionTitle($strRegionTitle){
		$this->strRegionTitle = $strRegionTitle;
	}

	/**
	 * getRegionTitle
	 * @param string $strRegionTitle
	 */
	public function getRegionTitle(){
		return $this->strRegionTitle;
	}
	
  /**
   * setRegionTypeId
   * @param integer $intRegionTypeId
   */
  public function setRegionTypeId($intRegionTypeId){
    $this->intRegionTypeId = $intRegionTypeId;
  }

  /**
   * getRegionTypeId
   * @param integer $intRegionTypeId
   */
  public function getRegionTypeId(){
    return $this->intRegionTypeId;
  }

	/**
	 * setRegionCollapsable
	 * @param boolean $blnRegionCollapsable
	 */
	public function setRegionCollapsable($blnRegionCollapsable){
		$this->blnRegionCollapsable = ($blnRegionCollapsable == 1) ? true : false;
	}

	/**
	 * getRegionCollapsable
	 * @param boolean $blnRegionCollapsable
	 */
	public function getRegionCollapsable(){
		return $this->blnRegionCollapsable;
	}
	
  /**
   * setRegionIsCollapsed
   * @param boolean $blnRegionIsCollapsed
   */
  public function setRegionIsCollapsed($blnRegionIsCollapsed){
    $this->blnRegionIsCollapsed = ($blnRegionIsCollapsed == 1) ? true : false;
  }

  /**
   * getRegionIsCollapsed
   * @param boolean $blnRegionIsCollapsed
   */
  public function getRegionIsCollapsed(){
    return $this->blnRegionIsCollapsed;
  }
  
  /**
   * setRegionIsMultiply
   * @param boolean $blnRegionIsMultiply
   */
  public function setRegionIsMultiply($blnRegionIsMultiply){
    $this->blnRegionIsMultiply = ($blnRegionIsMultiply == 1) ? true : false;
  }

  /**
   * getRegionIsMultiply
   * @param boolean $blnRegionIsMultiply
   */
  public function getRegionIsMultiply(){
    return $this->blnRegionIsMultiply;
  }
  
  /**
   * setRegionMultiplyRegion
   * @param boolean $blnRegionMultiplyRegion
   */
  public function setRegionMultiplyRegion($blnRegionMultiplyRegion){
    $this->blnRegionMultiplyRegion = ($blnRegionMultiplyRegion == 1) ? true : false;
  }

  /**
   * getRegionMultiplyRegion
   * @param boolean $blnRegionMultiplyRegion
   */
  public function getRegionMultiplyRegion(){
    return $this->blnRegionMultiplyRegion;
  }
}

?>