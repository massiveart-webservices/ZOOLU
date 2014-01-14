<?php

require_once(GLOBAL_ROOT_PATH . '/library/massiveart/gearman/mandrill/GearmanMandrillClient.php');

class NewsletterStatistics_Mandrill implements NewsletterStatisticsInterface {

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Newsletters
     */
    protected $objModelNewsletters;

    /**
     * @var Model_Subscribers
     */
    protected $objModelSubscribers;

    const MANDRILL_EVENTS_KEY = 'mandrill_events';

    /**
     * Mandrill Webhook event constants
     */
    const MANDRILL_EVENT_SEND = 'send';
    const MANDRILL_EVENT_HARD_BOUNCE = 'hard_bounce';
    const MANDRILL_EVENT_SOFT_BOUNCE = 'soft_bounce';
    const MANDRILL_EVENT_OPEN = 'open';
    const MANDRILL_EVENT_CLICK = 'click';
    const MANDRILL_EVENT_SPAM = 'spam';
    const MANDRILL_EVENT_UNSUBSCRIBE = 'unsub';
    const MANDRILL_EVENT_REJECT = 'reject';

    public function __construct() {
        $this->core = Zend_Registry::get('Core');
    }
    
    public function track($args) {
        $request = $args['request'];
        $this->processWebhook($request);    
    }
    
    /**
     * Processes the webhooks event.
     */
    protected function processWebhook($request)
    {
        $this->core->logger->debug('Webhooks->controller->contacts->processWebhook()');
        if (key_exists(self::MANDRILL_EVENTS_KEY, $request)) {
            $json = $request[self::MANDRILL_EVENTS_KEY];
            $event = $this->decodeRequest($json);
            $this->parseRequest($event, $json);
        }
    }
    
    /**
     * Parses the json response and create
     * database entries.
     * @param obj $event
     */
    protected function parseRequest($event, $json)
    {
        $arrData = array();

        // If event contains data
        if (count($event) > 0) {
            // stdClass object
            foreach ($event as $key) {
                $newsletterId = $this->getSpecificValue($key->msg->metadata, $this->core->sysConfig->mandrill->send_mail->zoolu_newsletter_id);
                // a newsletter id of 0 marks a test email, so skip those
                if ($newsletterId > 0) {
                    // Get subscriber of message
                    $subscriber = $this->getModelSubscribers()->loadByEmail($key->msg->email);
                    // No subscriber found, log error
                    if(empty($subscriber)) {
                        throw new Exception('Subscriber for newsletter not found.');
                    }
                    // Populate array with statistics to be saved
                    // TODO refactor: only set event data
                    $arrData = array();
                    $arrData['idNewsletter'] = $newsletterId;
                    $arrData['idSubscriber'] = $subscriber[0]->id;
                    $arrData['sent'] = ($key->event == self::MANDRILL_EVENT_SEND) ? 1 : 0;
                    $arrData['hard_bounced'] = ($key->event == self::MANDRILL_EVENT_HARD_BOUNCE) ? 1 : 0;
                    $arrData['soft_bounced'] = ($key->event == self::MANDRILL_EVENT_SOFT_BOUNCE) ? 1 : 0;
                    $arrData['opened'] = ($key->event == self::MANDRILL_EVENT_OPEN) ? 1 : 0;
                    $arrData['clicked'] = ($key->event == self::MANDRILL_EVENT_CLICK) ? 1 : 0;
                    $arrData['spam'] = ($key->event == self::MANDRILL_EVENT_SPAM) ? 1 : 0;
                    $arrData['unsubscribed'] = ($key->event == self::MANDRILL_EVENT_UNSUBSCRIBE) ? 1 : 0;
                    $arrData['rejected'] = ($key->event == self::MANDRILL_EVENT_REJECT) ? 1 : 0;
                    $arrData['json'] = $json;
                    
                    // Check if there is already a record for the subscriber => update row
                    $oldData = $this->userStatisticsExist($subscriber[0]->id, $newsletterId);
                    if ($oldData != null) {
                        $this->core->logger->debug('Updating statistics for subscriber ' . $subscriber[0]->id . ' with newsletter ' . $newsletterId);
                        $updateField = $this->determineFieldToUpdate($key->event);
                        $data = array($updateField => $arrData[$updateField]);
                        $whr =  $this->core->dbh->quoteInto('idSubscriber = ?', $subscriber[0]->id);
                        $whr .=  $this->core->dbh->quoteInto(' AND idNewsletter = ?', $newsletterId);
                        $this->core->dbh->update('newsletterStatistics', $data , $whr);
                    // No record found, create new record                        
                    } else {
                        $this->core->logger->debug('Adding new statistics for subscriber ' . $subscriber[0]->id . ' with newsletter ' . $newsletterId);
                        $this->getModelNewsletters()->addNewsletterStatistics($arrData);
                    }
                    // Mark hardbounced
                    if ($key->event == self::MANDRILL_EVENT_HARD_BOUNCE OR $key->event == self::MANDRILL_EVENT_SOFT_BOUNCE) {
                        $this->hardbounceSubscriber($subscriber[0]->id);
                    }
                }
            }
        } else {
            $this->core->logger->debug('webhooks->contacts->controller->parseRequest(): No request found. No statistics are going to be stored.');
        }
    }
    
    /**
     * Decodes the json and replaces escape
     * characters.
     * @param JSON $json
     */
    protected function decodeRequest($json)
    {
        return json_decode(preg_replace('/\\\"/', '"', $json));
    }    
    
    /**
     * Traverses an array of objects and returns a specified 
     * property.
     * 
     * @param array $array
     * @param string $key
     * @throws Exception
     * @return mixed
     */
    protected function getSpecificValue($array, $property)
    {
        if (!is_array($array)) {
            throw new Exception('getSpecificValue(' . $property . '): The passsed array was not recognized as such. Please provide valide parameters.');
        }
        // traverse array for passed key
        foreach ($array as $obj) {
            if (property_exists($obj, $property)) {
                return $obj->$property;
            }
        }
        throw new Exception('getSpecificValue(): The passed property was not found for object.');
    }

    /**
     * Determines event for database storage. 
     * 
     * @param string $event
     * @return string
     */
    protected function determineFieldToUpdate($event)
    {
        $result = '';
        
        switch ($event) {
            case self::MANDRILL_EVENT_SEND:        $result = 'sent';
                                break;
            case self::MANDRILL_EVENT_HARD_BOUNCE: $result = 'hard_bounced';
                                break;
            case self::MANDRILL_EVENT_SOFT_BOUNCE: $result = 'soft_bounced';
                                break;
            case self::MANDRILL_EVENT_OPEN:        $result = 'opened';
                                break;
            case self::MANDRILL_EVENT_CLICK:       $result = 'clicked';
                                break;
            case self::MANDRILL_EVENT_SPAM:        $result = 'spam';
                                break;                                                            
            case self::MANDRILL_EVENT_UNSUBSCRIBE:       $result = 'unsubscribed';
                                break;
            case self::MANDRILL_EVENT_REJECT:      $result = 'rejected';
        }
        return $result;
    }
    
    /**
     * Hardbounce subscriber.
     * 
     * @param integer $subscriberId
     */
    protected function hardbounceSubscriber($subscriberId) 
    {
        $data = array('hardbounce' => $this->core->sysConfig->mandrill->mappings->hardbounce);
        $where =  $this->core->dbh->quoteInto('id = ?', $subscriberId);
        return $this->core->dbh->update('subscribers', $data, $where);    
    }
    
    /**
     * Checks if statistics for user already exist.
     * 
     * @param integer $subscriberId
     * @return boolean
     */
    protected function userStatisticsExist($subscriberId, $newsletterId)
    {
        $query = "SELECT * FROM newsletterStatistics WHERE idSubscriber = ? AND idNewsletter = ?";
        $result = $this->core->dbh->fetchAll($query, array($subscriberId, $newsletterId));
        
        // statistics already are existent
        if (count($result) == 1) {
            return $result;
        }
        return null;
    }
    
    /**
     * getModelNewsletters
     * @return Model_Newsletters
     */
    protected function getModelNewsletters(){
        if (null === $this->objModelNewsletters) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH.'/application/zoolu/modules/newsletters/models/Newsletters.php';
            $this->objModelNewsletters = new Model_Newsletters();
        }
        return $this->objModelNewsletters;
    }
    
    /**
     * getModelSubscribers
     * @return Model_Subscribers
     */
    protected function getModelSubscribers(){
        if (null === $this->objModelSubscribers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH.'/application/zoolu/modules/contacts/models/Subscribers.php';
            $this->objModelSubscribers = new Model_Subscribers();
        }
        return $this->objModelSubscribers;
    }
}