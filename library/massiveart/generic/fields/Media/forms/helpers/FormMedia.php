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
 * @package    library.massiveart.generic.fields.Media.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormMedia
 * 
 * Helper to generate a "add media" element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-12: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormMedia
 */

class Form_Helper_FormMedia extends Zend_View_Helper_FormElement {

  // display position options 
  protected static $arrPositionOptions = array(array('Image' => '_left_top_45.gif',
                                                     'Key'   => Image::POSITION_LEFT_TOP), 
                                               array('Image' => '_center_top.gif',
                                                     'Key'   => Image::POSITION_CENTER_TOP), 
                                               array('Image' => '_right_top_45.gif',
                                                     'Key'   => Image::POSITION_RIGHT_TOP), 
                                               array('Image' => '_left_middle.gif',
                                                     'Key'   => Image::POSITION_LEFT_MIDDLE), 
                                               array('Image' => '_center_middle.gif',
                                                     'Key'   => Image::POSITION_CENTER_MIDDLE), 
                                               array('Image' => '_right_middle.gif',
                                                     'Key'   => Image::POSITION_RIGHT_MIDDLE), 
                                               array('Image' => '_left_bottom_45.gif',
                                                     'Key'   => Image::POSITION_LEFT_BOTTOM), 
                                               array('Image' => '_center_bottom.gif',
                                                     'Key'   => Image::POSITION_CENTER_BOTTOM), 
                                               array('Image' => '_right_bottom_45.gif',
                                                     'Key'   => Image::POSITION_RIGHT_BOTTOM)
  );
  
  /**
   * formMedia
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function formMedia($name, $value = null, $attribs = null){
    $info = $this->_getInfo($name, $value, $attribs);
    $core = Zend_Registry::get('Core');
    extract($info); // name, value, attribs, options, listsep, disable

    /**
     * is it disabled?
     */ 
    $disabled = '';
    if ($disable) {
      $disabled = ' disabled="disabled"';
    }

    /**
     * build the element
     */
    //$strOutput = '<textarea name="'.$this->view->escape($name).'" id="'.$this->view->escape($id).'"'.$disabled.' '. $this->_htmlAttribs($attribs).'>'.$this->view->escape($value).'</textarea>';
    $strOutput = '<div class="mediawrapper">
                    <div class="mediatop">'.$core->translate->_('Add_medias').': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myForm.getAddMediaOverlay(\'divMediaContainer_'.$this->view->escape($id).'\'); return false;"/></div>
                    <div id="divMediaContainer_'.$this->view->escape($id).'"'.$disabled.' class="'.$attribs['class'].'">
                    </div>
                    <input type="hidden" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" isCoreField="'.((array_key_exists('isCoreField', $attribs)) ? $attribs['isCoreField'] : '').'" fieldId="'.((array_key_exists('fieldId', $attribs)) ? $attribs['fieldId'] : '').'" value="'.$this->view->escape($value).'"/>';
    
    if(array_key_exists('showDisplayOptions', $attribs) && $attribs['showDisplayOptions'] != 0){
      $strDisplayOption = (isset($attribs['display_option'])) ? $attribs['display_option'] : '';
      $objDisplayOption = json_decode(str_replace("'", '"', $strDisplayOption));  
         
      if(!isset($objDisplayOption->position)) $objDisplayOption->position = 'LEFT_MIDDLE';
      if(!isset($objDisplayOption->size)) $objDisplayOption->size = null;
      
      $strOutput .= '
                    <div class="mediabottom">
                      <div class="mediaposition">';
      $strOutput .= $this->_buildPositionChooser($attribs['showDisplayOptions'], $name, $objDisplayOption->position);                      
      $strOutput .= '
                      </div>
                      <div class="mediasize">
                        <select id="'.$this->view->escape($id).'_display_option_size" onchange="myForm.updateMediaDisplaySize(\''.$this->view->escape($name).'_display_option\', this.value);">';

      $arrImagesSizes = Zend_Registry::get('Core')->sysConfig->upload->images->default_sizes->default_size->toArray();
      foreach($arrImagesSizes as $arrImageSize){
        if(isset($arrImageSize['display']) && isset($arrImageSize['display']['text_block'])){
          if($arrImageSize['display']['text_block'] == 'default' && $objDisplayOption->size == null) $objDisplayOption->size = $arrImageSize['folder'];          
          $strSelected = ($arrImageSize['folder'] == $objDisplayOption->size) ? ' selected="selected"' : '';          
          $strOutput .= '
                         <option value="'.$arrImageSize['folder'].'"'.$strSelected.'>'.$arrImageSize['folder'].'</option>';
        }
      }
      $strOutput .= '
                        </select>
                      </div>
                      <input type="hidden" id="'.$this->view->escape($id).'_display_option" name="'.$this->view->escape($name).'_display_option" value="'.str_replace('"', "'", json_encode($objDisplayOption)).'"/>
                      <div class="clear"></div>
                    </div>';
    }
    $strOutput .= '
                  </div>';
    
    return $strOutput;
  }
    
  /**
   * _buildPositionChooser
   * @param string $displayOptions
   * @param string $name
   * @return string
   */
  protected function _buildPositionChooser($displayOptions, $name, $selected){
    
    $strDisplayOptions = sprintf('%09d', $displayOptions);
    $arrDisplayOptions = str_split($strDisplayOptions);
    $strPositionChooser = '';
    
    foreach($arrDisplayOptions as $intPos => $intActive){
      $strCssClass = '';
      $strAction = '';
      $strImage = 'inactive.gif';
      if((int) $intActive === 1){
        $strCssClass = ' active';
        $strImage = ($selected == self::$arrPositionOptions[$intPos]['Key']) ? 'selected'.self::$arrPositionOptions[$intPos]['Image'] : 'active'.self::$arrPositionOptions[$intPos]['Image'];
        $strAction = 'myForm.updateMediaDisplayPosition(\''.$this->view->escape($name).'_display_option\', \''.self::$arrPositionOptions[$intPos]['Key'].'\');';        
      }
      
      $strPositionChooser .= '<div id="'.$this->view->escape($name).'_display_option_'.self::$arrPositionOptions[$intPos]['Key'].'" class="item'.$strCssClass.'" style="background-image:url(\'/zoolu-statics/images/position/'.$strImage.'\');" onclick="'.$strAction.'"></div>'; 
    }
    
    return $strPositionChooser;
  }
}

?>