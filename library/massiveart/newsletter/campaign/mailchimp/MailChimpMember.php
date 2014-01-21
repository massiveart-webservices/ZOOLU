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
 * MailChimpMember
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-06-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.contact.replication.MailChimp
 * @subpackage MailChimpMember
 */

class MailChimpMember
{

    /**
     * first name
     * @var String
     */
    private $strFirstName;

    /**
     * last name
     * @var String
     */
    private $strLastName;

    /**
     * email address
     * @var String
     */
    private $strEmail;

    /**
     * interest groups
     * @var Array
     */
    private $arrInterestGroups;

    /**
     * subscribe id
     * @var number;
     */
    private $intSubscribed;

    /**
     * if true the interests will be replaced, otherwise added
     * matters only on update
     * @var boolean
     */
    private $blnReplaceInterests = true;

    /**
     * salutation
     * @var string
     */
    private $strSalutation;

    /**
     * @var boolean
     */
    private $blnSubscribed;

    /**
     * @var boolean
     */
    private $blnHardBounce;

    /**
     * MailChimp Member construct
     * @param array $arrProperties
     * @return MailChimpMember
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function __construct(array $arrProperties = null)
    {
        if (is_array($arrProperties)) {
            $this->setProperties($arrProperties);
        }
    }

    /**
     * set first name
     * @param String $strFirstName
     * @return MailChimpMember
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function setFirstName($strFirstName)
    {
        $this->strFirstName = $strFirstName;
        return $this;
    }

    /**
     * get first name
     * @return String
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getFirstName()
    {
        return $this->strFirstName;
    }

    /**
     * set last name
     * @param String $strLastName
     * @return MailChimpMember
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function setLastName($strLastName)
    {
        $this->strLastName = $strLastName;
        return $this;
    }

    /**
     * get last name
     * @return String
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getLastName()
    {
        return $this->strLastName;
    }

    /**
     * set salutation
     * @param string $strSalutation
     * @return MailChimpMember
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setSalutation($strSalutation)
    {
        $this->strSalutation = $strSalutation;
        return $this;
    }

    /**
     * get Salutation
     * @return string
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function getSalutation()
    {
        return $this->strSalutation;
    }

    /**
     * set email address
     * @param String $strEmail
     * @return MailChimpMember
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function setEmail($strEmail)
    {
        $this->strEmail = $strEmail;
        return $this;
    }

    /**
     * get email address
     * @return String
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getEmail()
    {
        return $this->strEmail;
    }

    /**
     * set interest groups
     * @param Array $arrInterestGroups
     * @return MailChimpMember
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function setInterestGroups(array $arrInterestGroups)
    {
        $this->arrInterestGroups = $arrInterestGroups;
        return $this;
    }

    /**
     * get interest groups
     * @return Array
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getInterestGroups()
    {
        return $this->arrInterestGroups;
    }

    /**
     * set subscribe id
     * @return MailChimpMember
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setSubscribed($intSubscribed)
    {
        $this->intSubscribed = $intSubscribed;
        return $this;
    }

    /**
     * get subscribe id
     * @return MailChimpMember
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function getSubscribed()
    {
        return $this->intSubscribed;
    }

    /**
     * @return MailChimpMember
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setHardBounce($blnHardbounce)
    {
        $this->blnHardBounce = $blnHardbounce;
        return $this;
    }

    /**
     * @return MailChimpMember
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function getHardBounce()
    {
        return $this->blnHardBounce;
    }

    /**
     * set replace interests
     * @param boolean $blnReplaceInterests
     * @return MailChimpMember
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setReplaceInterests($blnReplaceInterests)
    {
        $this->blnReplaceInterests = $blnReplaceInterests;
        return $this;
    }

    /**
     * get replace interests
     * @return boolean
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function getReplaceInterests()
    {
        return $this->blnReplaceInterests;
    }

    /**
     * set member properties
     * @param Array $arrProperties
     * @return MailChimpMember
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function setProperties(array $arrProperties)
    {
        $arrMethods = get_class_methods($this);
        foreach ($arrProperties as $strName => $mixedValue) {
            $strMethod = 'set' . ucfirst($strName);
            if (in_array($strMethod, $arrMethods)) {
                $this->$strMethod($mixedValue);
            }
        }
        return $this;
    }
}