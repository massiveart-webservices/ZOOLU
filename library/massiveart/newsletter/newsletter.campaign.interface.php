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
 * @package    library.massiveart.command
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NewsletterCampaignInterface
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-06-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.newsletter
 * @subpackage NewsletterCampaignInterface
 */

interface NewsletterCampaignInterface
{
    public function init($args);
    
    public function update($args);
    
    public function send($args);
    
    public function sendTest($args);
    
    public function getRecipientsCount($args);
}