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
 * @package    application.zoolu.modules.core.properties.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Subscribers_SubscriberController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-05-05: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Contacts_SubscriberController extends AuthControllerAction
{
    const SUBSCRIBER_GENERIC_FORM_ID = 'DEFAULT_SUBSCRIBER';
    const MYSQL_ERROR_DUPLICATE_ENTRY = 1062;
    const IMPORT_PREVIEW_COUNT = 100;

    /**
     * @var GenericForm
     */
    protected $objForm;

    /**
     * @var inter
     */
    protected $intItemLanguageId;

    /**
     * request object instance
     * @var Zend_Controller_Request_Abstract
     */
    protected $objRequest;

    /**
     * @var Model_Subscribers
     */
    public $objModelSubscribers;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * @var Model_GenericData
     */
    protected $objModelGenericData;

    /**
     * @var Model_Categories
     */
    protected $objModelCategories;

    /**
     * @var CommandChain
     */
    protected $objCommandChain;

    /**
     * @var array
     */
    private $arrEncodings = array('ISO-8859', 'UTF8');

    /**
     * init
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        if (!Security::get()->isAllowed('contact', Security::PRIVILEGE_VIEW)) {
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
        $this->core->logger->debug('core->controllers->SubscriberController->initCommandChain()');
        $this->objCommandChain = new CommandChain();
        $this->objCommandChain->addCommand(new ContactReplicationCommand());
    }

    /**
     * The default action
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * listAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->listAction()');

        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'sname');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');
        $intRootLevelFilterId = $this->getRequest()->getParam('rootLevelFilter', null);
        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');
        $bounced = $this->getRequest()->getParam('bounced', '');

        if ($bounced != '' && ($bounced == $this->core->sysConfig->contact->bounce_mapping->hard || $bounced == $this->core->sysConfig->contact->bounce_mapping->soft) ) {
            $objSelect = $this->getModelSubscribers()->loadBounced($bounced, $intRootLevelId, $strSearchValue, $strSortOrder, $strOrderColumn, true);
        } else {
            $objSelect = $this->getModelSubscribers()->loadByRootLevelFilter($intRootLevelId, $intRootLevelFilterId, $strSearchValue, $strSortOrder, $strOrderColumn, true);
        }
        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
        $objPaginator = new Zend_Paginator($objAdapter);
        $objPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objPaginator->setView($this->view);

        $this->view->assign('paginator', $objPaginator);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);
        $this->view->assign('rootLevelFilterId', $intRootLevelFilterId);
        $this->view->assign('rootLevelId', $this->getRequest()->getParam('rootLevelId'));
    }

    /**
     * listfilterAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function listfilterAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->listfilterAction()');

        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');
        $objRootLevelFilters = $this->getModelRootLevels()->loadRootLevelFilters($intRootLevelId);

        $this->view->assign('rootLevelFilters', $objRootLevelFilters);
    }

    /**
     * exportlistAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function exportlistAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->exportlistAction()');

        $intRootLevelFilterId = $this->getRequest()->getParam('rootLevelFilter', null);
        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');
        $bounced = $this->getRequest()->getParam('bounced', '');
        
        if ($bounced != '' && ($bounced == $this->core->sysConfig->contact->bounce_mapping->hard || $bounced == $this->core->sysConfig->contact->bounce_mapping->soft) ) {
            $objRowset = $this->getModelSubscribers()->loadBounced($bounced, $intRootLevelId, '', 'ASC', 'sname', false, true);
        } else {
            $objRowset = $this->getModelSubscribers()->loadByRootLevelFilter($intRootLevelId, $intRootLevelFilterId, '', 'ASC', 'sname', false, true);
        }

        do {
            unset($objRowset->current()->type);
            unset($objRowset->current()->genericFormId);
            unset($objRowset->current()->version);
            unset($objRowset->current()->changed);
            $objRowset->next();
        } while ($objRowset->current());
        $strExport = Export::exportRowsetInCsv($objRowset);

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
        header("Content-Disposition: attachment; filename=\"subscribers" . date('Y-m-d') . ".csv\"");

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

    /**
     * importformAction
     * @author Daniel Rotter
     * @version 1.0
     */
    public function importuploadAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->importuploadAction()');

        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');

        $this->view->assign('rootLevelId', $intRootLevelId);
        $this->view->assign('formAction', '/zoolu/contacts/subscriber/upload');
    }

    /**
     * importformAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function importformAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->importformAction');

        $objForm = $this->getImportForm();

        $this->view->assign('form', $objForm);
    }

    /**
     * previewimportAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function previewimportAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->previewimportAction()');
        $this->_helper->viewRenderer->setNoRender();

        $blnChangeEncoding = ($this->arrEncodings[$this->getRequest()->getParam('encoding')] == 'ISO-8859');
        $blnImportHeader = $this->getRequest()->getParam('importHeader');

        $strFile = GLOBAL_ROOT_PATH . '/uploads/subscribers/' . $this->getRequest()->getParam('fileId');
        $fh = fopen($strFile, 'r');

        $strOutput = '<div id="importPreview">';
        $strOutput .= '<table>';
        //Show a few lines
        for ($i = 0; $i < self::IMPORT_PREVIEW_COUNT; $i++) {
            //Read line
            $strLine = fgets($fh);
            if ($strLine != '') {
                //Change encoding if neccessary
                if ($blnChangeEncoding) {
                    $strLine = utf8_encode($strLine);
                }
                //Explode the field
                $arrFields = array_map('trimQuotes', explode(';', $strLine));
                $strOutput .= '<tr>';
                foreach ($arrFields as $strField) {
                    $strOutputTag = (!$blnImportHeader && $i == 0) ? 'th' : 'td';
                    $strOutput .= '<' . $strOutputTag . '>' . $strField . '</' . $strOutputTag . '>';
                }
                $strOutput .= '</tr>';
            }
        }

        $strOutput .= '</table>';
        $strOutput .= '</div>';
        $this->getResponse()->setBody($strOutput);
    }

    /**
     * importAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function importAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->importAction()');
        $this->_helper->viewRenderer->setNoRender();

        $arrErrors = array();
        $arrWarnings = array();

        $intSubscriberAdded = 0;
        $intSubscriberUpdated = 0;
        $blnEmailAddress = true;
        $arrFields = $this->getRequest()->getParams();
        $strFileId = $arrFields['fileId'];
        $intRootLevelId = $arrFields['rootLevelId'];
        $arrPortals = array_key_exists('portal', $arrFields) ? $arrFields['portal'] : array();
        $arrInterestGroups = array_key_exists('interest_group', $arrFields) ? $arrFields['interest_group'] : array();
        $arrLanguages = array_key_exists('languages', $arrFields) ? $arrFields['languages'] : array();
        $blnImportHeader = $arrFields['import_header'];
        $blnChangeEncoding = ($this->arrEncodings[$arrFields['encoding']] == 'ISO-8859');
        unset($arrFields['']);
        unset($arrFields['module']);
        unset($arrFields['controller']);
        unset($arrFields['action']);
        unset($arrFields['portal']);
        unset($arrFields['interest_group']);
        unset($arrFields['languages']);
        unset($arrFields['fileId']);
        unset($arrFields['rootLevelId']);
        unset($arrFields['import_header']);
        unset($arrFields['encoding']);

        $strFile = GLOBAL_ROOT_PATH . '/uploads/subscribers/' . $strFileId;
        $fh = fopen($strFile, 'r');

        $strLine = fgets($fh); //header line
        $arrHeadlines = array_map('trimQuotes', explode(';', $strLine));
        $this->removeEmptyArrayElements($arrHeadlines);
        if ($blnImportHeader) {
            rewind($fh);
        }
        while (!feof($fh)) {
            $strLine = fgets($fh);
            if ($blnChangeEncoding) {
                $strLine = utf8_encode($strLine);
            }

            if ($strLine != '') {
                $arrData = array();
                $arrTmpData = explode(';', $strLine);
                foreach ($arrHeadlines as $intCount => $strHeadline) {
                    if (($strKey = array_search('headline' . $intCount, $arrFields)) !== false) {
                        $arrData[$strKey] = trimQuotes($arrTmpData[$intCount]);
                    }
                }
                $arrData['subscribed'] = $this->core->sysConfig->contact->subscribed;
                $arrData['idRootLevels'] = $intRootLevelId;
                $arrData['idGenericForms'] = $this->core->sysConfig->subscriber->default->genericFormId;
                $arrData['creator'] = Zend_Auth::getInstance()->getIdentity()->id;
                $arrData['idUsers'] = Zend_Auth::getInstance()->getIdentity()->id;
                $arrData['created'] = date('Y-m-d H:i:s');

                try {
                    //Check Preconditions

                    //Valid Email Address?
                    $validator = new Zend_Validate_EmailAddress();
                    if (!$validator->isValid($arrData['email'])) {
                        require_once(GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/InvalidAddressException.php');
                        throw new InvalidAddressException($arrData['email']);
                    }
                    //Does the host exist?
                    $arrMail = explode('@', $arrData['email']);
                    if (!checkdnsrr($arrMail[1])) {
                        require_once(GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/HostNotFoundException.php');
                        throw new HostNotFoundException($arrData['email']);
                    }

                    //Update Userdata
                    $intSubscriberId = null;
                    $blnUpdate = false; //Update an old user or insert a new one
                    if (isset($arrData['email']) && $arrData['email'] != '') {
                        $objSubscriber = $this->getModelSubscribers()->loadByEmail($arrData['email']);
                        if (count($objSubscriber) > 0) {
                            //Check Preconditions
                            if ($objSubscriber->current()->bounced == $this->core->sysConfig->contact->bounce_mapping->hard) {
                                require_once(GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/HardBounceException.php');
                                throw new HardBounceException($arrData['email']);
                            }
                            if ($objSubscriber->current()->dirty == $this->core->sysConfig->contact->dirty) {
                                require_once(GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/DirtyException.php');
                                throw new DirtyException($arrData['email']);
                            }
                            if ($objSubscriber->current()->subscribed != $this->core->sysConfig->subscribed) {
                                $arrWarnings[] = $arrData['email'] . ' was unsubscribed!';
                            }
                            $this->core->logger->warn('Subscriber ' . $arrData['email'] . ' already exists! User will be updated.');
                            $intSubscriberId = $objSubscriber->current()->id;
                            $blnUpdate = true;
                            $this->getModelSubscribers()->getSubscriberTable()->update($arrData, $this->core->dbh->quoteInto('email = ?', $arrData['email']));
                            $intSubscriberUpdated++;
                        } else {
                            $intSubscriberId = $this->getModelSubscribers()->getSubscriberTable()->insert($arrData);
                            $intSubscriberAdded++;
                        }
                        
                        //Update Interests
                        $this->updateInterests($arrPortals, $intSubscriberId, $this->core->sysConfig->contact->field_mappings->portal);
                        $this->updateInterests($arrInterestGroups, $intSubscriberId, $this->core->sysConfig->contact->field_mappings->interestgroup);
                        $this->updateInterests($arrLanguages, $intSubscriberId, $this->core->sysConfig->contact->field_mappings->language);
                    } else {
                        $blnEmailAddress = false;
                    }
                } catch (InvalidAddressException $exc) {
                    $arrErrors[] = $exc->getEmail() . ' is not a valid E-Mail Address!';
                } catch (HardBounceException $exc) {
                    $arrErrors[] = $exc->getEmail() . ' is hard bounced!';
                } catch (DirtyException $exc) {
                    $arrErrors[] = $exc->getEmail() . ' has changed!';
                } catch (HostNotFoundException $exc) {
                    $arrErrors[] = $exc->getEmail() . ' is not a valid Host!';
                }
            }
        }
        //Delete the file in the end
        fclose($fh);
        unlink(GLOBAL_ROOT_PATH . '/uploads/subscribers/' . $strFileId);

        //Return a success message
        echo str_replace('%t', $intSubscriberUpdated, str_replace('%s', $intSubscriberAdded, $this->core->translate->_('Import_success_message')));
        
        if (count($arrErrors)) {
            echo '<br><br>';
            echo count($arrErrors) . ' Errors occured: ';
            foreach ($arrErrors as $error) {
                echo $error . '<br/>';
            }
        }
        
        if (!$blnEmailAddress) {
            echo '<br /><br />';
            echo $this->core->translate->_('Missing_email', false);
        }
    }

    /**
     * updateInterests
     * @author Daniel Rotter <daniel.roter@massiveart.com>
     * @version 1.0
     */
    private function updateInterests($arrInterestGroups, $intSubscriberId, $intFieldId)
    {
        $objTable = $this->getModelGenericData()->getGenericTable('subscriber-' . self::SUBSCRIBER_GENERIC_FORM_ID . '-1-InstanceMultiFields');
        $objTable->delete('idSubscribers = ' . $intSubscriberId . ' AND idFields = ' . $intFieldId);
        foreach ($arrInterestGroups as $intInterestGroupId) {
            $arrInterestData = array('idSubscribers' => $intSubscriberId, 'idRelation' => $intInterestGroupId, 'idFields' => $intFieldId);
            $objTable->insert($arrInterestData);
        }
    }

    /**
     * uploadAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function uploadAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->uploadAction()');
        $this->_helper->viewRenderer->setNoRender();

        if (isset($_FILES['importFile'])) {
            if (isset($_FILES['importFile']['tmp_name'])) {
                $target_path = dirname(__FILE__) . '/../../../../../uploads/subscribers';

                if (!is_dir($target_path)) {
                    mkdir($target_path);
                }

                $strFileId = uniqid();
                $target_path = $target_path . '/' . basename($strFileId);

                if (move_uploaded_file($_FILES['importFile']['tmp_name'], $target_path)) {
                    $this->core->logger->debug('The file ' . basename($_FILES['importFile']['name']) . ' has been uploaded to' . $target_path);

                    $this->_forward('list', 'index', 'contacts', array('import' => true, 'success' => true, 'fileId' => $strFileId, 'rootLevelId' => $this->getRequest()->getParam('importRootLevelId')));
                } else {
                    $this->core->logger->debug('There was an error uploading the file!');
                    $this->_forward('list', 'index', 'contacts', array('import' => true, 'success' => false));
                }
            }
        }
    }

    /**
     * getImportForm
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function getImportForm()
    {

        //FIXME: source out
        $strFileId = $this->getRequest()->getParam('fileId');
        $fh = fopen(GLOBAL_ROOT_PATH . '/uploads/subscribers/' . $strFileId, 'r');
        $strLine = fgets($fh);
        $arrHeadlines = array_map('trimQuotes', explode(';', $strLine));
        $this->removeEmptyArrayElements($arrHeadlines);

        $objForm = new Zend_Form();

        /**
         * Use our own PluginLoader
         */
        $objLoader = new PluginLoader();
        $objLoader->setPluginLoader($objForm->getPluginLoader(PluginLoader::TYPE_FORM_ELEMENT));
        $objLoader->setPluginType(PluginLoader::TYPE_FORM_ELEMENT);
        $objForm->setPluginLoader($objLoader, PluginLoader::TYPE_FORM_ELEMENT);

        /**
         * clear all decorators
         */
        $objForm->clearDecorators();

        /**
         * add standard decorators
         */
        $objForm->addDecorator('TabContainer');
        $objForm->addDecorator('FormElements');
        $objForm->addDecorator('Form');

        /**
         * add form prefix path
         */
        $objForm->addPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/', 'decorator');

        /**
         * elements prefixes
         */
        $objForm->addElementPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/', 'decorator');

        /**
         * regions prefixes
         */
        $objForm->addDisplayGroupPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/');

        $objForm->setAttrib('id', 'importForm');
        $objForm->setAttrib('onsubmit', 'return false;');
        $objForm->addElement('hidden', 'id', array('decorators' => array('Hidden')));
        $objForm->addElement('hidden', 'fileId', array('decorators' => array('Hidden'), 'value' => $this->getRequest()->getParam('fileId')));
        $objForm->addElement('hidden', 'rootLevelId', array('decorators' => array('Hidden'), 'value' => $this->getRequest()->getParam('rootLevelId')));

        //Encoding
        $objForm->addElement('select', 'encoding', array('label' => $this->core->translate->_('Encoding'), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'select', 'required' => true, 'MultiOptions' => $this->arrEncodings));

        //RootLevels
        $arrTmpOptions = $this->core->dbh->query($this->core->dbh->quoteInto('SELECT tbl.id AS id, rootLevelTitles.title AS title FROM rootLevelTitles INNER JOIN rootLevels AS tbl ON tbl.id = rootLevelTitles.idRootLevels WHERE tbl.idRootLevelTypes = 1 AND tbl.active = 1 AND rootLevelTitles.idLanguages = ? ORDER BY rootLevelTitles.title', $this->core->intZooluLanguageId))->fetchAll();
        $arrOptions = array();
        foreach ($arrTmpOptions as $arrOption) {
            $arrOptions[$arrOption['id']] = $arrOption['title'];
        }
        $objForm->addElement('multiCheckbox', 'portal', array('label' => $this->core->translate->_('Import_portals'), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'multiCheckbox', 'required' => false, 'multiOptions' => $arrOptions));
        
        //Interest Groups
        $arrTmpOptions = $this->core->dbh->query($this->core->dbh->quoteInto('SELECT tbl.id AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = ?, categories AS rootCat WHERE rootCat.id = ' . $this->core->sysConfig->contact->interest_group_categories->interests . ' AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt  ORDER BY tbl.lft, categoryTitles.title', $this->core->intZooluLanguageId))->fetchAll();
        $arrOptions = array();
        foreach ($arrTmpOptions as $arrOption) {
            $arrOptions[$arrOption['id']] = $arrOption['title'];
        }
        $objForm->addElement('multiCheckbox', 'interest_group', array('label' => $this->core->translate->_('Import_interest_groups'), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'multiCheckbox', 'required' => false, 'multiOptions' => $arrOptions));

        //Languages
        $arrTmpOptions = $this->core->dbh->query($this->core->dbh->quoteInto('SELECT tbl.id AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = ?, categories AS rootCat WHERE rootCat.id = ' . $this->core->sysConfig->contact->interest_group_categories->languages . ' AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt  ORDER BY tbl.lft, categoryTitles.title', $this->core->intZooluLanguageId))->fetchAll();
        $arrOptions = array();
        foreach ($arrTmpOptions as $arrOption) {
            $arrOptions[$arrOption['id']] = $arrOption['title'];
        }
        $objForm->addElement('multiCheckbox', 'languages', array('label' => $this->core->translate->_('Languages'), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'multiCheckbox', 'required' => false, 'multiOptions' => $arrOptions));

        $importHeaderElement = new Zend_Form_Element_Checkbox('import_header'); 
        $importHeaderElement ->setLabel($this->core->translate->_('Import_header'))
                             ->setDecorators(array( array('ViewHelper', array('helper' => 'formCheckbox')), array('Errors'), array('Label', array('placement' =>'APPEND'))));
        $objForm->addElement($importHeaderElement);
        $objForm->addElement('textDisplay', 'blank', array('label' => '', 'decorators' => array('Input'), 'columns' => 12));
        
        $objForm->addDisplayGroup(array('encoding', 'portal', 'interest_group', 'languages'), 'preferences-group');
        $objForm->getDisplayGroup('preferences-group')->setLegend($this->core->translate->_('Import_preferences', false));
        $objForm->getDisplayGroup('preferences-group')->setDecorators(array('FormElements', 'Region'));

        $objForm->addDisplayGroup(array('import_header', 'blank'), 'row-import-preves');
        $objForm->getDisplayGroup('row-import-preves')->setLegend($this->core->translate->_('Import_header', false));
        $objForm->getDisplayGroup('row-import-preves')->setDecorators(array('FormElements', 'Region'));

        
        //Assignments
        $arrOptions = array();
        foreach ($arrHeadlines as $intCount => $strHeadline) {
            $arrOptions['headline' . $intCount] = $strHeadline;
        }

        $arrTmpOptions = $this->core->sysConfig->subscriber->import_fields->import_field->toArray();

        $arrAssignment = array();
        foreach ($arrTmpOptions as $arrTmpOption) {
            $arrAssignment[] = $arrTmpOption['title'];
            $label = ($arrTmpOption['title'] != '') ? $this->core->translate->_($arrTmpOption['title'], false) : '(' . $this->core->translate->_('Empty') . ')';
            $objForm->addElement('select', $arrTmpOption['title'], array('label' => $label, 'decorators' => array('Input'), 'columns' => 6, 'class' => 'select', 'required' => false, 'MultiOptions' => array_merge(array('' => ''), $arrOptions)));
            foreach ($arrHeadlines as $intCount => $strHeadline) {
                if (array_search($strHeadline, $arrTmpOption['defaults']['default']) !== FALSE) {
                    $objForm->setDefault($arrTmpOption['title'], 'headline' . $intCount);
                    break;
                }
            }
        }
        //$objForm->addElement('checkbox', 'import_header', array('label' => $this->core->translate->_('Import_header'), 'decorators' => array('Label', 'Input'), 'columns' => 12, 'class' => 'checkbox', 'required' => false));

        $objForm->addDisplayGroup($arrAssignment, 'assignment-group');
        $objForm->getDisplayGroup('assignment-group')->setLegend($this->core->translate->_('Import_assignment', false));
        $objForm->getDisplayGroup('assignment-group')->setDecorators(array('FormElements', 'Region'));

        return $objForm;
    }

    /**
     * addformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addformAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->addformAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/contacts/subscriber/add');

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
        //$this->setViewMetaInfos();

        $this->view->form = $this->objForm;
        $this->renderScript('form.phtml');
    }

    /**
     * addAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->addAction()');

        $this->getForm($this->core->sysConfig->generic->actions->add);

        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/contacts/subscriber/add');

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest() && Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objRequest->getParam('rootLevelId'), Security::PRIVILEGE_ADD)) {

            $arrFormData = $this->getRequest()->getPost();
            $this->objForm->Setup()->setFieldValues($arrFormData);

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            
            if ($this->objForm->isValid($arrFormData)) {

                /**
                 * set action
                 */
                $this->objForm->setAction('/zoolu/contacts/subscriber/edit');

                /**
                 * set rootlevelid and parentid for subscriber creation
                 */
                $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
                //$this->objForm->Setup()->setParentId($this->objForm->getElement('parentId')->getValue());

                $intSubscriberId = $this->objForm->saveFormData();
                $this->objForm->getElement('id')->setValue($intSubscriberId);

                $this->view->blnShowFormAlert = true;
            }
        } else {

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();
        }

        /**
         * get form title
         */
        $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

        /**
         * output of metainformation to hidden div
         */
        //$this->setViewMetaInfos();

        $this->view->form = $this->objForm;
        $this->renderScript('form.phtml');
    }

    /**
     * editformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editformAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->editformAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        /**
         * load generic data
         */
        $this->objForm->loadFormData();

        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/contacts/subscriber/edit');

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
        //$this->setViewMetaInfos();

        $this->view->form = $this->objForm;
        $this->renderScript('form.phtml');
    }

    /**
     * editAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->editAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        /**
         * get form title
         */
        $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest() && Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objRequest->getParam('rootLevelId'), Security::PRIVILEGE_UPDATE)) {
            $arrFormData = $this->getRequest()->getPost();
            $this->objForm->Setup()->setFieldValues($arrFormData);

            /**
             * set action
             */
            $this->objForm->setAction('/zoolu/contacts/subscriber/edit');

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();
            
            if ($this->objForm->isValid($arrFormData)) {
                $this->objForm->saveFormData();
                $this->view->blnShowFormAlert = true;
            }
        }

        /**
         * output of metainformation to hidden div
         */
        //$this->setViewMetaInfos();

        $this->view->form = $this->objForm;
        $this->renderScript('form.phtml');
    }

    /**
     * deleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->deleteAction()');

        $this->getModelSubscribers();

        if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
            $objSubscriber = $this->getModelSubscribers()->load($this->objRequest->getParam("id"));

            if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $objSubscriber->current()->idRootLevels, Security::PRIVILEGE_DELETE)) {
                $this->objModelSubscribers->delete($this->objRequest->getParam("id"));
                $this->view->blnShowFormAlert = true;
            }
        }
        $this->renderScript('form.phtml');
    }

    /**
     * listdeleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function listdeleteAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->listdeleteAction()');

        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $strTmpUserIds = trim($this->objRequest->getParam('values'), '[]');
                $arrSubscriberIds = array();
                $arrSubscriberIds = explode('][', $strTmpUserIds);

                foreach ($arrSubscriberIds as $intSubscriberId) {
                    $objSubscribers = $this->getModelSubscribers()->load($intSubscriberId);
                    if (count($objSubscribers) > 0) {
                        foreach ($objSubscribers as $objSubscriber) {
                            if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $objSubscriber->idRootLevels, Security::PRIVILEGE_DELETE)) {
                                $this->objModelSubscribers->delete($intSubscriberId);
                            }
                        }
                    }
                }

                // if(count($arrSubscriberIds) > 1){
                // $this->getModelSubscribers()->deleteMultiple($arrSubscriberIds);
                // }else{
                // $this->getModelSubscribers()->delete($arrSubscriberIds[0]);
                // }

            }
            $this->_forward('list', 'subscriber', 'contacts');

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * listdeleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function listunsubscribeAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->listunsubscribeAction()');

        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $strTmpUserIds = trim($this->objRequest->getParam('values'), '[]');
                $arrSubscriberIds = array();
                $arrSubscriberIds = explode('][', $strTmpUserIds);

                foreach ($arrSubscriberIds as $intSubscriberId) {
                    $objSubscribers = $this->getModelSubscribers()->load($intSubscriberId);
                    if (count($objSubscribers) > 0) {
                        foreach ($objSubscribers as $objSubscriber) {
                            $this->objModelSubscribers->update($objSubscriber->id, array('subscribed' => $this->core->sysConfig->contact->unsubscribed));
                        }
                    }
                }

                // if(count($arrSubscriberIds) > 1){
                // $this->getModelSubscribers()->deleteMultiple($arrSubscriberIds);
                // }else{
                // $this->getModelSubscribers()->delete($arrSubscriberIds[0]);
                // }

            }
            $this->_forward('list', 'subscriber', 'contacts');

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getForm
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function getForm($intActionType = null)
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->getForm(' . $intActionType . ')');

        try {
            $strFormId = $this->objRequest->getParam("formId", $this->core->sysConfig->form->ids->subscribers->default);
            $intElementId = ($this->objRequest->getParam("id") != '') ? $this->objRequest->getParam("id") : null;

            /**
             * if there is no formId
             */
            if ($strFormId == '') {
                throw new Exception('Not able to create a form, because there is no form id!');
            }

            $objFormHandler = FormHandler::getInstance();
            $objFormHandler->setFormId($strFormId);
            $objFormHandler->setActionType($intActionType);
            $objFormHandler->setLanguageId($this->getItemLanguageId($intActionType));
            $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
            $objFormHandler->setElementId($intElementId);

            $this->objForm = $objFormHandler->getGenericForm();

            /**
             * add location & unit specific hidden fields
             */
            $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objRequest->getParam("rootLevelId"), 'decorators' => array('Hidden')));

            /**
             * add currlevel hidden field
             */
            $this->objForm->addElement('hidden', 'currLevel', array('value' => $this->objRequest->getParam("currLevel"), 'decorators' => array('Hidden'), 'ignore' => true));

            /**
             * add elementTye hidden field (folder, element, ...)
             */
            $this->objForm->addElement('hidden', 'elementType', array('value' => $this->objRequest->getParam("elementType"), 'decorators' => array('Hidden'), 'ignore' => true));

            /**
             * add subscriber specific hidden fields
             */
            $this->objForm->addElement('hidden', 'rootLevelFilterId', array('value' => $this->getRequest()->getParam('rootLevelFilterId'), 'decorators' => array('Hidden')));

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
            $blnGeneralUpdateAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_UPDATE, false, false);

            $this->view->authorizedDelete = ($this->objForm->Setup()->getIsStartElement(false) == true || $this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->add) ? false : (($blnGeneralDeleteAuthorization == true) ? $blnGeneralDeleteAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_' . $this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_DELETE, false, false));
            $this->view->authorizedUpdate = ($blnGeneralUpdateAuthorization == true) ? $blnGeneralUpdateAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_' . $this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_UPDATE, false, false);
        }
    }

    /**
     * getItemLanguageId
     * @param integer $intActionType
     * @return integer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getItemLanguageId($intActionType = null)
    {
        if ($this->intItemLanguageId == null) {
            if (!$this->objRequest->getParam("languageId")) {
                $this->intItemLanguageId = $this->objRequest->getParam("rootLevelLanguageId") != '' ? $this->objRequest->getParam("rootLevelLanguageId") : $this->core->intZooluLanguageId;

                $intRootLevelId = $this->objRequest->getParam("rootLevelId");
                $PRIVILEGE = ($intActionType == $this->core->sysConfig->generic->actions->add) ? Security::PRIVILEGE_ADD : Security::PRIVILEGE_UPDATE;

                $arrLanguages = $this->core->config->languages->language->toArray();
                foreach ($arrLanguages as $arrLanguage) {
                    if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId . '_' . $arrLanguage['id'], $PRIVILEGE, false, false)) {
                        $this->intItemLanguageId = $arrLanguage['id'];
                        break;
                    }
                }

            } else {
                $this->intItemLanguageId = $this->objRequest->getParam("languageId");
            }
        }

        return $this->intItemLanguageId;
    }

    /**
     * removeEmptyArrayElements
     * @param array $array The empty elements will be removed from this array
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function removeEmptyArrayElements(&$array)
    {
        for ($i = 0; $i < count($array); $i++) {
            if ($array[$i] == '') {
                unset($array[$i]);
            }
        }
    }

    /**
     * getModelSubscribers
     * @author Thomas Schedler <tsh@massiveart.com>
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
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/Subscribers.php';
            $this->objModelSubscribers = new Model_Subscribers();
            $this->objModelSubscribers->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelSubscribers;
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
     * getModelGenericData
     * @return Model_GenericData
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelGenericData()
    {
        if (null === $this->objModelGenericData) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/GenericData.php';
            $this->objModelGenericData = new Model_GenericData();
        }
        return $this->objModelGenericData;
    }

    /**
     * getModelCategories
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelCategories()
    {
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
}

/**
 * callback for array_map
 */
function trimQuotes($str)
{
    return trim(trim($str), '"\'');
}
