<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search\Handler;

use Elastica\Client;
use Elastica\Document;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\Term;
use Elastica\Index;
use Elastica\Query\Bool;
use Elastica\Query\Common;
use Elastica\Query\Fuzzy;
use Elastica\Query\Match;
use Elastica\Query\QueryString;
use Elastica\Query\Text;
use Elastica\Query\Wildcard;
use Elastica\Result;
use Sulu\Search\Hit;
use Sulu\Search\Search;
use Sulu\Search\Query;

class ElasticaHandler extends AbstractHandler implements HandlerInterface
{

    /**
     * @var \Elastica\Index
     */
    private $index;

    /**
     * @var \Elastica\Client
     */
    private $client;

    /**
     * add document to index
     * @param $key
     * @param $data
     */
    public function add($key, $data)
    {
        if ($this->getIndex() !== false) {

            // create a type
            $type = $this->getIndex()->getType($this->getType());

            // First parameter is the id of document.
            $doc = new Document($key);

            // add fields to document
            if (count($data) > 0) {
                foreach ($data as $fieldId => $fieldData) {
                    $this->addFieldToDocument($fieldId, $fieldData, $doc);
                }

                if ('' !== strip_tags($this->nodeSummary)) {
                    $doc->set(Search::ZO_NODE_SUMMARY, strip_tags($this->nodeSummary));
                }
            }

            // Add tweet to type
            $type->addDocument($doc);
        }
    }

    /**
     * delete indexed document
     * @param $key
     */
    public function delete($key)
    {
        if ($this->getIndex() !== false) {
            // TODO: Implement delete() method.
        }
    }

    /**
     * add where statement
     * @param $value
     * @param null $field
     * @param int $group
     * @param bool $bool
     */
    public function where($value, $field = null, $group = 0, $bool = true)
    {
        if (!empty($value)) {
            if (null !== $field) {
                //$query = new Common($field, $value, 0.001);
                $query = new Match();
                $query->setField($field, $value);
            } else {
                $query = new QueryString();
                $query->setQuery($value);
            }

            $this->queries[] = $query;
        }
    }

    /**
     * filter search result by
     * @param $field
     * @param null $value
     */
    public function filter($field, $value = null)
    {
        if (!empty($value)) {
            $filter = new Term();
            $filter->setTerm($field, $value);
            $this->filters[] = $filter;
        }
    }

    /**
     * fetch query result
     * @return array|null
     */
    public function fetch()
    {
        if ($this->getIndex() !== false) {
            $type = $this->getIndex()->getType($this->getType(true));

            $resultSet = $type->search($this->buildQuery());
            if ($resultSet->getTotalHits() > 0) {
                $hits = array();

                foreach ($resultSet->getResults() as $result) {
                    /** @var Result $result */
                    $hits[] = new Hit($result->getScore(), array_merge(
                        array(
                            '_id' => $result->getId(),
                            '_type' => $result->getType(),
                        ),
                        $result->getData()
                    ));
                }
                return $hits;
            } else {
                return null;
            }

        }
    }

    /**
     * @param string $key
     * @param array|string $data
     * @param Document $doc
     */
    protected function addFieldToDocument($key, $data, Document &$doc)
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
                    case Search::FIELD_TYPE_TEXT:
                        $doc->set($key, $data['value']);
                        break;
                    case Search::FIELD_TYPE_KEYWORD:
                    case Search::FIELD_TYPE_UNINDEXED: // for redisplay, not for searching
                        $addToNodeSummary = false;
                        $doc->set($key, $data['value']);
                        break;
                    case Search::FIELD_TYPE_BINARY:
                        $addToNodeSummary = false;
                        // TODO binary handling
                        break;
                    case Search::FIELD_TYPE_SUMMARY_INDEXED:
                    case Search::FIELD_TYPE_UNSTORED: // for searching, not for redisplay
                        // only index within the summary field
                        break;
                }
            } else {
                // TODO define default index field
            }

            // add all field values to node summary too
            if ($addToNodeSummary === true) {
                $this->addToNodeSummary($data['value']);
            }
        }
    }

    /**
     * @param $value
     */
    protected function addToNodeSummary($value)
    {
        $this->nodeSummary .= ' ' . $value;
    }

    protected function buildQuery()
    {
        $query = new \Elastica\Query();

        if (count($this->queries) > 0) {
            $boolQuery = new Bool();
            foreach ($this->queries as $subQuery) {
                $boolQuery->addShould($subQuery);
            }
            $query->setQuery($boolQuery);
        }

        if (count($this->filters) > 0) {
            $andFilter = new BoolAnd();
            foreach ($this->filters as $filter) {
                $andFilter->addFilter($filter);
            }
            $query->setFilter($andFilter);
        }

        echo '<pre>';
        var_dump($query->getQuery());

        return $query;
    }

    /**
     * @return Index
     */
    protected function getIndex()
    {

        if (!is_object($this->index) || !($this->index instanceof Index)) {
            $this->index = $this->getClient()->getIndex('zoolu'); // TODO config ??
        }

        return $this->index;
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        if (null === $this->client) {
            $this->client = new Client(); // TODO config ??
        }

        return $this->client;
    }
}
