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
 * ContactReplication_MailChimp
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-06-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.contact.replication
 * @subpackage ContactReplication_MailChimp
 */

// MailChimp API Class v1.3
require_once(GLOBAL_ROOT_PATH.'library/MailChimp/MCAPI.class.php');

// ZOOLU MailChimp integration
require_once(GLOBAL_ROOT_PATH.'library/massiveart/newsletter/mailchimp/MailChimpConfig.php');
require_once(GLOBAL_ROOT_PATH.'library/massiveart/newsletter/mailchimp/MailChimpList.php');
require_once(GLOBAL_ROOT_PATH.'library/massiveart/newsletter/mailchimp/MailChimpMember.php');

class ContactReplication_MailChimp implements ContactReplicationInterface  {

  /**
   * @var Core
   */
  protected $core;
  
  /**
   * @var MailChimpConfig
   */
  private static $objMailChimpConfig;
  
  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
    
    self::$objMailChimpConfig = new MailChimpConfig();
    self::$objMailChimpConfig->setApiKey($this->core->sysConfig->mail_chimp->api_key)
                             ->setListId($this->core->sysConfig->mail_chimp->list_id);
  }
  
  /**
   * add contact
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function add($arrArgs) {
    //Only subscribe if the flag is set
    if($arrArgs['Subscribed'] == $this->core->sysConfig->mail_chimp->mappings->subscribe){
      $objMailChimpList = new MailChimpList(self::$objMailChimpConfig);
      $objMailChimpList->subscribe(new MailChimpMember($arrArgs));
    }
  }
  
  /**
   * update contact
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function update($arrArgs) {
    $objMailChimpList = new MailChimpList(self::$objMailChimpConfig);
    $blnSubscribe = ($arrArgs['Subscribed'] == $this->core->sysConfig->mail_chimp->mappings->subscribe);
    $objMailChimpList->update(new MailChimpMember($arrArgs), $blnSubscribe);
  }
  
  /**
   * delete contact
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function delete($arrArgs) {
    $objMailChimpList = new MailChimpList(self::$objMailChimpConfig);
    $objMailChimpList->unsubscribe(new MailChimpMember($arrArgs), true);
  }
  
}