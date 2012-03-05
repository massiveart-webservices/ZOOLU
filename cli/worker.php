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
}catch (Zend_Console_Getopt_Exception $exc) {
  // Bad options passed: report usage
  echo $exc->getUsageMessage();
  return false;
}
 
// Show help message in case it was requested or params were incorrect (module, controller and action)
if($getopt->getOption('h')) {
  echo $getopt->getUsageMessage();
  return true;
}
 
// Define application environment
$env = $getopt->getOption('e');
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (null === $env) ? 'development' : $env);

// include general (autoloader, config)
require_once(dirname(__FILE__).'/../sys_config/general.inc.php');

$worker= new GearmanWorker();
$worker->addServer();

$strPrefix = Zend_Registry::get('Core')->sysConfig->client->id;

$worker->addFunction($strPrefix.'_contact_replication_mailchimp_add', array('GearmanReplicationMailChimp', 'add'));
$worker->addFunction($strPrefix.'_contact_replication_mailchimp_update', array('GearmanReplicationMailChimp', 'update'));
$worker->addFunction($strPrefix.'_contact_replication_mailchimp_delete', array('GearmanReplicationMailChimp', 'delete'));
$worker->addFunction($strPrefix.'_contact_replication_mailchimp_done', array('GearmanReplicationMailChimp', 'done'));

while ($worker->work());