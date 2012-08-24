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
 * @package    library.massiveart.newsletter.mailchimp
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * MailChimpList
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-13: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 * @package massiveart.contact.replication.MailChimp
 * @subpackage MailChimpCampaign
 */

// MailChimp API Class v1.3
require_once(GLOBAL_ROOT_PATH . 'library/MailChimp/MCAPI.class.php');

// ZOOLU MailChimp integration
require_once(GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/mailchimp/MailChimpConfig.php');
require_once(GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/mailchimp/MailChimpList.php');
require_once(GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/mailchimp/MailChimpMember.php');

class MailChimpCampaign
{

    const API_ERROR_CODE_UNABLE_TO_UPDATE_CAMPAIGN = -90;

    /**
     * @var MailChimpConfig
     */
    private $objConfig;

    /**
     * @var Core
     */
    protected $core;

    private $strCampaignType;
    private $arrOptions;
    private $arrSegmentationOptions;
    private $strContent;
    private $strCampaignId;
    private $arrStatistics;
    private $arrClickStatistics;
    private $arrUnsubscribes;
    private $arrComplaints;
    private $arrBounces;
    private $arrBouncesHard;
    private $arrBouncesSoft;
    private $arrCountries;
    private $arrMembers;

    /**
     * Constructor
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');

        $this->objConfig = new MailChimpConfig();
        $this->objConfig->setApiKey($this->core->sysConfig->mail_chimp->api_key)
            ->setListId($this->core->sysConfig->mail_chimp->list_id);
    }

    /**
     * setCampaignType
     * @param string $strCampaignType
     * @return MailChimpCampaign
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setCampaignType($strCampaignType)
    {
        $this->strCampaignType = $strCampaignType;
        return $this;
    }

    /**
     * setOptions
     * @param string $arrOptions
     * @return MailChimpCampaign
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setOptions($arrOptions)
    {
        $this->arrOptions = $arrOptions;
        return $this;
    }

    /**
     * getTitle
     * @return string
     */
    public function getTitle()
    {
        return $this->arrOptions['title'];
    }

    /**
     * getRecipientCount
     * @return number
     */
    public function getRecipientCount()
    {
        return $this->arrStatistics['emails_sent'];
    }

    /**
     * getUnsubscribeCount()
     * @return number
     */
    public function getUnsubscribeCount()
    {
        return $this->arrStatistics['unsubscribes'];
    }

    /**
     * getComplaintCount
     * @return number
     */
    public function getComplaintCount()
    {
        return $this->arrStatistics['abuse_reports'];
    }

    /**
     * getClicksCounter
     * @return number
     */
    public function getClicksCount()
    {
        return $this->arrStatistics['clicks'];
    }

    /**
     * getUniqueClickCount
     * @return number
     */
    public function getUniqueClickCount()
    {
        return $this->arrStatistics['unique_clicks'];
    }

    /**
     * getUniqueRecipientClickCount
     * @return number
     */
    public function getUniqueRecipientClickCount()
    {
        return $this->arrStatistics['users_who_clicked'];
    }

    /**
     * getUniqueOpenCount
     * @return number
     */
    public function getUniqueOpenCount()
    {
        return $this->arrStatistics['unique_opens'];
    }

    /**
     * getOpenCounter
     * @return number
     */
    public function getOpenCount()
    {
        return $this->arrStatistics['opens'];
    }

    /**
     * getBounceCount
     * @return number
     */
    public function getBounceCount()
    {
        return $this->arrStatistics['hard_bounces'] + $this->arrStatistics['soft_bounces'];
    }

    /**
     * getForwardCount
     * @return number
     */
    public function getForwardCount()
    {
        return $this->arrStatistics['forwards'];
    }

    /**
     * getForwardOpenCount
     * @return number
     */
    public function getForwardOpenCount()
    {
        return $this->arrStatistics['forwards_opens'];
    }

    /**
     * getClickStatistics
     * @return array
     */
    public function getClickStatistics()
    {
        return $this->arrClickStatistics;
    }

    /**
     * getUnsubscribes
     * @return array
     */
    public function getUnsubscribes()
    {
        return $this->arrUnsubscribes['data'];
    }

    /**
     * getComplaints
     * @return array
     */
    public function getComplaints()
    {
        return $this->arrComplaints['data'];
    }

    /**
     * getBounces
     * @return array
     */
    public function getBounces()
    {
        $arrReturn = array_merge($this->arrBouncesHard['data'], $this->arrBouncesSoft['data']);
        for ($i = 0; $i < count($arrReturn); $i++) {
            unset($arrReturn[$i]['absplit_group']);
            unset($arrReturn[$i]['tz_group']);
        }
        return $arrReturn;
    }

    /**
     * getCountryStatistics
     * @return array
     */
    public function getCountryStatistics()
    {
        return $this->arrCountries;
    }

    /**
     * getSuccessfulDelivers
     * @return array
     */
    public function getSuccessfulDelivers()
    {
        return $this->arrMembers['total'];
    }

    /**
     * setContent
     * @param string $strContent
     * @return MailChimpCampaign
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setContent($strContent)
    {
        $this->strContent = array('html' => $strContent);
        return $this;
    }

    /**
     * setSegmentationOptions
     * @param string $arrSegmentationOptions
     * @return MailChimpCampaign
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setSegmentationOptions($arrSegmentationOptions)
    {
        $this->arrSegmentationOptions = $arrSegmentationOptions;
        return $this;
    }

    /**
     * setCampaignId
     * @param string $strCampaignId
     * @return MailChimpCampaign
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function setCampaignId($strCampaignId)
    {
        $this->strCampaignId = $strCampaignId;
        return $this;
    }

    /**
     * getCampaignId
     * @return string
     */
    public function getCampaignId()
    {
        return $this->strCampaignId;
    }

    /**
     * create
     * @throws MailChimpException
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function update($intRemoteId = null)
    {

        $objMailChimpApi = new MCAPI($this->objConfig->getApiKey());
        if ($intRemoteId == null) {
            //No Remote Id -> Create Campaign
            $strCampaignId = $objMailChimpApi->campaignCreate($this->strCampaignType, $this->arrOptions, $this->strContent, $this->arrSegmentationOptions);
        } else {
            //Remote id -> Update campaign
            foreach ($this->arrOptions as $name => $value) {
                $objMailChimpApi->campaignUpdate($intRemoteId, $name, $value);
            }
            $objMailChimpApi->campaignUpdate($intRemoteId, 'content', $this->strContent);
            $objMailChimpApi->campaignUpdate($intRemoteId, 'segment_opts', $this->arrSegmentationOptions);
            $strCampaignId = $intRemoteId;
        }

        if ($objMailChimpApi->errorCode && $objMailChimpApi->errorCode != self::API_ERROR_CODE_UNABLE_TO_UPDATE_CAMPAIGN) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to update campaign!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }
        $this->setCampaignId($strCampaignId);
        return $strCampaignId;
    }

    /**
     * segmentTest
     * @throws MailChimpException
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function segmentTest()
    {
        $objMailChimpApi = new MCAPI($this->objConfig->getApiKey());

        if ($this->arrSegmentationOptions) {
            $intSegmentCount = $objMailChimpApi->campaignSegmentTest($this->objConfig->getListId(), $this->arrSegmentationOptions);
        } else {
            $arrListMembers = $objMailChimpApi->listMembers($this->objConfig->getListId());
            $intSegmentCount = $arrListMembers['total'];
        }

        if ($objMailChimpApi->errorCode && $objMailChimpApi->errorCode != self::API_ERROR_CODE_UNABLE_TO_UPDATE_CAMPAIGN) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to test segment!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        return $intSegmentCount;
    }

    /**
     * send
     * @throws MailChimpException
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function send()
    {
        $objMailChimpApi = new MCAPI($this->objConfig->getApiKey());

        $objMailChimpApi->campaignSendNow($this->strCampaignId);

        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to send campaign!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }
    }

    /**
     * sendTest
     * @throws MailChimpException
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function sendTest($arrMails)
    {
        $objMailChimpApi = new MCAPI($this->objConfig->getApiKey());
        $objMailChimpApi->campaignSendTest($this->strCampaignId, $arrMails);

        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to test send campaign!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }
    }

    /**
     * loadStatistics
     * @throws MailChimpException
     * @author Daniel Rotter
     * @version 1.0
     */
    public function loadStatistics()
    {
        $objMailChimpApi = new MCAPI($this->objConfig->getApiKey());

        $this->arrStatistics = $objMailChimpApi->campaignStats($this->strCampaignId);
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        $this->arrClickStatistics = $objMailChimpApi->campaignClickStats($this->strCampaignId);
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load click statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        $this->arrUnsubscribes = $objMailChimpApi->campaignUnsubscribes($this->strCampaignId);
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load click statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        $this->arrComplaints = $objMailChimpApi->campaignAbuseReports($this->strCampaignId);
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load click statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        $this->arrBounces = $objMailChimpApi->campaignBounceMessages($this->strCampaignId);
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load click statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        $this->arrBouncesHard = $objMailChimpApi->campaignMembers($this->strCampaignId, 'hard');
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load click statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        $this->arrBouncesSoft = $objMailChimpApi->campaignMembers($this->strCampaignId, 'soft');
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load click statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        $this->arrCountries = $objMailChimpApi->campaignGeoOpens($this->strCampaignId);
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load click statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }

        $this->arrMembers = $objMailChimpApi->campaignMembers($this->strCampaignId, 'sent');
        if ($objMailChimpApi->errorCode) {
            require_once(dirname(__FILE__) . '/MailChimpException.php');
            throw new MailChimpException("\n\tUnable to load click statistics!\n\tCode=" . $objMailChimpApi->errorCode . "\n\tMsg=" . $objMailChimpApi->errorMessage . "\n");
        }
    }
}