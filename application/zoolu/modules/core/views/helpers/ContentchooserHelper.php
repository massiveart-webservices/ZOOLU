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
 * @package    application.zoolu.modules.core.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ContentchooserHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-11-05: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

require_once (dirname(__FILE__) . '/../../../media/views/helpers/ViewHelper.php');

class ContentchooserHelper
{
    /**
     * @var Core
     */
    private $core;

    /**
     * @var Translate
     */
    private $objTranslate;


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
     * getModuleListView
     * @param object $objElements
     * @param string $strOverlayTitle
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getModuleListView($objElements, $strOverlayTitle)
    {
        $this->core->logger->debug('core->views->helpers->ContentchooserHelper->getModuleListView()');

        $strReturn = '';

        if (count($objElements) > 0) {
            /**
             * create header of list output
             */
            $strReturn .= '
                <div id="olModules">
                    <div id="olModules_title" style="display:none;">' . $strOverlayTitle . '</div>
                    <div class="olcontentchoosertop">
                        ' . $this->objTranslate->_('Name') . '
                    </div>
                    <div class="olcontentitemcontainer">';

            foreach ($objElements as $objRow) {
                // only PORTALS, GLOBAL, MEDIA visible
                if ($objRow->resourceKey == 'portals' || $objRow->resourceKey == 'global' || $objRow->resourceKey == 'media') {
                    $strReturn .= '
                        <div class="olcontactitem" id="olModuleItem' . $objRow->id . '" onclick="myContentchooser.getModule(' . $objRow->id . '); return false;">
                            <div class="olcontactleft"></div>
                            <div style="display:none;" id="Remove' . $objRow->id . '" class="itemremovelist"></div>
                            <div class="olcontactitemtitle">' . $this->objTranslate->_($objRow->resourceKey) . '</div>
                            <div class="olcontactright"></div>
                            <div class="clear"></div>
                        </div>';
                }
            }

            /**
             * list footer
             */
            $strReturn .= '
                        <div class="clear"></div>
                    </div>
                    <div class="olcontentchooserbottom">
                    </div>
                </div>';
        }

        return $strReturn;
    }

    /**
     * getRootLevelListView
     * @param object $objElements
     * @param string $strOverlayTitle
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getRootLevelListView($objElements, $strOverlayTitle)
    {
        $this->core->logger->debug('core->views->helpers->ContentchooserHelper->getRootLevelListView()');

        $strReturn = '';

        if (count($objElements) > 0) {
            /**
             * create header of list output
             */
            $strReturn .= '
                <div id="olRootLevels_title" style="display:none;">' . $strOverlayTitle . '</div>
                    <div class="olcontentchoosertop">' . $this->objTranslate->_('Name') . '</div>
                    <div class="olcontentitemcontainer">';

            foreach ($objElements as $objRow) {
                if ($objRow->id != $this->core->sysConfig->product->rootLevels->list->id) { // 11 - All Products
                    $strReturn .= '
                        <div class="olcontactitem" id="olRootLevelItem' . $objRow->id . '" onclick="myContentchooser.getRootLevel(' . $objRow->id . ',' . $objRow->idRootLevelTypes . ',' . $objRow->idRootLevelGroups . ((isset($objRow->rootLevelLanguageId) && $objRow->rootLevelLanguageId != '') ? ', ' . $objRow->rootLevelLanguageId : '') . '); return false;">
                            <div class="olcontactleft"></div>
                            <div style="display:none;" id="Remove' . $objRow->id . '" class="itemremovelist"></div>
                            <div class="olcontactitemtitle">' . $objRow->title . '</div>
                            <div class="olcontactright"></div>
                            <div class="clear"></div>
                        </div>';
                }
            }

            /**
             * list footer
             */
            $strReturn .= '
                    <div class="clear"></div>
                </div>
                <div class="olcontentchooserbottom">
                </div>';
        }

        return $strReturn;
    }

    /**
     * getNavigationElements
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getNavigationElements($rowset, $viewtype, $intFolderId = 0, $intRootLevelId = 0, $intRootLevelTypeId = 0, $intRootLevelGroupId = 0, $strContentType = null, $strOverlayTitle = '')
    {
        $this->core->logger->debug('core->views->helpers->ContentchooserHelper->getNavigationElements()');

        $strReturn = '';

        if ($strOverlayTitle != '') {
            $strReturn .= '
    	        <div id="olContentItems_title" style="display:none;">' . $strOverlayTitle . '</div>';
        }

        $strType = '';
        if ($strContentType != null) {
            $strType = ', \'' . $strContentType . '\'';
        }

        /*if($intRootLevelTypeId > 0 && $intRootLevelId > 0 && $intFolderId == 0){
          $strRootTitle = '';
          switch($intRootLevelTypeId){
            case $this->core->sysConfig->root_level_types->images:
              $strRootTitle = $this->core->translate->_('All_Images');
              break;
            case $this->core->sysConfig->root_level_types->documents:
              $strRootTitle = $this->core->translate->_('All_Documents');
              break;
            case $this->core->sysConfig->root_level_types->videos:
              $strRootTitle = $this->core->translate->_('All_Videos');
              break;
            default;
              $strRootTitle = $this->core->translate->_('All');
              break;
          }
          $strReturn .= '<div id="olnavitemAll" class="olnavrootitem" style="display:none;">
                           <div onclick="myOverlay.getRootNavItem('.$intRootLevelId.', '.$viewtype.'); return false;" style="position:relative;">
                             <div class="filterTitle">'.$strRootTitle.' <span class="small gray666">('.$this->core->translate->_('Only_with_filter').')</span></div>
                             <div class="clear"></div>
                           </div>
                         </div>';
        }*/

        if ($rowset != '' && count($rowset) > 0) {
            foreach ($rowset as $row) {
                if ($intFolderId == 0) {
                    $strReturn .= '
                        <div id="olnavitem' . $row->id . '" class="olnavrootitem">
                            <div onclick="myContentchooser.getNavItem(' . $row->id . ', ' . $intRootLevelTypeId . ', ' . $intRootLevelGroupId . ', ' . $viewtype . $strType . '); return false;" style="position:relative;">
                                <div class="icon img_folder_on"></div>
                                <span id="olnavitemtitle' . $row->id . '">' . htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</span>
                            </div>
                        </div>';
                } else {
                    $strReturn .= '
                        <div id="olnavitem' . $row->id . '" class="olnavchilditem">
                            <div onclick="myContentchooser.getNavItem(' . $row->id . ', ' . $intRootLevelTypeId . ', ' . $intRootLevelGroupId . ', ' . $viewtype . $strType . '); return false;" style="position:relative;">
                                <div class="icon img_folder_on"></div>
                                <span id="olnavitemtitle' . $row->id . '">' . htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</span>
                            </div>
                        </div>';
                }
            }
        }

        /**
         * return html output
         */
        return $strReturn;
    }

    /**
     * getListView
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getListView($rowset, $arrFileIds)
    {
        $this->core->logger->debug('core->views->helpers->DashboardHelper->getListView()');

        $this->objViewHelper = new ViewHelper();

        /**
         * create header of list output
         */
        $strOutputTop = '
            <div class="olcontentchoosertop">Titel</div>
            <div class="olcontentchoosercontentcontainer">';

        /**
         * output of list rows (elements)
         */
        $strOutput = '';
        if ($rowset != '' && count($rowset) > 0) {
            foreach ($rowset as $row) {

                $strHidden = '';
                /*if(array_search($row->id, $arrFileIds) !== false){
               $strHidden = ' style="display:none;"';
              }*/
                if ($row->isImage) {
                    if ($row->xDim < $row->yDim) {
                        $strMediaSize = 'height="32"';
                    } else {
                        $strMediaSize = 'width="32"';
                    }
                    $strOutput .= '
                        <div class="olfileitem" id="olItem' . $row->id . '" onclick="myDashboard.addItemToListArea(' . $row->id . '); return false;"' . $strHidden . '>
                            <div class="olfileleft"></div>
                            <div style="display:none;" id="Remove' . $row->id . '" class="itemremovelist"></div>
                            <div class="olfileitemicon"><img ' . $strMediaSize . ' id="File' . $row->id . '" src="' . sprintf($this->core->sysConfig->media->paths->icon32, $row->path) . $row->filename . '?v=' . $row->version . '" alt="' . $row->description . '"/></div>
                            <div class="olfileitemtitle">' . htmlentities((($row->title == '' && (isset($row->alternativeTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativeTitle) && $row->alternativeTitle != '') ? $row->alternativeTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
                            <div class="olfileright"></div>
                            <div class="clear"></div>
                        </div>';
                } else {
                    $strOutput .= '
                        <div class="olfileitem" id="olItem' . $row->id . '" onclick="myDashboard.addItemToListArea(' . $row->id . '); return false;"' . $strHidden . '>
                            <div class="olfileleft"></div>
                            <div style="display:none;" id="Remove' . $row->id . '" class="itemremovelist"></div>
                            <div class="olfileitemicon"><img width="32" height="32" id="File' . $row->id . '" src="' . $this->objViewHelper->getDocIcon($row->extension, 32) . '" alt="' . $row->description . '"/></div>
                            <div class="olfileitemtitle">' . htmlentities((($row->title == '' && (isset($row->alternativeTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativeTitle) && $row->alternativeTitle != '') ? $row->alternativeTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
                            <div class="olfileright"></div>
                            <div class="clear"></div>
                        </div>';
                }
            }
            $strOutput .= '
                <div class="clear"></div>';
        }

        /**
         * list footer
         */
        $strOutputBottom = '
            </div>
            <div class="olcontentchooserbottom"></div>';

        /**
         * return html output
         */
        if ($strOutput != '') {
            return $strOutputTop . $strOutput . $strOutputBottom . '<div class="clear"></div>';
        }
    }

    /**
     * getListElements
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getListElements($rowset, $arrElements, $strContentType = '')
    {
        $this->core->logger->debug('core->views->helpers->DashboardHelper->getListElements()');

        /**
         * create header of list output
         */
        $strOutputTop = '
            <div class="olcontentchoosertop">Titel</div>
            <div class="olcontentchoosercontentcontainer">';

        /**
         * output of list rows (elements)
         */
        $strOutput = '';
        if ($rowset != '' && count($rowset) > 0) {
            foreach ($rowset as $row) {
                $strHidden = '';
                // TODO : check if element is in object
                /*if(array_search($row->id, $arrElements) !== false){
                 $strHidden = ' style="display:none;"';
                }*/

                $strStartElement = '';
                if ($strContentType != '') {
                    $strStartElement = 'isStart' . ucfirst($strContentType);

                    $strOutput .= '
                        <div class="olpageitem" id="olItem' . $row->id . '" onclick="myContentchooser.callback(' . $row->id . ((isset($row->linkId) && $row->linkId > 0) ? ',' . $row->linkId : '') . '); return false;"' . $strHidden . '>
                            <div class="olpageleft"></div>
                            <div style="display:none;" id="Remove' . $row->id . '" class="itemremovelist"></div>
                            <div class="icon olpageicon img_' . (($row->$strStartElement == 1) ? 'startpage' : 'page') . '_' . (($row->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>
                            <div class="olpageitemtitle">' . htmlentities((($row->title == '' && (isset($row->alternativeTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativeTitle) && $row->alternativeTitle != '') ? $row->alternativeTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
                            <div class="olpageright"></div>
                            <div class="clear"></div>
                        </div>';
                }
            }
            $strOutput .= '
                <div class="clear"></div>';
        }

        /**
         * list footer
         */
        $strOutputBottom = '
            </div>
            <div class="olcontentchooserbottom"></div>';

        /**
         * return html output
         */
        return $strOutputTop . $strOutput . $strOutputBottom . '<div class="clear"></div>';
    }

    /**
     * setTranslate
     * @param object $objTranslate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setTranslate($objTranslate)
    {
        $this->objTranslate = $objTranslate;
    }

    /**
     * getTranslate
     * @param object $objTranslate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTranslate()
    {
        return $this->objTranslate;
    }
}
