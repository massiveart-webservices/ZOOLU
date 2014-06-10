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
 * @package    cli
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    0.9
 */

class GearmanMandrillClient
{
    /**
     * @var Core
     */
    protected $core;

    /**
     * @var GearmanClient
     */
    protected static $gearmanClient;
    
    /**
     * @var GearmanMandrillHandler
     */
    protected static $mandrillHandler;
    
    /**
     * @var string
     */
    protected $strPrefix;
    
    /**
     * Constructor
     * 
     * @param Core $core Core object
     */
    public function __construct($core) {
        $this->core = $core;
        // create gearman client
        if ($this->core->sysConfig->mandrill->gearman == 'true' && self::$gearmanClient === null) {
            self::$gearmanClient = new GearmanClient();
        }

        $this->strPrefix = $this->core->sysConfig->client->id;
        // add gearman server
        if ($this->core->sysConfig->mandrill->gearman == 'true' && self::$gearmanClient instanceof GearmanClient) {
            self::$gearmanClient->addServer($this->core->sysConfig->gearman->server->host);
        }
        $this->core->logger->debug('GearmanMandrillClient->__construct(): Added GearmanClient with server ' . $this->core->sysConfig->gearman->server->host);
    }
    
    /**
     * Puts a subscriber to gearman queue and 
     * sends the newsletter to users which subscribed
     * the passed campaign.
     * 
     * @param NewsletterCampaign_Mandrill $mandrillCampaign
     * @param boolean $testMode
     */
    public function sendNewsletter(NewsletterCampaign_Mandrill $mandrillCampaign, $testMode = false)
    {
        // standardize recipient objects
        $recipients = $this->standardizeObjects($mandrillCampaign->getRecipients());
        $unsubLink = $mandrillCampaign->getUnsubscribeLinks();
        
        // Send newsletter
        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                if ($testMode) {
                    $recipient->newsletterId = 0;
                } else {
                    $recipient->unsubLink = $unsubLink[$recipient->id];
                    $recipient->newsletterId = $mandrillCampaign->getNewsletterId();
                }
                $recipient->content = $mandrillCampaign->getContent();
                $recipient->global_merge_vars = $this->buildGlobalMergeVars($recipient);
                $recipient->subject = $mandrillCampaign->getTitle();
                $recipient->from_name = $mandrillCampaign->getSenderName();
                $recipient->from_email = $mandrillCampaign->getSenderEmail();
               
                $this->core->logger->debug('GearmanMandrillClient->sendNewsletter(): Trying to send newsletter to ' . $recipient->email);
                $this->callFunction($this->strPrefix . '_contact_replication_mandrill_send', serialize($recipient));
            }
        }
        else {
            $this->core->logger->debug('GearmanMandrillClient->sendNewsletter(): No recipients found for newsletter with id: ' . $mandrillCampaign->getNewsletterId());
        }
    }
    
    public function sendSingleMail($object)
    {
        $this->callFunction($this->strPrefix . '_contact_replication_mandrill_send_single', serialize($object));
    }
    
    /**
     * Builds a global merge var array for
     * injecting user information into message.
     *
     * @param object $recipient
     * @return array
     */
    protected function buildGlobalMergeVars($recipient)
    {
        $this->core->logger->debug('GearmanMandrillClient->buildGlobalMergeVars(): Building global merge vars');
        $merge_vars = $this->core->sysConfig->mandrill->global_merge_vars;
        $result = array();
        
        if (sizeof($merge_vars) > 0) {
            foreach ($merge_vars as $var => $field) {
                $tmpClass = new stdClass();
                $tmpClass->name = $var;
                $tmpClass->content = $recipient->$field;
                //$tmpClass->content = ($field == 'salutation') ? $recipient->$field . ' ' . $recipient->title : $recipient->$field;
                if ($recipient->$field == null) {
                    $tmpClass->content = '';
                }
                array_push($result, $tmpClass);
            }
        }
        
        if (!empty($recipient->unsubLink)) {
            $unsub = new stdClass();
            $unsub->name = 'unsub';
            $unsub->content = $recipient->unsubLink;
            array_push($result, $unsub);
        }
        return $result;
    }
    
    /**
     * Sends a newsletter with template.
     * @param object $campaigns
     */
    public function sendTemplateNewsletter($campaigns) 
    {
        $this->core->logger->debug('Sending newsletter with template.');
        $this->callFunction($this->strPrefix . '_contact_replication_mandrill_template_send', serialize($campaigns));
    }
    
    /**
     * Lists all templates available to user.
     */
    public function listTemplates()
    {
        $this->core->logger->debug('Listing templates available to user.');
        $this->callFunction($this->strPrefix . '_contact_replication_mandrill_templates_list', serialize('null'));
    }
    
    /**
     * Lists all of the user-defined tag information.
     */
    public function listTags()
    {
        $this->core->logger->debug('Listing user-defined tag information.');
        $this->callFunction($this->strPrefix . '_contact_replication_mandrill_tags_list', serialize('null'));
    }
    
    /**
     * Lists the recent history for all tags.
     */
    public function listTagsAllTimeSeries()
    {
        $this->core->logger->debug('Listing recent history for all tags.');
        $this->callFunction($this->strPrefix . '_contact_replication_mandrill_tags_list_all_time_history', serialize('null'));
    }
    
    /**
     * Standardizes an array of objects.
     * 
     * @param array $objects
     * @return array
     */
    protected function standardizeObjects($objects)
    {
        $stdObjects = array();
        
        foreach ($objects as $key) {
            $stdObjectTmp = new stdClass();
            foreach ($key as $data => $value) {
                $stdObjectTmp->$data = $value;
            }
            array_push($stdObjects, $stdObjectTmp);
        } 
        return $stdObjects;
    }

    /**
     * use gearman when set in config
     *
     *
     * @param $function
     * @param $workParams
     */
    public function callFunction($function, $workParams)
    {
        if ($this->core->sysConfig->mandrill->gearman == 'true') {
            self::$gearmanClient->doBackground($function, $workParams);
        } else {
            try {
                $handler = new GearmanMandrillHandler();
                switch ($function) {
                    case $this->strPrefix . '_contact_replication_mandrill_send':
                        $handler::send($workParams);
                        break;
                    case $this->strPrefix . '_contact_replication_mandrill_send_single':
                        $handler::sendSingle($workParams);
                        break;
                    case $this->strPrefix . '_contact_replication_mandrill_templates_list':
                        $handler::listTemplates($workParams);
                        break;
                    case $this->strPrefix . '_contact_replication_mandrill_template_send':
                        $handler::sendTemplate($workParams);
                        break;
                    case $this->strPrefix . '_contact_replication_mandrill_tags_list':
                        $handler::listTags($workParams);
                        break;
                    case $this->strPrefix . '_contact_replication_mandrill_tags_list_all_time_history':
                        $handler::listTagsAllTimeSeries($workParams);
                        break;
                    case $this->strPrefix . '_contact_replication_mandrill_templates_render':
                        $handler::renderTemplate($workParams);
                        break;
                }
            } catch (Exception $e) {
                $this->core->logger->err($e->getMessage());
            }
        }
    }
    
}
