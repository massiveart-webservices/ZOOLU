<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 *
 * LICENSE
 *
 * This file is part of ZOOLU.
 *
 * ZOOLU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ZOOLU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ZOOLU. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * For further information visit our website www.getzoolu.org
 * or contact us at zoolu@getzoolu.org
 *
 * @category   ZOOLU
 * @package    cli
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

// Zend_Console_Getopt
require_once 'Zend/Console/Getopt.php';

$objConsoleOpts = new Zend_Console_Getopt(
    array(
         'env|e-s' => 'defines application environment (defaults to "production")',
         'globalId|g=s'    => 'Global Id',
         'linkId|gl=s'     => 'Global Link Id',
         'version|v=i'     => 'Global Version',
         'languageId|l=i'  => 'Language Id',
         'rootLevelId|r=i' => 'RootLevelId Id'
    )
);

// Define application environment
$env = $opt->getOption('e');
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (null === $env) ? 'production' : $env);

// include general (autoloader, config)
require_once(dirname(__FILE__) . '/../sys_config/general.inc.php');

try {
    if (isset($objConsoleOpts->globalId) && isset($objConsoleOpts->linkId) && isset($objConsoleOpts->version) && isset($objConsoleOpts->languageId) && isset($objConsoleOpts->rootLevelId)) {
        $objIndex = new Index();
        $core->logger->info('index global now ...');
        $objIndex->indexGlobal($objConsoleOpts->globalId, $objConsoleOpts->linkId, $objConsoleOpts->version, $objConsoleOpts->languageId, $objConsoleOpts->rootLevelId);
        $core->logger->info('... finished!');
    }

} catch (Exception $exc) {
    $core->logger->err($exc);
    exit();
}
?>