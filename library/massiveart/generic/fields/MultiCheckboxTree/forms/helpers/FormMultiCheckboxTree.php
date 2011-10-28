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
 * @package    library.massiveart.generic.fields.MultiCheckboxTree.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormMultiCheckboxTree
 * 
 * Helper to generate a "checkbox tree" element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-27: Thomas Schedler
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormMultiCheckboxTree
 */

class Form_Helper_FormMultiCheckboxTree extends Zend_View_Helper_FormRadio {
  
  /**
   * Input type to use
   * @var string
   */
  protected $_inputType = 'checkbox';
  
  /**
   * Whether or not this element represents an array collection by default
   * @var bool
   */
  protected $_isArray = true;
    
  /**
   * formMultiCheckboxTree    
   * @param string $name
   * @param string $value
   * @param array $attribs
   * @param mixed $options
   * @param string $listsep
   * @return string
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function formMultiCheckboxTree($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n"){
        
    $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
    extract($info); // name, value, attribs, options, listsep, disable

    // retrieve attributes for labels (prefixed with 'label_' or 'label')
    $label_attribs = array('style' => 'white-space: nowrap;');
    foreach($attribs as $key => $val){
      $tmp    = false;
      $keyLen = strlen($key);
      if((6 < $keyLen) && (substr($key, 0, 6) == 'label_')){
        $tmp = substr($key, 6);
      } elseif((5 < $keyLen) && (substr($key, 0, 5) == 'label')){
        $tmp = substr($key, 5);
      }

      if($tmp){
        // make sure first char is lowercase
        $tmp[0] = strtolower($tmp[0]);
        $label_attribs[$tmp] = $val;
        unset($attribs[$key]);
      }
    }

    $labelPlacement = 'append';
    foreach($label_attribs as $key => $val){
      switch(strtolower($key)){
        case 'placement':
          unset($label_attribs[$key]);
          $val = strtolower($val);
          if(in_array($val, array('prepend', 'append'))){
            $labelPlacement = $val;
          }
          break;
      }
    }

    // the radio button values and labels
    $options = (array) $options;

    // build the element
    $xhtml = '';
    $list  = array();
    
    // should the name affect an array collection?
    $name = $this->view->escape($name);
    if($this->_isArray && ('[]' != substr($name, -2))){
      $name .= '[]';
    }

    // ensure value is an array to allow matching multiple times
    $value = (array) $value;

    // XHTML or HTML end tag?
    $endTag = ' />';
    if(($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()){
      $endTag= '>';
    }

    // add radio buttons to the list.
    require_once 'Zend/Filter/Alnum.php';
    $filter = new Zend_Filter_Alnum();
    
    $label_style = '';
    if(array_key_exists('style', $label_attribs)){
      $label_style = $label_attribs['style'];
    }
    
    foreach($options as $opt_value => $opt){
      
      $depth = 0;
      if(is_array($opt) && array_key_exists('title', $opt)){
        $opt_label = $opt['title'];
        if(array_key_exists('depth', $opt)){
          $depth = $opt['depth'] - 1;
        }
      }else{
        $opt_label = $opt;  
      }
            
      if($depth > 0){
        $label_attribs['style'] = $label_style.' padding-left:'.(15*$depth).'px';
      }else{
        $label_attribs['style'] = $label_style;
      }
      
      // Should the label be escaped?
      if($escape){
        $opt_label = $this->view->escape($opt_label);
      }

      // is it disabled?
      $disabled = '';
      if(true === $disable){
        $disabled = ' disabled="disabled"';
      } elseif (is_array($disable) && in_array($opt_value, $disable)) {
        $disabled = ' disabled="disabled"';
      }

      // is it checked?
      $checked = '';
      if(in_array($opt_value, $value)){
        $checked = ' checked="checked"';
      }

      // generate ID
      $optId = $id . '-' . $filter->filter($opt_value);

      // Wrap the radios in labels
      $radio = '<label'
              . $this->_htmlAttribs($label_attribs) . '>'
              . (('prepend' == $labelPlacement) ? $opt_label : '')              
              . '<input type="' . $this->_inputType . '"'
              . ' name="' . $name . '"'
              . ' id="' . $optId . '"'
              . ' value="' . $this->view->escape($opt_value) . '"'
              . $checked
              . $disabled
              . $this->_htmlAttribs($attribs) 
              . $endTag
              . (('append' == $labelPlacement) ? $opt_label : '')
              . '</label>';

      // add to the array of radio buttons
      $list[] = $radio;
    }

    // done!
    $xhtml .= implode($listsep, $list);

    return $xhtml;
  }
}

?>