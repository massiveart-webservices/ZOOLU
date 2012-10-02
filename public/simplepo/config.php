<?php

require_once(dirname(__FILE__) . '/../../sys_config/general.inc.php');



$simplepo_config = array();
$simplepo_config['db_user'] = $core->sysConfig->database->params->username;
$simplepo_config['db_pass'] = $core->sysConfig->database->params->password;
$simplepo_config['db_host'] = $core->sysConfig->database->params->host;
$simplepo_config['db_name'] = $core->sysConfig->database->params->dbname;
$simplepo_config['table_prefix'] = "simplepo_";