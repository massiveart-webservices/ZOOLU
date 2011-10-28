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
 * @package    library.massiveart.security
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * RoleProvider
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-19: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.security
 * @subpackage RoleProvider
 */

class RoleProvider implements Iterator, Countable {

  /**
   * array of roles
   * @var array
   */
  private $arrRoles = array();

  /**
   * construct
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct() { }

  /**
   * addRole
   * @param Zend_Acl_Role_Interface $objRole
   * @param string $strName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addRole(Zend_Acl_Role_Interface $objRole, $strName){
    $this->arrRoles[$strName] = $objRole;
  }

  /**
   * rewind
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function rewind() {
    reset($this->arrRoles);
  }

  /**
   * current
   * @return NavigationItem|NavigationTree
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function current() {
    return current($this->arrRoles);
  }

  /**
   * key
   * @return string
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function key() {
    return key($this->arrRoles);
  }

  /**
   * next
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function next() {
    next($this->arrRoles);
  }

  /**
   * valid
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function valid() {
    return (current($this->arrRoles) !== false);
  }

  /**
   * count
   * @return integer
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function count(){
    return count($this->arrRoles);
  }

}
?>