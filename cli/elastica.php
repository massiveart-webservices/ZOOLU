<?php
// Zend_Console_Getopt
require_once 'Zend/Console/Getopt.php';

// define application options and read params from CLI
$getopt = new Zend_Console_Getopt(array(
    'env|e-s'    => 'defines application environment (defaults to "development")',
    'help|h'     => 'displays usage information',
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
|| define('APPLICATION_ENV', (null === $env) ? 'development' : $env);

// include general (autoloader, config)
require_once(dirname(__FILE__) . '/../sys_config/general.inc.php');

$client = new \Elastica\Client();

// load index
$index = $client->getIndex('zoolu');

// create the index new
$index->create(
    array(
        'number_of_shards'   => 4,
        'number_of_replicas' => 1,
        'analysis'           => array(
            'analyzer' => array(
                'indexAnalyzer'  => array(
                    'type'      => 'custom',
                    'tokenizer' => 'standard',
                    'filter'    => array('lowercase', 'mySnowball')
                ),
                'searchAnalyzer' => array(
                    'type'      => 'custom',
                    'tokenizer' => 'standard',
                    'filter'    => array('standard', 'lowercase', 'mySnowball')
                )
            ),
            'filter'   => array(
                'mySnowball' => array(
                    'type'     => 'snowball',
                    'language' => 'German'
                )
            )
        )
    ),
    true
);

// create a type
$type = $index->getType('game');

// The Id of the document
$id = 1;

// Create a document
$tweet = array(
    'id'      => $id,
    'user'    => array(
        'name'      => 'mewantcookie',
        'fullName'  => 'Cookie Monster'
    ),
    'msg'     => 'Me wish there were expression for cookies like there is for apples. "A cookie a day make the doctor diagnose you with diabetes" not catchy.',
    'tstamp'  => '1238081389',
    'location'=> '41.12,-71.34',
    '_boost'  => 1.0
);
// First parameter is the id of document.
$tweetDocument = new \Elastica\Document($id);

$index->delete();

// Add tweet to type
//$type->addDocument($tweetDocument);

// Refresh Index
//$type->getIndex()->refresh();