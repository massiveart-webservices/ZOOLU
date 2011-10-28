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
 * @package    library.massiveart.generic.fields.SelectTree.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormSelectTree
 * 
 * Helper to generate a "select tree" element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-19: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormSelectTree
 */

class Form_Helper_FormSelectTree extends Zend_View_Helper_FormElement {
	
	  /**
	   * formSelectTree    
	   * @param string $name
	   * @param string $value
	   * @param array $attribs
	   * @param mixed $options
	   * @param string $listsep
	   * @return string
	   * @author Cornelius Hansjakob <cha@massiveart.com>
	   * @version 1.0
	   */
    public function formSelectTree($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n"){
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // force $value to array so we can compare multiple values to multiple 
        // options; also ensure it's a string for comparison purposes.
        $value = array_map('strval', (array) $value);

        // check if element may have multiple values
        $multiple = '';

        if (substr($name, -2) == '[]') {
            // multiple implied by the name
            $multiple = ' multiple="multiple"';
        }

        if (isset($attribs['multiple'])) {
            // Attribute set
            if ($attribs['multiple']) {
                // True attribute; set multiple attribute
                $multiple = ' multiple="multiple"';

                // Make sure name indicates multiple values are allowed
                if (!empty($multiple) && (substr($name, -2) != '[]')) {
                    $name .= '[]';
                }
            } else {
                // False attribute; ensure attribute not set
                $multiple = '';
            }
            unset($attribs['multiple']);
        } 

        // now start building the XHTML.
        $disabled = '';
        if (true === $disable) {
            $disabled = ' disabled="disabled"';
        }

        // Build the surrounding select element first.
        $xhtml = '<select'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $multiple
                . $disabled
                . $this->_htmlAttribs($attribs)
                . ">\n    ";

        // build the list of options
        $list = array();

		    foreach((array) $options as $opt_value => $opt){		    	
		      $depth = 0;
		      if(is_array($opt) && array_key_exists('title', $opt)){
		        
		      	$opt_label = $opt['title'];
		        if(array_key_exists('depth', $opt)){
		          $depth = $opt['depth'];
		        }
		        $list[] = $this->_build($opt_value, $opt_label, $value, $disable, $depth);           
		      }else{		        
		      	$opt_label = $opt;
		      	$list[] = $this->_build($opt_value, $opt_label, $value, $disable, 0);  
		      }
		    }
		    
        // add the options to the xhtml and close the select
        $xhtml .= implode("\n    ", $list) . "\n</select>";

        return $xhtml;
    }

    /**
     * Builds the actual <option> tag
     *
     * @param string $value Options Value
     * @param string $label Options Label
     * @param array  $selected The option value(s) to mark as 'selected'
     * @param array|bool $disable Whether the select is disabled, or individual options are
     * @param integer $depth = 1
     * @return string Option Tag XHTML
     */
    protected function _build($value, $label, $selected, $disable, $depth = 0) {
        if (is_bool($disable)) {
            $disable = array();
        }

        $opt = '<option'
             . ' value="' . $this->view->escape($value) . '"';
        //     . ' label="' . $this->view->escape($label) . '"';

        // selected?
        if (in_array((string) $value, $selected)) {
            $opt .= ' selected="selected"';
        }

        // disabled?
        if (in_array($value, $disable)) {
            $opt .= ' disabled="disabled"';
        }
        
        $strBlanks = '';
        for($i = 1; $i <= $depth; $i++){
          $strBlanks .= '&nbsp;&nbsp;&nbsp;&nbsp;';	
        }

        $opt .= '>'.$strBlanks.$this->view->escape($label).'</option>';

        return $opt;
    }

}
