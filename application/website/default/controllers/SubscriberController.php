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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * SubscriberController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2010-04-15: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
class SubscriberController extends WebControllerAction {

    const INTEREST_GROUPS_ID = 615;
    const SALUTATIONS_ID = 640;
    const SUBSCRIBER_ROOTLEVEL_ID = 48;
    const SUBSCRIBER_GENERICFORM_ID = 36;
    const SUBSCRIBED_FLAG_ID = 632;
    const UN_SUBSCRIBED_FLAG_ID = 633;

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Subscribers
     */
    private $objModelSubscribers;

    /**
     * @var Model_Categories
     */
    public $objModelCategories;
    
    /**
     * @var Model_Newsletter
     */
    protected $objModelNewsletters;

    /**
     * preDispatch
     * Called before action method.
     * 
     * @return void  
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function preDispatch() {
        $this->core = Zend_Registry::get('Core');
        $this->request = $this->getRequest();
    }

    /**
     * init
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function init() {
        parent::init();
        $this->validateLanguage();

        $this->setTranslate();

        $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/scripts');
    }

    /**
     * initPageView
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     * @version 1.0
     */
    private function initPageView() {
        Zend_Layout::startMvc(array(
            'layout' => 'master',
            'layoutPath' => GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path
        ));
        Zend_Layout::getMvcInstance()->setViewSuffix('php');

        $this->setTranslate();

        $this->initNavigation();

        // Initialize CommunityHelper
        if (file_exists(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/SubscriberHelper.php')) {
            require_once(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/SubscriberHelper.php');
            $strSubscriberHelper = ucfirst($this->objTheme->path) . '_SubscriberHelper';
            $objSubscriberHelper = new $strSubscriberHelper();
        } else {
            require_once(dirname(__FILE__) . '/../helpers/SubscriberHelper.php');
            $objSubscriberHelper = new SubscriberHelper();
        }

        Zend_Registry::set('SubscriberHelper', $objSubscriberHelper);
        Zend_Registry::set('TemplateCss', '');
        Zend_Registry::set('TemplateJs', '');
    }

    /**
     * indexAction
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     */
    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender();
        echo 'index';
    }

    /**
     * subscribeAction
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     */
    public function subscribeAction() {
        $this->loadTheme();
        $this->initPageView();
        $objSubscriberHelper = Zend_Registry::get('SubscriberHelper');
        $objSubscriberHelper->setMetaTitle($this->translate->_('Newsletter_subscribe'), false);
        $this->view->success = false;
        $this->view->successMsg = '';
        
        foreach ($this->getRequest()->getParams() as $key => $val) {
            $objSubscriberHelper->setFormData($key, $val);
        }
        
        //check if request is post or a opt in key is transmitted
        if ($this->getRequest()->isPost() || $this->getRequest()->getParam('key', '') != '') {
            $formData['email'] = $this->getRequest()->getParam('email', '');
            $formData['salutation'] = $this->getRequest()->getParam('salutation', 0);
            $formData['title'] = $this->getRequest()->getParam('title', '');
            $formData['fname'] = $this->getRequest()->getParam('fname', '');
            $formData['sname'] = $this->getRequest()->getParam('sname', '');
            $formData['interestgroups'] = $this->getRequest()->getParam('interestgroups', array());
            // portals evaluation
            $formData['portals'] = array($this->objTheme->idRootLevels);    
            // language evaluation
            $formData['languages'] = array();
            foreach ($this->core->sysConfig->contact->language_mappings->language as $language) {
                if ($language->id == $this->core->intLanguageId) {
                    $formData['languages'][] = $language->category;
                }
                break;
            }
            
            $valid = $this->validateFormData($formData, $objSubscriberHelper);
            if ($valid) {
                $subscribed = false;
                $key = '';
                $optInStrategy = $this->core->sysConfig->subscriber->optInStrategy;
                if ($this->validateEmail($formData['email'], $objSubscriberHelper)) {
                    $key = $this->addSubscriber($formData, $optInStrategy);
                    $subscribed = true;
                } else {
                    // reactivate if subscriber exists but was unsubscribed
                    $objInactiveSubscriber = $this->getInactiveSubscriber($formData['email']);
                    if ($objInactiveSubscriber != null) {
                        $key = $this->reactivateSubscriber($formData, $objInactiveSubscriber, $optInStrategy);
                        $subscribed = true;
                    }
                }
                if ($subscribed) {
                    if ($optInStrategy == 'double') {
                        if ($key != '') {
                            //send mail
                            $this->sendOptInMail($formData, $key, $optInStrategy);
                            $this->view->success = true;
                            $this->view->successMsg = $this->translate->_('subscribe_doubleoptin_information');
                        }
                    } else if ($optInStrategy == 'single') {
                        $this->view->success = true;
                        $this->view->successMsg = $this->translate->_('subscribe_confirmation');
                    }
                }
            } else {
                // try to activate subscriber if a key was set
                $key = $this->getRequest()->getParam('key', '');
                if ($key != '' && $this->activateSubscriber($key)) {
                    $this->view->success = true;    
                    $this->view->successMsg = $this->translate->_('subscribe_confirmation');
                }
            }
        }

        $this->getModelCategories()->setLanguageId($this->core->intLanguageId);
        $interestGroupsData = $this->getModelCategories()->loadCategoryTree(self::INTEREST_GROUPS_ID);
        $this->view->interestgroups = $interestGroupsData;

        $salutationsData = $this->getModelCategories()->loadCategoryTree(self::SALUTATIONS_ID);
        $this->view->salutations = $salutationsData;
    }
    
    /*
     * validateEmail
     */
    private function validateEmail($email, $objSubscriberHelper) {
        // Email validation
        $objMailValidator = new Zend_Validate_EmailAddress();
        $validEmail = $objMailValidator->isValid($email);
        if (!$validEmail) {
            $objSubscriberHelper->setFormError('email', $this->translate->_('please_insert_valid_emailaddress'));
        }
        $objSubscribersEmail = $this->getModelSubscribers()->loadByEmail($email);
        if (count($objSubscribersEmail) > 0) {
            $validEmail = false;
            $objSubscriberHelper->setFormError('email', $this->translate->_('emailaddress_already_used'));
        }
        return $validEmail;
    }
    
    /*
     * validateFormData
     */
    private function validateFormData($formData, $objSubscriberHelper) {

        // Name validation
        if ($formData['salutation'] > 0) {
            $validSalutation = true;
        } else {
            $validSalutation = false;
            $objSubscriberHelper->setFormError('salutation', $this->translate->_('Salutation_mandatory'));
        }

        if ($formData['fname'] != '') {
            $validFname = true;
        } else {
            $validFname = false;
            $objSubscriberHelper->setFormError('fname', $this->translate->_('Fname_mandatory'));
        }

        if ($formData['sname'] != '') {
            $validSname = true;
        } else {
            $validSname = false;
            $objSubscriberHelper->setFormError('sname', $this->translate->_('Sname_mandatory'));
        }

        // Interesgroup validation
        if (count($formData['interestgroups']) > 0) {
            $validInterestgroups = true;
        } else {
            $validInterestgroups = false;
            $objSubscriberHelper->setFormData('interestgroups', $formData['interestgroups']);
        }

        $valid = ($validSalutation && $validFname && $validSname && $validInterestgroups);
        return $valid;
    }
    
    /**
     * getInactiveSubscriber
     * return checks if subscriber already was registrated and unsubscribed later and return id of subscriber
     */
    private function getInactiveSubscriber($email) {
        $objSubscribers = $this->getModelSubscribers()->loadByEmail($email);
        if (count($objSubscribers) == 1) {
            $objSubscriber = $objSubscribers->current();
            if ($objSubscriber->subscribed != self::SUBSCRIBED_FLAG_ID) {
                return $objSubscriber;
            } else {
                return null;
            }
        }
    }
    
    /**
     * reactivateSubscriber
     * @return Sting optInKey;
     */
    private function reactivateSubscriber($formData, $objInactiveSubscriber, $optInStrategy) {
        $optInKey  = '';
        $subscribed = '';
        if ($optInStrategy == 'single') {
            $subscribed = self::SUBSCRIBED_FLAG_ID;
        }
        if ($optInStrategy == 'double') {
            $optInKey  = md5(uniqid(rand(), true));
        }
        
        $this->core->dbh->beginTransaction();
        // add subscriber
        $data = array(
            'salutation'        =>  $formData['salutation'],
            'title'             =>  $formData['title'],
            'fname'             =>  $formData['fname'],
            'sname'             =>  $formData['sname'],
            'email'             =>  $formData['email'],
            'subscribed'        =>  $subscribed,
            'reactivated'       =>  date('Y-m-d H:i:s'),
            'optinkey'          =>  $optInKey,
        );
        $this->getModelSubscribers()->update($objInactiveSubscriber->id, $data);

        // add interests
        $interests = array(
            'interest_group' => $formData['interestgroups'],
            'portal'         => $formData['portals'],
            'language'       => $formData['languages']
        );
        $this->objModelSubscribers->updateInterests($objInactiveSubscriber->id, $interests);
        $this->core->dbh->commit();        
        return $optInKey;
    }
    
    /**
     * activateSubscriber
     */
    private function activateSubscriber($key) {
        $objSubscribers = $this->getModelSubscribers()->loadByOptinkey($key);
        if (count($objSubscribers) == 1) {
            $objSubscriber = $objSubscribers->current();
            $data = array(
                'subscribed' => self::SUBSCRIBED_FLAG_ID,
                'optInKey'   => ''
            );
            $this->getModelSubscribers()->update($objSubscriber->id, $data);
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * addSubscriber
     * @return Sting $optInStrategy;
     */
    private function addSubscriber($formData, $optInStrategy) {
        $optInKey  = '';
        $subscribed = '';
        if ($optInStrategy == 'single') {
            $subscribed = self::SUBSCRIBED_FLAG_ID;
        }
        if ($optInStrategy == 'double') {
            $optInKey  = md5(uniqid(rand(), true));
        }
        
        $this->core->dbh->beginTransaction();
        // add subscriber
        $data = array(
            'idUsers'           =>  $this->core->sysConfig->subscriber->default->userId,
            'creator'           =>  $this->core->sysConfig->subscriber->default->userId,
            'idRootLevels'      =>  self::SUBSCRIBER_ROOTLEVEL_ID,
            'idGenericForms'    =>  self::SUBSCRIBER_GENERICFORM_ID,
            'salutation'        =>  $formData['salutation'],
            'title'             =>  $formData['title'],
            'fname'             =>  $formData['fname'],
            'sname'             =>  $formData['sname'],
            'email'             =>  $formData['email'],
            'subscribed'        =>  $subscribed,
            'created'           =>  date('Y-m-d H:i:s'),
            'optinkey'          =>  $optInKey
        );
        $id = $this->getModelSubscribers()->add($data);

        // add interests
        $interests = array(
            'interest_group' => $formData['interestgroups'],
            'portal'         => $formData['portals'],
            'language'       => $formData['languages']
        );
        $this->objModelSubscribers->updateInterests($id, $interests);
        $this->core->dbh->commit();        
        return $optInKey;
    }

    /**
     * unsubscribeAction
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     */
    public function unsubscribeAction() {
        $this->view->error = true;
        $this->view->success = false;
        $this->loadTheme();
        $this->initPageView();
        $objSubscriberHelper = Zend_Registry::get('SubscriberHelper');
        $objSubscriberHelper->setMetaTitle($this->translate->_('Newsletter_unsubscribe'), false);
        $hash = $this->getRequest()->getParam('hash', '');
        $newsletterId = $this->getRequest()->getParam('nid', 0);
        if ($hash != '' && $newsletterId != 0) {
            $subscribers = $this->getModelSubscribers()->loadByHash($hash);
            if (count($subscribers) == 1) {
                $this->view->error = false;
                $confirm = $this->getRequest()->getParam('confirm', '');
                if ($this->getRequest()->isPost() && $confirm == 'true') {
                    $subscriber = $subscribers->current();
                    $data = array(
                        'subscribed' => self::UN_SUBSCRIBED_FLAG_ID
                    );
                    $this->getModelSubscribers()->update($subscriber->id, $data);
                    
                    $data = array();
                    $data['unsubscribed'] = 1;
                    $subscriberStats = $this->getModelNewsletters()->loadSubscribersNewsletterStatistics($subscriber->id, $newsletterId);
                    if (count($subscriberStats) > 0) {
                        $subscriberStat = $subscriberStats->current();
                        $this->getModelNewsletters()->updateNewsletterStatistics($subscriberStat->id, $data);
                    } else {
                        $data['idNewsletter'] = 1;
                        $data['idSubscriber'] = $subscriber->id;
                        $this->getModelNewsletters()->addNewsletterStatistics($data);
                    }
                    $this->view->success = true;
                } else {
                    $this->view->hash = $hash;
                    $this->view->nid = $newsletterId;
                }
            }
        }
    }
    
    /**
     * sendDoubleOptInMail
     */
    private function sendOptInMail($formData, $key, $optInStrategy) {
        $objMail = new Zend_Mail('utf-8');

        $objTransport = null;
        if (!empty($this->core->config->mail->params->host)) {
            // config for SMTP with auth
            $arrConfig = array('auth' => 'login',
                'username' => $this->core->config->mail->params->username,
                'password' => $this->core->config->mail->params->password);

            // smtp
            $objTransport = new Zend_Mail_Transport_Smtp($this->core->config->mail->params->host, $arrConfig);
        }

        // set mail subject
        $objMail->setSubject($this->translate->_('Subscriber_' . $optInStrategy . 'optin_subject'));

        $strUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/subscribe?key=' . $key;

        $objView = new Zend_View();
        $objView->setScriptPath(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->getTheme()->path.'/scripts/');
        $objView->url = $strUrl;
        $objView->translate = $this->translate;
        $strBody = $objView->render('subscriber/mail/' . $optInStrategy . 'OptIn.phtml');

        // set body
        $objMail->setBodyHtml($strBody);

        // set mail from address
        $objMail->setFrom($this->core->config->mail->from->address, $this->core->config->mail->from->name);

        // add to address
        $objMail->addTo($formData['email'], $formData['fname'] . ' ' . $formData['sname']);

        //set header for sending mail
        $objMail->addHeader('Sender', $this->core->config->mail->params->username);

        // send mail now
        if ($this->core->config->mail->transport == 'smtp') {
            $objMail->send($objTransport);
        } else {
            $objMail->send();
        }
    }

    /**
     * getModelSubscribers
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelSubscribers() {
        if (null === $this->objModelSubscribers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/Subscribers.php';
            $this->objModelSubscribers = new Model_Subscribers();
            $this->objModelSubscribers->setLanguageId($this->core->intZooluLanguageId);
        }
        return $this->objModelSubscribers;
    }

    /**
     * getModelCategories
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelCategories() {
        if (null === $this->objModelCategories) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Categories.php';
            $this->objModelCategories = new Model_Categories();
        }

        return $this->objModelCategories;
    }
    
    /**
     * getModelNewsletters
     * @return Model_Newsletters
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelNewsletters() {
        if (null === $this->objModelNewsletters) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'newsletters/models/Newsletters.php';
            $this->objModelNewsletters = new Model_Newsletters();
        }
        return $this->objModelNewsletters;
    }
}

?>