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
 * @package    library.massiveart.generic.fields.Template.forms.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormTemplate
 *
 * Helper to generate a "template" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-11: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormTemplate
 */

class Form_Helper_FormTemplate extends Zend_View_Helper_FormElement
{

    /**
     * formTemplate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @param string $name
     * @param string $value
     * @param array $attribs
     * @param mixed $options
     * @param Zend_Db_Table_Rowset $objTemplatesData
     * @version 1.0
     */
    public function formTemplate($name, $value = null, $attribs = null, $options = null, $objTemplatesData)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // XHTML or HTML end tag
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag = '>';
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
            . ' onchange="myForm.changeTemplate(this.value);"'
            . ' class="select"'
            . $disabled
            //. $this->_htmlAttribs($attribs)
            . ">\n    ";

        // build the list of options
        $list = array();

        if (count($objTemplatesData)) {
            foreach ($objTemplatesData as $objTemplate) {
                $list[] = $this->_build($objTemplate->id, $objTemplate->title, $value);
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
     * @param string  $selected The option value to mark as 'selected'
     * @return string Option Tag XHTML
     */
    protected function _build($value, $label, $selected)
    {
        $opt = '<option'
            . ' value="' . $this->view->escape($value) . '"';

        // selected?
        if ((string) $value == $selected) {
            $opt .= ' selected="selected"';
        }

        $opt .= '>' . $this->view->escape($label) . '</option>';

        return $opt;
    }

}

?>