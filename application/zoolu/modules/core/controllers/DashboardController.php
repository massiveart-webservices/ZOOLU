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
 * @package    application.zoolu.modules.cms.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * DashboardController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-21: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Core_DashboardController extends AuthControllerAction
{

    /**
     * request object instance
     * @var Zend_Controller_Request_Abstract
     */
    protected $objRequest;

    /**
     * @var Model_Pages
     */
    protected $objModelPages;

    /**
     * @var Model_Globals
     */
    protected $objModelGlobals;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    /**
     * @var Model_Activities
     */
    protected $objModelActivities;

    /**
     * @var Model_Users
     */
    protected $objModelUsers;

    /**
     * @var integer
     */
    protected $intItemLanguageId;

    /**
     * @var array
     */
    protected $arrRecipients = array();

    /**
     * init
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->objRequest = $this->getRequest();
    }

    /**
     * The default action
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * formAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function formAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->formAction()');
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                /**
                 * get form mode
                 * e.g. OVERLAY
                 */
                $strFormMode = $this->objRequest->getParam('mode', null);

                $this->view->assign('mode', $strFormMode);
                $this->view->assign('translate', $this->core->translate);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * entriesAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function entriesAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->entriesAction()');
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $strFilterType = $this->objRequest->getParam('filter', 'ALL');
                $intOffset = (int) $this->objRequest->getParam('offset', 0);
                $intLimit = (int) $this->objRequest->getParam('limit', 0);

                $objActivities = $this->getModelActivities()->loadActivities($strFilterType, $intOffset, $intLimit);
                $this->view->assign('objElements', $objActivities);
                $this->view->assign('translate', $this->core->translate);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getRecipientsAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getRecipientsAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->getRecipientsAction()');
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intActivityId = $this->objRequest->getParam('id');

                $objRecipients = $this->getModelActivities()->loadRecipientsByActivityId($intActivityId);
                $this->view->assign('objElements', $objRecipients);
                $this->view->assign('translate', $this->core->translate);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getContentLinksAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getContentLinksAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->getContentLinksAction()');
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intActivityId = $this->objRequest->getParam('id');

                $objActivityLinks = $this->getModelActivities()->loadLinksByActivityId($intActivityId);

                $arrElements = array();
                if (count($objActivityLinks) > 0) {
                    $arrTmpLinks = array();
                    $intCounter = 0;
                    foreach ($objActivityLinks as $objLink) {
                        $arrTmpLinks[$objLink->idModules][$intCounter]['relationId'] = $objLink->idRelation;
                        $arrTmpLinks[$objLink->idModules][$intCounter]['linkId'] = $objLink->idLink;
                        $arrTmpLinks[$objLink->idModules][$intCounter]['rootLevelGroupId'] = $objLink->idRootLevelGroups;
                        $intCounter++;
                    }

                    foreach ($arrTmpLinks as $intModuleKey => $arrModuleData) {

                        $strRelationIds = '';
                        $strLinkIds = '';
                        foreach ($arrModuleData as $key => $arrRelationData) {
                            if ($arrRelationData['linkId'] != '' && $arrRelationData['linkId'] > 0) {
                                $strLinkIds .= $arrRelationData['linkId'] . ',';
                            } else {
                                $strRelationIds .= $arrRelationData['relationId'] . ',';
                            }
                        }

                        switch ($intModuleKey) {
                            case $this->core->sysConfig->modules->cms:
                                $arrElements[$intModuleKey] = $this->getModelPages()->getElementsByIds(trim($strRelationIds, ','));
                                break;
                            case $this->core->sysConfig->modules->media:
                                $arrElements[$intModuleKey] = $this->getModelFiles()->getElementsByIds(trim($strRelationIds, ','));
                                break;
                            case $this->core->sysConfig->modules->global:
                                if (trim($strLinkIds, ',') != '') {
                                    $arrElements[$intModuleKey]['products'] = $this->getModelGlobals()->getElementsByIds(trim($strLinkIds, ','), $this->core->sysConfig->root_level_groups->product);
                                }
                                $arrElements[$intModuleKey]['default'] = $this->getModelGlobals()->getElementsByIds(trim($strRelationIds, ','));
                                break;
                        }
                    }
                }

                $this->view->assign('objActivityLinks', $objActivityLinks);
                $this->view->assign('arrElements', $arrElements);
                $this->view->assign('translate', $this->core->translate);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getCommentsAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getCommentsAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->getCommentsAction()');
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intActivityId = $this->objRequest->getParam('id');

                $objComments = $this->getModelActivities()->loadCommentsByActivityId($intActivityId);
                $this->view->assign('objElements', $objComments);
                $this->view->assign('translate', $this->core->translate);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * addAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function addAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->addAction()');
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $arrFormData = $this->objRequest->getPost();

                if (array_key_exists('title', $arrFormData) && $arrFormData != '') {
                    // save activity
                    $objActivity = $this->getModelActivities()->add($arrFormData);

                    if (isset($objActivity->id)) {
                        // save users
                        if (array_key_exists('users', $arrFormData) && $arrFormData['users'] != '') {
                            $this->getModelActivities()->addActivityUsers($objActivity->id, $arrFormData['users']);
                        }
                        // save content links
                        if (array_key_exists('links', $arrFormData) && $arrFormData['links'] != '') {
                            $this->getModelActivities()->addActivityLinks($objActivity->id, $arrFormData['links']);
                        }

                        // send mail
                        $this->sendActivityMail($objActivity->id);
                    }
                }
            }
            $this->view->assign('translate', $this->core->translate);
            $this->renderScript('dashboard/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * deleteAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function deleteAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->deleteAction()');
        $this->_helper->viewRenderer->setNoRender();
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intActivityId = $this->objRequest->getParam('id');
                $this->getModelActivities()->delete($intActivityId);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * addCommentAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function addCommentAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->addCommentAction()');
        $this->_helper->viewRenderer->setNoRender();
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $arrFormData = array();
                $arrFormData['idActivities'] = $this->objRequest->getParam('id');
                $arrFormData['comment'] = $this->objRequest->getParam('comment', '');

                if ($arrFormData['comment'] != '') {
                    // save comment
                    $objActivityComment = $this->getModelActivities()->addComment($arrFormData);
                    // send mail
                    $this->sendActivityMail($objActivityComment->idActivities, $objActivityComment->id);
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * deleteCommentAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function deleteCommentAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->deleteCommentAction()');
        $this->_helper->viewRenderer->setNoRender();
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intCommentId = $this->objRequest->getParam('id');
                $this->getModelActivities()->deleteComment($intCommentId);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * changeStatusAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function changeStatusAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->changeStatusAction()');
        $this->_helper->viewRenderer->setNoRender();
        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intActivityId = $this->objRequest->getParam('id');
                $blnIsChecked = (($this->objRequest->getParam('checked') == 'true') ? true : false);

                $this->getModelActivities()->changeUserStatusByActivityId($intActivityId, $blnIsChecked);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * overlayUsersAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function overlayUsersAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->overlayUsersAction()');
        try {
            $arrSelectedIds = array();

            $strUserIds = $this->objRequest->getParam('userIds');
            if ($strUserIds != '') {
                $strTmpUserIds = trim($strUserIds, '[]');
                $arrSelectedIds = explode('][', $strTmpUserIds);
            }

            $objUsers = $this->getModelUsers()->loadUsers();

            $this->view->assign('elements', $objUsers);
            $this->view->assign('arrSelectedIds', $arrSelectedIds);
            $this->view->assign('overlaytitle', $this->core->translate->_('Assign_contacts'));
            $this->view->assign('translate', $this->core->translate);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getItemLanguageId
     * @param integer $intActionType
     * @return integer
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getItemLanguageId($intActionType = null)
    {
        if ($this->intItemLanguageId == null) {
            if (!$this->getRequest()->getParam("languageId")) {
                $this->intItemLanguageId = $this->getRequest()->getParam("rootLevelLanguageId") != '' ? $this->getRequest()->getParam("rootLevelLanguageId") : $this->core->intZooluLanguageId;
            } else {
                $this->intItemLanguageId = $this->getRequest()->getParam("languageId");
            }
        }
        return $this->intItemLanguageId;
    }

    /**
     * sendActivityMail
     * @param stdClass $objActivity
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function sendActivityMail($intActivityId, $intCommentId = null)
    {
        $this->core->logger->debug('core->controllers->DashboardController->sendActivityMail(' . $intActivityId . ',' . $intCommentId . ')');

        $arrMailData = array();
        $intUserId = 0;
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
        }

        // get activity content
        $objActivity = $this->getModelActivities()->loadActivity($intActivityId);

        if (count($objActivity) > 0) {
            $strCreatorProfile = '/zoolu-statics/images/main/user_default.jpg';
            $blnDefaultImg = true;
            if ($objActivity->filename != '') {
                $strCreatorProfile = $this->core->sysConfig->media->paths->imgbase . $objActivity->path . 'icon32/' . $objActivity->filename;
                $blnDefaultImg = false;
            }

            $arrMailData['subject'] = $this->core->sysConfig->client->title . ' - Neue Nachricht: ' . $objActivity->title;
            $arrMailData['type'] = 'dashboard';

            $arrMailData['body']['author'] = $objActivity->fname . ' ' . $objActivity->sname;
            $arrMailData['body']['image'] = $strCreatorProfile;
            $arrMailData['body']['title'] = $objActivity->title;
            $arrMailData['body']['message'] = $objActivity->description;
            $arrMailData['body']['created'] = $objActivity->created;

            if ($intCommentId !== null) {
                // get comment content
                $objComment = $this->getModelActivities()->loadComment($intCommentId);
                if (count($objComment) > 0) {
                    $strCreatorProfile = '/zoolu-statics/images/main/user_default.jpg';
                    $blnDefaultImg = true;
                    if ($objComment->filename != '') {
                        $strCreatorProfile = $this->core->sysConfig->media->paths->imgbase . $objComment->path . 'icon32/' . $objComment->filename;
                        $blnDefaultImg = false;
                    }

                    // comment
                    $arrMailData['comment']['author'] = $objComment->fname . ' ' . $objComment->sname;
                    $arrMailData['comment']['image'] = $strCreatorProfile;
                    $arrMailData['comment']['message'] = $objComment->comment;
                    $arrMailData['comment']['created'] = $objComment->created;
                }
            }

            // loadRecipients
            $objRecipients = $this->getModelActivities()->loadRecipientsByActivityId($intActivityId);
            if (count($objRecipients) > 0) {
                foreach ($objRecipients as $objRecipient) {
                    if (isset($objRecipient->email) && $objRecipient->email != '' && $intUserId != $objRecipient->idUsers) {
                        $arrData = array();
                        $arrData['email'] = $objRecipient->email;
                        $arrData['name'] = $objRecipient->fname . ' ' . $objRecipient->sname;
                        $this->arrRecipients[] = $arrData;
                    }
                }

                if ($intCommentId !== null) {
                    // add activity creator to recipients
                    if (isset($objActivity->email) && $objActivity->email != '' && $intUserId != $objActivity->idUsersCreator) {
                        $arrData = array();
                        $arrData['email'] = $objActivity->email;
                        $arrData['name'] = $objActivity->fname . ' ' . $objActivity->sname;
                        $this->arrRecipients[] = $arrData;
                    }
                }

                // send mail
                $this->sendMail($arrMailData);
            }
        }
    }

    /**
     * sendMail
     * @param array $arrData
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function sendMail($arrData)
    {
        $this->core->logger->debug('core->controllers->DashboardController->sendMail()');
        try {
            $mail = new Zend_Mail('utf-8');

            $transport = null;
            if(!empty($this->core->config->mail->params->host)){
                //config for SMTP with auth
                $config = array('auth'     => 'login',
                                'username' => $this->core->config->mail->params->username,
                                'password' => $this->core->config->mail->params->password);

                // smtp
                $transport = new Zend_Mail_Transport_Smtp($this->core->config->mail->params->host, $config);
            }

            /**
             * mail content
             */
            $strHtmlBody = '';
            $strHtmlBody .= $this->getMailHtml($arrData);

            /**
             * set mail subject
             */
            $strSubject = '';
            if (array_key_exists('subject', $arrData)) {
                $strSubject = $arrData['subject'];
            }
            $mail->setSubject($strSubject);

            /**
             * set html body
             */
            $mail->setBodyHtml($strHtmlBody);

            /**
             * set default FROM address
             */
            $mail->setFrom($this->core->config->mail->from->address, $this->core->config->mail->from->name);

            if (count($this->arrRecipients) > 0) {
                foreach ($this->arrRecipients as $arrRecipient) {
                    $mail->clearRecipients();
                    $mail->addTo($arrRecipient['email'], $arrRecipient['name']);
                    /**
                     * send mail if mail body is not empty
                     */
                    if ($strHtmlBody != '') {
                        $mail->send($transport);
                    }
                }
            } else {
                $this->core->logger->err('The member email address is empty! No able to send mail');
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getMailHtml
     * @param array $arrData
     * @return string
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getMailHtml($arrData)
    {
        $strReturn = '';

        $arrBody = array();
        if (array_key_exists('body', $arrData)) {
            $arrBody = $arrData['body'];
        }

        $arrComment = array();
        if (array_key_exists('comment', $arrData)) {
            $arrComment = $arrData['comment'];
        }

        $strPostType = 'dashboard';
        if (array_key_exists('type', $arrData)) {
            $strPostType = $arrData['type'];
        }

        $strReturn .= '
    	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
      <html>
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          <title></title>
          <style type="text/css">
            body { margin:0; padding:20px; color:#333333; height:100%; font-size:12px; font-family: Arial, Sans-Serif; background-color:#ffffff; line-height:20px;}            
            h1   { color:#333333; font-weight:bold; font-size:14px; font-family: Arial, Sans-Serif; padding:0; margin: 0; line-height:20px;}
            
            a       { color:#754480; font-size:12px; text-decoration:none; margin:0; padding:0; }
            a:hover { color:#754480; font-size:12px; text-decoration:underline; margin:0; padding:0; }
            
            p    { margin:0 0 10px 0; padding:0; }
            span { line-height:20px; font-size:12px; }
            
            table { border:0px; padding:0; margin:0; }
            tr    { border:0; margin:0; padding:0; }
            td    { margin:0; padding:0; }
            img   { border:0; margin:0; padding:0; }
            
            .type  { line-height:15px;}
            .title { font-size:18px; text-transform:uppercase; font-weight:bold; }
            
            .author  { margin:0 0 10px 0; }
            .name    { font-size:11px; line-height:15px; }
            .name a  { font-size:11px; line-height:15px; }
            .info    { color:#999999; font-size:11px; line-height:15px; }           
            .message { margin:0 0 20px 0; } 
            .messageGray { margin:0 0 20px 0; color: #cccccc; }  
            .messageGray h1 { color: #cccccc; }     
            .footerStrong { font-size:11px; line-height:15px; font-weight:bold; }
            .footer  { color:#999999; font-size:11px; line-height:15px; }
            
            .bbottom { border-bottom: 1px solid #cccccc; height:9px; font-size:0; line-height:9px; }
            .btop    { border-top: 1px solid #cccccc; height:9px; font-size:0; line-height:9px; }
          </style>
        </head>
        <body>
        	<table width="100%" border="0" cellpadding="0" cellspacing="0">
        		<tr>
        			<td width="30%">
        				<img src="http://' . $_SERVER['SERVER_NAME'] . '/zoolu-statics/images/main/logo_zoolu_w.gif" alt="ZOOLU"/>
        			</td>        			
        			<td width="70%" align="right">
        				<span class="type">New ' . $strPostType . ' post from</span><br/>
        				<span class="title">' . $this->core->sysConfig->client->title . '</span>
        			</td>
        		</tr>
        		<tr>
              <td colspan="2" class="bbottom">&nbsp;</td>
            </tr>
        		<tr>
        			<td colspan="2">
        				<br/>';
        if (count($arrComment) > 0) {
            $strReturn .= '
      					<table border="0" cellpadding="0" cellspacing="0" width="100%">	
        					<tr>
        						<td width="50" valign="top">
        							<img src="http://' . $_SERVER['SERVER_NAME'] . $arrBody['image'] . '" width="40" height="40"/>
        						</td>
        						<td>	
      								<div class="author">
        								<span class="name">' . $arrBody['author'] . '</span><br/>
        								<span class="info">' . date('d.m.Y, H:i', strtotime($arrBody['created'])) . '</span>
        							</div>
        							<div class="messageGray">
        								<h1>' . $arrBody['title'] . '</h1>
        								<p>' . nl2br($arrBody['message']) . '</p>
        							</div>
        							<div class="comment">        								
        								<table border="0" cellpadding="0" cellspacing="0" width="100%">	
                					<tr>
                						<td width="40" valign="top">
                							<img src="http://' . $_SERVER['SERVER_NAME'] . $arrComment['image'] . '" width="30" height="30"/>
                						</td>
                						<td>
                							<div class="author">
                								<span class="name">' . $arrComment['author'] . '</span>&nbsp;<span class="info">' . date('d.m.Y, H:i', strtotime($arrComment['created'])) . '</span>
                							</div>
                							<div class="message">
                								<p>' . nl2br($arrComment['message']) . '</p>
                								<a href="#">Write a comment</a>
                							</div>
                						</td>
                				  </tr>
                				</table>
                		  </div>
                		</td>
        					</tr>
        				</table>';
        } else {
            $strReturn .= '
      				  <table border="0" cellpadding="0" cellspacing="0" width="100%">	
        					<tr>
        						<td width="50" valign="top">
        							<img src="http://' . $_SERVER['SERVER_NAME'] . $arrBody['image'] . '" width="40" height="40"/>
        						</td>
        						<td>	
      								<div class="author">
        								<span class="name">' . $arrBody['author'] . '</span><br/>
        								<span class="info">' . date('d.m.Y, H:i', strtotime($arrBody['created'])) . '</span>
        							</div>
        							<div class="message">
        								<h1>' . $arrBody['title'] . '</h1>
        								<p>' . nl2br($arrBody['message']) . '</p>
        								<a href="#">Write a comment</a>
        							</div>
        						</td>
        					</tr>
        				</table>';
        }
        $strReturn .= '
        			</td>
        		</tr>
        		<tr>
              <td colspan="2" class="btop">&nbsp;</td>
            </tr>
        		<tr>
        			<td width="30%"><span class="footerStrong">Do not reply to this email!</span></td>        			
        			<td width="70%" align="right"><span class="footer">ZOOLU is an OpenSource Project by MASSIVE ART WebServices GmbH &bull; &copy;2011</span></td>
        		</tr>
        	</table>
        </body>
      </html>';

        return $strReturn;
    }

    /**
     * getModelPages
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelPages()
    {
        if (null === $this->objModelPages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/Pages.php';
            $this->objModelPages = new Model_Pages();
            $this->objModelPages->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelPages;
    }

    /**
     * getModelGlobals
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelGlobals()
    {
        if (null === $this->objModelGlobals) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/Globals.php';
            $this->objModelGlobals = new Model_Globals();
            $this->objModelGlobals->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelGlobals;
    }

    /**
     * getModelFiles
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelFiles()
    {
        if (null === $this->objModelFiles) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Files.php';
            $this->objModelFiles = new Model_Files();
            $this->objModelFiles->setLanguageId($this->getItemLanguageId());
            $this->objModelFiles->setAlternativLanguageId(Zend_Auth::getInstance()->getIdentity()->languageId);
        }

        return $this->objModelFiles;
    }

    /**
     * getModelActivities
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelActivities()
    {
        if (null === $this->objModelActivities) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Activities.php';
            $this->objModelActivities = new Model_Activities();
        }

        return $this->objModelActivities;
    }

    /**
     * getModelUsers
     * @author Cornelius Hansjakob <cha@massiveart.com>
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

?>
