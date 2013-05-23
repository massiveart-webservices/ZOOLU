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
 * @package    library.massiveart.generic.fields.Url.forms.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormUrl
 *
 * Helper to generate a "tag" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-27: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormUrl
 */
class Form_Helper_FormUrl extends Zend_View_Helper_FormElement
{

    /**
     * formUrl
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param string $name
     * @param string $value
     * @param array $attribs
     * @param mixed $options
     * @version 1.0
     */
    public function formUrl($name, $value = null, $attribs = null, $blnIsStartElement = null, $options = null, $intParentId = null, $arrMessages = array(), $intLanguageDefinitionType = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        $core = Zend_Registry::get('Core');
        extract($info); // name, value, attribs, options, listsep, disable

        // XHTML or HTML end tag
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag = '>';
        }

        $strValue = ltrim($this->view->escape($value), '/');
        $arrUrl = explode('/', $strValue);

        $strLanguage = '';
        if ($intLanguageDefinitionType == $core->config->language_definition->folder) {
            $strLanguage = array_shift($arrUrl) . '/';
        }

        if (is_null($intParentId) && $blnIsStartElement == true) {
            $strOutput = '<div class="urlwrapper">
                      <span class="gray666 bold">Adresse: /' . $strLanguage . '</span>
                    </div>';
        } else {
            $strOutput = '';
            if ($value != '') {

                if (count($arrUrl) >= 1) {

                    $blnSuggestion = isset($arrMessages['suggestion']);
                    if ($blnSuggestion) {
                        $arrUrl = explode('/', $arrMessages['suggestion']);
                    }

                    // if is start page, delete last empty array element
                    if ($blnIsStartElement == true) {
                        array_pop($arrUrl);
                    }

                    $strUrlShown = '/';
                    if (!empty($strLanguage)) {
                        $strUrlShown .= $strLanguage;
                    }

                    switch ($core->config->url_layout) {
                        case UniformResourceLocator::LAYOUT_SHORT:
                            $strUrlEditable = implode('/', $arrUrl);
                            break;
                        case UniformResourceLocator::LAYOUT_TREE:
                        default:
                            $strUrlEditable = array_pop($arrUrl);
                            if (count($arrUrl) > 0) {
                                $strUrlShown .= implode('/', $arrUrl) . '/';
                            }
                            break;
                    }

                    $strOutput = '
                  <div class="urlwrapper">
                    <span class="gray666 bold">' . $core->translate->_('Address') . ': ' . $strUrlShown . '</span><span id="' . $this->view->escape($id) . '_UrlValue" class="gray666">' . $strUrlEditable . '</span>' . (($blnIsStartElement == true) ? '<span class="gray666 bold">/</span>' : '') . '<span id="' . $this->view->escape($id) . '_Controls">&nbsp;<a href="#" onclick="myForm.editUrl(\'' . $this->view->escape($id) . '\'); return false;">' . $core->translate->_('Edit') . '</a></span>
                    <input type="hidden" value="' . $value . '" id="' . $this->view->escape($id) . '" name="' . $this->view->escape($name) . '" ' . $endTag . '
                    <input type="hidden" value="' . $strUrlEditable . '" id="' . $this->view->escape($id) . '_EditableUrl" name="' . $this->view->escape($name) . '_EditableUrl" ' . $endTag . '
                    <input type="hidden" value="' . $strUrlShown . '" id="' . $this->view->escape($id) . '_ShownUrl" name="' . $this->view->escape($name) . '_ShownUrl" ' . $endTag . '
                    <input type="hidden" value="" id="' . $this->view->escape($id) . '_preventSaving" name="' . $this->view->escape($name) . '_PreventSaving" ' . $endTag . '
                  </div>';

                    if ($blnSuggestion) {
                        $strOutput .= '<script type="text/javascript">myForm.editUrl(\'url\');</script>';
                    }
                }

                $strOutput .= '
                  <div id="' . $this->view->escape($id) . '_UrlHistory" class="urlTop" onclick="myForm.toggleUrlHistory(\'' . $this->view->escape($id) . '\')"><div class="urlTopTitle">' . $core->translate->_('Url_history') . '</div></div>
                  <div id="' . $this->view->escape($id) . '_ToggleUrlHistory" class="urlHistoryContainer" style="display:none"></div>';
            } else {

                $strTmpLanguageCode = '/' . ((array_key_exists('LanguageCode', $attribs)) ? $attribs['LanguageCode'] : $core->sysConfig->languages->default->code) . '/';

                $strOutput .= '
                  <div class="urlwrapper" style="display:none">
                    <input type="hidden" value="' . $strTmpLanguageCode . '" id="' . $this->view->escape($id) . '" name="' . $this->view->escape($name) . '" ' . $endTag . '
                    <input type="hidden" value="" id="' . $this->view->escape($id) . '_EditableUrl" name="' . $this->view->escape($name) . '_EditableUrl" ' . $endTag . '
                    <input type="hidden" value="' . $strTmpLanguageCode . '" id="' . $this->view->escape($id) . '_ShownUrl" name="' . $this->view->escape($name) . '_ShownUrl" ' . $endTag . '
                    <input type="hidden" value="" id="' . $this->view->escape($id) . '_preventSaving" name="' . $this->view->escape($name) . '_PreventSaving" ' . $endTag . '
                  </div>';
            }
        }

        $strOutput .= '
                  <script type="text/javascript">
                      myForm.preventSavingFields.push(\'' . $this->view->escape($id) . '\');
                  </script>';

        return $strOutput;
    }
}
