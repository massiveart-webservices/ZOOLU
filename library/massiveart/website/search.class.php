<?php

/**
 * Search
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-09: Thomas Schedler
 * 1.1, 2013-08-20: Cornelius Hansjakob
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website
 * @subpackage Search
 */

class Search
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var \Sulu\Search\Search
     */
    protected $search = null;

    const ZO_NODE_SUMMARY = 'zo_node_summary';

    protected $strSearchValue;
    protected $intLimitSearch;
    protected $intLimitLiveSearch;
    protected $intRootLevelId;
    protected $intLanguageId;
    protected $strParentFolderId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
        Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0);
    }

    /**
     * search
     *
     * @return object $objHits
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function search()
    {
        $this->core->logger->debug('massiveart->website->search->search()');

        $this->search = new \Sulu\Search\Search($this->core->sysConfig->search->toArray());
        $this->search->getConfig()->setLanguageId($this->intLanguageId);

        $this->setQuery();

        $this->search->getConfig()->setDataType('page');
        $objHits = $this->search->getQuery()->fetch();

        $this->search->getConfig()->setDataType('global');
        $objGlobalHits = $this->search->getQuery()->fetch();

        if (!empty($objGlobalHits)) {
            $objHits = array_merge($objHits, $objGlobalHits);
            usort($objHits, array($this, 'cmp'));
        }

        return $objHits;
    }

    /**
     * livesearch
     *
     * @return object $objHits
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function livesearch()
    {
        $this->core->logger->debug('massiveart->website->search->livesearch()');

        // TODO : livesearch
    }

    private function setQuery()
    {
        if (strlen($this->strSearchValue) < 3) {
            $this->search->getQuery()->find($this->strSearchValue);
        } else {
            $arrSearchValue = explode(' ', $this->strSearchValue);
            $counter = 0;
            foreach ($arrSearchValue as $strSearchValue) {
                $this->search->getQuery()->find($strSearchValue, self::ZO_NODE_SUMMARY, $counter);

                $strSearchValue = preg_replace('/([^\pL\s\d])/u', '?', $strSearchValue);
                $this->search->getQuery()->orFind($strSearchValue . \Sulu\Search\Query::Q_WILDCARD_MULTI, self::ZO_NODE_SUMMARY, $counter);

                $strSearchValue = str_replace('?', '', $strSearchValue);
                $this->search->getQuery()->orFind($strSearchValue . \Sulu\Search\Query::Q_FUZZY, self::ZO_NODE_SUMMARY, $counter);

                $counter++;
            }
        }

        $this->search->getQuery()
            ->filterBy('languageId', $this->intLanguageId)
            ->filterBy('rootLevelId', $this->intRootLevelId);
    }

    /**
     * compare search hits
     *
     * @param Zend_Search_Lucene_Search_QueryHit $objHitA
     * @param Zend_Search_Lucene_Search_QueryHit $objHitB
     *
     * @return integer
     */
    private function cmp($objHitA, $objHitB)
    {
        if ($objHitA->score == $objHitB->score) {
            return 0;
        }
        return ($objHitA->score < $objHitB->score) ? 1 : -1;
    }

    /**
     * @param $strSearchValue
     *
     * @return $this
     */
    public function setSearchValue($strSearchValue)
    {
        $this->strSearchValue = $strSearchValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearchValue()
    {
        return $this->strSearchValue;
    }

    /**
     * @param $intLimitSearch
     *
     * @return $this
     */
    public function setLimitSearch($intLimitSearch)
    {
        $this->intLimitSearch = $intLimitSearch;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLimitSearch()
    {
        return $this->intLimitSearch;
    }

    /**
     * @param $intLimitLiveSearch
     *
     * @return $this
     */
    public function setLimitLiveSearch($intLimitLiveSearch)
    {
        $this->intLimitLiveSearch = $intLimitLiveSearch;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLimitLiveSearch()
    {
        return $this->intLimitLiveSearch;
    }

    /**
     * @param $intRootLevelId
     *
     * @return $this
     */
    public function setRootLevelId($intRootLevelId)
    {
        $this->intRootLevelId = $intRootLevelId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRootLevelId()
    {
        return $this->intRootLevelId;
    }

    /**
     * @param $intLanguageId
     *
     * @return $this
     */
    public function setLanguageId($intLanguageId)
    {
        $this->intLanguageId = $intLanguageId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguageId()
    {
        return $this->intLanguageId;
    }

    /**
     * @param $strParentFolderId
     *
     * @return $this
     */
    public function setParentFolderId($strParentFolderId)
    {
        $this->strParentFolderId = $strParentFolderId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParentFolderId()
    {
        return $this->strParentFolderId;
    }
}