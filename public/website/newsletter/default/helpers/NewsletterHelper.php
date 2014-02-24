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
 * @package    application.zoolu.modules.newsletter.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NewsletterHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-06-17: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
class NewsletterHelper {

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;
    
    /**
     * @var Zend_Translate
     */
    protected $objTranslate;
    
    /**
     * @var String
     */
    protected $languageCode;
    
    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * @var GenericSetup
     */
    protected $objGenericSetup;

    public function __construct() {
        $this->core = Zend_Registry::get('Core');
    }

    public function setNewsletter(GenericSetup $objGenericSetup) {
        $this->objGenericSetup = $objGenericSetup;
    }
    
    /**
     * getPreviewLink
     */
    public function getPreviewLink() {
        $previewLink = '';
        $baseportalJSON = $this->objGenericSetup->getField('baseportal')->getValue();
        $baseportal = json_decode($baseportalJSON);
        $rootLevelId = $baseportal->rootlevel;
        
        $objUrl = $this->getModelRootLevels()->loadRootLevelUrl($rootLevelId);
        $previewLink = '<a href="http://' . $objUrl->url . '/zoolu-website/newsletter/preview/?id=' . $this->objGenericSetup->getElementId() . '&salutation=*|SALUTATION|*&fname=*|FNAME|*&lname=*|LNAME|*&unsub=*|UNSUB|*&language=' . $this->languageCode . '">'
                        . $this->objTranslate->_('Newsletter_preview') .
                       '</a>';
        return $previewLink;
    }

    /**
     * getSalutation
     * @return string
     */
    public function getSalutation() {
        $strSalutation = (isset($_GET['salutation'])) ? $_GET['salutation'] : null;
        $strFname = (isset($_GET['fname'])) ? $_GET['fname'] : null;
        $strLname = (isset($_GET['lname'])) ? $_GET['lname'] : null;
        $strReturn = '';
        $strReturn .= ($strSalutation == null) ? '*|SALUTATION|* ' : $strSalutation . ' ';
        $strReturn .= ($strFname == null) ? '*|FNAME|* ' : $strFname . ' ';
        $strReturn .= ($strLname == null) ? '*|LNAME|*' : $strLname . ' ';
        $strReturn .= ', <br />';
        return $strReturn;
    }

    /**
     * getTitle
     * @param string $strTag
     * @param boolean $blnTitleFallback
     * @return string $strReturn
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTitle($strTag) {
        $strReturn = '';

        if ($strTag != '')
            $strReturn .= '<' . $strTag . '>';
        $strReturn .= htmlentities($this->objGenericSetup->getField('title')->getValue(), ENT_COMPAT, $this->core->sysConfig->encoding->default);
        if ($strTag != '')
            $strReturn .= '</' . $strTag . '>';
        return $strReturn;
    }

    /**
     * getArticle
     * @return string
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getArticle() {
        return $this->objGenericSetup->getField('article')->getValue();
    }

    /**
     * getTextBlocks
     * @return string $strReturn
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTextBlocks($strImageFolder) {
        $strReturn = '';

        $objMyMultiRegion = $this->objGenericSetup->getRegion(98); // 98 is the default textblock region for newsletter

        if ($objMyMultiRegion instanceof GenericElementRegion) {
            foreach ($objMyMultiRegion->RegionInstanceIds() as $intRegionInstanceId) {
                $strTextBlock = '';

                $strBlockTitle = htmlentities($objMyMultiRegion->getField('block_title')->getInstanceValue($intRegionInstanceId), ENT_COMPAT, $this->core->sysConfig->encoding->default);
                $strBlockDescription = $objMyMultiRegion->getField('block_description')->getInstanceValue($intRegionInstanceId);
                if ($strBlockTitle != '' || $strBlockDescription != '') {
                    if ($strBlockTitle != '')
                        $strTextBlock .= '<tr><td><h3>' . $strBlockTitle . '</h3></td></tr>';

                    $strFileIds = $objMyMultiRegion->getField('block_pics')->getInstanceValue($intRegionInstanceId);
                    $objFiles = ($strFileIds != '') ? $this->getModelFiles()->loadFilesById($strFileIds) : '';
                    $objDisplayOption = json_decode(str_replace("'", '"', $objMyMultiRegion->getField('block_pics')->getInstanceProperty($intRegionInstanceId, 'display_option')));

                    if (!isset($objDisplayOption->position) || $objDisplayOption->position == null)
                        $objDisplayOption->position = 'LEFT_MIDDLE';
                    if (!isset($objDisplayOption->size) || $objDisplayOption->size == null)
                        $objDisplayOption->size = $strImageFolder;

                    $strHtmlOutputImage = '<td>';

                    if ($objFiles != '' && count($objFiles) > 0) {
                        $intImgCounter = 0;
                        foreach ($objFiles as $objFile) {
                            $intImgCounter++;
                            $strHtmlOutputImage .= '<img class="img' . $objDisplayOption->size . '" style="display:block;" src="http://' . $_SERVER['SERVER_NAME'] . $this->core->sysConfig->media->paths->imgbase . $objFile->path . $objDisplayOption->size . '/' . $objFile->filename . '?v=' . $objFile->version . '" alt="' . $objFile->title . '" title="' . $objFile->title . '"/>';
                        }
                    }
                    $strHtmlOutputImage .= '</td>';

                    $strHtmlOutputContent = '';
                    if ($objFiles != '' && count($objFiles) > 0)
                        $strHtmlOutputContent .= '<td class="description">' . $strBlockDescription . '</td>';
                    else
                        $strHtmlOutputContent .= '<td colspan="2" class="description">' . $strBlockDescription . '</td>';

                    if ($objFiles != '' && count($objFiles) > 0) {
                        switch ($objDisplayOption->position) {
                            case Image::POSITION_RIGHT_MIDDLE:
                                $strTextBlock .= '<tr>' . $strHtmlOutputContent . $strHtmlOutputImage . '</tr>';
                                break;
                            case Image::POSITION_CENTER_BOTTOM:
                                $strTextBlock .= '<tr>' . $strHtmlOutputContent . '</tr><tr>' . $strHtmlOutputImage . '</tr>';
                                break;
                            case Image::POSITION_CENTER_TOP:
                                $strTextBlock .= '<tr>' . $strHtmlOutputImage . '</tr><tr>' . $strHtmlOutputContent . '</tr>';
                                break;
                            case Image::POSITION_LEFT_MIDDLE:
                            default:
                                $this->core->logger->debug($strHtmlOutputImage);
                                $strTextBlock .= '<tr>' . $strHtmlOutputImage . $strHtmlOutputContent . '</tr>';
                                break;
                        }
                    } else {
                        $strTextBlock .= $strHtmlOutputContent;
                    }
                }
                $strReturn .= $strTextBlock;
            }
        }
        return $strReturn;
    }
    
    /**
     * getUnsubscribeLink
     */
    public function getUnsubscribeLink() {
        $unsub = (isset($_GET['unsub'])) ? $_GET['unsub'] : '*|UNSUB|*';
        return '<a href="'. $unsub . '">' . $this->objTranslate->_('Unsubscribe') . '</a>';
    }

    /**
     * getModelFiles
     * @return Model_Files
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelFiles() {
        if (null === $this->objModelFiles) {
            /**
             * autoload only handles "library" components.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Files.php';
            $this->objModelFiles = new Model_Files();
            $this->objModelFiles->setLanguageId($this->core->intLanguageId);
        }

        return $this->objModelFiles;
    }
    
    /**
     * setTranslate
     * @param Zend_Translate $objTranslate
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function setTranslate(Zend_Translate $objTranslate){
        $this->objTranslate = $objTranslate;
    }
    
    /**
     * setLanguageCode
     * @param String languageCode
     */
    public function setLanguageCode($languageCode){
        $this->languageCode = $languageCode;
    }
    
    /**
     * getModelRootLevels
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     * @return Model_RootLevels
     */
    protected function getModelRootLevels()
    {
        if (null === $this->objModelRootLevels) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/RootLevels.php';
            $this->objModelRootLevels = new Model_RootLevels();
        }
        return $this->objModelRootLevels;
    }
}

?>