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
 * Form_Helper_FormDatetimes
 *
 * Helper to generate a "add Datetimes" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-12-16: Thomas Schedler
 *
 * @author Alexander Schranz <alexander.schranz@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormDatetimes
 */

class Form_Helper_FormDatetimes extends Zend_View_Helper_FormElement
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
    public function formDatetimes($name, $value = null, $attribs = null, $options = null, $regionId = null, $rawDataObject = null)
    {

        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag = '>';
        }

        $data = $value;

        $core = Zend_Registry::get('Core');

        $template = '
                <div id="{id}_" class="box-12 datetime">
                    <div class="datetime-fieldgroup">
                        <div id="{id}_field_from_date" class="{class_from_date}">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_from_date">' . $core->translate->_('From_date') . '</label>
                                <input  type="text" id="{id}_from_date" name="{name}_from_date" value="{from_date}" ' . $endTag . '
                            </div>
                        </div>
                        <div id="{id}_field_from_time" class="field-3" style="{style_from_time}">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_from_time">' . $core->translate->_('From_time') . '</label>
                                <input type="text" id="{id}_from_time" name="{name}_from_time" value="{from_time}" ' . $endTag . '
                            </div>
                        </div>
                        <div id="{id}_field_to_time" class="field-3" style="{style_to_time}">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_to_time">' . $core->translate->_('To_time') . '</label>
                                <input type="text" id="{id}_to_time" name="{name}_to_time" value="{to_time}" ' . $endTag . '
                            </div>
                        </div>
                        <div id="{id}_field_to_date" class="{class_to_date}">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_to_date">' . $core->translate->_('To_date') . '</label>
                                <input type="text" id="{id}_to_date" name="{name}_to_date" value="{to_date}" ' . $endTag . '
                            </div>
                        </div>
                    </div>
                    <div class="datetime-fieldgroup">
                        <div class="field-3">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_fulltime">
                                    <input onchange="myForm.datetimeChangeFulltime(this, \'{id}\')" class="checkbox" type="checkbox" id="{id}_fulltime" name="{name}_fulltime" value="1" {fulltime} ' . $endTag . '
                                    ' . $core->translate->_('Fulltime') . '
                                </label>
                            </div>
                        </div>
                        <div class="field-3">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_repeat">
                                    <input onchange="myForm.toggleCheckbox(\'{id}_repeat_container\', this);" class="checkbox" type="checkbox" id="{id}_repeat" name="{name}_repeat" value="1" {repeat} ' . $endTag . '
                                    ' . $core->translate->_('Repeated') . '
                                </label>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div id="{id}_repeat_container" class="datetime-repeat-container" class="field-12" style="{style_repeat_container}">
                            <div class="field-12">
                                <div class="field">
                                    <label class="fieldtitle" for="{id}_repeat_frequency">' . $core->translate->_('Is_repeated_at') . ': </label>
                                    <select id="{id}_repeat_frequency" name="{id}_repeat_frequency" onchange="myForm.datetimeChanged(this, \'{id}\');">
                                        {repeat_frequency}
                                    </select>
                                </div>
                            </div>
                            <div id="{id}_daily" class="field-12 {id}_frequency_specific" style="{style_daily}">
                                <div class="field">
                                    <label class="fieldtitle" for="{id}_repeat_interval_daily">
                                        '.$core->translate->_('repeat_all').':
                                    </label>
                                    <select id="{id}_repeat_interval_daily" name="{id}_repeat_interval_daily">
                                        {repeat_interval_daily}
                                    </select> ' . $core->translate->_('days') . '
                                </div>
                            </div>
                            <div id="{id}_weekly" class="field-12 {id}_frequency_specific" style="{style_weekly}">
                                <div class="field">
                                    <label class="fieldtitle" for="{id}_repeat_interval_weekly">
                                        '.$core->translate->_('repeat_all').':
                                    </label>
                                    <select id="{id}_repeat_interval_weekly" name="{id}_repeat_interval_weekly">
                                        {repeat_interval_weekly}
                                    </select> ' . $core->translate->_('weeks') . '<br/>
                                </div>
                                <div class="field">
                                    <label class="fieldtitle" for="{id}_repeat_weekly_type">
                                        '.$core->translate->_('repeat_at').':
                                    </label>
                                    {repeat_weekly_type}
                                </div>
                            </div>
                            <div id="{id}_monthly" class="field-12 {id}_frequency_specific" style="{style_monthly}">
                                <div class="field">
                                    <label class="fieldtitle" for="{id}_repeat_interval_monthly">
                                        '.$core->translate->_('repeat_all').':
                                    </label>
                                    <select id="{id}_repeat_interval_monthly" name="{id}_repeat_interval_monthly">
                                        {repeat_interval_monthly}
                                    </select> ' . $core->translate->_('months') . '
                                </div>
                                <div class="field">
                                    <label class="fieldtitle" for="{id}_repeat_monthly_type">
                                        '.$core->translate->_('repeat_at').':
                                    </label>
                                    {repeat_monthly_type}
                                </div>
                            </div>
                            <div id="{id}_yearly" class="field-12 {id}_frequency_specific" style="{style_yearly}">
                                <div class="field">
                                    <label class="fieldtitle" for="{id}_repeat_interval_yearly">
                                        '.$core->translate->_('repeat_all').':
                                    </label>
                                    <select id="{id}_repeat_interval_yearly" name="{id}_repeat_interval_yearly">
                                        {repeat_interval_yearly}
                                    </select> ' . $core->translate->_('years') . '
                                </div>
                            </div>
                            <div class="clear"></div>
                            <div class="field-12">
                                <div class="field">
                                    <label class="fieldtitle" for="{id}_repeat_ending">' . $core->translate->_('Ended') . ':

                                    <select onchange="myForm.toggle(\'{id}_end_date\', this, \'1\');" id="{id}_repeat_ending" name="{id}_repeat_ending">
                                        {repeat_ending}
                                    </select>
                                    </label>
                                     <div class="field-3">
                                        <input type="text" id="{id}_end_date" name="{name}_end_date" value="{end_date}" style="{style_end_date}" ' . $endTag . '
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br/>
                    <div class="clear"></div>
                </div>';

        $wildcards = array('{id}', '{name}', '{from_date}', '{from_time}', '{to_time}', '{to_date}', '{fulltime}', '{repeat}', '{repeat_frequency}', '{repeat_interval_daily}', '{repeat_interval_weekly}', '{repeat_weekly_type}', '{repeat_interval_monthly}', '{repeat_monthly_type}', '{repeat_interval_yearly}', '{repeat_ending}', '{end_date}',
        '{style_daily}', '{style_weekly}', '{style_monthly}', '{style_yearly}', '{style_repeat_container}',
        '{class_from_date}', '{class_to_date}', '{style_from_time}', '{style_to_time}',
        '{style_end_date}');

        $repeat_frequencyes = array(
            'daily' => $core->translate->_('daily'),
            'weekly' => $core->translate->_('weekly'),
            'monthly' => $core->translate->_('monthly'),
            'yearly' => $core->translate->_('yearly'),
        );

        $onetothirty = $this->getRange(1, 30);

        $dateProperties = array(
            'from_date' => 'field-3',
            'to_date' => 'field-3',
            'from_time' => 'display: block;',
            'to_time' => 'display: block;'
        );

        $datePropertiesFulltime = array(
            'from_date' => 'field-6',
            'to_date' => 'field-6',
            'from_time' => 'display: none;',
            'to_time' => 'display: none;'
        );


        $repeat_weekly_types = array(
            '1' => $core->translate->_('Mo'),
            '2' => $core->translate->_('Tu'),
            '4' => $core->translate->_('We'),
            '8' => $core->translate->_('Th'),
            '16' => $core->translate->_('Fr'),
            '32' => $core->translate->_('Sa'),
            '64' => $core->translate->_('Su')
        );

        $repeat_monthly_types = array(
            '1' => $core->translate->_('day_of_month'),
            '2' => $core->translate->_('weekday')
        );

        $repeat_type_weekly = 0;
        $repeat_type_monthly = 1;

        $end_date = array(
            '0' => $core->translate->_('never'),
            '1' => $core->translate->_('at')
        );
        $style = array(
            'daily' => 'display: none;',
            'weekly' => 'display: none;',
            'monthly' => 'display: none;',
            'yearly' => 'display: none;'
        );


        $htmlData = '';
        if (!empty($data)) {
            if ($data->fulltime == '1') {
                $dateProperties = $datePropertiesFulltime;
            }
            if ($data->repeat_frequency == 'weekly') {
                $repeat_type_weekly = $data->repeat_type;
            } elseif($data->repeat_frequency == 'monthly') {
                $repeat_type_monthly = $data->repeat_type;
            }
            $style[$data->repeat_frequency] = 'display: block;';
            $htmlData .= str_replace($wildcards, array(
                $this->view->escape($id),
                $this->view->escape($name),
                $data->from_date,
                $this->deleteSecondsFromTime($data->from_time),
                $this->deleteSecondsFromTime($data->to_time),
                $data->to_date,
                $this->buildChecked($data->fulltime),
                $this->buildChecked($data->repeat),
                $this->buildSelect($data->repeat_frequency, $repeat_frequencyes, 'weekly'),
                $this->buildSelect($data->repeat_interval, $onetothirty, 1),
                $this->buildSelect($data->repeat_interval, $onetothirty, 1),
                $this->buildCheckboxes($this->view->escape($id), $repeat_type_weekly, $repeat_weekly_types),
                $this->buildSelect($data->repeat_interval, $onetothirty, 1),
                $this->buildRadioboxes($this->view->escape($id), $repeat_type_monthly, $repeat_monthly_types),
                $this->buildSelect($data->repeat_interval, $onetothirty, 1),
                $this->buildSelect($data->end, $end_date, 'never'),
                $data->end_date,
                $style['daily'],
                $style['weekly'],
                $style['monthly'],
                $style['yearly'],
                ($data->repeat == 1) ? ('display: block;') : ('display: none;'),
                $dateProperties['from_date'],
                $dateProperties['to_date'],
                $dateProperties['from_time'],
                $dateProperties['to_time'],
                ($data->end == 1) ? ('display: block;') : ('display: none;'),
             ), $template);
        } else {
            $style['weekly'] = 'display: block;';
            $htmlData .= str_replace($wildcards, array(
                $this->view->escape($id),
                $this->view->escape($name),
                date('Y-m-d'),
                '08:00',
                '16:00',
                date('Y-m-d'),
                $this->buildChecked(''),
                $this->buildChecked(''),
                $this->buildSelect('', $repeat_frequencyes, 'weekly'),
                $this->buildSelect('', $onetothirty, 1),
                $this->buildSelect('', $onetothirty, 1),
                $this->buildCheckboxes($this->view->escape($id), array(), $repeat_weekly_types),
                $this->buildSelect('', $onetothirty, 1),
                $this->buildRadioboxes($this->view->escape($id), array(), $repeat_monthly_types),
                $this->buildSelect('', $onetothirty, 1),
                $this->buildSelect('', $end_date, 0),
                '',
                $style['daily'],
                $style['weekly'],
                $style['monthly'],
                $style['yearly'],
                'display: none;',
                $dateProperties['from_date'],
                $dateProperties['to_date'],
                $dateProperties['from_time'],
                $dateProperties['to_time'],
                'display: none;'
            ), $template);
        }

        $xhtml = '
            <div id="' . $this->view->escape($id) . '" class="datetimewrapper">
                ' . $htmlData . '
                <div class="clear"></div>
            </div>';

        return $xhtml;
    }

    protected function getRange ($min, $max)
    {
        $range = array();
        for ($x=1;$x<=30;$x++) {
            $range[$x] = $x;
        }
        return $range;
    }

    protected function buildCheckboxes ($id, $curValue, $options)
    {
        $strCheckboxes = '';
        foreach ($options as $value => $title) {
            $checked = '';
            if ($value & $curValue) { // Bitwise
                $checked = ' checked="checked"';
            }
            $strCheckboxes .= '
                <input class="checkbox" type="checkbox" id="'.$id.'_repeat_weekly_type_'.$value.'" name="'.$id.'_repeat_weekly_type[]" value="'.$value.'" '.$checked.' /><label for="'.$id.'_repeat_weekly_type_'.$value.'">' . $title . '</label>';
        }
        return $strCheckboxes;
    }

    protected function deleteSecondsFromTime ($time)
    {
        return substr($time, 0, -3);
    }

    protected function buildRadioboxes ($id, $curValue, $options)
    {
        $strCheckboxes = '';
        $counter = 0;
        $wasChecked = false;
        foreach ($options as $value => $title) {
            $counter++;
            $checked = '';
            if ($value & $curValue && !$wasChecked) { // Bitwise
                $checked = ' checked="checked"';
                $wasChecked = true;
            }
            $strCheckboxes .= '
                <input class="checkbox" type="radio" id="'.$id.'_repeat_monthly_type_'.$value.'" name="'.$id.'_repeat_monthly_type[]" value="'.$value.'" '.$checked.' /><label for="'.$id.'_repeat_monthly_type_'.$value.'">' . $title . '</label>';
        }
        return $strCheckboxes;
    }

    protected function buildSelect ($value, $options, $default = '')
    {
        $selectOptions = print_r($options, true);
        if (empty($value)) {
            $value = $default;
        }
        foreach ($options as $optionValue => $title) {
            $selected = '';
            if ($optionValue == $value) {
                $selected = ' selected';
            }
            $selectOptions .= '<option value="'.$optionValue.'"'.$selected.'>'.$title.'</option>';
        }
        return $selectOptions;
    }


    protected function buildChecked($value)
    {
        if ($value == 1) {
            return 'checked="checked"';
        }
        return '';
    }
}