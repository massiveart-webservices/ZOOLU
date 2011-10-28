<?php
/**
 * ZOOLU - Content Management System
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
 * @package    application.website.default.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Navigation output functions
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

/**
 * getNavigationHelperObject
 * @return NavigationHelper
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function getNavigationHelperObject(){
  return Zend_Registry::get('NavigationHelper');
}

/**
 * get_main_navigation
 * @param string $strElement
 * @param string|array $mixedElementProperties element css class or array with element properties
 * @param string $strSelectedClass
 * @param boolean $blnWithHomeLink
 * @param boolean $blnImageNavigation
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */	
function get_main_navigation($strElement = 'li', $mixedElementProperties = '', $strSelectedClass = 'selected', $blnWithHomeLink = true, $blnImageNavigation = false){
  echo getNavigationHelperObject()->getMainNavigation($strElement, $mixedElementProperties, $strSelectedClass, $blnWithHomeLink, $blnImageNavigation);
}

/**
 * get_side_navigation
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_side_navigation(){
  echo getNavigationHelperObject()->getSideNavigation();
}

/**
 * get_footer_navigation
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_footer_navigation(){
  echo getNavigationHelperObject()->getFooterNavigation();
}

/**
 * get_main_navigation_title
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */ 
function get_main_navigation_title(){
  echo getNavigationHelperObject()->getMainNavigationTitle();
}

/**
 * has_sub_navigation
 * @return boolean
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function has_sub_navigation(){
  return getNavigationHelperObject()->hasSubNavigation(); 
}

/**
 * get_static_sub_navigation
 * @param string $strElement
 * @param string|array $mixedElementProperties element css class or array with element properties
 * @param string $strSelectedClass 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */ 
function get_sub_navigation($strElement = 'li', $mixedElementProperties = '', $strSelectedClass = 'selected'){
  echo getNavigationHelperObject()->getSubNavigation($strElement, $mixedElementProperties, $strSelectedClass);
}

/**
 * get_breadcrumb
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_breadcrumb($blnHomeLink = false, $strHomeUrl = ''){
  echo getNavigationHelperObject()->getBreadcrumb($blnHomeLink, $strHomeUrl);
}

/**
 * get_sub_navigation_by_level
 * @author Thomas Schedler <tsh@massiveart.com>
 */
function get_sub_navigation_by_level($intLevel){
  echo getNavigationHelperObject()->getSubNavigationByLevel($intLevel);
}

/**
 * get_sub_navigation_select_by_level
 * @author Thomas Schedler <tsh@massiveart.com>
 */
function get_sub_navigation_select_by_level($intLevel){
  echo getNavigationHelperObject()->getSubNavigationSelectByLevel($intLevel);
}

/**
 * get_sitemap
 * @author Thomas Schedler <tsh@massiveart.com>
 */
function get_sitemap(){
  echo getNavigationHelperObject()->getSitemap();
}

?>