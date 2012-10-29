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
 * @package    application.zoolu.language
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

$core = Zend_Registry::get('Core');

/**
 * top navigation
 */
$core->translate->_('portals');
$core->translate->_('media');
$core->translate->_('global');
$core->translate->_('user_administration');
$core->translate->_('settings');
$core->translate->_('contacts');
$core->translate->_('newsletters');

/**
 * user list
 */
$core->translate->_('name');
$core->translate->_('fname');
$core->translate->_('sname');
$core->translate->_('editor');
$core->translate->_('changed');

/**
 * group list
 */
$core->translate->_('title');

/**
 * resources list
 */
$core->translate->_('key');

/**
 * member list
 */
$core->translate->_('status');
$core->translate->_('company');
$core->translate->_('username');
$core->translate->_('companyStatus');
$core->translate->_('country');
$core->translate->_('lastLogin');

/**
 * subscriber list
 */
$core->translate->_('email');
$core->translate->_('one');
$core->translate->_('none');
$core->translate->_('all');
$core->translate->_('subscribed');
$core->translate->_('dirty');

/**
 * navigation mehtods
 */
$core->translate->_('New_Content');
$core->translate->_('New_Product');
$core->translate->_('New_Product_Link');
$core->translate->_('New_Press');
$core->translate->_('New_Course');
$core->translate->_('New_Event');

/*
 * customers
 */
$core->translate->_('private');
$core->translate->_('work');
?>