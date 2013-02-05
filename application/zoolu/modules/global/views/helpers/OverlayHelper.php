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
 * OverlayHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-12-17: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

require_once (dirname(__FILE__) . '/../../../media/views/helpers/ViewHelper.php');

class OverlayHelper
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
     * getNavigationElements
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getNavigationElements($rowset, $viewtype, $intFolderId = 0, $intRootLevelId = 0, $intRootLevelTypeId = 0, $strContentType = null)
    {
        $this->core->logger->debug('global->views->helpers->OverlayHelper->getNavigationElements()');

        $strOutput = '';

        $strType = '';
        if ($strContentType != null) {
            $strType = ', \'' . $strContentType . '\'';
        }

        if ($intRootLevelTypeId > 0 && $intRootLevelId > 0 && $intFolderId == 0) {
            $strRootTitle = '';
            switch ($intRootLevelTypeId) {
                default;
                    $strRootTitle = $this->core->translate->_('All');
                    break;
            }
            $strOutput .= '<div id="olnavitemAll" class="olnavrootitem" style="display:none;">
                       <div onclick="myOverlay.getRootNavItem(' . $intRootLevelId . ', ' . $viewtype . '); return false;" style="position:relative;">
                         <div class="filterTitle">' . $strRootTitle . ' <span class="small gray666">(' . $this->core->translate->_('Only_with_filter') . ')</span></div>
                         <div class="clear"></div>
                       </div>
                     </div>';
        }
        
        if (count($rowset) > 0) {
            foreach ($rowset as $row) {
                $title = $row->title;
                if ($title == '' && isset($row->fallbackTitle)) {
                    $title = $row->fallbackTitle;            
                }
                if ($intFolderId == 0) {
                    $strOutput .= '<div id="olnavitem' . $row->id . '" class="olnavrootitem">
                           <div onclick="myOverlay.getNavItem(' . $row->id . ',' . $viewtype . $strType . '); return false;" style="position:relative;">
                             <div class="icon img_folder_on"></div>
                             <span id="olnavitemtitle' . $row->id . '">' . htmlentities($title, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</span>
                           </div>
                         </div>';
                } else {
                    $strOutput .= '<div id="olnavitem' . $row->id . '" class="olnavchilditem">
                           <div onclick="myOverlay.getNavItem(' . $row->id . ',' . $viewtype . $strType . '); return false;" style="position:relative;">
                             <div class="icon img_folder_on"></div>
                             <span id="olnavitemtitle' . $row->id . '">' . htmlentities($title, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</span>
                           </div>
                         </div>';
                }
            }
        }

        /**
         * return html output
         */
        return $strOutput;
    }

    /**
     * getListGlobal
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getListGlobal($rowset, $arrGlobalIds, $intRootLevelGroupId = 0)
    {
        $this->core->logger->debug('global->views->helpers->OverlayHelper->getListGlobal()');

        $this->objViewHelper = new ViewHelper();

        /**
         * create header of list output
         */
        $strOutputTop = '
            <div>
              <div class="olpagetopleft"></div>
              <div class="olpagetopitemtitle bold">Titel</div>
              <div class="olpagetopright"></div>
              <div class="clear"></div>
            </div>';

        /**
         * output of list rows (elements)
         */
        $strOutput = '';
        if (count($rowset) > 0) {
            $strOutput .= '
            <div class="olpageitemcontainer">';
            foreach ($rowset as $row) {
                $intGlobalId = $row->id;
                $strGlobalId = $row->globalId;
                if ($intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product) {
                    //$intGlobalId = $row->linkId;
                    $strGlobalId = $row->linkGlobalId;
                }

                $strHidden = '';
                if (array_search($strGlobalId, $arrGlobalIds) !== false) {
                    $strHidden = ' style="display:none;"';
                }

                $strOutput .= '
            <div class="olpageitem" id="olItem' . $strGlobalId . '" onclick="myOverlay.addPageToListArea(' . $intGlobalId . ', \'' . $strGlobalId . '\'); return false;"' . $strHidden . '>
              <div class="olpageleft"></div>
              <div style="display:none;" id="Remove' . $intGlobalId . '" class="itemremovelist"></div>
              <div class="icon olpageicon img_' . (($row->isStartGlobal == 1) ? 'startpage' : 'page') . '_' . (($row->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>
              <div class="olpageitemtitle">' . htmlentities((($row->title == '' && (isset($row->alternativeTitle))) ? ((isset($row->alternativeTitle) && $row->alternativeTitle != '') ? $row->alternativTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
              <div class="olpageright"></div>
              <div class="clear"></div>
            </div>';
            }
            $strOutput .= '
              <div class="clear"></div>
            </div>';
        }

        /**
         * list footer
         */
        $strOutputBottom = '
            <div>
              <div class="olpagebottomleft"></div>
              <div class="olpagebottomcenter"></div>
              <div class="olpagebottomright"></div>
              <div class="clear"></div>
            </div>';

        /**
         * return html output
         */
        return $strOutputTop . $strOutput . $strOutputBottom . '<div class="clear"></div>';
    }

    /**
     * getGlobalTree
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalTree($objRowset, $strItemAction, $arrElementIds = array())
    {
        $this->core->logger->debug('global->views->helpers->OverlayHelper->getGlobalTree()');

        $strOutput = '';

        if (count($objRowset) > 0) {
            $intLastFolderId = 0;
            foreach ($objRowset as $objRow) {
                $strHidden = '';
                if (array_search($objRow->globalId, $arrElementIds) !== false) {
                    $strHidden = ' style="display:none;"';
                }

                if ($intLastFolderId != $objRow->folderId) {

                    $intFolderDepth = $objRow->depth;

                    $strOutput .= '<div id="folder' . $objRow->folderId . '" class="olnavrootitem">
                           <div style="position:relative; padding-left:' . (20 * $intFolderDepth) . 'px">
                             <div class="icon img_folder_' . (($objRow->folderStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>' . htmlentities($objRow->folderTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '
                           </div>
                         </div>';

                    $intLastFolderId = $objRow->folderId;
                }

                if ($objRow->idGlobal > 0) {
                    $strOutput .= '
                        <div id="olItem' . $objRow->globalId . '" class="olnavrootitem"' . $strHidden . '>
                          <div style="display:none;" id="Remove' . $objRow->idGlobal . '" class="itemremovelist2"></div>
                          <div id="Item' . $objRow->idGlobal . '" style="position:relative; margin-left:' . (20 * $intFolderDepth + 20) . 'px; cursor:pointer;" onclick="' . $strItemAction . '(' . $objRow->idGlobal . ', \'' . $objRow->globalId . '\'); return false;">
                            <div class="icon img_' . (($objRow->isStartGlobal == 1) ? 'start' : 'global') . '_' . (($objRow->globalStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>' . htmlentities($objRow->globalTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '
                          </div>
                        </div>';
                }
            }
        }

        /**
         * return html output
         */
        return $strOutput;
    }

    /**
     * getMediaFilter
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getMediaFilter($intRootLevelId, $intViewType = 0, $strContentType = null)
    {
        $this->core->logger->debug('global->views->helpers->OverlayHelper->getMediaFilter(' . $intRootLevelId . ', ' . $intViewType . ')');

        $strOutput = '';

        $strType = '';
        if ($strContentType != null) {
            $strType = ', \'' . $strContentType . '\'';
        }

        $strOutput .= '
      <div class="olfilter">
        <div class="filter">
          <ol>
            <li id="autocompletList_mediaFilter" class="autocompletList input-text">
              <input type="text" value="" onchange="myOverlay.loadFileFilterContent(' . $intViewType . $strType . ');" id="mediaFilter_Tags" name="mediaFilter_Tags"/>
              <div id="mediaFilter_Tags_autocompleter" class="autocompleter">
                <div class="default">' . $this->core->translate->_('Search_tags') . '</div>
                <ul class="feed"></ul>
              </div>
            </li>
          </ol>
        </div>
        <input type="hidden" value="" id="mediaFilter_Folders" name="mediaFilter_Folders"/>
        <input type="hidden" value="' . $intRootLevelId . '" id="mediaFilter_RootLevel" name="mediaFilter_RootLevel"/>
      </div>
      <script type="text/javascript">//<![CDATA[ 
        myForm.initTag("mediaFilter_Tags",' . $this->getAllTagsForAutocompleter() . ');
      //]]>
      </script>';

        return $strOutput;
    }

    /**
     * getAllTagsForAutocompleter
     * @return string $strAllTags
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getAllTagsForAutocompleter()
    {
        require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Tags.php';
        $objModelTags = new Model_Tags();
        $objAllTags = $objModelTags->loadAllTags();

        $strAllTags = '[';
        if (count($objAllTags) > 0) {
            foreach ($objAllTags as $objTag) {
                $strAllTags .= '{"caption":"' . htmlentities($objTag->title, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '","value":' . $objTag->id . '},';
            }
            $strAllTags = trim($strAllTags, ',');
        }
        $strAllTags .= ']';
        return $strAllTags;
    }

    public function getSearch()
    {
        return $this->core->translate->_('Search');
    }

    public function getReset()
    {
        return $this->core->translate->_('Reset');
    }
}

?>