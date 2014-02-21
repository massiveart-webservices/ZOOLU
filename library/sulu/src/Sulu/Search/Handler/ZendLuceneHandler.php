<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search\Handler;

use Sulu\Search\Hit;
use Sulu\Search\Search;
use Sulu\Search\Query;

class ZendLuceneHandler extends AbstractHandler implements HandlerInterface
{

    /**
     * @var \Zend_Search_Lucene
     */
    private $index;

    /**
     * @var string
     */
    private $path;

    /**
     * @param $key
     * @param $data
     */
    public function add($key, $data)
    {

        if ($this->getIndex() !== false) {

            \Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive()
            );

            $doc = new \Zend_Search_Lucene_Document();

            // define key of document
            $doc->addField(\Zend_Search_Lucene_Field::keyword('key', $key));

            // add fields to document
            if (count($data) > 0) {
                foreach ($data as $fieldId => $fieldData) {
                    $doc = $this->addFieldToDocument($fieldId, $fieldData, $doc);
                }

                $this->indexNodeSummaryNow($doc);
            }

            // add document to the index.
            $this->index->addDocument($doc);

            $this->index->optimize();
        }
    }

    /**
     * @return array|null
     */
    public function fetch()
    {
        $this->clearIndex();

        if ($this->getIndex(false) !== false) {

            \Zend_Search_Lucene_Analysis_Analyzer::setDefault(
                new \Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive()
            );
            \Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0);

            $query = \Zend_Search_Lucene_Search_QueryParser::parse(
                $this->renderQuery(),
                $this->config->getValue('encoding')
            );

            $resultSet = $this->index->find($query);;
            if (count($resultSet) > 0) {
                $hits = array();

                foreach ($resultSet as $result) {
                    /** @var \Zend_Search_Lucene_Search_QueryHit $result */
                    $fieldNames = $result->getDocument()->getFieldNames();
                    $data = array();
                    foreach ($fieldNames as $fieldName) {
                        $data[$fieldName] = $result->getDocument()->getFieldValue($fieldName);
                    }

                    $hits[] = new Hit($result->score, array_merge(
                        array(
                            '_id' => $result->id,
                            '_type' => $this->type,
                        ),
                        $data
                    ));
                }
                return $hits;
            } else {
                return null;
            }
        }

        return null;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {

        $indexRootPath = $this->clearRawPathLanguage($this->clearRawPathRoot($this->getRawIndexPath()));

        if (is_dir($indexRootPath)) {
            $folders = array_diff(scandir($indexRootPath), array('.', '..'));
            if ($folders > 0) {
                foreach ($folders as $folder) {
                    if (is_dir($indexRootPath . '/' . $folder)) {
                        $this->index = \Zend_Search_Lucene::open($indexRootPath . '/' . $folder);

                        $term = new \Zend_Search_Lucene_Index_Term($key, 'key');
                        $query = (strpos($key, '*') !== false) ? new \Zend_Search_Lucene_Search_Query_Wildcard($term) :
                            new \Zend_Search_Lucene_Search_Query_Term($term);

                        // find hits via query in index
                        $hits = $this->index->find($query);

                        // delete hits
                        if (count($hits) > 0) {
                            foreach ($hits as $hit) {
                                $this->index->delete($hit->id);
                            }
                        }

                        $this->index->commit();

                        $this->index->optimize();
                    }
                }
            }
        }
    }

    /**
     * @param $field
     * @param $type
     * @param null $value
     * @param int $group
     * @param bool $bool
     */
    public function where($value, $type, $field = null, $group = 0, $bool = true)
    {
        if (!empty($value)) {

            switch ($type) {
                case Query::Q_WILDCARD:
                    if (false === strpos($value, Query::Q_WILDCARD)) {
                        $value .= '*';
                    }
                    break;
                case Query::Q_FUZZY:
                    if (false === strpos($value, Query::Q_FUZZY)) {
                        $value .= Query::Q_FUZZY;
                    }
                    break;
            }

            $cond = '';
            if (isset($this->queries[$group])) {
                if ($bool === true) {
                    $cond = Query::Q_AND . ' ';
                } else {
                    $cond = Query::Q_OR . ' ';
                }
            }

            if (!empty($field)) {
                $this->queries[$group][] = $cond . $field . ':' . $value;
            } else {
                $this->queries[$group][] = $cond . $value;
            }
        }
    }

    /**
     * @param $field
     * @param null $value
     */
    public function filter($field, $value = null)
    {
        if (!empty($value)) {
            $this->filters[] = $field . ':' . $value;
        }
    }

    public function clear()
    {
        $this->queries = null;
        $this->filters = null;
    }

    /**
     * @return string
     */
    protected function renderQuery()
    {
        $query = '';
        if (count($this->queries) > 0) {
            foreach ($this->queries as $group => $data) {
                $query .= '+(';
                if (is_array($data) && count($data) > 0) {
                    foreach ($data as $line) {
                        $query .= $line . ' ';
                    }
                } else {
                    if (is_string($data)) {
                        $query .= $data;
                    }
                }
                $query .= ') ';
            }
        }

        if (count($this->filters) > 0) {
            foreach ($this->filters as $filter) {
                $query .= '+(' . $filter . ') ';
            }
        }

        return trim($query);
    }

    /**
     * @param bool $doCreate
     *
     * @return \Zend_Search_Lucene|bool
     */
    protected function getIndex($doCreate = true)
    {
        // init index path
        $this->getPath();

        if (!is_object($this->index) || !($this->index instanceof \Zend_Search_Lucene)) {
            if (is_dir($this->path) && count(scandir($this->path)) > 4) {
                $this->index = \Zend_Search_Lucene::open($this->path);
            } else {
                if (true === $doCreate) {
                    $this->index = \Zend_Search_Lucene::create($this->path);
                } else {
                    return false;
                }
            }
        }

        return $this->index;
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        // get path from config
        if (empty($this->path)) {
            $this->path = $this->getRawIndexPath();
        }

        // clean replacers in config path
        $this->cleanPath();

        return $this->path;
    }

    protected function getRawIndexPath()
    {
        $indexCfg = $this->config->getValue('index');

        if (array_key_exists('path', $indexCfg)) {
            if (array_key_exists($this->getType(true), $indexCfg['path'])) {
                return $indexCfg['path'][$this->getType()];
            } else {
                if (array_key_exists(self::TYPE_PAGE, $indexCfg['path'])) {
                    // default type page
                    return $this->path = $indexCfg['path'][self::TYPE_PAGE];
                }
            }
        }
    }

    protected function cleanPath()
    {
        $this->path = $this->clearRawPathRoot($this->path);
        $this->path = $this->clearRawPathLanguage($this->path, $this->getLanguageId());
    }

    protected function clearRawPathRoot($path)
    {
        // replace placeholder: {ROOT_PATH}
        if (strpos($path, '{ROOT_PATH}') !== false) {
            return str_replace('{ROOT_PATH}', GLOBAL_ROOT_PATH, $path);
        }
    }

    protected function clearRawPathLanguage($path, $languageId = null)
    {
        // replace placeholder: {LANG_ID}
        if (strpos($path, '{LANG_ID}') !== false) {
            if (null !== $languageId) {
                return str_replace('{LANG_ID}', '/' . sprintf('%02d', $languageId), $path);
            } else {
                return str_replace('{LANG_ID}', '', $path);
            }
        }
    }

    protected function clearIndex()
    {
        $this->path = null;
        $this->index = null;
    }

    /**
     * @param string $key
     * @param array $data
     * @param \Zend_Search_Lucene_Document $doc
     *
     * @return \Zend_Sea    rch_Lucene_Document
     */
    protected function addFieldToDocument($key, $data, \Zend_Search_Lucene_Document $doc)
    {
        // check if field has value (only add field to document when value exists)
        if (is_array($data) && array_key_exists('value', $data)) {

            // check for params
            $searchFieldTypeId = null;
            if (array_key_exists('params', $data)) {
                $params = $data['params'];

                // check for param: searchFieldTypeId
                if (array_key_exists('searchFieldTypeId', $params)) {
                    $searchFieldTypeId = $params['searchFieldTypeId'];
                }
            }

            $addToNodeSummary = true;
            if (!empty($searchFieldTypeId)) {
                switch ($searchFieldTypeId) {
                    case Search::FIELD_TYPE_KEYWORD:
                        $doc->addField(
                            \Zend_Search_Lucene_Field::keyword(
                                $key,
                                $data['value'],
                                $this->config->getValue('encoding')
                            )
                        );
                        break;
                    case Search::FIELD_TYPE_UNINDEXED: // for redisplay, not for searching
                        $addToNodeSummary = false;
                        $doc->addField(
                            \Zend_Search_Lucene_Field::unIndexed(
                                $key,
                                $data['value'],
                                $this->config->getValue('encoding')
                            )
                        );
                        break;
                    case Search::FIELD_TYPE_BINARY:
                        $addToNodeSummary = false;
                        $doc->addField(
                            \Zend_Search_Lucene_Field::binary($key, $data['value'], $this->config->getValue('encoding'))
                        );
                        break;
                    case Search::FIELD_TYPE_TEXT:
                        $doc->addField(
                            \Zend_Search_Lucene_Field::text($key, $data['value'], $this->config->getValue('encoding'))
                        );
                        break;
                    case Search::FIELD_TYPE_UNSTORED: // for searching, not for redisplay
                        $doc->addField(
                            \Zend_Search_Lucene_Field::unStored(
                                $key,
                                $data['value'],
                                $this->config->getValue('encoding')
                            )
                        );
                        break;
                }
            } else {
                // TODO : define default index field
            }

            // add all field values to node summary too
            if ($addToNodeSummary === true) {
                $this->addToNodeSummary($data['value']);
            }
        }

        return $doc;
    }

    /**
     * @param $value
     */
    protected function addToNodeSummary($value)
    {
        $this->nodeSummary .= ' ' . $value;
    }

    /**
     * @param \Zend_Search_Lucene_Document $doc
     *
     * @return \Zend_Search_Lucene_Document
     */
    protected function indexNodeSummaryNow(\Zend_Search_Lucene_Document $doc)
    {
        $doc->addField(
            \Zend_Search_Lucene_Field::unStored(
                Search::NODE_SUMMARY,
                $this->nodeSummary,
                $this->config->getValue('encoding')
            )
        );
        return $doc;
    }

}
