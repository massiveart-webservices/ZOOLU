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
/**
 * include general (autoloader, config)
 */
require_once(dirname(__FILE__) . '/../sys_config/general.inc.php');

try {
    /**
     * @var Image
     */
    $objImage = new Image();

    $objImage->setUploadPath(GLOBAL_ROOT_PATH . $core->sysConfig->upload->images->path->local->private);
    $objImage->setPublicFilePath(GLOBAL_ROOT_PATH . $core->sysConfig->upload->images->path->local->public);
    $objImage->setDefaultImageSizes($core->sysConfig->upload->images->default_sizes->default_size->toArray());

    $objConsoleOpts = new Zend_Console_Getopt(
        array(
             'size=s'     => 'Image size folder'
        )
    );

    $size = isset($objConsoleOpts->size) ? $objConsoleOpts->size : null;
    
    $core->logger->debug('start render all images ...');
    if ($size != null) {
        $core->logger->debug('for image size ' . $size);
    }
    $objImage->renderAllImages($size, true);
    $core->logger->debug('... finished render all images!');

} catch (Exception $exc) {
    $core->logger->err($exc);
    exit();
}
?>