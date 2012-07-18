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
 * @package    library.massiveart.generic.fields.Articles.forms.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormArticles
 *
 * Helper to generate a "add Articles" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-12-16: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormArticles
 */

class Form_Helper_FormArticles extends Zend_View_Helper_FormElement
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
    public function formArticles($name, $value = null, $attribs = null, $options = null, $regionId = null, $rawDataObject = null)
    {
        
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // XHTML or HTML end tag?
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
        
        if (array_key_exists('fieldOptions', $attribs) && !empty($attribs['fieldOptions'])) {
            $fieldOptions = json_decode($attribs['fieldOptions']);
        } else {
            $fieldOptions = new stdClass();
            $fieldOptions->size = new stdClass();
            $fieldOptions->size->active = true;
            $fieldOptions->size->sql = '';

            $fieldOptions->price = new stdClass();
            $fieldOptions->price->size = true;

            $fieldOptions->discount = new stdClass();
            $fieldOptions->discount->active = true;
        }


        $options = $this->loadSizes($fieldOptions->size, $core, $attribs['LanguageId']);

        $template = '
                <div id="{id}_{n}" class="box-12 article" {box_style}>
                    <div class="article-fieldgroup">
                        <div class="field-4">
                            <div class="field">
                                <select class="select" id="{id}_size_{n}" name="{name}_size_{n}">{options}</select>
                            </div>
                        </div>
                        <div class="field-4">
                            <div class="field">
                                <input type="text" id="{id}_price_{n}" name="{name}_price_{n}" value="{value_price}" ' . $endTag . '
                            </div>
                        </div>
                        <div class="field-4">
                            <div class="field">
                                <input type="text" id="{id}_discount_{n}" name="{name}_discount_{n}" value="{value_discount}" ' . $endTag . '
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="article-edit-box">
                        <div id="removeArticle_{id}_{n}" class="article-remove" onclick="myForm.removeArticle(\'{id}\', \'{n}\'); return false;"></div>
                        <div id="addArticle_{id}_{n}" class="article-add" onclick="myForm.addArticle(\'{id}\'); return false;" style="{add_article_style}"></div>
                        <div class="clear"></div>
                    </div>
                </div>';

        $wildcards = array('{id}', '{name}', '{n}', '{options}', '{box_style}', '{value_price}', '{value_discount}', '{add_article_style}' );

        $htmlData = '';
        $strIstances = '';
        if (count($values) > 0) {
            $i = 1;
            foreach($values as $data){
                $style = 'display: none;';
                if (count($values) == $i) {
                    $style = '';    
                }
                $htmlData .= str_replace($wildcards, array(
                                                     $this->view->escape($id),
                                                     $this->view->escape($name),
                                                     $i,
                                                     implode("\n    ", $this->buildSelect($options, $data)),
                                                     '',
                                                     $data->price,
                                                     $data->discount,
                                                     $style
                                                ), $template);
                          $strIstances .= '['.$i.']';
                $i++;
            }
        } else {
            $strIstances = '[1]';
            $htmlData = str_replace($wildcards, array(
                                                 $this->view->escape($id),
                                                 $this->view->escape($name),
                                                 '1',
                                                  implode("\n    ", $this->buildSelect($options, null)),
                                                 '',
                                            ), $template);
        }

        $xhtml = '
            <div id="' . $this->view->escape($id) . '" class="articlewrapper">
                <div class="box-12 article">
                    <div class="article-labels">
                        <div class="field-4">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_size_{n}">' . $core->translate->_('Size') . '</label>
                            </div>
                        </div>
                        <div class="field-4">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_price_{n}">' . $core->translate->_('Price') . '</label>
                            </div>
                        </div>
                        <div class="field-4">
                            <div class="field">
                                <label class="fieldtitle" for="{id}_discount_{n}">' . $core->translate->_('Discount') . '</label>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                ' . $htmlData . '
                ' . str_replace($wildcards, array(
                                                 $this->view->escape($id),
                                                 $this->view->escape($name),
                                                 'REPLACE_x',
                                                 implode("\n    ", $this->buildSelect($options, null)),
                                                 'style="display:none;"',
                                            ), $template) . '
                <div class="clear"></div>
                <input type="hidden" id="' . $this->view->escape($name) . '_Instances" value="'.$strIstances.'" name="' . $this->view->escape($name) . '_Instances"' . $endTag . '
                <input type="hidden" id="' . $this->view->escape($name) . '_Order" value="" name="' . $this->view->escape($name) . '_Order"' . $endTag . '
            </div>';

        return $xhtml;
    }
    
    protected function loadSizes($objSizeOptions, $core, $intLanguageId){

        $arrOptions = array();
        if(!empty($objSizeOptions->sql)){
            $objReplacer = new Replacer();
            
            $sqlSelect = $objReplacer->sqlReplacer($objSizeOptions->sql, array('LANGUAGE_ID' => $intLanguageId));
            $sqlStmt = $core->dbh->query($sqlSelect)->fetchAll();


            $arrOptions[''] = $core->translate->_('Please_choose', false);
            foreach($sqlStmt as $arrSql){
                if(array_key_exists('depth', $arrSql)){
                    $arrOptions[$arrSql['id']] = array('title' => $arrSql['title'],
                                                       'depth' => $arrSql['depth']);
                }else{
                    $arrOptions[$arrSql['id']] = $arrSql['title'];
                }
            }
        }
        
        return $arrOptions;
    }
    
    /**
     * buildSelect
     * @param array $options
     * @param object $objData
     * @return array
     */
    protected function buildSelect($options, $objData) {
        $arrSelected =  array();
        if ($objData != null) {
          $arrSelected[] = $objData->size;
        }
        
        foreach($options as $opt_value => $opt){
            $depth = 0;
            if(is_array($opt) && array_key_exists('title', $opt)){

                $opt_label = $opt['title'];
                if(array_key_exists('depth', $opt)){
                    $depth = $opt['depth'];
                }

                $list[] = $this->_build($opt_value, $opt_label, $arrSelected, false, $depth - 2);
            }else{
                $opt_label = $opt;
                $list[] = $this->_build($opt_value, $opt_label, $arrSelected, false, 0);
            }
        }
        return $list; 
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