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
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive()
        );
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

        if (null !== $objGlobalHits && !empty($objGlobalHits)) {
            if (null !== $objHits && !empty($objHits)) {
                $objHits = array_merge($objHits, $objGlobalHits);
                usort($objHits, array($this, 'cmp'));
            } else {
                $objHits = $objGlobalHits;
            }
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
            $this->search->getQuery()->where($this->strSearchValue, \Sulu\Search\Query::Q_MATCH);
        } else {
            $arrSearchValue = explode(' ', $this->strSearchValue);
            $counter = 0;
            foreach ($arrSearchValue as $strSearchValue) {
                $this->search->getQuery()->where(
                    $strSearchValue,
                    \Sulu\Search\Query::Q_MATCH,
                    \Sulu\Search\Search::NODE_SUMMARY,
                    $counter
                );

                $strSearchValue = preg_replace('/([^\pL\s\d])/u', '?', $strSearchValue);
                $this->search->getQuery()->orWhere(
                    $strSearchValue,
                    \Sulu\Search\Query::Q_WILDCARD,
                    \Sulu\Search\Search::NODE_SUMMARY,
                    $counter
                );

                $strSearchValue = str_replace('?', '', $strSearchValue);
                $this->search->getQuery()->orWhere(
                    $strSearchValue,
                    \Sulu\Search\Query::Q_FUZZY,
                    \Sulu\Search\Search::NODE_SUMMARY,
                    $counter
                );

                $counter++;
            }
        }

        $this->search->getQuery()
            ->filterBy('languageId', (int)$this->intLanguageId)
            ->filterBy('rootLevelId', (int)$this->intRootLevelId);
    }

    /**
     * compare search hits
     *
     * @param \Sulu\Search\Hit $objHitA
     * @param \Sulu\Search\Hit $objHitB
     *
     * @return integer
     */
    private function cmp($objHitA, $objHitB)
    {
        if ($objHitA->score() === $objHitB->score()) {
            return 0;
        }

        return ($objHitA->score() < $objHitB->score()) ? 1 : -1;
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
