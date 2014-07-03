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
 * @package    application.zoolu.modules.users.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ListHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-05: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class ListHelper
{

    /**
     * @var Core
     */
    private $core;

    /**
     * Constructor
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * getList
     * @param Zend_Paginator $objPaginator
     * @param string $strOrderColumn
     * @param string $strSortOrder
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getList($objPaginator, $strOrderColumn, $strSortOrder, $strSearchValue, $strJsNavigationFunction = 'getEditFormList', $type = null)
    {
        $this->core->logger->debug('users->views->helpers->ListHelper->getList()');

        $intCounter = 0;

        $strThead = '<thead>';
        $strTbody = '<tbody id="listEntries">';

        foreach ($objPaginator as $objItem) {
            $intCounter++;

            if ($intCounter == 1) {
                $strThead .= '
                    <tr>
                        <th class="topcornerleft"><div>&nbsp;</div></th>
                        <th class="topcheckbox"><input type="checkbox" class="listSelectAll" name="listSelectAll" id="listSelectAll"/></th>';
            }

            $strTbody .= '
                <tr id="Row' . $objItem->id . '" class="listrow">
                    <td class="rowcheckbox" colspan="2"><input type="checkbox" class="listSelectRow" value="' . $objItem->id . '" name="listSelect" id="listSelect' . $objItem->id . '"/></td>';

            $arrItem = $objItem->toArray();
            $intColumCounter = 0;
            unset($arrItem['id']);
            unset($arrItem['changed']);
            $intColums = count($arrItem);

            foreach ($arrItem as $column => $value) {
                if ($this->core->translate->getAdapter()->isTranslated($column)) {
                    $intColumCounter++;

                    if (empty($type)) {
                        $type = $objItem->type;
                    }

                    if ($intCounter == 1) {
                        $strSortOrderClass = '';
                        $strOrderColumnClass = '';
                        if ($column == $strOrderColumn) {
                            $strSortOrderClass = ' class="' . $strSortOrder . '"';
                            $strOrderColumnClass = ' sort';
                        }
                        $strThead .= '<th class="top' . $column . $strOrderColumnClass . '"><div' . $strSortOrderClass . ' onclick="myList.sort(\'' . $column . '\'' . (($column == $strOrderColumn && $strSortOrder == 'asc') ? ', \'desc\'' : ', \'asc\'') . '); return false;">' . $this->core->translate->_($column) . '</div></th>';
                    }

                    $strColspan = ($intColumCounter == $intColums) ? ' colspan="2"' : '';

                    if ($intColumCounter <= 2) {
                        $strTbody .= '
                            <td class="row' . $column . '"' . $strColspan . '><a href="#" onclick="myNavigation.' . $strJsNavigationFunction . '(' . $objItem->id . ',\'' . $type . '\',\'' . $objItem->genericFormId . '\',' . $objItem->version . '); return false;">' . (($value != '') ? htmlentities($value, ENT_COMPAT, $this->core->sysConfig->encoding->default) : '- - -') . '</a></td>';
                    } else {
                        $strTbody .= '
                            <td class="row' . $column . '"' . $strColspan . '>' . (($value != '') ? htmlentities($value, ENT_COMPAT, $this->core->sysConfig->encoding->default) : '- - -') . '</td>';
                    }


                }
            }

            if ($intCounter == 1) {
                $strThead .= '
              <th class="topcornerright"><div>&nbsp;</div></th>
            </tr>';
            }

            $strTbody .= '
                    <td></td>
            </tr>';
        }
        $strThead .= '</thead>';
        $strTbody .= '</tbody>';

        $strOutput = '';
        /**
         * if list is filtered by search
         */
        $searchReset = '';
        if ($strSearchValue != '') {
            if (count($objPaginator) > 0) {
                $searchReset = '
                    <div class="formsubtitle searchtitle">' . sprintf($this->core->translate->_('Search_for_'), $strSearchValue) . '</div>';
            } else {
                $searchReset = '
                    <div class="formsubtitle searchtitle">' . sprintf($this->core->translate->_('No_search_results_for_'), $strSearchValue) . '</div>';
            }

            $searchReset .= '
                <div class="bttnSearchReset" onclick="myList.resetSearch();">
                  <div class="button17leftOff"></div>
                  <div class="button17centerOff">
                    <div>' . $this->core->translate->_('Reset') . '</div>
                    <div class="clear"></div>
                  </div>
                  <div class="button17rightOff"></div>
                  <div class="clear"></div>
                </div>
                <div class="clear"></div>';
        } else {
            $strOutput = '
                <div class="spacer2"></div>';
        }

        if ($intCounter == 0 && $strSearchValue == '') {
            $strOutput = $searchReset . $this->core->translate->_('No_entries_available');
        } else {
            $strOutput .= $searchReset . '
                <table class="tablelist">
                  ' . $strThead . '
                  ' . $strTbody . '
                </table>';
        }

        return $strOutput;
    }
}

?>
