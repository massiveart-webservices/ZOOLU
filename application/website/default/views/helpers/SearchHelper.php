<?php

/**
 * SearchHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-09-03: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class SearchHelper
{

    /**
     * @var Core
     */
    private $core;

    protected $intSegmentId;
    protected $strSegmentCode;

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
     * getSearchList
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getSearchList($objHits, $strSearchValue, $translate)
    {
        $this->core->logger->debug('website->views->helpers->SearchHelper->getSearchList()');

        $strHtmlOutput = '';

        $strHtmlSearchHeader = '
            <div class="header">
                <p>' . sprintf($translate->_('Search_Result_Text', false), count($objHits), ((count($objHits) == 1) ? $translate->_('Searchresult') : $translate->_('Searchresults')), $strSearchValue) . '</p>
            </div>';

        $strHtmlOutput .= '<div class="searchResultContainer">' . $strHtmlSearchHeader;
        if (count($objHits) > 0) {
            foreach ($objHits as $objHit) {
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
                        $arrParentPages = @unserialize($objHit->parentPages); //FIXME!!!
                        if (is_array($arrParentPages)) {
                            $arrUrls = array();
                            $blnFirst = true;
                            foreach ($arrParentPages as $objEntry) {
                                $arrUrls[$objEntry->getEntryId()] = $objEntry->url . substr($objHit->url, strpos($objHit->url, '/', 1) + 1);
                                if ($blnFirst == true || ($objEntry->entry_category == 0 && $objEntry->entry_label == 0)) {
                                    $strUrl = $arrUrls[$objEntry->getEntryId()];
                                    $blnFirst = false;
                                }
                            }
                        }
                    }

                    if (!empty($this->intSegmentId)) {
                        if (array_search('segmentId', $arrDocFields) && $objHit->segmentId > 0 && $objHit->segmentId != $this->intSegmentId) {

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
                            $strUrl = '/' . $this->strSegmentCode . $strUrl;
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
                        $strIcon = '<div class="img"><img class="img47x47" src="' . $this->core->sysConfig->media->paths->imgbase . $arrPic['path'] . '47x47/' . $arrPic['filename'] . '?v=' . $arrPic['version'] . '"/></div>';
                        //$strIcon = '<div class="img"><img class="img117x88" src="'.$this->core->sysConfig->media->paths->imgbase.$arrPic['path'].'117x88/'.$arrPic['filename'].'?v='.$arrPic['version'].'"/></div>';
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
        echo $strHtmlOutput;
    }

    /**
     * getLiveSearchList
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getLiveSearchList($objHits)
    {
        $this->core->logger->debug('website->views->helpers->SearchHelper->getLiveSearchList()');

        $strHtmlOutput = '';

        if (count($objHits) > 0) {
            $strHtmlOutput .= '<ul id="search_list">';
            foreach ($objHits as $objHit) {
                $objDoc = $objHit->getDocument();
                $arrDocFields = $objDoc->getFieldNames();

                if (array_search('url', $arrDocFields) && array_search('title', $arrDocFields)) {
                    $strTitle = htmlentities($objHit->title, ENT_COMPAT, $this->core->sysConfig->encoding->default);

                    $strUrl = $objHit->url;
                    if (array_search('parentPages', $arrDocFields)) {
                        $arrParentPages = @unserialize($objHit->parentPages); //FIXME!!!
                        if (is_array($arrParentPages)) {
                            $blnFirst = true;
                            foreach ($arrParentPages as $objEntry) {
                                if ($blnFirst == true || ($objEntry->entry_category == 0 && $objEntry->entry_label == 0)) {
                                    $strUrl = $objEntry->url . substr($objHit->url, strpos($objHit->url, '/', 1) + 1);
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

                    $strHtmlOutput .= '<li><a href="' . $strUrl . '"><table cellpadding="0" cellspacing="0"><tr><td class="icon">' . $strIcon . '</td><td class="info"><a href="' . $strUrl . '">' . $strTitle . '</a></td></tr></table></a></li>';
                }
            }
            $strHtmlOutput .= '</ul>';
        } else {
            $strHtmlOutput .= '<ul id="search_list"><li>Sorry, no search result.</li></ul>';
        }

        echo $strHtmlOutput;
    }

    /**
     * setSegmentId
     * @param integer $intSegmentId
     */
    public function setSegmentId($intSegmentId)
    {
        $this->intSegmentId = $intSegmentId;
    }

    /**
     * getSegmentId
     * @param integer $intSegmentId
     */
    public function getSegmentId()
    {
        return $this->intSegmentId;
    }

    /**
     * setSegmentCode
     * @param string $strSegmentCode
     */
    public function setSegmentCode($strSegmentCode)
    {
        $this->strSegmentCode = $strSegmentCode;
    }

    /**
     * getSegmentCode
     * @param string $strSegmentCode
     */
    public function getSegmentCode()
    {
        return $this->strSegmentCode;
    }
}