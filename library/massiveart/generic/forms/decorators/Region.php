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
 * @package    library.massiveart.generic.forms.decorators
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Form_Decorator_Region
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-23: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Form_Decorator_Region extends Zend_Form_Decorator_Fieldset {

  /**
   * Render a region
   *
   * @param  string $content
   * @return string
   */
  public function render($content) {
    $core = Zend_Registry::get('Core');

  	$objElement = $this->getElement();
    $objView = $objElement->getView();

    if (null === $objView) {
        return $content;
    }

    $strLegend = $this->getLegend();
    $arrAttribs = $this->getOptions();
    $strName = $objElement->getFullyQualifiedName();
    $strId = $objElement->getId();

    $strPosition = $objElement->getAttrib('position');

    $intRegionId = $objElement->getAttrib('regionId');
    $strRegionExt = $objElement->getAttrib('regionExt');
    $blnIsMultiply = $objElement->getAttrib('isMultiply');
    $blnIsEmptyWidget = $objElement->getAttrib('isEmptyWidget');
    $intRegionCounter = $objElement->getAttrib('regionCounter');
    $strRegionTitle = $objElement->getAttrib('regionTitle');
    $intRegionTypeId = $objElement->getAttrib('regionTypeId');

    $strBoxStyle = '';
    if($blnIsEmptyWidget == true){
      $strBoxStyle = ' style="display:none;"';
    }

    $strAddonCssClass = '';
    $intColumns = $objElement->getAttrib('columns');
    if($blnIsMultiply == true){
      $strAddonCssClass = ' sortablebox';
      $intColumns = '12';

    }

    $strCssPos = '';
    if($strPosition != ''){
      $strCssPos = 'absolute'.$objElement->getAttrib('position').' ';
    }

    $strTypeCss = '';
    if($intRegionTypeId != '' && $intRegionTypeId == $core->sysConfig->region_types->config){
      $strTypeCss = ' configbox';
      if($objElement->getAttrib('style') != ''){
        $strTypeCss = ' configbox-closed';
      }
    }

    $strEditboxClass = 'editbox';
    $strCornerBlClass = 'cornerbl';
    if($objElement->getAttrib('style') != ''){
      $strEditboxClass = $strEditboxClass.'-closed';
    	$strCornerBlClass = $strCornerBlClass.'-closed';
    }



    $strOutput = '';
    if($blnIsMultiply == true && $intRegionCounter == 1){
    	$strOutput .= '<div id="divRegion_'.$intRegionId.'" class="box-'.$objElement->getAttrib('columns').'">';
    	$strOutput .= '<div class="multiregionline">
                       <div class="multiregiontop white bold">'.$strLegend.'</div>
                     </div>';
    }

    $strOutput .= '<div id="divRegion_'.$strId.'" class="'.$strCssPos.'box-'.$intColumns.$strAddonCssClass.'"'.$strBoxStyle.'>
          <div id="editbox'.$strId.'" class="'.$strEditboxClass.$strTypeCss.'">
            <div class="cornertl"';
    if($objElement->getAttrib('collapsable')){
      $strOutput .= ' onclick="myForm.toggleFieldsBox(\''.$strId.'\'); return false;"';
    }
    $strOutput .= '>
              <div id="pointer'.$strId.'"';
    if($objElement->getAttrib('collapsable')){
    	if($objElement->getAttrib('style') != ''){
    	  $strOutput .= ' class="pointerwhite closed"';
    	}else{
    		$strOutput .= ' class="pointerwhite opened"';
    	}
    }
    $strOutput .= '></div>
            </div>
            <div class="cornertr"></div>
            <div class="editboxtitlecontainer">
              <div class="editboxtitle';
    if($objElement->getAttrib('collapsable')){
      $strOutput .= ' cursorhand" onclick="myForm.toggleFieldsBox(\''.$strId.'\'); return false;';
    }
    $strOutput .= '">';
    if($blnIsMultiply != true){
      $strOutput .= $strLegend;
    }

    if($blnIsMultiply == true){
    	$strOutput .= '<span id="spanRegionTitle_'.$strId.'" class="regiontitlecopy black normal">'.$strRegionTitle.'</span>';
    }

    $strOutput .= '</div>';

    if($blnIsMultiply){
      // drag & drop region
    	$strOutput .= '<div class="editboxdrag"></div>';
      // plus & minus for region
    	$strOutput .= '<div class="editboxmultiplycontainer">
                <div id="divRemoveRegion_'.$strId.'" onclick="myForm.removeRegion(\''.$intRegionId.'\',\''.$strRegionExt.'\');" class="editboxmultiplyminus"></div>
                <div id="divAddRegion_'.$strId.'" onclick="myForm.addRegion(\''.$intRegionId.'\');" class="editboxmultiplyplus"></div>
                <div class="clear"></div>
              </div>';
    }
    $strOutput .= '<div class="clear"></div>
            </div>
            <div id="fieldsbox'.$strId.'" class="editboxfields" style="'.$objElement->getAttrib('style').'">';
    $strOutput .=    $content;
    $strOutput .= '  <div class="clear"></div>
            </div>
            <div id="cornerbl'.$strId.'" class="'.$strCornerBlClass.'"></div>
            <div id="cornerbr'.$strId.'" class="cornerbr"></div>
          </div>
        </div>';

    if($blnIsEmptyWidget == true){
      $strOutput .= '
          <div class="clear"></div>
          <div class="multiregionline"></div>
        </div>
        <script type="text/javascript">//<![CDATA[
          myForm.createSortableRegion(\''.$intRegionId.'\');
        //]]>
        </script>';
    }

    return $strOutput;
  }

}

?>