<?php
/**
 * ZOOLU - Community Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * SearchHelper
 *
 * Version History (please keep backward compatible):
 * 1.0, 2013-08-05: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class SearchHelper
{
    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Zend_Translate
     */
    protected $translate;

    /**
     * @var string
     */
    protected $strMetaTitle;

    /**
     * @var string
     */
    protected $strMetaKeywords;

    /**
     * @var string
     */
    protected $strMetaDescription;

    /**
     * @var array
     */
    protected $arrMetaRobots = array();

    /**
     * @var string
     */
    protected $strBottomContent = '';

    /**
     * @var string
     */
    protected $strDomLoadedJs = '';

    /**
     * @var string
     */
    protected $strTheme;

    /**
     * @var
     */
    protected $hits;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $base;

    /**
     * @var int
     */
    protected $segmentId;

    /**
     * @var string
     */
    protected $segmentCode;

    /**
     * @var bool
     */
    protected $hasSegments;

    /**
     * @var int
     */
    protected $languageDefinitionType;

    /**
     * @param bool $blnRequireFunctionWrapper
     */
    public function __construct($blnRequireFunctionWrapper = true)
    {
        $this->core = Zend_Registry::get('Core');

        if ($blnRequireFunctionWrapper == true) {
            require_once(dirname(__FILE__) . '/search.inc.php');
        }
    }

    /**
     * getMetaTitle
     *
     * @param string $strTag
     *
     * @return string $strReturn
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getMetaTitle($strTag = '')
    {
        $strReturn = '';

        if ($this->strMetaTitle != '') {
            if ($strTag != '') $strReturn .= '<' . $strTag . '>';
            $strReturn .= htmlentities($this->strMetaTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default);
            if ($strTag != '') $strReturn .= '</' . $strTag . '>';
        }

        return $strReturn;
    }

    /**
     * @param $strMetaTitle
     *
     * @return $this
     */
    public function setMetaTitle($strMetaTitle)
    {
        $this->strMetaTitle = $strMetaTitle;
        return $this;
    }

    /**
     * getMetaDescription
     *
     * @return string
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getMetaDescription()
    {
        $strReturn = '';
        if ($this->strMetaDescription != '') {
            $strReturn .= '<meta name="description" content="' . htmlentities($this->strMetaDescription, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '"/>';
        }
        return $strReturn;
    }

    /**
     * @param $strMetaDescription
     *
     * @return $this
     */
    public function setMetaDescription($strMetaDescription)
    {
        $this->strMetaDescription = $strMetaDescription;
        return $this;
    }

    /**
     * getMetaKeywords
     *
     * @return string $strReturn
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getMetaKeywords()
    {
        $strReturn = '';

        if ($this->strMetaKeywords != '') {
            $strReturn .= '<meta name="keywords" content="' . trim($this->strMetaKeywords, ', ') . '"/>';
        }
        return $strReturn;
    }

    /**
     * getMetaRobots
     *
     * @return string $strReturn
     * @author Mathias Ober <mob@massiveart.com>
     * @version 1.0
     */
    public function getMetaRobots()
    {

        if (!empty($this->arrMetaRobots)) {
            $strReturn = '<meta name="robots" content="' . $this->arrMetaRobots[0] . ', ' . $this->arrMetaRobots[1] . '"/>';
        } else {
            $strReturn = '<meta name="robots" content="noindex,nofollow"/>';
        }

        return $strReturn;
    }

    /**
     * @param $strMetaIndex
     * @param $strMetaFollow
     *
     * @return $this
     */
    public function setMetaRobots($strMetaIndex, $strMetaFollow)
    {
        $this->arrMetaRobots[0] = $strMetaIndex;
        $this->arrMetaRobots[1] = $strMetaFollow;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchList()
    {
        $strHtmlOutput = '';

        $strHtmlSearchHeader = '
            <div class="header">
                <p>' . sprintf($this->translate->_('Search_Result_Text', false), count($this->hits), ((count($this->hits) == 1) ? $this->translate->_('Searchresult') : $this->translate->_('Searchresults')), $this->subject) . '</p>
            </div>';

        $strHtmlOutput .= '<div class="searchResultContainer">' . $strHtmlSearchHeader;

        if (count($this->hits) > 0) {
            foreach ($this->hits as $objHit) {
                $objDoc = $objHit->getDocument();
                $arrDocFields = $objDoc->getFieldNames();

                $strHtmlOutput .= '<div class="item">';

                if (array_search('url', $arrDocFields) && array_search('title', $arrDocFields)) {
                    $strTitle = '';
                    if (array_search('articletitle', $arrDocFields) && $objHit->articletitle != '') {
                        $strTitle = htmlentities($objHit->articletitle, ENT_COMPAT, $this->core->sysConfig->encoding->default);
                    } else {
                        $strTitle = htmlentities($objHit->title, ENT_COMPAT, $this->core->sysConfig->encoding->default);
                    }

                    $strUrl = $objHit->url;
                    $arrUrls = array($objHit->url);
                    if (array_search('parentPages', $arrDocFields)) {
                        $arrParentPages = @unserialize(base64_decode($objHit->parentPages)); //FIXME!!!
                        if (is_array($arrParentPages)) {
                            $arrUrls = array();
                            $blnFirst = true;
                            foreach ($arrParentPages as $objEntry) {
                                if (array_search('parentFolderId', $arrDocFields) && $objHit->parentFolderId == $objEntry->entry_point) {
                                    $arrUrls[$objEntry->getEntryId()] = $objEntry->url;
                                } else {
                                    $arrUrls[$objEntry->getEntryId()] = $objEntry->url . substr($objHit->url, strpos($objHit->url, '/', 1) + 1);
                                }
                                if ($blnFirst == true || ($objEntry->entry_category == 0 && $objEntry->entry_label == 0)) {
                                    $strUrl = $arrUrls[$objEntry->getEntryId()];
                                    if ($this->languageDefinitionType == $this->core->config->language_definition->folder) {
                                        $strUrl = '/' . $this->core->strLanguageCode . $strUrl;
                                    }
                                    $blnFirst = false;
                                }
                            }
                        }
                    }

                    if (!empty($this->segmentId)) {
                        if (array_search('segmentId', $arrDocFields) && $objHit->segmentId > 0 && $objHit->segmentId != $this->segmentId) {

                            $arrPortals = $this->core->config->portals->toArray();

                            if (array_key_exists('id', $arrPortals['portal'])) {
                                $arrPortals = array($arrPortals['portal']);
                            } else {
                                $arrPortals = $arrPortals['portal'];
                            }

                            foreach ($arrPortals as $arrPortal) {
                                if (array_key_exists('id', $arrPortal['segment'])) {
                                    $arrSegments = array($arrPortal['segment']);
                                } else {
                                    $arrSegments = $arrPortal['segment'];
                                }

                                $arrDefaultSegment = null;
                                foreach ($arrSegments as $arrSegment) {
                                    if (array_key_exists('id', $arrSegment) && $arrSegment['id'] == $objHit->segmentId) {
                                        $strUrl = '/' . $arrSegment['code'] . $strUrl;
                                        break 2;
                                    }
                                }
                            }
                        } else {
                            $strUrl = '/' . $this->segmentCode . $strUrl;
                        }
                    }


                    $arrPics = array();
                    if (array_search('pic_shortdescription', $arrDocFields)) {
                        $arrPics = unserialize($objHit->pic_shortdescription);

                    } elseif (array_search('mainpics', $arrDocFields)) {
                        $arrPics = unserialize($objHit->mainpics);
                    }

                    $strIcon = '';
                    if (count($arrPics) > 0) {
                        $arrPic = current($arrPics);
                        $strIcon = '<div class="img"><img class="img47x47" src="' . $this->core->sysConfig->media->paths->imgbase . $arrPic['path'] . 'icon32/' . $arrPic['filename'] . '?v=' . $arrPic['version'] . '"/></div>';
                    }
                    $strHtmlOutput .= $strIcon;
                    $strHtmlOutput .= '<div class="info">
                              <div class="title"><a href="' . $strUrl . '">' . $strTitle . '</a></div>';
                    if (array_search('shortdescription', $arrDocFields) && $objHit->shortdescription != '') {
                        $this->core->logger->debug($objHit->shortdescription);
                        $strHtmlOutput .= '<div class="description">' . strip_tags($objHit->shortdescription, '<p>') . '</div>';
                    }
                    //foreach($arrUrls as $strUrl){
                    $strHtmlOutput .= '<div class="url"><a href="http://' . $_SERVER['HTTP_HOST'] . $strUrl . '">http://' . $_SERVER['HTTP_HOST'] . $strUrl . '</a></div>';
                    //}
                    $strHtmlOutput .= '</div>
                          <div class="clear"></div>';
                }
                $strHtmlOutput .= '</div>';
            }
        }
        $strHtmlOutput .= '
                <div class="clear"></div>
            </div>';

        return $strHtmlOutput;
    }

    /**
     * @return string
     */
    public function getLiveSearchList() // $objHits, $translate, $intLanguageDefinitionType = 1, $searchBase, $searchTerm
    {
        $strHtmlOutput = '';

        if (count($this->hits) > 0) {
            $strHtmlOutput .= '<ul id="search_list">';
            foreach ($this->hits as $objHit) {
                $objDoc = $objHit->getDocument();
                $arrDocFields = $objDoc->getFieldNames();

                if (array_search('url', $arrDocFields) && array_search('title', $arrDocFields)) {
                    $strTitle = htmlentities($objHit->title, ENT_COMPAT, $this->core->sysConfig->encoding->default);

                    $strUrl = $objHit->url;
                    if (array_search('parentPages', $arrDocFields)) {
                        $arrParentPages = @unserialize(base64_decode($objHit->parentPages)); //FIXME!!!
                        if (is_array($arrParentPages)) {
                            $arrUrls = array();
                            $blnFirst = true;
                            foreach ($arrParentPages as $objEntry) {
                                if (array_search('parentFolderId', $arrDocFields) && $objHit->parentFolderId == $objEntry->entry_point) {
                                    $arrUrls[$objEntry->getEntryId()] = $objEntry->url;
                                } else {
                                    $arrUrls[$objEntry->getEntryId()] = $objEntry->url . substr($objHit->url, strpos($objHit->url, '/', 1) + 1);
                                }
                                if ($blnFirst == true || ($objEntry->entry_category == 0 && $objEntry->entry_label == 0)) {
                                    $strUrl = $arrUrls[$objEntry->getEntryId()];
                                    if ($this->languageDefinitionType == $this->core->config->language_definition->folder) {
                                        $strUrl = '/' . $this->core->strLanguageCode . $strUrl;
                                    }
                                    $blnFirst = false;
                                }
                            }
                        }
                    }

                    $arrPics = array();
                    if (array_search('pic_shortdescription', $arrDocFields)) {
                        $arrPics = unserialize($objHit->pic_shortdescription);

                    } elseif (array_search('mainpics', $arrDocFields)) {
                        $arrPics = unserialize($objHit->mainpics);
                    }

                    $strIcon = '';
                    if (count($arrPics) > 0) {
                        $arrPic = current($arrPics);
                        $strIcon = '<img src="' . sprintf($this->core->sysConfig->media->paths->icon32, $arrPic['path']) . $arrPic['filename'] . '?v=' . $arrPic['version'] . '"/>';
                    }

                    $strHtmlOutput .= '<li>
                                           <a href="#" onclick="
                                              _gaq.push([\'_set\', \'hitCallback\', function(){ window.location.href=\'' . $strUrl . '\'; }]);
                                              _gaq.push([\'_trackPageview\', \'' . $searchBase . '?q=' . $this->subject . '\']);
                                              return false;
                                              ">
                                               <table cellpadding="0" cellspacing="0">
                                                   <tr>
                                                       <td class="icon">' . $strIcon . '</td>
                                                       <td class="info"><a href="' . $strUrl . '">' . $strTitle . '</a></td>
                                                   </tr>
                                               </table>
                                           </a>
                                       </li>';
                }
            }
            $strHtmlOutput .= '</ul>';
        } else {
            $strHtmlOutput .= '<ul id="search_list"><li>' . $this->translate->_('Sorry, no search result.') . '</li></ul>';
        }

        return $strHtmlOutput;
    }

    /**
     * @param $strTheme
     *
     * @return $this
     */
    public function setTheme($strTheme)
    {
        $this->strTheme = $strTheme;
        return $this;
    }

    /**
     * @return string
     */
    public function Theme()
    {
        return $this->strTheme;
    }

    /**
     * @param $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param $hits
     *
     * @return $this
     */
    public function setHits($hits)
    {
        $this->hits = $hits;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * @param $strBottomContent
     *
     * @return $this
     */
    public function setBottomContent($strBottomContent)
    {
        $this->strBottomContent = $strBottomContent;
        return $this;
    }

    /**
     * @return string
     */
    public function getBottomContent()
    {
        return $this->strBottomContent;
    }

    /**
     * @param $strDomLoadedJs
     *
     * @return $this
     */
    public function setDomLoadedJs($strDomLoadedJs)
    {
        $this->strDomLoadedJs = strDomLoadedJs;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomLoadedJs()
    {
        return $this->strDomLoadedJs;
    }

    /**
     * @param Zend_Translate $translate
     *
     * @return $this
     */
    public function setTranslate(Zend_Translate $translate)
    {
        $this->translate = $translate;
        return $this;
    }

    /**
     * @return Zend_Translate
     */
    public function getTranslate()
    {
        return $this->translate;
    }

    /**
     * @param $hasSegments
     *
     * @return $this
     */
    public function setHasSegments($hasSegments)
    {
        $this->hasSegments = $hasSegments;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHasSegments()
    {
        return $this->hasSegments;
    }

    /**
     * @param $segmentCode
     *
     * @return $this
     */
    public function setSegmentCode($segmentCode)
    {
        $this->segmentCode = $segmentCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSegmentCode()
    {
        return $this->segmentCode;
    }

    /**
     * @param $segmentId
     *
     * @return $this
     */
    public function setSegmentId($segmentId)
    {
        $this->segmentId = $segmentId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSegmentId()
    {
        return $this->segmentId;
    }

    /**
     * @param $languageDefinitionType
     *
     * @return $this
     */
    public function setLanguageDefinitionType($languageDefinitionType)
    {
        $this->languageDefinitionType = $languageDefinitionType;
        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageDefinitionType()
    {
        return $this->languageDefinitionType;
    }

    /**
     * @param $base
     *
     * @return $this
     */
    public function setBase($base)
    {
        $this->base = $base;
        return $this;
    }

    /**
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }
}