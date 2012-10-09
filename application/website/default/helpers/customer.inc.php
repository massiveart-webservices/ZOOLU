<?php
/**
 * ZOOLU - Community Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Customer output Functions
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

/**
 * @return CustomerHelper
 */
function getCustomerHelperObject()
{
    return Zend_Registry::get('CustomerHelper');
}

/**
 * @return Core
 */
function getCoreObject()
{
    return Zend_Registry::get('Core');
}

function get_meta_title($strTag = '')
{
    echo getCustomerHelperObject()->getMetaTitle($strTag);
}

function get_meta_description()
{
    echo getCustomerHelperObject()->getMetaDescription();
}

function get_meta_keywords()
{
    echo getCustomerHelperObject()->getMetaKeywords();
}

function get_canonical_tag() { }

function get_static_component_domain()
{
    echo getCoreObject()->config->domains->static->components;
}

function get_zoolu_header() { }

function get_language_chooser() { }

function get_template_file() {
    return 'login.phtml';
}

function get_content($objView) {
    echo getCustomerHelperObject()->getContent($objView);
}

function get_bottom_content() { }