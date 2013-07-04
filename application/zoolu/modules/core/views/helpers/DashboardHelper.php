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
 * DashboardHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-22: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

require_once (dirname(__FILE__) . '/../../../media/views/helpers/ViewHelper.php');

class DashboardHelper
{

    /**
     * @var Core
     */
    private $core;

    /**
     * object Translate
     */
    private $objTranslate;

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
     * getEntries
     * @param object $objElements
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getEntries($objElements)
    {
        $this->core->logger->debug('core->views->helpers->DashboardHelper->getEntries()');

        $strReturn = '';
        $strJs = '';

        $objAuth = Zend_Auth::getInstance();
        $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
        $intUserId = $objAuth->getIdentity()->id;

        if (count($objElements) > 0) {
            foreach ($objElements as $objRow) {
                /**
                 * creator
                 */
                $strCreator = $objRow->fname . ' ' . $objRow->sname;

                /**
                 * profile image of creator
                 */
                $strCreatorProfile = '/zoolu-statics/images/main/user_default.jpg';
                $blnDefaultImg = true;
                if ($objRow->filename != '') {
                    $strCreatorProfile = $this->core->sysConfig->media->paths->imgbase . $objRow->path . 'icon32/' . $objRow->filename;
                    $blnDefaultImg = false;
                }

                /**
                 * show checkbox
                 */
                $blnShowCheckbox = false;
                if (($objRow->idUsers == $intUserId) || ($objRow->idUsersCreator == $intUserId)) {
                    $blnShowCheckbox = true;
                }

                /**
                 * show delete
                 */
                $blnShowDelete = false;
                if ($objRow->idUsersCreator == $intUserId) {
                    $blnShowDelete = true;
                }

                /**
                 * decide if activity is done or not
                 */
                $blnIsChecked = false;
                if (($objRow->idUsers == $intUserId && $objRow->idActivityUserStatus == 1) ||
                    ($objRow->idUsersCreator == $intUserId && $objRow->idActivityUserStatusCreator == 1)
                ) {
                    $blnIsChecked = true;
                }

                $strReturn .= '
                    <div id="activity_' . $objRow->id . '" class="section' . (($blnIsChecked) ? ' checked' : '') . '">
                    <div id="activityStatus_' . $objRow->id . '" class="activityStatus">';
                if ($blnShowCheckbox) {
                    $strReturn .= '
            	        <input' . (($blnIsChecked) ? ' checked="checked"' : '') . ' type="checkbox" id="checked_' . $objRow->id . '" name="checked_' . $objRow->id . '" onclick="myDashboard.changeActivityStatus(' . $objRow->id . ');"/><label for="checked_' . $objRow->id . '">' . $this->objTranslate->_('Checked') . '</label>';
                }
                $strReturn .= '
                    </div>';
                if ($blnShowCheckbox) {
                    $strReturn .= '
          		        <div class="props">
          			        <div class="icon" onclick="myDashboard.toggleProps(' . $objRow->id . '); return false;"></div>
          			        <div id="activityPropsContent_' . $objRow->id . '" class="content" style="display:none;">
          				        <div class="inner">
                                    <div id="activityToggle_' . $objRow->id . '"' . ((!$blnIsChecked) ? ' style="display:none;"' : '') . '>
                                        <a id="activityHide_' . $objRow->id . '" href="#"' . (($blnIsChecked) ? ' style="display:none;"' : '') . ' onclick="myDashboard.toggleEntry(' . $objRow->id . '); return false;">' . $this->objTranslate->_('Hide') . '</a>
                                        <a id="activityShow_' . $objRow->id . '" href="#"' . ((!$blnIsChecked) ? ' style="display:none;"' : '') . ' onclick="myDashboard.toggleEntry(' . $objRow->id . '); return false;">' . $this->objTranslate->_('Show') . '</a>
                                    </div>';
                    if ($blnShowDelete) {
                        $strReturn .= '
                                    <div id="activityDelete_' . $objRow->id . '">
                                        <a href="#" onclick="myDashboard.deleteEntry(' . $objRow->id . '); return false;">' . $this->objTranslate->_('Delete') . '</a>
                                    </div>';
                    }
                    $strReturn .= '
          				        </div>
          			        </div>
          		        </div>';
                }
                $strReturn .= '
                    <div id="activityEntry_' . $objRow->id . '" class="activity">
                        <div class="entry">
                            <div class="img' . (($blnDefaultImg) ? ' default' : '') . '">
                                <img src="' . $strCreatorProfile . '" width="40" height="40" alt="' . $strCreator . '"/>
                                <div class="status"></div>
                            </div>
                            <div class="message">
                                <div class="author">';
                if ($objRow->email != '') {
                    $strReturn .= '
                                    <a href="mailto:' . $objRow->email . '">' . $strCreator . '</a>';
                } else {
                    $strReturn .= $strCreator;
                }
                $strReturn .= '
                                    <div class="info"><abbr title="' . date('d.m.Y, H:i', strtotime($objRow->created)) . '">' . $this->calcTimeDifference($objRow->created) . '</abbr></div>
                                </div>
                                <div id="recipients_' . $objRow->id . '" class="recipientsContainer"></div>
                                <div class="clear"></div>
                                <div class="text">
                                    <div class="headline">' . htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</div>
                                    <p>' . nl2br(htmlentities($objRow->description, ENT_COMPAT, $this->core->sysConfig->encoding->default)) . '</p>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div id="attachments_' . $objRow->id . '" class="attach"></div>
                        <div id="commentsContainer_' . $objRow->id . '" class="commentsContainer"></div>
                        <div class="write">
                            <div id="commentBox_' . $objRow->id . '" class="commentBox">
                	            <input type="text" id="commentTmp_' . $objRow->id . '" name="commentTmp_' . $objRow->id . '" value="' . $this->objTranslate->_('Write_a_comment') . ' ..." class="commentTmp" />
        				    </div>
        				    <div id="buttonsave_' . $objRow->id . '" class="save" style="display:none;">
          				        <div class="buttonsave" onclick="myDashboard.saveComment(' . $objRow->id . '); return false;">
                                    <div class="button25leftOn"></div>
                                    <div class="button25centerOn"><div>' . $this->objTranslate->_('Send') . '</div></div>
                                    <div class="button25rightOn"></div>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>';

                $strJs .= '
                    myDashboard.getRecipients(' . $objRow->id . ');
                    myDashboard.getContentLinks(' . $objRow->id . ');
                    myDashboard.getComments(' . $objRow->id . ');';
            }
        } else {
            $strReturn = '
      	        <div id="empty" class="empty">' . $this->objTranslate->_('No_entries_available') . '</div>';
            $strJs .= '
                myDashboard.blnNoEntries = true;';
        }

        if ($strJs != '') {
            $strReturn .= '
      	        <script type="text/javascript">//<![CDATA[
      		        ' . $strJs . '
      		        //]]>
    		    </script>';
        }

        return $strReturn;
    }

    /**
     * getRecipients
     * @param object $objElements
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getRecipients($objElements)
    {
        $this->core->logger->debug('core->views->helpers->DashboardHelper->getRecipients()');

        $strReturn = '';
        $strHidden = '';
        $intActivityId = 0;

        if (count($objElements) > 0) {
            $strReturn .= '
      		    <div class="recipients">';

            $intCounter = 0;
            foreach ($objElements as $objRow) {
                $intActivityId = $objRow->idActivities;
                $strCreator = $objRow->fname . ' ' . $objRow->sname;

                $strCreatorProfile = '/zoolu-statics/images/main/user_default.jpg';
                $blnDefaultImg = true;
                if ($objRow->filename != '') {
                    $strCreatorProfile = $this->core->sysConfig->media->paths->imgbase . $objRow->path . 'icon32/' . $objRow->filename;
                    $blnDefaultImg = false;
                }

                if ($intCounter < 3) {
                    $strReturn .= '
                        <div title="' . $strCreator . '"' . (($blnDefaultImg) ? ' class="default"' : '') . '>
                            <img src="' . $strCreatorProfile . '" width="30" height="30" alt="' . $strCreator . '"/>
                        </div>';
                } else {
                    $strHidden .= '
                        <div title="' . $strCreator . '"' . (($blnDefaultImg) ? ' class="default"' : '') . '>
                            <img src="' . $strCreatorProfile . '" width="30" height="30" alt="' . $strCreator . '"/>
                        </div>';
                }
                $intCounter++;
            }

            if ($intCounter >= 3 && $strHidden != '') {
                $intDiff = $intCounter - 3;
                $strReturn .= '
        		    <div id="recipients_hidden_' . $intActivityId . '" style="display:none;">
        			    ' . $strHidden . '
        		    </div>
                    <div id="recipientsMore_' . $intActivityId . '" class="more">(' . $this->objTranslate->_('And') . ' <a href="#" onclick="myDashboard.showRecipients(' . $intActivityId . '); return false;">' . $intDiff . (($intDiff == 1) ? ' ' . $this->objTranslate->_('other') : ' ' . $this->objTranslate->_('others')) . '</a>)</div>';
            }

            $strReturn .= '
                    <div class="clear"></div>
                </div>';
        }

        return $strReturn;
    }

    /**
     * getLinks
     * @param array $arrElements
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getLinks($objActivityLinks, $arrElements)
    {
        $this->core->logger->debug('core->views->helpers->DashboardHelper->getLinks()');

        $strReturn = '';

        $arrModules = array();
        $arrModules = $this->core->sysConfig->modules->toArray();

        if (count($arrElements) > 0) {
            foreach ($arrElements as $intModulelId => $objElements) {

                $strUrl = '/zoolu/';
                if (count($arrModules) > 0) {
                    foreach ($arrModules as $strKey => $intModule) {
                        if ($intModule == $intModulelId) {
                            $strUrl .= $strKey . '/';
                        }
                    }
                }

                if (array_key_exists('products', $objElements) && count($objElements['products']) > 0) {
                    foreach ($objElements['products'] as $productRow) {
                        $strReturn .= $this->getLinksByRow($objActivityLinks, $productRow, $strUrl);
                    }
                } else if (array_key_exists('default', $objElements) && count($objElements['default']) > 0) {
                    foreach ($objElements['default'] as $globalRow) {
                        $strReturn .= $this->getLinksByRow($objActivityLinks, $globalRow, $strUrl);
                    }
                } else {
                    foreach ($objElements as $row) {
                        $strReturn .= $this->getLinksByRow($objActivityLinks, $row, $strUrl);
                    }
                }
            }
            $strReturn .= '
                <div class="clear"></div>';
        }

        return $strReturn;
    }

    private function getLinksByRow($objActivityLinks, $row, $strUrl)
    {
        $strReturn = '';

        //$this->core->logger->debug(var_export($row, true));

        $intTmpRootLevelId = 0;
        $intRootLevelGroupId = 0;
        if (count($objActivityLinks) > 0) {
            foreach ($objActivityLinks as $objActivityLink) {
                if ($objActivityLink->idRelation == $row->id) {
                    $intTmpRootLevelId = $objActivityLink->idRootLevels;
                    $intRootLevelGroupId = $objActivityLink->idRootLevelGroups;
                }
            }
        }

        $intId = $row->id;
        $intParentId = $row->idParent;
        $intParentTypeId = $row->idParentTypes;
        if (isset($row->linkId) && $row->linkId != '') {
            $intParentId = $row->linkIdParent;
            $intParentTypeId = $row->linkIdParentTypes;
        }

        $intRootLevelId = ((isset($row->idRootLevels) && $row->idRootLevels != '') ? $row->idRootLevels : (($intTmpRootLevelId > 0) ? $intTmpRootLevelId : (($row->idParentTypes == $this->core->sysConfig->parent_types->rootlevel) ? $row->idParent : 0)));

        $strElementType = '';
        if (isset($row->elementType) && $row->elementType != '') {
            $strElementType = $row->elementType;

            $strReturn .= '
                <div class="item">
                    <div class="icon' . (($strElementType != '') ? ' img_' . ((isset($row->isStartElement) && $row->isStartElement) ? 'start' : $strElementType) . '_' . ((isset($row->idStatus) && $row->idStatus == 2) ? 'on' : 'off') : '') . '"></div>
                    <div class="unit"><a href="#" onclick="myDashboard.selectItem(' . $intRootLevelId . ', ' . $intRootLevelGroupId . ', ' . $intId . ', ' . $intParentId . ', ' . $intParentTypeId . ', \'' . $strUrl . '\');">' . htmlentities((($row->title == '' && (isset($row->alternativeTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativeTitle) && $row->alternativeTitle != '') ? $row->alternativeTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</a></div>
                    <div class="clear"></div>
                </div>';
        } else {
            $strReturn .= '
      	        <div class="item">';

            if ($row->isImage) {
                $strMediaSize = '';
                if ($row->xDim < $row->yDim) {
                    $strMediaSize = 'height="16"';
                } else {
                    $strMediaSize = 'width="16"';
                }

                $strReturn .= '
                    <div class="icon"><img ' . $strMediaSize . ' src="' . sprintf($this->core->sysConfig->media->paths->icon32, $row->path) . $row->filename . '?v=' . $row->version . '"/></div>';
            } else {
                $this->objViewHelper = new ViewHelper();

                $strReturn .= '
                    <div class="icon"><img width="16" height="16" src="' . $this->objViewHelper->getDocIcon($row->extension, 32) . '"/></div>';
            }
            $strReturn .= '
                    <div class="unit"><a href="#" onclick="myDashboard.selectItem(' . $intRootLevelId . ', ' . $intRootLevelGroupId . ', ' . $intId . ', ' . $intParentId . ', ' . $intParentTypeId . ', \'' . $strUrl . '\');">' . htmlentities((($row->title == '' && (isset($row->alternativeTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativeTitle) && $row->alternativeTitle != '') ? $row->alternativeTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default) . '</a></div>
                    <div class="clear"></div>
                </div>';
        }
        return $strReturn;
    }

    /**
     * getComments
     * @param object $objElements
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getComments($objElements)
    {
        $this->core->logger->debug('core->views->helpers->DashboardHelper->getComments()');
        $intShowEntries = 2;

        $strReturn = '';
        $strHidden = '';
        $strEntries = '';
        $intActivityId = 0;

        if (count($objElements) > 0) {
            $intCountElements = count($objElements);

            $strReturn .= '
      		    <div class="comments">';

            $intCounter = 0;
            foreach ($objElements as $objRow) {
                $intActivityId = $objRow->idActivities;

                $strCreator = $objRow->fname . ' ' . $objRow->sname;
                $strAuthor = $strCreator;
                if ($objRow->email != '') {
                    $strAuthor = '<a href="mailto:' . $objRow->email . '">' . $strCreator . '</a>';
                }

                $strCreatorProfile = '/zoolu-statics/images/main/user_default.jpg';
                $blnDefaultImg = true;
                if ($objRow->filename != '') {
                    $strCreatorProfile = $this->core->sysConfig->media->paths->imgbase . $objRow->path . 'icon32/' . $objRow->filename;
                    $blnDefaultImg = false;
                }

                if ($intCounter < ($intCountElements - $intShowEntries)) {
                    $strHidden .= '
                        <div class="entry' . (($intCounter == 0) ? ' first' : '') . '">
                            <div class="img' . (($blnDefaultImg) ? ' default' : '') . '"><img src="' . $strCreatorProfile . '" width="30" height="30" alt="' . $strCreator . '"/></div>
                            <div class="message">
                                <div class="inner">
                                    <div class="author">' . $strAuthor . '</div>
                                    <div class="info"><abbr title="' . date('d.m.Y, H:i', strtotime($objRow->created)) . '">' . $this->calcTimeDifference($objRow->created) . '</abbr></div>
                                    <div class="clear"></div>
                                    <span>' . nl2br(htmlentities($objRow->comment, ENT_COMPAT, $this->core->sysConfig->encoding->default)) . '</span>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>';
                } else {
                    $strEntries .= '
                        <div class="entry' . (($intCounter == 0 && $intCountElements <= $intShowEntries) ? ' first' : '') . '">
                            <div class="img' . (($blnDefaultImg) ? ' default' : '') . '"><img src="' . $strCreatorProfile . '" width="30" height="30" alt="' . $strCreator . '"/></div>
                            <div class="message">
                                <div class="inner">
                                    <div class="author">' . $strAuthor . '</div>
                                    <div class="info"><abbr title="' . date('d.m.Y, H:i', strtotime($objRow->created)) . '">' . $this->calcTimeDifference($objRow->created) . '</abbr></div>
                                    <div class="clear"></div>
                                    <span>' . nl2br(htmlentities($objRow->comment, ENT_COMPAT, $this->core->sysConfig->encoding->default)) . '</span>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>';
                }
                $intCounter++;
            }

            if ($intCountElements > $intShowEntries && $strHidden != '') {
                $strReturn .= '
                    <div class="show" id="commentsMore_' . $intActivityId . '"><a href="#" onclick="myDashboard.showComments(' . $intActivityId . '); return false;">' . sprintf($this->objTranslate->_('show_all_%s_comments'), $intCountElements) . '</a></div>
                    <div id="comments_hidden_' . $intActivityId . '" style="display:none;">
                        ' . $strHidden . '
                    </div>';
            }

            $strReturn .=
                $strEntries . '
                    <div class="clear"></div>
                </div>';
        }

        return $strReturn;
    }

    /**
     * getUserListView
     * @param object $objElements
     * @param array $arrSelectedIds
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getUserListView($objElements, $arrSelectedIds)
    {
        $this->core->logger->debug('core->views->helpers->DashboardHelper->getUserListView()');

        $strReturn = '';

        if (count($objElements) > 0) {

            /**
             * create header of list output
             */
            $strReturn .= '
                <div class="olcontacttop">
                    ' . $this->objTranslate->_('Name') . '
                </div>
                <div class="olcontactitemcontainer">';

            foreach ($objElements as $objRow) {
                $strHidden = '';
                if (array_search($objRow->id, $arrSelectedIds) !== false) {
                    $strHidden = ' style="display:none;"';
                }

                $strCssClassAddon = '';
                if ($objRow->email == '') {
                    $strCssClassAddon = ' noMail';
                }

                $strReturn .= '
                    <div class="olcontactitem' . $strCssClassAddon . '" id="olUserItem' . $objRow->id . '" onclick="myDashboard.addUserItemToListArea(\'olUserItem' . $objRow->id . '\', ' . $objRow->id . '); return false;"' . $strHidden . '>
                        <div class="olcontactleft"></div>
                        <div style="display:none;" id="Remove' . $objRow->id . '" class="itemremovelist"></div>
                        <div class="olcontactitemtitle">' . $objRow->fname . ' ' . $objRow->sname . (($objRow->email == '') ? ' <span>(' . $this->objTranslate->_('no_email') . ')</span>' : '') . '</div>
                        <div class="olcontactright"></div>
                        <div class="clear"></div>
                    </div>';
            }

            /**
             * list footer
             */
            $strReturn .= '
                    <div class="clear"></div>
                </div>
                <div class="olcontactbottom">
                </div>';
        }

        return $strReturn;
    }

    /**
     * getContentView
     * @param object $objElements
     * @param string $strViewType
     * @param string $strOverlayTitle
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     *
    public function getContentView($objElements, $strViewType, $strOverlayTitle){
    $this->core->logger->debug('core->views->helpers->DashboardHelper->getContentView()');

    $strReturn = '';

    $strReturn .= '
    <div id="olContentItems_title" style="display:none;">'.$strOverlayTitle.'</div>';

    return $strReturn;
    }*/

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

    /**
     * calcTimeDifference
     * @param string $strDate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    private function calcTimeDifference($strDate)
    {
        $strReturn = '';

        $arrPeriods = array(
            $this->objTranslate->_('second'),
            $this->objTranslate->_('minute'),
            $this->objTranslate->_('hour'),
            $this->objTranslate->_('day'),
            $this->objTranslate->_('week'),
            $this->objTranslate->_('month'),
            $this->objTranslate->_('year')
        );
        $arrLengths = array('60', '60', '24', '7', '4.35', '12', '10');

        $now = time();
        $date = strtotime($strDate);
        $difference = 0;

        if ($now > $date) {
            $difference = $now - $date;
        }

        for ($j = 0; $difference >= $arrLengths[$j] && $j < count($arrLengths) - 1; $j++) {
            $difference /= $arrLengths[$j];
        }
        $difference = round($difference);

        if ($difference != 1) {
            if ($this->core->intZooluLanguageId == 1) {
                switch ($arrPeriods[$j]) {
                    case $this->objTranslate->_('day'):
                    case $this->objTranslate->_('month'):
                        $arrPeriods[$j] .= 'en';
                        break;
                    default:
                        $arrPeriods[$j] .= 'n';
                        break;
                }
            } else {
                $arrPeriods[$j] .= 's';
            }
        }

        return sprintf($this->objTranslate->_('time_difference'), $difference, $arrPeriods[$j]);
    }
}

?>