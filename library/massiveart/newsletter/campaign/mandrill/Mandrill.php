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
     * @var boolean
     */
    private $delivered;

    private $sends;
    private $bounces;
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
        $this->gearmanMandrillClient = new GearmanMandrillClient($this->core);
    }
    
    /*
     * init
     */
    public function init($args) {
        $objNewsletter = $args['newsletter'];
        $this->setNewsletterId($objNewsletter->id);
        if (key_exists('filter', $args)) {
            $objFilter= $args['filter'];
            $this->objFilter = $objFilter;
            $this->setCampaignId($objFilter->id);
        }
        $this->loadInformation();
        $this->loadStatistics();
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
        $recipient = new stdClass();
        $recipient->email = $args['email'];
        $recipient->salutation = 'Test';
        $recipient->title = '';
        $recipient->fname = 'FirstName';
        $recipient->sname = 'LastName';
        $this->recipients = array($recipient);
        
        // set the content
        $this->setContent($args['content']);
        
        //set Subject title
        $this->setTitle($args['title']);
        
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
        
        // set the content
        $this->setContent($args['content']);
        
        //set Subject title
        $this->setTitle($args['title']);
        
        $this->loadRecipients();
        $this->buildUnsubscribeLinks();
        
        // Send newsletter
        $this->gearmanMandrillClient->sendNewsletter($this, false);
    }
    
    /**
     * Builds links for every recipient to unsubscribe
     * newsletter.
     */
    protected function buildUnsubscribeLinks()
    {
        if (sizeof($this->recipients) > 0) {
            foreach ($this->recipients as $recipient) {
                $subscriber = $this->getModelNewsletterUnsubscribeHashes()->loadBySubscriberId($recipient->id);
                // hash for subscriber does not exist 
                if (count($subscriber) != 1) {
                    $arrData['idSubscriber'] = $recipient->id;
                    $arrData['hash'] = $this->buildHashValue($recipient->email);
                    $this->getModelNewsletterUnsubscribeHashes()->getNewsletterUnsubcribeHashesTable()->insert($arrData);
                    $this->unsubscribeLinks[$recipient->id] = $arrData['hash'];
                    $this->core->logger->debug('Wrote unsubscription link for user ' . $arrData['idSubscriber']);
                // else load existing hash
                } else {
                    $this->unsubscribeLinks[$recipient->id] = $subscriber->current()->hash;
                }
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
        $modelSubscribers->setLanguageId(1);
        return $this->recipients = $modelSubscribers->loadByRootLevelFilter($this->objFilter->idRootLevels, $this->campaignId, '', 'ASC', 'sname', false, true);
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
     * 
     * @return 
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
     * @param object $recipient
     */
    private function setTestRecipient($recipient)
    {
        $this->recipients = $recipient;    
    }
    
    /**
     * Returns a successfull deliveries count.
     * 
     * @return number
     */
    public function getSuccessfullDelivered()
    {
        $fails = $this->getBouncesCount() + $this->getStatisticsRejectsCount();
        return $this->getRecipientsCount() - $fails;   
    }
    
    public function getUnsubscribeLinks()
    {
        return $this->unsubscribeLinks;
    }
    
}