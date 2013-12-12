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
 * @package    library.massiveart.generic.fields.Datetimes.forms.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormMultipleChoice
 *
 * Helper to generate a "add MultipleChoice" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-12-16: Thomas Schedler
 *
 * @author Mathias Ober <mob@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormMultipleChoice
 */

class Form_Helper_FormMultipleChoice extends Zend_View_Helper_FormElement
{
    /**
     * @param string $name
     * @param string $value
     * @param string $attribs
     * @param string $options
     * @param string $regionId
     * @return string
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function formMultipleChoice($name, $value = null, $attribs = null, $options = null, $regionId = null, $rawDataObject = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        // XHTML or HTML end tag
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag = '>';
        }

        $values = array();
        if (is_array($value)) {
            $values = $value;
        } else {
            $values = json_decode(str_replace('][', ', ', $value));
        }

        $core = Zend_Registry::get('Core');

        $template = '
                <div id="{id}_{n}" class="box-12 option" {box_style}>
                    <div class="option-fieldgroup">
                        <div class="field-9">
                            <div class="field">
                                <input type="text" id="{id}_option_{n}" name="{name}_option_{n}" value="{value_option}" ' . $endTag . '
                            </div>
                        </div>
                        <div class="field-3">
                            <div class="field">
                                <label style="white-space: nowrap;">
                                    <input type="radio" id="{id}_validity_{n}_true" name="{name}_validity_{n}" value="true" {value_validity_true} style="width:auto; margin-left: 10px;" ' . $endTag . ' ' . $core->translate->_('true') . '
                                </label>
                                <label style="white-space: nowrap;">
                                    <input type="radio" id="{id}_validity_{n}_false" name="{name}_validity_{n}" value="false" {value_validity_false} style="width:auto; margin-left: 10px;" ' . $endTag . ' ' . $core->translate->_('false') . '
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="option-edit-box">
                        <div id="removeOption_{id}_{n}" class="option-remove" onclick="myForm.removeOption(\'{id}\', \'{n}\'); return false;"></div>
                        <div id="addOption_{id}_{n}" class="option-add" onclick="myForm.addOption(\'{id}\'); return false;" style="{add_option_style}"></div>
                        <div class="clear"></div>
                    </div>
                </div>';

        $wildcards = array('{id}', '{name}', '{n}', '{value_option}', '{box_style}', '{value_validity_true}', '{value_validity_false}', '{add_option_style}');


        $htmlData = '';
        $strIstances = '';
        if (count($values) > 0) {
            $i = 1;
            foreach ($values as $data) {
                $style = 'display: none;';
                if (count($values) == $i) {
                    $style = '';
                }

                $arrValidity = array();
                ($data->validity == 'true') ? $arrValidity[0] = 'checked' : $arrValidity[0] = '';
                ($data->validity == 'false') ? $arrValidity[1] = 'checked' : $arrValidity[1] = '';


                $htmlData .= str_replace($wildcards, array(
                        $this->view->escape($id),
                        $this->view->escape($name),
                        $i,
                        $data->option,
                        '',
                        $arrValidity[0],
                        $arrValidity[1],
                        $style
                    ), $template);
                $strIstances .= '[' . $i . ']';
                $i++;
            }
        } else {
            $strIstances = '[1]';
            $htmlData = str_replace($wildcards, array(
                    $this->view->escape($id),
                    $this->view->escape($name),
                    '1',
                    '',
                    '',
                ), $template);
        }

        $xhtml = '
            <div id="' . $this->view->escape($id) . '" class="multiplechoicewrapper">
                <div class="box-12 option">
                    <div class="option-labels">';

            $xhtml .= '
                        <div class="field-9">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_option_number_{n}">' . $core->translate->_('option') . '</label>
                            </div>
                        </div>
                        ';



        $xhtml .= '
                    </div>
                    <div class="clear"></div>
                </div>
                ' . $htmlData . '
                ' . str_replace($wildcards, array(
                    $this->view->escape($id),
                    $this->view->escape($name),
                    'REPLACE_x',
                    '',
                    'style="display:none;"',
                ), $template) . '
                <div class="clear"></div>
                <input type="hidden" id="' . $this->view->escape($name) . '_Instances" value="' . $strIstances . '" name="' . $this->view->escape($name) . '_Instances"' . $endTag . '
                <input type="hidden" id="' . $this->view->escape($name) . '_Order" value="" name="' . $this->view->escape($name) . '_Order"' . $endTag . '
            </div>';

        return $xhtml;
    }
}
