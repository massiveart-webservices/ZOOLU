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
 * @package    application.zoolu.modules.newsletters.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NewsletterController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-04-28: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Newsletters_NewsletterController extends AuthControllerAction
{

    /**
     * @var GenericForm
     */
    protected $objForm;

    /**
     * request object instance
     * @var Zend_Controller_Request_Abstract
     */
    protected $objRequest;

    /**
     * @var Model_Newsletters
     */
    protected $objModelNewsletters;

    /**
     * @var Model_Templates
     */
    protected $objModelTemplates;

    /**
     * @var Model_GenericForms
     */
    protected $objModelGenericForm;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * @var Model_Subscribers
     */
    protected $objModelSubscribers;

    /**
     * @var Model_Users
     */
    protected $objModelUsers;

    private $blnSent;

    const REMOTE_ID = 'remoteId';

    /**
     * init
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        if (!Security::get()->isAllowed('newsletters', Security::PRIVILEGE_VIEW)) {
            $this->_redirect('/zoolu');
        }
        $this->objRequest = $this->getRequest();
        $this->initCommandChain();
    }
    
    /**
     * init command chain
     * @author Thomas Schedler <tsh@massiveart.com>
     * @return void
     */
    private function initCommandChain()
    {
        $this->core->logger->debug('core->controllers->NewsletterController->initCommandChain()');
        $this->objCommandChain = new CampaignCommandChain();
        $this->objCommandChain->addCommand(new NewsletterCampaignCommand());
    }

    /**
     * renderNewsletter
     * @param Zend_Db_Table_Row $objNewsletter
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    private function renderNewsletter($objNewsletter)
    {
        $objGenericData = $this->getModelNewsletters()->loadGenericForm($objNewsletter);
        
        //Load Template
        $objTemplate = $this->getModelTemplates()->loadTemplateById($objGenericData->Setup()->getTemplateId());

        //Assign the values to the template
        $this->view->assign('setup', $objGenericData->Setup());
        
        // set up translate obj
        $languageCode = $objGenericData->Setup()->getField('language')->getValue();
        if (file_exists(GLOBAL_ROOT_PATH . 'application/website/default/language/website-' . $languageCode . '.mo')) {
            $translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH . 'application/website/default/language/website-' . $languageCode . '.mo');
        } else {
            $translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH . 'application/website/default/language/website-' . $this->core->sysConfig->languages->default->code . '.mo');
        }
        $this->view->translate = $translate;
        
        if (count($objTemplate) > 0) {
            $this->view->assign('template_file', $objTemplate->current()->filename);
        }

        $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/newsletter/' . $this->core->sysConfig->newsletter->theme);
    }

    /**
     * previewAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function previewAction()
    {
        $this->core->logger->debug('core->controllers->NewsletterController->previewAction()');
        //Load the newsletter with the given Id
        $intNewsletterId = $this->getRequest()->getParam('newsletterId');
        $objNewsletters = $this->getModelNewsletters()->load($intNewsletterId);
        if (count($objNewsletters) > 0) {
            $objNewsletter = $objNewsletters->current();
            $this->renderNewsletter($objNewsletter);
            $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/newsletter/' . $this->core->sysConfig->newsletter->theme);
            $this->renderScript('/master.php');
        }
    }

    /**
     * viewAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function viewAction()
    {
        $intNewsletterId = $this->getRequest()->getParam('id', 0);
        if ($intNewsletterId > 0) {
            $this->core->logger->debug('newsletters->controllers->newsletter->viewAction()');
            $this->view->assign('newsletterId', $this->getRequest()->getParam('id'));
        } else {
            $this->_helper->viewRenderer->setNoRender();
        }
    }

    /**
     * statsAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function statsAction()
    {
        $this->core->logger->debug('newsletters->controllers->newsletter->statsAction');
        $newsletterId = $this->getRequest()->getParam('id');
        $newsletterData = $this->getModelNewsletters()->load($newsletterId);
        if (count($newsletterData) > 0 && $newsletterData->current()->sent == 1) {
            $newsletter = $newsletterData->current();
            $objFilterData = $this->getModelRootLevels()->loadRootLevelFilter($newsletter->idRootLevelFilters);
            $objFilter = $objFilterData->current();
            $campaign = $this->objCommandChain->runCommand('campaign:init', array('newsletter' => $newsletter, 'filter' => $objFilter));
            $this->view->assign('campaign', $campaign);
            $this->view->assign('deliveryDate', $newsletter->delivered);
            $this->view->assign('filterTitle', $newsletter->filtertitle);
        } else {
            $this->_helper->viewRenderer->setNoRender();
        }
    }

    /**
     * printAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function printstatsAction()
    {
        $intNewsletterId = $this->getRequest()->getParam('id');
        $strType = $this->getRequest()->getParam('type');

        $objNewsletters = $this->getModelNewsletters()->load($intNewsletterId);

        if (count($objNewsletters) > 0) {
            $objNewsletter = $objNewsletters->current();
            $objCampaign = $this->buildCampaign($objNewsletter);
            $objCampaign->loadStatistics();

            $strFilterTitle = ($objNewsletter->filtertitle == '') ? $this->core->translate->_('All') : $objNewsletter->filtertitle;

            $this->view->assign('type', $strType);
            $this->view->assign('objCampaign', $objCampaign);
            $this->view->assign('strDeliveryDate', $objNewsletter->delivered);
            $this->view->assign('strFilterTitle', $strFilterTitle);
            $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'application/zoolu/modules/newsletters/views/scripts');
        }
    }

    /**
     * sendmessageAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function sendmessageAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->sendmessageAction()');
        $blnTestMail = $this->getRequest()->getParam('test');
        $this->view->assign('test', $blnTestMail);
        $objNewsletters = $this->getModelNewsletters()->load($this->getRequest()->getParam('id'));
        if (count($objNewsletters) > 0) {
            $objNewsletter = $objNewsletters->current();
            $objGenericData = $this->getModelNewsletters()->loadGenericForm($objNewsletter);
            $this->core->logger->debug($blnTestMail);
            if ($blnTestMail == 'true') {
                $strEmail = Zend_Auth::getInstance()->getIdentity()->email;
                $this->view->assign('recipients', $strEmail);
            } else {
                $intRecipients = 0;
                $objFilter = $this->getModelRootLevels()->loadRootLevelFilter($objGenericData->Setup()->getField('filter')->getValue());
                if (count($objFilter) > 0) {
                    $objFilter = $objFilter->current();
                    $this->view->assign('filtertitle', $objFilter->filtertitle);
                    $this->objCommandChain->runCommand('campaign:init', array('newsletter' => $objNewsletter, 'filter' => $objFilter));
                    $intRecipients = $this->objCommandChain->runCommand('recipients:count:get', array());
                }
                $this->view->assign('recipients', $intRecipients);
            }
            $this->view->assign('subject', $objGenericData->Setup()->getField('title')->getValue());
        }
        $this->view->setScriptPath(dirname(__FILE__) . '/../views/scripts');
    }

    /**
     * sendAction
     * @author Daniel Rotter <daniel.rotter@massivart.com>
     * @version 1.0
     */
    public function sendAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->sendAction()');

        $this->_helper->viewRenderer->setNoRender();

        $blnTestSend = $this->getRequest()->getParam('test');

        //Load the newsletter with the given Id
        $intNewsletterId = $this->getRequest()->getParam('newsletterId');
        $objNewsletters = $this->getModelNewsletters()->load($intNewsletterId);
        if (count($objNewsletters) > 0) {
            $objNewsletter = $objNewsletters->current();
            $objGenericData = $this->getModelNewsletters()->loadGenericForm($objNewsletter);
            $objFilter = $this->getModelRootLevels()->loadRootLevelFilter($objGenericData->Setup()->getField('filter')->getValue());
            if (count($objFilter) > 0) {
                $objFilter = $objFilter->current();

                // init before send
                $this->objCommandChain->runCommand('campaign:init', array('newsletter' => $objNewsletter, 'filter' => $objFilter));

                // create or update campaing on a remote system
                $remoteId = $this->objCommandChain->runCommand('campaign:update', array('remoteId' => $objNewsletter->remoteId));
                $this->getModelNewsletters()->update($objGenericData->Setup(), array(self::REMOTE_ID => $remoteId));

                $this->renderNewsletter($objNewsletter);      
                $content = $this->view->render('master.php');
                if ($blnTestSend) {
                    $strEmail = $this->getRequest()->getParam('recipient');
                    // send Testnewsletter
                    $this->objCommandChain->runCommand('newsletter:sendTest', array('content' => $content, 'newsletter' => $objNewsletter, 'email' => $strEmail));
                } else {
                    // ==>
                    $this->objCommandChain->runCommand('newsletter:send', array('content' => $content, 'newsletter' => $objNewsletter));
                    $this->getModelNewsletters()->update($objGenericData->Setup(),
                        array(
                             'sent'               => 1,
                             'delivered'          => date('Y-m-d H:i:s'),
                             'idRootLevelFilters' => $objGenericData->Setup()->getField('filter')->getValue()
                        )
                    );
                }
            }
        }
    }

    /**
     * listAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->listAction()');

        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'title');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

        $objSelect = $this->getModelNewsletters()->getNewsletterTable()->select();
        $objSelect->setIntegrityCheck(false);
        $objSelect->from($this->getModelNewsletters()->getNewsletterTable(), array('id', 'idTemplates', 'remoteId', 'sent', 'title', 'changed'))
            ->joinLeft(array('editor' => 'users'), 'editor.id = newsletters.idUsers', array('editor' => 'CONCAT(`editor`.`fname`, \' \', `editor`.`sname`)'))
            ->where('idRootLevels = ?', $this->getRequest()->getParam('rootLevelId'));
        if ($strSearchValue != '') {
            $objSelect->where('newsletters.title LIKE ?', '%' . $strSearchValue . '%');
        }
        $objSelect->order($strOrderColumn . ' ' . strtoupper($strSortOrder));

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
        $objNewslettersPaginator = new Zend_Paginator($objAdapter);
        $objNewslettersPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objNewslettersPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objNewslettersPaginator->setView($this->view);

        $this->view->assign('paginator', $objNewslettersPaginator);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);
    }

    /**
     * exportAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function exportAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->exportAction()');

        $newsletterId = $this->getRequest()->getParam('id');
        $newsletterData = $this->getModelNewsletters()->load($newsletterId);
        if (count($newsletterData) > 0 && $newsletterData->current()->sent == 1) {
            $newsletter = $newsletterData->current();
            $objFilterData = $this->getModelRootLevels()->loadRootLevelFilter($newsletter->idRootLevelFilters);
            $objFilter = $objFilterData->current();
            $campaign = $this->objCommandChain->runCommand('campaign:init', array('newsletter' => $newsletter, 'filter' => $objFilter));
            $strData = $this->getRequest()->getParam('data');
            $strExport = '';
            switch ($strData) {
                case 'unsubscribes':
                    $arrData = $campaign->getUnsubscribes();
                    break;
                case 'complaints':
                    $arrData = $campaign->getComplaints();
                    break;
                case 'bounces':
                    $arrData = $campaign->getBounces();
                    break;
            }

            if (count($arrData) > 0) {
                $strExport = implode(';', array_keys($arrData[0])) . '
';
                foreach ($arrData as $arrRow) {
                    $strExport .= implode(';', $arrRow) . '
';
                }
            }

            $this->_helper->viewRenderer->setNoRender();

            // fix for IE catching or PHP bug issue
            header("Pragma: public");
            header("Expires: 0"); // set expiration time
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            // browser must download file from server instead of cache

            // force download dialog
            header("Content-Type: application/force-download; charset=utf-8");
            header("Content-Type: application/octet-stream; charset=utf-8");
            header("Content-Type: application/csv; charset=utf-8");

            // Set filename
            header("Content-Disposition: attachment; filename=\"newsletter" . $newsletterId . "-Statistics.csv\"");

            /**
             * The Content-transfer-encoding header should be binary, since the file will be read
             * directly from the disk and the raw bytes passed to the downloading computer.
             * The Content-length header is useful to set for downloads. The browser will be able to
             * show a progress meter as a file downloads. The content-lenght can be determines by
             * filesize function returns the size of a file.
             */
            header("Content-Transfer-Encoding: binary");

            echo $strExport;
        }
    }

    /**
     * addformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addformAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->getaddformAction()');

        try {
            $this->getForm($this->core->sysConfig->generic->actions->add);
            $this->addNewsletterSpecificFormElements();

            /**
             * set action
             */
            $this->objForm->setAction('/zoolu/newsletters/newsletter/add');

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('newsletter/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * addAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->addAction()');

        try {
            $this->getForm($this->core->sysConfig->generic->actions->add);
            $this->addNewsletterSpecificFormElements();

            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

                $arrFormData = $this->objRequest->getPost();
                $this->objForm->Setup()->setFieldValues($arrFormData);

                /**
                 * prepare form (add fields and region to the Zend_Form)
                 */
                $this->objForm->prepareForm();

                if ($this->objForm->isValid($arrFormData)) {
                    /**
                     * set action
                     */
                    $this->objForm->setAction('/zoolu/newsletters/newsletter/edit');

                    $intElementId = $this->objForm->saveFormData();
                    $this->objForm->Setup()->setElementId($intElementId);
                    $this->objForm->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);
                    $this->objForm->getElement('id')->setValue($intElementId);

                    $this->view->assign('blnShowFormAlert', true);
                } else {
                    /**
                     * set action
                     */
                    $this->objForm->setAction('/zoolu/newsletters/newsletter/add');
                    $this->view->assign('blnShowFormAlert', false);
                }
            } else {

                /**
                 * prepare form (add fields and region to the Zend_Form)
                 */
                $this->objForm->prepareForm();
            }

            /**
             * update special field values
             */
            $this->objForm->updateSpecificFieldValues();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('newsletter/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * editformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editformAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->editformAction()');

        try {
            $this->validateItemProperties();

            $this->getForm($this->core->sysConfig->generic->actions->edit);

            /**
             * load generic data
             */
            $this->objForm->loadFormData();
            $this->addNewsletterSpecificFormElements();

            /**
             * set action
             */
            $this->objForm->setAction('/zoolu/newsletters/newsletter/edit');

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('newsletter/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * editAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->editAction()');

        try {
            $this->getForm($this->core->sysConfig->generic->actions->edit);
            $this->addNewsletterSpecificFormElements();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

                $arrFormData = $this->objRequest->getPost();
                $this->objForm->Setup()->setFieldValues($arrFormData);

                /**
                 * prepare form (add fields and region to the Zend_Form)
                 */
                $this->objForm->prepareForm();


                if ($this->objForm->isValid($arrFormData)) {
                    $this->objForm->saveFormData();
                    $this->view->assign('blnShowFormAlert', true);
                } else {
                    $this->view->assign('blnShowFormAlert', false);
                }
            } else {
                /**
                 * prepare form (add fields and region to the Zend_Form)
                 */
                $this->objForm->prepareForm();
            }

            /**
             * update special field values
             */
            $this->objForm->updateSpecificFieldValues();

            /**
             * set action
             */
            $this->objForm->setAction('/zoolu/newsletters/newsletter/edit');


            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('newsletter/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * setViewMetaInfos
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function setViewMetaInfos()
    {
        if (is_object($this->objForm) && $this->objForm instanceof GenericForm) {
            $this->view->version = $this->objForm->Setup()->getElementVersion();
            $this->view->publisher = $this->objForm->Setup()->getPublisherName();
            $this->view->changeUser = $this->objForm->Setup()->getChangeUserName();
            $this->view->changeDate = $this->objForm->Setup()->getChangeDate('d. M. Y, H:i');
            $this->view->templateId = $this->objForm->Setup()->getTemplateId();
            $this->view->statusOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT statusTitles.title AS DISPLAY FROM statusTitles WHERE statusTitles.idStatus = status.id AND statusTitles.idLanguages = ' . $this->objForm->Setup()->getFormLanguageId() . ') AS DISPLAY FROM status', $this->objForm->Setup()->getStatusId());
            $this->view->creatorOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, CONCAT(fname, \' \', sname) AS DISPLAY FROM users', $this->objForm->Setup()->getCreatorId());

            if ($this->objForm->Setup()->getField('url')) {
                $this->view->newsletterurl = $this->objForm->Setup()->getField('url')->getValue();
            }

            $arrSecurityCheck = array();
            if (!Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_VIEW, false, false)) {
                $arrSecurityCheck = array(
                    'ResourceKey'           => Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_%d',
                    'Privilege'             => Security::PRIVILEGE_VIEW,
                    'CheckForAllLanguages'  => false,
                    'IfResourceNotExists'   => false
                );
            }

            $blnGeneralDeleteAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_DELETE, false, false);
            $blnGeneralUpdateAuthorization = false;
            if ($this->getRequest()->getParam('sent') == false) {
                $blnGeneralUpdateAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_UPDATE, false, false);
            } else {
                $blnGeneralUpdateAuthorization = false;
            }
            $this->view->authorizedDelete = ($this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->add) ? false : (($blnGeneralDeleteAuthorization == true) ? $blnGeneralDeleteAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_' . $this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_DELETE, false, false));
            $this->view->authorizedUpdate = ($blnGeneralUpdateAuthorization == true) ? $blnGeneralUpdateAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_' . $this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_UPDATE, false, false);
        }
    }

    /**
     * deleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->deleteAction()');

        try {
            $this->getModelNewsletters();

            $blnGeneralDeleteAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objRequest->getParam("rootLevelId"), Security::PRIVILEGE_DELETE, false, false);
            $blnDeleteAuthorization = ($blnGeneralDeleteAuthorization == true) ? $blnGeneralDeleteAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objRequest->getParam("rootLevelId") . '_' . $this->objRequest->getParam("languageId"), Security::PRIVILEGE_DELETE, false, false);

            if ($blnDeleteAuthorization == true) {
                if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                    $this->objModelNewsletters->delete($this->objRequest->getParam("id"));

                    $this->view->blnShowFormAlert = true;
                }
            }

            $this->renderScript('newsletter/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * listdeleteAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function listdeleteAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->listdeleteAction()');

        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $strTmpNewsletterIds = trim($this->objRequest->getParam('values'), '[]');
                $arrNewsletterIds = array();
                $arrNewsletterIds = explode('][', $strTmpNewsletterIds);

                foreach ($arrNewsletterIds as $intNewsletterId) {
                    $objNewsletters = $this->getModelNewsletters()->load($intNewsletterId);
                    if (count($objNewsletters) > 0) {
                        foreach ($objNewsletters as $objNewsletter) {
                            $this->objModelNewsletters->delete($intNewsletterId);
                        }
                    }
                }
            }
            $this->_forward('list', 'subscriber', 'contacts');

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * dashboardAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function dashboardAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->dashboardAction()');

        try {
            $this->getModelFolders();

            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intRootLevelId = $this->objRequest->getParam('rootLevelId');
                $intLimitNumber = 10;

                $objNewsletters = $this->objModelFolders->loadLimitedRootLevelChilds($intRootLevelId, $intLimitNumber);

                $this->view->assign('objNewsletters', $objNewsletters);
                $this->view->assign('limit', $intLimitNumber);
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * changetemplateAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function changetemplateAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->changetemplateAction()');

        try {
            $objGenericData = new GenericData();

            $objGenericData->Setup()->setFormId($this->objRequest->getParam("formId"));
            $objGenericData->Setup()->setFormVersion($this->objRequest->getParam("formVersion"));
            $objGenericData->Setup()->setFormTypeId($this->objRequest->getParam("formTypeId"));
            $objGenericData->Setup()->setTemplateId($this->objRequest->getParam("templateId"));
            $objGenericData->Setup()->setElementId($this->objRequest->getParam("id"));
            $objGenericData->Setup()->setElementLinkId($this->objRequest->getParam("linkId", -1));
            $objGenericData->Setup()->setElementTypeId($this->objRequest->getParam("newsletterTypeId"));
            $objGenericData->Setup()->setParentTypeId($this->objRequest->getParam("parentTypeId"));
            $objGenericData->Setup()->setRootLevelId($this->objRequest->getParam("rootLevelId"));
            $objGenericData->Setup()->setRootLevelGroupId($this->objRequest->getParam("rootLevelGroupId"));
            $objGenericData->Setup()->setParentId($this->objRequest->getParam("parentFolderId"));
            $objGenericData->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);
            $objGenericData->Setup()->setFormLanguageId($this->core->intZooluLanguageId);
            $objGenericData->Setup()->setModelSubPath('newsletters/models/');

            /**
             * change Template
             */
            $objGenericData->changeTemplate($this->objRequest->getParam("newTemplateId"));

            $this->objRequest->setParam("formId", $objGenericData->Setup()->getFormId());
            $this->objRequest->setParam("templateId", $objGenericData->Setup()->getTemplateId());
            $this->objRequest->setParam("formVersion", $objGenericData->Setup()->getFormVersion());

            $this->getForm($this->core->sysConfig->generic->actions->edit);

            /**
             * load generic data
             */
            $this->objForm->setGenericSetup($objGenericData->Setup());
            $this->addNewsletterSpecificFormElements();

            /**
             * set action
             */
            if (intval($this->objRequest->getParam('id')) > 0) {
                $this->objForm->setAction('/zoolu/newsletters/newsletter/edit');
            } else {
                $this->objForm->setAction('/zoolu/newsletters/newsletter/add');
            }

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('newsletter/form.phtml');

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * changelanguageAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function changelanguageAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->changelanguageAction()');

        try {

            if (intval($this->objRequest->getParam('id')) > 0) {
                $objNewsletterData = $this->getModelNewsletters()->loadFormAndTemplateById($this->objRequest->getParam('id'));
                if (count($objNewsletterData) == 1) {
                    $objNewsletter = $objNewsletterData->current();
                    if ((int) $objNewsletter->idTemplates > 0) $this->objRequest->setParam('templateId', $objNewsletter->idTemplates);
                    if ($objNewsletter->genericFormId != '') $this->objRequest->setParam('formId', $objNewsletter->genericFormId);
                }
                $this->_forward('geteditform');
            } else {
                $this->_forward('getaddform');
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getoverlaysearchAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getoverlaysearchAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->getoverlaysearchAction()');

        $this->view->currLevel = $this->objRequest->getParam('currLevel');
        $this->view->overlaytitle = $this->core->translate->_('Search');
    }

    /**
     * overlaysearchAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function overlaysearchAction()
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->getoverlaysearchAction()');

        $strSearchValue = $this->objRequest->getParam('searchValue');

        if ($strSearchValue != '') {
            $this->view->searchResult = $this->getModelNewsletters()->search($strSearchValue);
        }

        $this->view->searchValue = $strSearchValue;
        $this->view->currLevel = $this->objRequest->getParam('currLevel');
    }

    /**
     * addnewsletterlinkAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addnewsletterlinkAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $objNewsletter = new stdClass();
        $objNewsletter->parentId = $this->objRequest->getParam('parentFolderId');
        $objNewsletter->rootLevelId = $this->objRequest->getParam('rootLevelId');
        $objNewsletter->isStartNewsletter = $this->objRequest->getParam('isStartNewsletter');
        $objNewsletter->newsletterId = $this->objRequest->getParam('linkId');

        $this->getModelNewsletters()->addLink($objNewsletter);
    }

    /**
     * getForm
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function getForm($intActionType = null)
    {
        $this->core->logger->debug('newsletters->controllers->NewsletterController->getForm(' . $intActionType . ')');

        try {

            $strFormId = $this->objRequest->getParam("formId", $this->core->sysConfig->form->ids->newsletters->default);
            $intTemplateId = $this->objRequest->getParam("templateId");

            /**
             * if there is now formId, try to load form template
             */
            if ($strFormId == '') {

                if ($intTemplateId != '') {
                    /**
                     * get files
                     */
                    $this->getModelTemplates();
                    $objTemplateData = $this->objModelTemplates->loadTemplateById($intTemplateId);

                    if (count($objTemplateData) == 1) {
                        $objTemplate = $objTemplateData->current();

                        /**
                         * set form id from template
                         */
                        $strFormId = $objTemplate->genericFormId;
                    } else {
                        throw new Exception('Not able to create a form, because there is no form id!');
                    }
                } else {
                    throw new Exception('Not able to create a form, because there is no form id!');
                }
            }

            $intFormVersion = ($this->objRequest->getParam("formVersion") != '') ? $this->objRequest->getParam("formVersion") : null;
            $intElementId = ($this->objRequest->getParam("id") != '') ? $this->objRequest->getParam("id") : null;

            $objFormHandler = FormHandler::getInstance();
            $objFormHandler->setFormId($strFormId);
            $objFormHandler->setTemplateId($intTemplateId);
            $objFormHandler->setFormVersion($intFormVersion);
            $objFormHandler->setActionType($intActionType);
            $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
            $objFormHandler->setElementId($intElementId);

            $this->objForm = $objFormHandler->getGenericForm();

            /**
             * set newsletter default & specific form values
             */
            $this->objForm->Setup()->setCreatorId((($this->objRequest->getParam("creator") != '') ? $this->objRequest->getParam("creator") : Zend_Auth::getInstance()->getIdentity()->id));
            $this->objForm->Setup()->setStatusId((($this->objRequest->getParam("idStatus") != '') ? $this->objRequest->getParam("idStatus") : $this->core->sysConfig->form->status->default));
            $this->objForm->Setup()->setTemplateId($intTemplateId);
            $this->objForm->Setup()->setRootLevelId((($this->objRequest->getParam("rootLevelId") != '') ? $this->objRequest->getParam("rootLevelId") : null));
            $this->objForm->Setup()->setRootLevelGroupId((($this->objRequest->getParam("rootLevelGroupId") != '') ? $this->objRequest->getParam("rootLevelGroupId") : 0));
            $this->objForm->Setup()->setModelSubPath('newsletters/models/');

            /**
             * add currlevel hidden field
             */
            $this->objForm->addElement('hidden', 'currLevel', array('value' => $this->objRequest->getParam("currLevel"), 'decorators' => array('Hidden'), 'ignore' => true));

            /**
             * add newsletterTye hidden field (folder, newsletter, ...)
             */
            $this->objForm->addElement('hidden', 'newsletterType', array('value' => $this->objRequest->getParam("newsletterType"), 'decorators' => array('Hidden'), 'ignore' => true));

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * addNewsletterSpecificFormElements
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function addNewsletterSpecificFormElements()
    {
        if (is_object($this->objForm) && $this->objForm instanceof GenericForm) {
            /**
             * add newsletter specific hidden fields
             */
            $this->objForm->addElement('hidden', 'creator', array('value' => $this->objForm->Setup()->getCreatorId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'idStatus', array('value' => $this->objForm->Setup()->getStatusId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objForm->Setup()->getRootLevelId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'rootLevelTypeId', array('value' => $this->objForm->Setup()->getRootLevelTypeId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'rootLevelGroupId', array('value' => $this->objForm->Setup()->getRootLevelGroupId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'sent', array('value' => $this->getRequest()->getParam('sent'), 'decorators' => array('Hidden')));
        }
    }

    /**
     * validateItemProperties
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function validateItemProperties()
    {
        if ($this->objRequest->getParam('formId', '') == '' && $this->objRequest->getParam('formVersion', '') == '' && (int) $this->objRequest->getParam('id') > 0) {
            $objData = $this->getModelNewsletters()->loadProperties($this->objRequest->getParam('id'));
            if (count($objData) > 0) {
                $objNewsletterProperties = $objData->current();
                $this->objRequest->setParam('formId', $objNewsletterProperties->genericFormId);
                $this->objRequest->setParam('formVersion', $objNewsletterProperties->genericFormVersion);
                // has no templates at the moment
                // $this->objRequest->setParam('templateId', $objNewsletterProperties->templateId);
            }
        }
    }

    /**
     * getModelNewsletters
     * @return Model_Newsletters
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelNewsletters()
    {
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

    /**
     * getModelTemplates
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelTemplates()
    {
        if (null === $this->objModelTemplates) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Templates.php';
            $this->objModelTemplates = new Model_Templates();
        }

        return $this->objModelTemplates;
    }

    /**
     * getModelGenericForm
     * @return Model_GenericForms
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelGenericForm()
    {
        if (null === $this->objModelGenericForm) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/GenericForms.php';
            $this->objModelGenericForm = new Model_GenericForms();
            $this->objModelGenericForm->setLanguageId($this->objRequest->getParam("languageId", $this->core->intZooluLanguageId));
        }

        return $this->objModelGenericForm;
    }

    /**
     * getModelRootLevels
     * @return Model_RootLevels
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
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
            $this->objModelRootLevels->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelRootLevels;
    }

    /**
     * getModelSubscribers
     * @return Model_Subscribers
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function getModelSubscribers()
    {
        if (null === $this->objModelSubscribers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Subscribers.php';
            $this->objModelSubscribers = new Model_Subscribers();
            $this->objModelSubscribers->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelSubscribers;
    }

    /**
     * getModelUsers
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelUsers()
    {
        if (null === $this->objModelUsers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/Users.php';
            $this->objModelUsers = new Model_Users();
        }

        return $this->objModelUsers;
    }
}