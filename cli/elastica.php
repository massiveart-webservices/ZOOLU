<?php
// Zend_Console_Getopt
require_once 'Zend/Console/Getopt.php';

// define application options and read params from CLI
$getopt = new Zend_Console_Getopt(array(
    'env|e-s' => 'defines application environment (defaults to "development")',
    'help|h' => 'displays usage information',
));

try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $exc) {
    // Bad options passed: report usage
    echo $exc->getUsageMessage();
    return false;
}

// Show help message in case it was requested or params were incorrect (module, controller and action)
if ($getopt->getOption('h')) {
    echo $getopt->getUsageMessage();
    return true;
}

// Define application environment
$env = $getopt->getOption('e');
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (null === $env) ? 'production' : $env);

// include general (autoloader, config)
require_once(dirname(__FILE__) . '/../sys_config/general.inc.php');

$client = new \Elastica\Client();

// load index
$index = $client->getIndex($core->sysConfig->search->client);

// create the index new
$index->create(
    array(
        'number_of_shards' => 4,
        'number_of_replicas' => 1,
    ),
    true
);

$mapping = new \Elastica\Type\Mapping();
$mapping->setType($index->getType('page'));
$mapping->setProperties(
    array(
        'rootLevelId' => array('type' => 'integer', 'store' => 'yes'),
        'templateId' => array('type' => 'integer', 'store' => 'yes'),
        'elementTypeId' => array('type' => 'integer', 'store' => 'yes'),
        'languageId' => array('type' => 'integer', 'store' => 'yes'),
        'segmentId' => array('type' => 'integer', 'store' => 'yes'),
        'url' => array(
            'type' => 'string',
            'store' => 'yes'
        ),
        'title' => array(
            'type' => 'string',
            'store' => 'yes'
        ),
        'articletitle' => array(
            'type' => 'string',
            'store' => 'yes'
        ),
        '_summary' => array(
            'type' => 'string',
            'store' => 'no',
            'index' => 'analyzed',
        ),
    )
);

$mapping->send();

$mapping = new \Elastica\Type\Mapping();
$mapping->setType($index->getType('global'));
$mapping->setProperties(
    array(
        'rootLevelId' => array('type' => 'integer', 'store' => 'yes'),
        'templateId' => array('type' => 'integer', 'store' => 'yes'),
        'elementTypeId' => array('type' => 'integer', 'store' => 'yes'),
        'languageId' => array('type' => 'integer', 'store' => 'yes'),
        'segmentId' => array('type' => 'integer', 'store' => 'yes'),
        'url' => array(
            'type' => 'string',
            'store' => 'yes'
        ),
        'title' => array(
            'type' => 'string',
            'store' => 'yes'
        ),
        'articletitle' => array(
            'type' => 'string',
            'store' => 'yes'
        ),
        '_summary' => array(
            'type' => 'string',
            'store' => 'no',
            'index' => 'analyzed',
        ),
    )
);

$mapping->send();
