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
 * @package    library.massiveart.generic.fields.Tag.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormTag
 * 
 * Helper to generate a "tag" element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-27: Thomas Schedler
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormTag
 */

class Form_Helper_FormTag extends Zend_View_Helper_FormElement {
  
  /**
   * formTag
   * @param string $name
   * @param string $value
   * @param array $attribs
   * @param mixed $options
   * @param Zend_Db_Table_Rowset $objAllTags
   * @param array $arrTagIds
   * @author Thomas Schedler <tsh@massiveart.com>  
   * @version 1.0
   */
  public function formTag($name, $value = null, $attribs = null, $options = null, $regionId = null, $objAllTags, $arrTagIds){
    $info = $this->_getInfo($name, $value, $attribs);
    $core = Zend_Registry::get('Core');
    extract($info); // name, value, attribs, options, listsep, disable
    
    // XHTML or HTML end tag
    $endTag = ' />';
   
    if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
      $endTag= '>';
    }
       
    // build the element
    $strTags = '';
    
    if(is_object($value) || is_array($value)){
      foreach($value as $objTag){
        $strTags .= '<li value="'.$objTag->id.'">'.htmlentities($objTag->title, ENT_COMPAT, $core->sysConfig->encoding->default).'</li>';        
      }
    }
        
    $strOutput = '<div>
	                  <ol>        
							        <li id="autocompletList_'.$this->view->escape($id).'" class="autocompletList input-text">
                        <input type="text" value="" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" '.$this->_htmlAttribs($attribs).$endTag.'
							          <div id="'.$this->view->escape($id).'_autocompleter" class="autocompleter">
							            <div class="default">'.$core->translate->_('Search_or_add_tags').'</div> 
							            <ul class="feed">
							              '.$strTags.'
							            </ul>
							          </div>
							        </li>
							      </ol>
						      </div>';
    
    /**
     * is empty element
     */
    $blnIsEmpty = false;
    if(array_key_exists('isEmptyField', $attribs) && $attribs['isEmptyField'] == 1){
      $blnIsEmpty = true;  
    }
    
    if($blnIsEmpty == true){
      $strOutput .= '
        <script type="text/javascript">//<![CDATA[ 
          myForm.addTag("'.$this->view->escape($id).'","'.$this->view->escape($regionId).'",'.$this->getAllTagsForAutocompleter($objAllTags).');
        //]]>
        </script>';
    }else{
      $strOutput .= '
        <script type="text/javascript">//<![CDATA[ 
          myForm.initTag("'.$this->view->escape($id).'",'.$this->getAllTagsForAutocompleter($objAllTags).');         
        //]]>
        </script>';
    } 
        
    return $strOutput;
  }
    
  /**
   * getAllTagsForAutocompleter
   * @return Zend_Db_Table_Rowset $objAllTags
   * @return string $strElementId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getAllTagsForAutocompleter($objAllTags){
  	$core = Zend_Registry::get('Core');
    $strAllTags = '[';
    if(count($objAllTags) > 0){
      foreach($objAllTags as $objTag){
        $strAllTags .= '{"caption":"'.htmlentities($objTag->title, ENT_COMPAT, $core->sysConfig->encoding->default).'","value":'.$objTag->id.'},';
      }
      $strAllTags = trim($strAllTags, ',');
    }
    $strAllTags .= ']';
    return $strAllTags;
  }
}

?>