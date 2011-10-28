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
 * @package    application.zoolu.modules.users.views.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ListHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-08-03: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class DirtySubscriberBuilder implements Zend_Feed_Builder_Interface {
  
  /**
   * @var Core
   */
  protected $core;
  
  /**
   * @var Zend_Db_Table_Rowset_Abstract
   */
  protected $_entries;
  
  /**
   * constructor
   * @param Zend_Db_Table_Rowset_Abstract $entries
   */
  public function __construct($entries){
    $this->core = Zend_Registry::get('Core');
    $this->_entries = $entries;
  }
  
  /**
   * Builds and returns the header for the rss feed
   * @return Zend_Feed_Builder_Header
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getHeader(){
    $header = new Zend_Feed_Builder_Header('Dirty Subscribers', '/zoolu/contacts/subscriber/dirtysubscribers', 'utf-8');
    return $header;
  }
  
  /**
   * Builds and returns the entries for the rss feed
   * @return array
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getEntries(){
    $entries = array();
    foreach($this->_entries as $entry){
      $objEntry = new Zend_Feed_Builder_Entry($entry->fname.' '.$entry->sname, '', $entry->subscribed.': '.$entry->email);
      $objEntry->setLastUpdate($entry->created);
      $entries[] = $objEntry;
    }
    return $entries;
  }
}
?>