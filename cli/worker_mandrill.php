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
$core = Zend_Registry::get('Core');

// Create Worker
// Attention: localhost must be set
$worker= new GearmanWorker();
$host = $core->sysConfig->gearman->server->host;
$worker->addServer($host);
$core->logger->debug('Added gearman worker for host ' . $host);

$strPrefix = $core->sysConfig->client->id;

// Map functions
$worker->addFunction($strPrefix . '_contact_replication_mandrill_send', array('GearmanMandrillHandler', 'send'));
$worker->addFunction($strPrefix . '_contact_replication_mandrill_send_single', array('GearmanMandrillHandler', 'sendSingle'));
$worker->addFunction($strPrefix . '_contact_replication_mandrill_templates_list', array('GearmanMandrillHandler', 'listTemplates'));
$worker->addFunction($strPrefix . '_contact_replication_mandrill_template_send', array('GearmanMandrillHandler', 'sendTemplate'));
$worker->addFunction($strPrefix . '_contact_replication_mandrill_tags_list', array('GearmanMandrillHandler', 'listTags'));
$worker->addFunction($strPrefix . '_contact_replication_mandrill_tags_list_all_time_history', array('GearmanMandrillHandler', 'listTagsAllTimeSeries'));
$worker->addFunction($strPrefix . '_contact_replication_mandrill_templates_render', array('GearmanMandrillHandler', 'renderTemplate'));

while($worker->work());