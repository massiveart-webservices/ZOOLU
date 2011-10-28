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
 * @package    library.massiveart.command
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * MailChimpException
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-06-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.contact.replication.MailChimp
 * @subpackage MailChimpException
 */
class MailChimpException extends Exception  {
  
	/**
   * @var null|Exception
   */
  private $_previous = null;
  
  /**
   * Construct the exception
   *
   * @param  string $msg
   * @param  int $code
   * @param  Exception $previous
   * @return void
   */
  public function __construct($msg = '', $code = 0, Exception $previous = null){
    parent::__construct($msg, (int) $code);
    $this->_previous = $previous;
  }
  
  /**
   * Returns previous Exception
   *
   * @return Exception|null
   */
  final public function getPrevious(){
    return $this->_previous;
  }
  
  /**
   * String representation of the exception
   *
   * @return string
   */
  public function __toString(){
    if(null !== ($e = $this->getPrevious())) {
      return $e->__toString() 
          . "\n\nNext " 
          . parent::__toString();
    }
    return parent::__toString();
  }
}