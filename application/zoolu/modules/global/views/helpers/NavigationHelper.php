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
 * @package    application.zoolu.modules.global.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NavigationHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-28: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class NavigationHelper
{

    /**
     * @var Core
     */
    private $core;

    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * getMainNavigation
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getMainNavigation(NavigationTree $rootLevelNavigation, $rootLevelId, $strViewType = '')
    {
        $this->core->logger->debug('cms->views->helpers->NavigationHelper->getMainNavigation()');

        $strOutput = '';

        foreach ($rootLevelNavigation as $objNavigationTree) {

            if (count($objNavigationTree) == 1) {
                foreach ($objNavigationTree as $objNavigation) {
                    if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $objNavigation->getId(), Security::PRIVILEGE_VIEW, true, false)) {
                        $strSelected = '';
                        if ($rootLevelId == $objNavigation->getId()) {
                            $strSelected = ' selected';

                            $strOutput .= '
              <script type="text/javascript">//<![CDATA[
                var preSelectedNaviItem = \'naviitem' . $objNavigation->getId() . '\';
              </script>';
                        }

                        $strOutput .= '
          <div class="naviitemcontainer">
            <div id="naviitem' . $objNavigation->getId() . '" class="naviitem' . $strSelected . '" onclick="myNavigation.selectRootLevel(' . $objNavigation->getId() . ', ' . $objNavigationTree->getTypeId() . ', \'' . $objNavigation->getUrl() . '\', true, \'' . $strViewType . '\'); myNavigation.loadDashboard(); return false;">
              <div class="producticon"></div>
              <div id="divRootLevelTitle_' . $objNavigation->getId() . '" class="itemtitle">' . htmlentities($objNavigation->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
              <div class="clear"></div>
              <input type="hidden" value="' . $objNavigationTree->getItemId() . '" id="rootLevelGroupKey' . $objNavigationTree->getTypeId() . '"/>
              <input type="hidden" value="' . $objNavigation->getLanguageId() . '" id="rootLevelLanguageId' . $objNavigation->getId() . '"/>
            </div>
            <div class="clear"></div>
          </div>';
                    }
                }
            } else {
                $strSubNavi = '';
                $strDisplaySubNavi = ' style="display:none;"';
                $strSubNaviSelected = '';
                foreach ($objNavigationTree as $objNavigation) {
                    if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $objNavigation->getId(), Security::PRIVILEGE_VIEW, true, false)) {
                        $strSelected = '';
                        if ($rootLevelId == $objNavigation->getId()) {
                            $strSelected = ' selected';
                            $strSubNaviSelected = ' selected';
                            $strDisplaySubNavi = '';

                            $strSubNavi .= '
              <script type="text/javascript">//<![CDATA[
                var preSelectedNaviItem = \'naviitem' . $objNavigationTree->getId() . '\';
                var preSelectedSubNaviItem = \'subnaviitem' . $objNavigation->getId() . '\';
              </script>';
                        }

                        $strSubNavi .= '
              <div id="subnaviitem' . $objNavigation->getId() . '" class="menulink' . $strSelected . '">
                <div class="portalcontenticon"></div>
                <div class="menutitle"><a onclick="myNavigation.selectRootLevel(' . $objNavigation->getId() . ', ' . $objNavigationTree->getTypeId() . ', \'' . $objNavigation->getUrl() . '\', true, \'' . $strViewType . '\');' . (($strViewType == 'tree') ? ' myNavigation.loadDashboard();' : '') . ' return false;" href="#">' . htmlentities($objNavigation->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</a></div>
                <div class="clear"></div>
              </div>';
                    }
                }

                if ($strSubNavi != '') {
                    $strOutput .= '
          <div class="naviitemcontainer">
            <div id="naviitem' . $objNavigationTree->getId() . '" class="naviitem hasmenu' . $strSubNaviSelected . '" onclick="myNavigation.selectRootLevel(' . $objNavigationTree->getId() . ', ' . $objNavigationTree->getTypeId() . ', \'\', false, \'' . $strViewType . '\'); return false;">
              <div class="producticon"></div>
              <div id="divRootLevelTitle_' . $objNavigationTree->getId() . '" class="itemtitle">' . htmlentities($objNavigationTree->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
              <div class="clear"></div>  
              <input type="hidden" value="' . $objNavigationTree->getItemId() . '" id="rootLevelGroupKey' . $objNavigationTree->getTypeId() . '"/>
              <input type="hidden" value="' . $objNavigationTree->getLanguageId() . '" id="rootLevelLanguageId' . $objNavigationTree->getId() . '"/>
            </div>
            <div id="naviitem' . $objNavigationTree->getId() . 'menu" class="menu"' . $strDisplaySubNavi . '>
            ' . $strSubNavi . '
            </div>
            <div class="clear"></div>
          </div>';
                }
            }
        }

        return $strOutput;
    }

    /**
     * getNavigationElements
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    function getNavigationElements($objRowset, $currLevel, $strGroupKey)
    {
        $this->core->logger->debug('cms->views->helpers->NavigationHelper->getNavigationElements()');

        $strOutput = '';
        $strOutputStartElement = '';

        $counter = 1;
        $strGroupKeyOverview = $strGroupKey . '_overview';

        if (count($objRowset) > 0) {
            foreach ($objRowset as $objRow) {

                $intSortRowId = ($objRow->linkGlobalId > 0) ? $objRow->linkGlobalId : $objRow->id;

                $strGlobalTitle = $objRow->title;
                $strFolderTitle = $objRow->title;

                // gui fallback title
                if ($strGlobalTitle == '' && $objRow->elementType == 'global') {
                    $strGlobalTitle = $objRow->guiTitle;
                    $objRow->type = 'global';
                    $objRow->genericFormId = '';
                    $objRow->version = 'null';
                    $objRow->templateId = ($objRow->isStartGlobal == 1) ? $this->core->sysConfig->global_types->$strGroupKeyOverview->default_templateId : $this->core->sysConfig->global_types->$strGroupKey->default_templateId;
                }

                // gui fallback title
                if ($strFolderTitle == '' && $objRow->elementType == 'folder') {
                    $strFolderTitle = $objRow->guiTitle;
                    $objRow->type = 'folder';
                    $objRow->genericFormId = $this->core->sysConfig->form->ids->folders->default;
                    $objRow->version = 'null';
                    $objRow->templateId = -1;
                }

                $objRow->version = ($objRow->version != '') ? $objRow->version : 'null';
                $objRow->templateId = ($objRow->templateId != '') ? $objRow->templateId : -1;

                if ($objRow->isStartGlobal == 1) {
                    /**
                     * overwrite type with 'global'
                     */
                    $objRow->type = 'global';

                    /**
                     * get values of the row and create startproduct output
                     */
                    $strOutputStartElement .= '<div id="' . $objRow->type . $objRow->id . '" class="' . $objRow->type . '">
            <div class="icon img_start_' . (($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>
            <div id="divNavigationTitle_element' . $objRow->id . '" class="title" title="' . $strFolderTitle . '" onclick="myNavigation.getEditForm(' . $objRow->id . ',\'' . $objRow->type . '\',\'' . $objRow->genericFormId . '\',' . $objRow->version . ',' . $objRow->templateId . ',' . $intSortRowId . '); return false;">' . htmlentities($strGlobalTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
          </div>';
                } else if ($objRow->elementType == 'global') {

                    /**
                     * overwrite type with 'global'
                     */
                    $objRow->type = 'global';

                    /**
                     * get values of the row and create global output
                     */
                    $strOutput .= '<div id="' . $objRow->type . $objRow->id . '" class="' . $objRow->type . '">
            <div class="icon img_' . $objRow->type . '_' . (($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>
            <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_' . $objRow->type . '_' . $intSortRowId . '" id="pos_' . $objRow->type . '_' . $intSortRowId . '" value="' . $counter . '" onfocus="myNavigation.toggleSortPosBox(\'pos_' . $objRow->type . '_' . $intSortRowId . '\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_' . $objRow->type . '_' . $intSortRowId . '\',\'' . $objRow->type . '\',' . $currLevel . '); myNavigation.toggleSortPosBox(\'pos_' . $objRow->type . '_' . $intSortRowId . '\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_' . $objRow->type . '_' . $intSortRowId . '\'); return false;" /></div>
            <div id="divNavigationTitle_element' . $objRow->id . '" class="title" title="' . $strFolderTitle . '" onclick="myNavigation.getEditForm(' . $objRow->id . ',\'' . $objRow->type . '\',\'' . $objRow->genericFormId . '\',' . $objRow->version . ',' . $objRow->templateId . ',' . $intSortRowId . '); return false;">' . htmlentities($strGlobalTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
          </div>';

                    $counter++;

                } else {
                    if (Security::get()->isAllowed(Security::RESOURCE_FOLDER_PREFIX . $objRow->id, Security::PRIVILEGE_VIEW)) {

                        /**
                         * get values of the row and create default output
                         */
                        $strOutput .= '<div id="' . $objRow->type . $objRow->id . '" class="' . $objRow->type . '">
              <div id="divNavigationEdit_' . $objRow->id . '" class="icon img_' . $objRow->type . '_' . (($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '" ondblclick="myNavigation.getEditForm(' . $objRow->id . ',\'' . $objRow->type . '\',\'' . $objRow->genericFormId . '\',' . $objRow->version . ',' . $objRow->templateId . '); return false;"></div>
              <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_' . $objRow->type . '_' . $objRow->id . '" id="pos_' . $objRow->type . '_' . $objRow->id . '" value="' . $counter . '" onfocus="myNavigation.toggleSortPosBox(\'pos_' . $objRow->type . '_' . $objRow->id . '\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_' . $objRow->type . '_' . $objRow->id . '\',\'' . $objRow->type . '\',' . $currLevel . '); myNavigation.toggleSortPosBox(\'pos_' . $objRow->type . '_' . $objRow->id . '\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_' . $objRow->type . '_' . $objRow->id . '\'); return false;"/></div>
              <div id="divNavigationTitle_' . $objRow->type . $objRow->id . '" class="title" title="' . $strFolderTitle . '" onclick="myNavigation.selectNavigationItem(' . $currLevel . ', \'' . $objRow->type . '\',' . $objRow->id . '); return false;">' . htmlentities($strFolderTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
            </div>';
                    }

                    $counter++;
                }
            }

            if ($strOutputStartElement != '') {
                $strOutputStartElement .= '<div class="linegray"></div>';
            }
        }

        return $strOutputStartElement . $strOutput;

    }

}

?>