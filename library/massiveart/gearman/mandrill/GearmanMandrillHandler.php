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
require_once(GLOBAL_ROOT_PATH . '/library/html2text/class.html2text.inc');

class GearmanMandrillHandler
{
    /**
     * @var Core
     */
    private static $core;
    
    /**
     * @var Mandrill
     */
    private static $mandrill;
    
    private static $job;
    private static $workload;
    
    private static $exceptions = array();
    
    // Call URLs
    const USERS_INFO = '/users/info';
    const USERS_PING = '/users/ping';
    const USERS_SENDERS = '/users/senders';
    const MESSAGES_SEND = '/messages/send';
    const MESSAGES_SEND_TEMPLATE = '/messages/send-template';
    const MESSAGES_SEARCH = '/messages/search';
    const MESSAGES_PARSE = '/messages/parse';
    const MESSAGES_SEND_RAW = '/messages/send-raw';
    const WEBHOOKS_LIST = '/webhooks/list';
    const WEBHOOKS_ADD = '/webhooks/add';
    const WEBHOOKS_INFO = '/webhooks/info';
    const WEBHOOKS_UPDATE = '/webhooks/update';
    const WEBHOOKS_DELETE = '/webhooks/delete';
    const TEMPLATES_ADD = '/templates/add';
    const TEMPLATES_LIST = '/templates/list';
    const TEMPLATES_RENDER = '/templates/render';
    const TAGS_LIST = '/tags/list';
    const TAGS_ALL_TIME_SERIES = '/tags/all-time-series';
        
    /**
     * Init
     * @param object $job
     */
    private static function init($job){
        self::$job = $job;
        if (method_exists($job, 'workload')) {
            self::$workload = unserialize($job->workload());
        } else {
            self::$workload = unserialize($job);
        }
    
        if(empty(self::$core)){
            self::$core = Zend_Registry::get('Core');
        }
        
        if(empty(self::$mandrill)) {
            self::$mandrill = new Mandrill(self::$core->sysConfig->mandrill->api_key);
        }
    }
    
    /**
     * handleException
     * @param Exception exc
     */
    private static function handleException(Exception $exc){
        self::$exceptions[] = $exc;
    }
    
    /**
     * Sends newsletter.
     * 
     * @param object $job
     */
    public static function send($job)
    {
        self::init($job);
        $h2t = new html2text(self::$workload->content);

        $params = array(
            'key' => self::$core->sysConfig->mandrill->api_key,
            'message' => array(
                'html' => self::$workload->content,
                'text' => $h2t->get_text(),
                'subject' => self::$workload->subject,
                'from_name' => self::$workload->from_name,
                'from_email' => self::$workload->from_email,
                'to' => array(
                    array('email' => self::$workload->email, 'name' => self::$workload->fname . " " . self::$workload->sname)                
                ),
                'global_merge_vars' => self::$workload->global_merge_vars,
                'track_opens' => self::$core->sysConfig->mandrill->send_mail->track_opens,
                'track_clicks' => self::$core->sysConfig->mandrill->send_mail->track_clicks,
                'preserve_recipients' => self::$core->sysConfig->mandrill->send_mail->preserve_recipients,                            
                'metadata' => array(
                    array(
                        self::$core->sysConfig->mandrill->send_mail->zoolu_newsletter_id => self::$workload->newsletterId                
                    )                
                )                
            )
        );
        
        try {
            self::$mandrill->call(self::MESSAGES_SEND, $params);
        } catch (Mandrill_Error $exc) {
            echo "Mandrill Exception thrown. [Message: " . $exc->getMessage() . ".].";
        }
    }
    
    public static function sendSingle($job)
    {
        self::init($job);

        $params = array(
            'key' => self::$core->sysConfig->mandrill->api_key,
            'message' => array(
                            'html' => self::$workload->html,
                            'text' => self::$workload->text,
                            'subject' => self::$workload->subject,
                            'from_name' => self::$workload->from_name,
                            'from_email' => self::$workload->from_email,
                            'to' => array(
                                array('email' => self::$workload->email, 'name' => self::$workload->fname . " " . self::$workload->sname)
                            ),
                            'track_opens' => false,
                            'track_clicks' => false,
                            'preserve_recipients' => false,
            )
        );

        try {
            self::$mandrill->call(self::MESSAGES_SEND, $params);
        } catch (Mandrill_Error $exc) {
            echo "Mandrill Exception thrown. [Message: " . $exc->getMessage() . ".].";
        }
    }
    
    public static function renderTemplate($job)
    {
        self::init($job);
        
        $params = array(
            'key' => self::$core->sysConfig->mandrill->api_key,
            'template_name' => self::$workload->template_name,
            'template_content' => self::$workload->template_content,
            'merge_vars' => self::$workload->merge_vars           
        );
        
        try {
            print_r(self::$mandrill->call(self::TEMPLATES_RENDER, $params));
        } catch (Mandrill_Error $exc) {
            echo "Mandrill Exception thrown. [Message: " . $exc->getMessage() . ".].";
        }
    }
    
    /**
     * Sends newsletter with template.
     * 
     * @param object $job
     */
    public static function sendTemplate($job)
    {
        self::init($job);
        
        $params = array(
            'key' => self::$core->sysConfig->mandrill->api_key,
            'template_name' => self::$workload->template_name,
            'template_content' => array(
                array(
                    'name' => 'NewTemplate1',
                    'content' => 'content'
                )
            ),
            'message' => array(
                'text' => 'message_text',
                'subject' => 'message_subject',
                'from_email' => self::$core->sysConfig->mandrill->from_email,
                'from_name' => self::$core->sysConfig->mandrill->from_name,
                'to' => array(
                    array(
                        'email' => self::$workload->to_email,
                        'name' => self::$workload->to_name
                    )                
                )
            ) 
        );
        
        try {
            echo self::$mandrill->call(self::MESSAGES_SEND_TEMPLATE, $params);
        } catch (Mandrill_Error $exc) {
            echo "Mandrill Exception thrown. [Message: " . $exc->getMessage() . ".].";
        }
    }
    
    /**
     * Reads users info.
     * 
     * @param object $job
     */
    public static function usersInfo($job)
    {
        self::executeBasicRequest($job, self::USERS_INFO);
    }
    
    /**
     * Adds a template.
     * 
     * @param object $job
     */
    public static function addTemplate($job)
    {
        self::init($job);
        
        $params = array(
            'key' => self::$core->sysConfig->mandrill->api_key,
             'name' => self::$workload->args['name'],
             'code' => self::$workload->args['code'],
             'publish' => self::$workload->args['publish']              
        );
        
        try {
            $result = self::$mandrill->call(self::TEMPLATES_ADD, $params);
            print_r($result);
        } catch (Mandrill_Error $exc) {
            echo "Mandrill Exception thrown. [Message: " . $exc->getMessage() . ".].";
        }
    }
    
    /**
     * Lists all templates available to user.
     * 
     * @param object $job
     */
    public static function listTemplates($job)
    {
        self::executeBasicRequest($job, self::TEMPLATES_LIST);
    }
    
    /**
     * Lists all of the user-defined tag information.
     * 
     * @param object $job
     */
    public static function listTags($job)
    {
        self::executeBasicRequest($job, self::TAGS_LIST);
    }
    
    /**
     * Returns the recent history for all tags.
     * 
     * @param object $job
     */
    public static function listTagsAllTimeSeries($job)
    {
        self::executeBasicRequest($job, self::TAGS_ALL_TIME_SERIES);    
    }
    
    /**
     * Executes a basic API request without specified params.
     * 
     * @param object $job
     * @param string $action
     */
    protected static function executeBasicRequest($job, $action)
    {
        self::init($job);

        $params = array(
            'key' => self::$core->sysConfig->mandrill->api_key
        );
         
        try {
            $result = self::$mandrill->call($action, $params);
        } catch (Mandrill_Error $exc) {
            echo "Mandrill Exception thrown. [Message: " . $exc->getMessage() . ".].";
        }
    }
    
}
