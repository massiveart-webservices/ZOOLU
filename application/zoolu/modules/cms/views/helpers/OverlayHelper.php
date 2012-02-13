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
 * @package    application.zoolu.modules.cms.views
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * OverlayHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-24: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
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
     * @var ViewHelper
     */
    private $objViewHelper;

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
     * getNavigationElements
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getNavigationElements($rowset, $viewtype, $intFolderId = 0, $intRootLevelId = 0, $intRootLevelTypeId = 0, $strContentType = null, $blnSelectOne = 'false')
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getNavigationElements()');

        $strOutput = '';

//        $strType = '';
//        if ($strContentType != null) {
            $strType = ', \'' . $strContentType . '\'';
//        }

        if ($intRootLevelTypeId > 0 && $intRootLevelId > 0 && $intFolderId == 0) {
            $strRootTitle = '';
            switch ($intRootLevelTypeId) {
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
            $strOutput .= '
                <div id="olnavitemAll" class="olnavrootitem" style="display:none;">
                    <div onclick="myOverlay.getRootNavItem(' . $intRootLevelId . ', ' . $viewtype . '); return false;" style="position:relative;">
                        <div class="filterTitle">' . $strRootTitle . ' <span class="small gray666">(' . $this->core->translate->_('Only_with_filter') . ')</span></div>
                        <div class="clear"></div>
                    </div>
                </div>';
        }

        if (count($rowset) > 0) {
            foreach ($rowset as $row) {
                if ($intFolderId == 0) {
                    $strOutput .= '
                        <div id="olnavitem' . $row->id . '" class="olnavrootitem">
                            <div onclick="myOverlay.getNavItem(' . $row->id . ',' . $viewtype . $strType . ', '.$blnSelectOne.'); return false;" style="position:relative;">
                                <div class="icon img_folder_on"></div>
                                <span id="olnavitemtitle' . $row->id . '">' . htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</span>
                            </div>
                        </div>';
                } else {
                    $strOutput .= '
                        <div id="olnavitem' . $row->id . '" class="olnavchilditem">
                            <div onclick="myOverlay.getNavItem(' . $row->id . ',' . $viewtype . $strType . ', '.$blnSelectOne.'); return false;" style="position:relative;">
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
        return $strOutput;
    }

    /**
     * getContactNavigationElements
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getContactNavigationElements($rowset, $intUnitId = 0)
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getContactNavigationElements()');

        $strOutput = '';

        if ($intUnitId == 0) {
            $strOutput .= '
                <div id="olnavitem0" class="olnavrootitem">
                    <div onclick="myOverlay.getContactNavItem(0); return false;" style="position:relative;"><div class="icon img_folder_off"></div>' . $this->core->translate->_('Conatcts') .'</div>
                    <div id="olsubnav0" class="" style="display: none;">';
        }

        if (count($rowset) > 0) {
            foreach ($rowset as $row) {
                $intFolderDepth = $row->depth + 1;
                $strOutput .= '
                    <div id="olnavitem' . $row->id . '" class="olnavchilditem" style="padding-left:' . (22 * $intFolderDepth) . 'px">
                        <div onclick="myOverlay.getContactNavItem(' . $row->id . '); return false;" style="position:relative;">
                            <div class="icon img_folder_off"></div>' . htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '
                        </div>
                    </div>';

            }
        }

        if ($intUnitId == 0) {
            $strOutput .= '
                    </div>
                </div>';
        }

        /**
         * return html output
         */
        return $strOutput;
    }

    /**
     * getPageTree
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageTree($objRowset, $strItemAction, $arrPageIds = array(), $blnIndent = true)
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getPageTree()');

        $strOutput = '';

        if (count($objRowset) > 0) {
            $intLastFolderId = 0;
            foreach ($objRowset as $objRow) {
                $strHidden = '';
                if (array_search($objRow->pageId, $arrPageIds) !== false) {
                    $strHidden = ' style="display:none;"';
                }

                if ($intLastFolderId != $objRow->folderId) {

                    $intFolderDepth = $objRow->depth;

                    $strIndent = '';
                    $strIndentPage = '';
                    if ($blnIndent) {
                        $strIndent = 'position:relative; padding-left:' . (20 * $intFolderDepth) . 'px;';
                        $strIndentPage = 'position:relative; padding-left:' . (20 * $intFolderDepth + 20) . 'px;';
                    }

                    $strOutput .= '
                        <div id="folder' . $objRow->folderId . '" class="olnavrootitem">
                            <div style="' . $strIndent . '">
                                <div class="icon img_folder_' . (($objRow->folderStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>' . htmlentities($objRow->folderTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '
                            </div>
                        </div>';

                    $intLastFolderId = $objRow->folderId;
                }

                if ($objRow->idPage > 0) {
                    $strOutput .= '
                        <div id="olItem' . $objRow->pageId . '" class="olnavrootitem"' . $strHidden . '>
                            <div style="display:none;" id="Remove' . $objRow->idPage . '" class="itemremovelist2"></div>
                            <div id="Item' . $objRow->idPage . '" style="' . $strIndentPage . 'cursor:pointer;" onclick="' . $strItemAction . '(' . $objRow->idPage . ', \'' . $objRow->pageId . '\'); return false;">
                                <div class="icon img_' . (($objRow->isStartPage == 1) ? 'startpage' : 'page') . '_' . (($objRow->pageStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>' . htmlentities($objRow->pageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '
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
     * getThumbView
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getThumbView($rowset, $arrFileIds)
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getThumbView()');

        $strOutputTop = '
            <div class="olmediacontainer">';

        /**
         * output of each thumb
         */
        $strOutput = '';
        foreach ($rowset as $row) {
            if ($row->isImage) {
                $strHidden = '';

                if ($row->xDim < $row->yDim) {
                    $strMediaSize = 'height="100"';
                } else {
                    $strMediaSize = 'width="100"';
                }

                if (array_search($row->id, $arrFileIds) !== false) {
                    $strHidden = ' style="display:none;"';
                }

                $strOutput .= '
                    <div id="olMediaItem' . $row->id . '" class="olmediaitem" fileid="' . $row->id . '"' . $strHidden . '>
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <img onclick="myOverlay.addItemToThumbArea(\'olMediaItem' . $row->id . '\', ' . $row->id . '); return false;" id="Img' . $row->id . '" alt="' . $row->title . '" title="' . $row->title . '" src="' . sprintf($this->core->sysConfig->media->paths->thumb, $row->path) . $row->filename . '?v=' . $row->version . '" ' . $strMediaSize . '/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div id="Remove' . $row->id . '" class="itemremovethumb" style="display:none;"></div>
                    </div>';
            }
        }

        /**
         * return html output
         */
        if ($strOutput != '') {
            return $strOutputTop . $strOutput . '
		           <div class="clear"></div>
		         </div>';
        }
    }

    /**
     * getListView
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getListView($rowset, $arrFileIds)
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getListView()');

        $this->objViewHelper = new ViewHelper();

        /**
         * create header of list output
         */
        $strOutputTop = '
            <div>
                <div class="olfiletopleft"></div>
                <div class="olfiletopitemicon"></div>
                <div class="olfiletopitemtitle bold">Titel</div>
                <div class="olfiletopright"></div>
                <div class="clear"></div>
            </div>';

        /**
         * output of list rows (elements)
         */
        $strOutput = '';
        $blnIsImageView = false;
        if (count($rowset) > 0) {
            $strOutput .= '
                <div class="olfileitemcontainer">';
            foreach ($rowset as $row) {
                $strHidden = '';
                if (array_search($row->id, $arrFileIds) !== false) {
                    $strHidden = ' style="display:none;"';
                }
                if ($row->isImage) {
                    $blnIsImageView = true;
                    if ($row->xDim < $row->yDim) {
                        $strMediaSize = 'height="32"';
                    } else {
                        $strMediaSize = 'width="32"';
                    }
                    $strOutput .= '
                        <div class="olfileitem" id="olFileItem' . $row->id . '" onclick="myOverlay.addItemToThumbArea(\'olFileItem' . $row->id . '\', ' . $row->id . '); return false;"' . $strHidden . '>
                            <div class="olfileleft"></div>
                            <div style="display:none;" id="Remove' . $row->id . '" class="itemremovelist"></div>
                            <div class="olfileitemicon"><img ' . $strMediaSize . ' id="File' . $row->id . '" src="' . sprintf($this->core->sysConfig->media->paths->icon32, $row->path) . $row->filename . '?v=' . $row->version . '" alt="' . $row->description . '"/></div>
                            <div class="olfileitemtitle">' . htmlentities((($row->title == '' && (isset($row->alternativTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativTitle) && $row->alternativTitle != '') ? $row->alternativTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
                            <div class="olfileright"></div>
                            <div class="clear"></div>
                        </div>';
                } else {
                    $strOutput .= '
                        <div class="olfileitem" id="olFileItem' . $row->id . '" onclick="myOverlay.addFileItemToListArea(\'olFileItem' . $row->id . '\', ' . $row->id . '); return false;"' . $strHidden . '>
                            <div class="olfileleft"></div>
                            <div style="display:none;" id="Remove' . $row->id . '" class="itemremovelist"></div>
                            <div class="olfileitemicon"><img width="32" height="32" id="File' . $row->id . '" src="' . $this->objViewHelper->getDocIcon($row->extension, 32) . '" alt="' . $row->description . '"/></div>
                            <div class="olfileitemtitle">' . htmlentities((($row->title == '' && (isset($row->alternativTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativTitle) && $row->alternativTitle != '') ? $row->alternativTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
                            <div class="olfileright"></div>
                            <div class="clear"></div>
                        </div>';
                }
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
                <div class="olfilebottomleft"></div>
                <div class="olfilebottomcenter"></div>
                <div class="olfilebottomright"></div>
                <div class="clear"></div>
            </div>';

        /**
         * return html output
         */
        if ($strOutput != '') {
            if ($blnIsImageView) {
                return $strOutput . '<div class="clear"></div>';
            } else {
                return $strOutputTop . $strOutput . $strOutputBottom . '<div class="clear"></div>';
            }
        }
    }

    /**
     * getListView
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getListPage($rowset, $arrPageIds, $blnSelectOne = false)
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getListPage()');

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
                $strHidden = '';
                if (array_search($row->pageId, $arrPageIds) !== false) {
                    $strHidden = ' style="display:none;"';
                }

                if($blnSelectOne){
                  $strAction = 'myOverlay.selectPage('.$row->id.', \''.$row->pageId.'\'); return false;';
                }else{
                  $strAction = 'myOverlay.addPageToListArea('.$row->id.', \''.$row->pageId.'\'); return false;';
                }
                
                $strOutput .= '
                    <div class="olpageitem" id="olItem' . $row->pageId . '" onclick="'.$strAction.'"' . $strHidden . '>
                        <div class="olpageleft"></div>
                        <div style="display:none;" id="Remove' . $row->id . '" class="itemremovelist"></div>
                        <div class="icon olpageicon img_' . (($row->isStartPage == 1) ? 'startpage' : 'page') . '_' . (($row->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off') . '"></div>
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
     * getGroupListView
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGroupListView($rowset, $arrFileIds)
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getContactListView()');

        $this->objViewHelper = new ViewHelper();

        /**
         * create header of list output
         */
        $strOutputTop = '
            <div>
                <div class="olcontacttopleft"></div>
                <div class="olcontacttopitemtitle bold">Titel</div>
                <div class="olcontacttopitemicon"></div>
                <div class="olcontacttopright"></div>
                <div class="clear"></div>
            </div>
            <div class="olcontactitemcontainer">';

        /**
         * output of list rows (elements)
         */
        $strOutput = '';
        foreach ($rowset as $row) {
            $strHidden = '';
            if (array_search($row->id, $arrFileIds) !== false) {
                $strHidden = ' style="display:none;"';
            }

            $strOutput .= '
                <div class="olcontactitem" id="olContactItem' . $row->id . '" onclick="myOverlay.addContactItemToListArea(\'olContactItem' . $row->id . '\', ' . $row->id . '); return false;"' . $strHidden . '>
                    <div class="olcontactleft"></div>
                    <div style="display:none;" id="Remove' . $row->id . '" class="itemremovelist"></div>
                    <div class="olcontactitemtitle">' . $row->title . '</div>
                    <div class="olcontactright"></div>
                    <div class="clear"></div>
                </div>';
        }

        /**
         * list footer
         */
        $strOutputBottom = '
                <div class="clear"></div>
            </div>
            <div>
                <div class="olcontactbottomleft"></div>
                <div class="olcontactbottomcenter"></div>
                <div class="olcontactbottomright"></div>
                <div class="clear"></div>
            </div>';

        /**
         * return html output
         */
        if ($strOutput != '') {
            return $strOutputTop . $strOutput . $strOutputBottom . '<div class="clear"></div>';
        }
    }

    /**
     * getContactListView
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getContactListView($rowset, $arrFileIds)
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getContactListView()');

        $this->objViewHelper = new ViewHelper();

        /**
         * create header of list output
         */
        $strOutputTop = '
            <div>
                <div class="olcontacttopleft"></div>
                <div class="olcontacttopitemicon"></div>
                <div class="olcontacttopitemtitle bold">Titel</div>
                <div class="olcontacttopright"></div>
                <div class="clear"></div>
            </div>
            <div class="olcontactitemcontainer">';

        /**
         * output of list rows (elements)
         */
        $strOutput = '';
        foreach ($rowset as $row) {
            $strHidden = '';
            if (array_search($row->id, $arrFileIds) !== false) {
                $strHidden = ' style="display:none;"';
            }

            $strOutput .= '
                <div class="olcontactitem" id="olContactItem' . $row->id . '" onclick="myOverlay.addContactItemToListArea(\'olContactItem' . $row->id . '\', ' . $row->id . '); return false;"' . $strHidden . '>
                    <div class="olcontactleft"></div>
                    <div style="display:none;" id="Remove' . $row->id . '" class="itemremovelist"></div>
                    <div class="olcontactitemicon">';

            if ($row->filename != '') {
                $strOutput .= '<img width="32" height="32" id="Contact' . $row->id . '" src="' . sprintf($this->core->sysConfig->media->paths->icon32, $row->filepath) . $row->filename . '?v=' . $row->fileversion . '" alt="' . $row->title . '" width="16" height="16"/>';
            }

            $strOutput .= '
                    </div>
                    <div class="olcontactitemtitle">' . $row->title . '</div>
                    <div class="olcontactright"></div>
                    <div class="clear"></div>
                </div>';
        }

        /**
         * list footer
         */
        $strOutputBottom = '
                <div class="clear"></div>
            </div>
            <div>
                <div class="olcontactbottomleft"></div>
                <div class="olcontactbottomcenter"></div>
                <div class="olcontactbottomright"></div>
                <div class="clear"></div>
            </div>';

        /**
         * return html output
         */
        if ($strOutput != '') {
            return $strOutputTop . $strOutput . $strOutputBottom . '<div class="clear"></div>';
        }
    }

    /**
     * getMediaFilter
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getMediaFilter($intRootLevelId, $intViewType = 0, $strContentType = null)
    {
        $this->core->logger->debug('cms->views->helpers->OverlayHelper->getMediaFilter(' . $intRootLevelId . ', ' . $intViewType . ')');

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