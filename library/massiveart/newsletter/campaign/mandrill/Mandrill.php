<?php

require_once(GLOBAL_ROOT_PATH . '/library/massiveart/gearman/mandrill/GearmanMandrillClient.php');

class NewsletterCampaign_Mandrill implements NewsletterCampaignInterface
{
    /**
     * Core
     */
    protected $core;
    
    /**
     * @var GearmanMandrillClient
     */
    protected $gearmanMandrillClient;

    /**
     * @var integer 
     */
    private $campaignId;
    
    /**
     * @var object
     */
    private $objFilter;
    
    /**
     * @var integer
     */
    private $newsletterId;
    
    /**
     * @var Object
     */
    private $objNewsletter;
    
    /**
     * @var string
     */
    private $content;
    
    /**
     * @var integer
     */
    private $templateId;
    
    /**
     * @var string
     */
    private $title;
    
    /**
     * @var String
     */
    private $senderName;
    
    /**
     * @var String
     */
    private $senderEmail;
    
    /**
     * @var boolean
     */
    private $delivered;

    private $sends;
    private $bouncesHard;
    private $bouncesSoft;
    private $unsubscribes;
    private $statisticsClicks;
    private $statisticsOpens;
    private $statisticsSpams;
    private $statisticsRejects;
    private $recipients;
    private $unsubscribeLinks;

    /**
     * @var Model_Newsletters
     */
    protected $objModelNewsletters;
    
    /**
     * @var Model_Subscribers
     */
    protected $objModelSubscribers;
    
    /**
     * @var Model_NewsletterUnsubscribeHashes
     */
    protected $objModelUnsubscribeHashes;
        
    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;
    
    /**
     * int $languageId
     */
    protected $languageId;
    
    /**
     * rootLevelFilterId
     */
    const ROOT_LEVEL_FILTER_ID = '67';

    /**
     * Constructor
     * 
     * @param Core $core
     */
    public function __construct() {
        $this->core = Zend_Registry::get('Core');
    }
    
    /*
     * init
     */
    public function init($args) {
        $objNewsletter = $args['newsletter'];
        $this->objNewsletter = $objNewsletter;
        $this->setNewsletterId($objNewsletter->id);
        if (key_exists('filter', $args) && isset($args['filter']->id)) {
            $objFilter = $args['filter'];
            $this->objFilter = $objFilter;
            $this->setCampaignId($objFilter->id);
            $this->languageId = $objNewsletter->languageId;
        }
        if (!key_exists('prevent_load_information', $args) || !$args['prevent_load_information']) {
            $this->loadInformation();
        }
        if (!key_exists('prevent_load_statistics', $args) || !$args['prevent_load_statistics']) {
            $this->loadStatistics();
        }
        return $this;
    }
    
    /*
     * update
     */
    public function update($args) {
        if ($args['remoteId'] != '' && $args['remoteId'] != null) {
            return $args['remoteId'];
        } else {
            return uniqid();
        }
    }
    
    /**
     * sendTest
     * @param type $args
     */
    public function sendTest($args) {
        $this->gearmanMandrillClient = new GearmanMandrillClient($this->core);
        $recipient = new stdClass();
        $recipient->email = $args['email'];
        $recipient->salutation = 'Salutation';
        $recipient->title = '';
        $recipient->fname = 'FirstName';
        $recipient->sname = 'LastName';
        $this->recipients = array($recipient);
        
        // set the content
        $this->setContent($args['content']);
        
        $this->objNewsletter = $args['newsletter'];
        
        //set Subject title
        $this->setTitle($this->objNewsletter->title);
        
        //set Sender data
        $this->setSenderData();
        
        // Send newsletter
        $this->gearmanMandrillClient->sendNewsletter($this, true);
    }
    
    /**
     * Send
     * @param boolean $string
     * @param string $email
     */
    public function send($args)
    {
        $this->gearmanMandrillClient = new GearmanMandrillClient($this->core);
        // set the content
        $this->setContent($args['content']);
        
        $this->objNewsletter = $args['newsletter'];
        
        //set Subject title
        $this->setTitle($this->objNewsletter->title);
        
        //set Sender data
        $this->setSenderData();
        
        $this->loadRecipients();
        $this->buildUnsubscribeLinks();
        
        // Send newsletter
        $this->gearmanMandrillClient->sendNewsletter($this, false);
    }
    
    private function setSenderData() {
        //set Sender data
        if ($this->objNewsletter->newsletter_from_name != '') {
            $this->setSenderName($this->objNewsletter->newsletter_from_name);
        } else {
            $this->setSenderName($this->core->sysConfig->mandrill->from_name);
        }
        
        if ($this->objNewsletter->newsletter_from_email != '') {
            $this->setSenderEmail($this->objNewsletter->newsletter_from_email);
        } else {
            $this->setSenderEmail($this->core->sysConfig->mandrill->from_email);
        }
    }
    
    /**
     * Builds links for every recipient to unsubscribe
     * newsletter.
     */
    protected function buildUnsubscribeLinks()
    {
        if (sizeof($this->recipients) > 0) {
           
            $baseportalJSON = $this->objNewsletter->baseportal;
            $baseportal = json_decode($baseportalJSON);
            $rootLevelId = $baseportal->rootlevel;
            $language = $baseportal->language;
            $objUrl = $this->getModelRootLevels()->loadRootLevelUrl($rootLevelId);
            
            // load baseportal for unscubscribe link
            foreach ($this->recipients as $recipient) {
                $hash = '';
                $subscriber = $this->getModelNewsletterUnsubscribeHashes()->loadBySubscriberId($recipient->id);
                // hash for subscriber does not exist 
                if (count($subscriber) != 1) {
                    $arrData['idSubscriber'] = $recipient->id;
                    $arrData['hash'] = $this->buildHashValue($recipient->email);
                    $this->getModelNewsletterUnsubscribeHashes()->getNewsletterUnsubcribeHashesTable()->insert($arrData);
                    $hash = $arrData['hash'];
                    $this->core->logger->debug('Wrote unsubscription link for user ' . $arrData['idSubscriber']);
                // else load existing hash
                } else {
                    $hash = $subscriber->current()->hash;
                }
                $this->unsubscribeLinks[$recipient->id] = 'http://' . $objUrl->url . '/unsubscribe?language=' . strtolower($language) . '&hash=' . $hash . '&nid=' . $this->newsletterId;
            }
        }
    }
    
    /**
     * Builds a random hash value.
     * 
     * @param string $email
     * @return string
     */
    protected function buildHashValue($email)
    {
        $hash = md5(uniqid(rand(), true));
        $emailHash = sha1($email);
        return $hash = str_shuffle($hash . $emailHash);
    }
        
    /**
     * Get all subscribers subscribing to given campaign.
     *
     * @param integer $campaignId
     * @return array
     */
    public function loadRecipients()
    {
        $this->core->logger->debug('Getting all subscribers of campaign with id: ' . $this->campaignId);
        // Get campaign subscribers
        $modelSubscribers = $this->getModelSubscribers();
        $modelSubscribers->setLanguageId($this->languageId);
        return $this->recipients = $modelSubscribers->loadByRootLevelFilter($this->objFilter->idRootLevels, $this->campaignId, '', 'ASC', 'sname', false, true, true);
    }

    /**
    * Loads statistics of a campaign.
    *
    * @param $campaignId The id of the campaign to be laoded.
    * @return array The statistics of the campaign.
    */
    public function loadStatistics()
    {
        if (empty($this->newsletterId) OR !is_numeric($this->newsletterId)) {
            throw new Exception('CampaignId invalid. Cannot load statistics.');
        }
        // Get stats
        $statistics = $this->getModelNewsletters()->loadNewsletterStatistics($this->newsletterId);

        // Load statistics
        if (count($statistics) > 0) {
            $this->sends = $this->calculateStat($statistics, 'sent');
            $this->bouncesHard = $this->loadStatByType($statistics, 'hard_bounced');
            $this->bouncesSoft = $this->loadStatByType($statistics, 'soft_bounced');
            $this->unsubscribes = $this->loadStatByType($statistics, 'unsubscribed');
            $this->statisticsClicks = $this->loadStatByType($statistics, 'clicked');
            $this->statisticsOpens = $this->loadStatByType($statistics, 'opened');
            $this->statisticsSpams = $this->loadStatByType($statistics, 'spam');
            $this->statisticsRejects = $this->loadStatByType($statistics, 'rejected');
            return $statistics;
        }
        return false;
    }
    
    /**
     * Loads general informations of a campaign.
     * 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function loadInformation()
    {
        if (!empty($this->newsletterId) AND is_numeric($this->newsletterId)) {
            $information = $this->getModelNewsletters()->load($this->newsletterId);
            $this->title = $information->current()->title;
            $this->delivered = $information->current()->delivered;
            $this->templateId = $information->current()->idTemplates;
            return $information;
        }
    }
    
    /**
     * Determines stats.
     * 
     * @param array $statistics
     * @param string $statType
     * @return number
     */
    private function calculateStat($statistics, $statType)
    {
        $statCount = 0;

        if (count($statistics) > 0) {
            foreach ($statistics as $stat) {
                if ($stat->$statType == 1)
                    $statCount++;
            }
        }
        return $statCount;
    }
    
    /**
     * Load stats by type.
     * 
     * @param array $statistics
     * @param string $type
     * @return array
     */
    protected function loadStatByType($statistics, $type)
    {
        $unsubscribes = array();

        if (count($statistics) > 0) {
            foreach ($statistics as $stat) {
                $obj = array();
                if ($stat->$type == 1) {
                    $subscriber = $this->getModelSubscribers()->load($stat->idSubscriber);
                    if (count($subscriber) > 0) {
                        $obj['email'] = $subscriber->current()->email;
                        $obj['status'] = $type;
                        array_push($unsubscribes, $obj);
                    }
                }
            }
        }
        return $unsubscribes;
    }
    
    /**
     * getModelNewsletters
     * @return Model_Newsletters
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelNewsletters(){
        if (null === $this->objModelNewsletters) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'newsletters/models/Newsletters.php';
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
            require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'contacts/models/Subscribers.php';
            $this->objModelSubscribers = new Model_Subscribers();
        }
        return $this->objModelSubscribers;
    }
    
    /**
     * getModelNewsletterUnsubscribeHashes
     * @return Model_Newsletters
     */
    protected function getModelNewsletterUnsubscribeHashes(){
        if (null === $this->objModelUnsubscribeHashes) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'newsletters/models/NewsletterUnsubscribeHashes.php';
            $this->objModelUnsubscribeHashes = new Model_NewsletterUnsubscribeHashes();
        }
    
        return $this->objModelUnsubscribeHashes;
    }

    /**
     * Get templateId.
     *  
     * @return integer 
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * Get title.
     * 
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get delivery status.
     * 
     * @return boolean 
     */
    public function getDelivered()
    {
        return $this->delivered;
    }
    
    /**
     * Set campaignId.
     */
    public function setCampaignId($campaignId)
    {
        $this->campaignId = $campaignId;
    }
    
    /**
     * Get campaignId.
     *
     * @return $campaignId
     */
    public function getCampaignId()
    {
        return $this->campaignId;
    }
    
    /**
     * Get all sends.
     *
     * @return number
     */
    public function getSends()
    {
        return sizeof($this->sends);
    }
    
    /**
     * Get all bounces.
     *
     * @return number or null
     */
    public function getBounces()
    {
        if (is_array($this->bouncesSoft) AND is_array($this->bouncesHard)) {
            return array_merge($this->bouncesSoft, $this->bouncesHard);
        }
        return null;
    }
    
    /**
     * Get all bounces count.
     *
     * @return number
     */
    public function getBouncesCount()
    {
        return $this->getBouncesHardCount() + $this->getBouncesSoftCount();
    }
    
    /**
     * Get hard bounces.
     *
     * @return number
     */
    public function getBouncesHard()
    {
        return $this->bouncesHard;
    }
    
    /**
     * Get hard bounces count.
     *
     * @return number
     */
    public function getBouncesHardCount()
    {
        return sizeof($this->bouncesHard);
    }
    
    /**
     * Get soft bounces.
     *
     * @return number
     */
    public function getBouncesSoft()
    {
        return $this->bouncesSoft;
    }
    
    /**
     * Get soft bounces count.
     *
     * @return number
     */
    public function getBouncesSoftCount()
    {
        return sizeof($this->bouncesSoft);
    }
    
    /**
     * Get unsubsribes count.
     *
     * @return number
     */
    public function getUnsubscribesCount()
    {
        return sizeof($this->unsubscribes);
    }
    
    /**
     * Get unsubsribes.
     *
     * @return number
     */
    public function getUnsubscribes()
    {
        return $this->unsubscribes;
    }
    
    /**
     * Get clicks.
     *
     * @return number
     */
    public function getStatisticsClicks()
    {
        return $this->statisticsClicks;
    }
    
    /**
     * Get clicks count.
     *
     * @return number
     */
    public function getStatisticsClicksCount()
    {
        return sizeof($this->statisticsClicks);
    }
    
    /**
     * Get opens.
     *
     * @return number
     */
    public function getStatisticsOpens()
    {
        return $this->statisticsOpens;
    }
    
    /**
     * Get opens count.
     *
     * @return number
     */
    public function getStatisticsOpensCount()
    {
        return sizeof($this->statisticsOpens);
    }
    
    /**
     * Get spams.
     *
     * @return number
     */
    public function getStatisticsSpams()
    {
        return $this->statisticsSpams;
    }
    
    /**
     * Get spams count.
     *
     * @return number
     */
    public function getStatisticsSpamsCount()
    {
        return sizeof($this->statisticsSpams);
    }
    
    /**
     * Get rejects.
     *
     * @return number
     */
    public function getStatisticsRejects()
    {
        return $this->statisticsRejects;
    }
    
    /**
     * Get rejects count.
     *
     * @return number
     */
    public function getStatisticsRejectsCount()
    {
        return sizeof($this->statisticsRejects);
    }
    
    /**
     * Get recipients.
     * 
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }
    
    /**
     * Get recipients count.
     * 
     * @return number
     */
    public function getRecipientsCount($args = null)
    {
        $this->loadRecipients();
        return sizeof($this->recipients);
    }
    
    /**
     * getRecipientsCountOnDelivery
     * 
     * @return number
     */
    public function getRecipientsCountOnDelivery($args = null)
    {
        return $this->objNewsletter->recipients_on_delivery;
    }
    
    /**
     * Get content.
     *  
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content. 
     * 
     * @param $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * Set title.
     *
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
     * setSenderName
     * @param String $senderName
     */
    public function setSenderName($senderName) {
        $this->senderName = $senderName;
    }
    
    /**
     * getSenderName
     * @return String
     */
    public function getSenderName() {
        return $this->senderName;
    }

    /**
     * setSenderEmail
     * @param String $senderEmail
     */
    public function setSenderEmail($senderEmail) {
        $this->senderEmail= $senderEmail;
    }
    
    /**
     * getSenderEmail
     * @return String
     */
    public function getSenderEmail() {
        return $this->senderEmail;
    }

    /**
     * @return int
     */
    public function getNewsletterId()
    {
        return $this->newsletterId;
    }

    /**
     * 
     * @param $newsletterId
     */
    public function setNewsletterId($newsletterId)
    {
        $this->newsletterId = $newsletterId;
    }
    
    /**
     * Returns a successfull deliveries count.
     * 
     * @return number
     */
    public function getSuccessfullDelivered()
    {
        $fails = $this->getBouncesCount() + $this->getStatisticsRejectsCount();
        return $this->getRecipientsCountOnDelivery() - $fails;   
    }
    
    public function getUnsubscribeLinks()
    {
        return $this->unsubscribeLinks;
    }
    
     /**
     * getModelRootLevels
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     * @return Model_RootLevels
     */
    protected function getModelRootLevels()
    {
        if (null === $this->objModelRootLevels) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/RootLevels.php';
            $this->objModelRootLevels = new Model_RootLevels();
        }

        return $this->objModelRootLevels;
    }
    
}
