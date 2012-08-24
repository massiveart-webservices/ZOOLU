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
 * @package    application.zoolu.modules.core.media.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ViewHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-19: Cornelius Hansjakob
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class ViewHelper
{

    /**
     * @var Core
     */
    private $core;

    const LANGUAGE_EN = 2;

    /**
     * Constructor
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    public function getFilterOutput($objRootLevelTypeFilters, $strCount, $blnShow = true, $value = null)
    {
        $arrValues = array();
        if (isset($value)) {
            $arrValues = explode(',', $value->value);
        }

        $strOutput = '';
        $intFilterId = 0;

        $strOutput .= '
        	<div id="line_' . $strCount . '" style="' . (!$blnShow ? 'display: none;' : '') . '" class="field filtertype">
        		<div class="errors filtererror" id="error_' . $strCount . '"></div>
        		<select name="filter_' . $strCount . '" id="filter_' . $strCount . '" class="filterselect" onchange="myFilter.toggleSelects(' . $strCount . ', this.options[this.selectedIndex].readAttribute(\'filterid\'))">
        			<option>&nbsp;</option>
        			';
        //Filtername
        foreach ($objRootLevelTypeFilters as $objFilter) {
            $strSelectFiltername = '';
            if ((isset($value) && $objFilter->name == $value->field)) {
                $strSelectFiltername = ' selected="selected"';
                $intFilterId = $objFilter->id;
            }
            $strOutput .= '<option filterid="' . $objFilter->id . '" value="' . $objFilter->name . '"' . $strSelectFiltername . '>' . $objFilter->title . '</option>';
        }
        $strOutput .= '
        		</select>
        		<select name="operator_' . $strCount . '" id="operator_' . $strCount . '" class="filterselect operator">
        			<option>&nbsp;</option>';
        //Filteroperators
        foreach ($objRootLevelTypeFilters as $objFilter) {
            $arrOptions = json_decode($objFilter->operators);
            foreach ($arrOptions as $strOption) {
                $strSelectOperator = ((isset($value) && $strOption == $value->operator && $intFilterId == $objFilter->id) ? ' selected="selected"' : '');
                $strCss = ($intFilterId != $objFilter->id) ? 'display:none;' : '';
                $strOutput .= '<option style="' . $strCss . '" filterid="' . $objFilter->id . '" value="' . $strOption . '"' . $strSelectOperator . '>' . $this->core->translate->_($strOption) . '</option>';
            }
        }
        $strOutput .= '
        		</select>
        		<select name="value_' . $strCount . '[]" id="value_' . $strCount . '" multiple="multiple" class="filterselect filtervalue">';
        //Filtervalues
        foreach ($objRootLevelTypeFilters as $objFilter) {
            if (isset($objFilter->sqlSelect) && $objFilter->sqlSelect != '') {
                $strSelect = str_replace('%LANGUAGE_ID%', $this->core->intZooluLanguageId, $objFilter->sqlSelect);
                $this->core->logger->debug($strSelect);
                $objOptions = $this->core->dbh->query($strSelect)->fetchAll();
                foreach ($objOptions as $objOption) {
                    $strSelectValue = ((isset($arrValues) && in_array($objOption['altTitle'], $arrValues)) ? ' selected="selected"' : '');
                    $strCss = ($intFilterId != $objFilter->id) ? 'display:none;' : '';
                    $strOutput .= '<option style="' . $strCss . '" filterid="' . $objFilter->id . '" value="' . $objOption['altTitle'] . '"' . $strSelectValue . '>' . $objOption['title'] . '</option>';
                }
            }
        }
        $strOutput .= '
        	</select>
        	<div class="multiplyOptions">
        		<div class="plus" onclick="myFilter.addLine(); return false;"></div>
        		<div id="minus_' . $strCount . '" onclick="myFilter.removeLine(' . $strCount . ')" class="minus" style="display:none"></div>
        	</div>
        	<div class="clear"></div>
        </div>';

        return $strOutput;
    }

    /**
     * getFilterList
     * @param Zend_Db_Table_Rowset $objRootLevelTypeFilters
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getFilterList($objRootLevelTypeFilters, $objRootLevelFilter = null, $objRootLevelFilterValues = null)
    {
        $strOutput = '';
        $arrCount = array();
        $initCount = 1;

        if ($objRootLevelFilter == null) {
            $strOutput .= $this->getFilterOutput($objRootLevelTypeFilters, $initCount);
            $arrCount[] = $initCount;
        } else {
            $strOutput .= '<input type="hidden" name="rootLevelFilterEditId" id="rootLevelFilterEditId" value="' . $objRootLevelFilter->current()->id . '">';
            foreach ($objRootLevelFilterValues as $intCount => $objRootLevelFilterValue) {
                $strOutput .= $this->getFilterOutput($objRootLevelTypeFilters, $intCount, true, $objRootLevelFilterValue);
                $arrCount[] = $intCount;
            }
        }

        $strOutput .= $this->getFilterOutput($objRootLevelTypeFilters, 'REPLACE_n', false);
        $strOutput .= '<input type="hidden" name="lineInstances" id="lineInstances" value="[' . implode('][', $arrCount) . ']" />';

        return $strOutput;
    }

    /**
     * getRootLevelFilterList
     * @param Zend_Db_Table_Rowset $objRootLevelFilters
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getRootLevelFilterList($objRootLevelFilters)
    {
        $strOutput = '';
        $strRootLevelType = '';

        $strOutput .= '
      		  <div class="menulink">
                <div class="menutitle portalnoicon">
                	<a onclick="myNavigation.selectHardbounces(' . $objRootLevelFilters->current()->idRootLevels . ')" href="#">' . htmlentities($this->core->translate->_('Hardbounces'), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</a>
                </div>
                <div class="clear"></div>
              </div>';

        foreach ($objRootLevelFilters as $objRootLevelFilter) {
            if ($objRootLevelFilter->idRootLevelTypes = $this->core->sysConfig->root_level_types->subscribers) {
                $strRootLevelType = 'subscriber';
            }
            $strOutput .= '
      		  <div id="subnaviitem' . $objRootLevelFilter->id . '" class="menulink">
                <div class="menutitle portalnoicon">
                	<a id="subnaviitem' . $objRootLevelFilter->id . '_link" onclick="myNavigation.selectSubscribers(' . $objRootLevelFilter->idRootLevels . ', ' . $objRootLevelFilter->idRootLevelGroups . ', \'\', \'list\', \'' . $strRootLevelType . '\', ' . $objRootLevelFilter->id . ')" href="#">' . htmlentities($objRootLevelFilter->filtertitle, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</a>
                </div>
                <div class="clear"></div>
              </div>';
//       $strOutput.= '
//             				<div id="subnaviitem'.$objRootLevelFilter->id.'" class="menulink">
//                       <div class="portalcontenticon"></div>
//                       <div class="menutitle">
//                       	<a id="subnaviitem'.$objRootLevelFilter->id.'_link" onclick="myNavigation.getFilterEditOverlay('.$objRootLevelFilter->idRootLevels.', '.$objRootLevelFilter->idRootLevelTypes.', '.$objRootLevelFilter->id.')" href="#">'.htmlentities($objRootLevelFilter->filtertitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>  
//                       </div>
//                       <div class="clear"></div>
//                     </div>';
        }

        return $strOutput;
    }

    public function getOverviewFilterTitle()
    {
        return $this->core->translate->_('Edit_filter');
    }

    /**
     * getOverviewFilter
     * @param Zend_Db_Table_Rowset $objRootLevelFilters
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getOverviewFilter($objRootLevelFilters)
    {
        $strReturn = '';

        foreach ($objRootLevelFilters as $objRootLevelFilter) {
            $strReturn .= '<div class="olnavrootitem">
      	<a href="#" onclick="myNavigation.getFilterEditOverlay(' . $objRootLevelFilter->idRootLevels . ', ' . $objRootLevelFilter->idRootLevelTypes . ', ' . $objRootLevelFilter->id . '); return false;">
      		<div class="icon"></div>
      		' . $objRootLevelFilter->filtertitle . '
      	</a>
      </div>';
        }
        return $strReturn;
    }
}

?>