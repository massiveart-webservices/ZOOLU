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
 * TagController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-07: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Properties_TagController extends AuthControllerAction
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
     * @var Model_Tags
     */
    public $objModelTags;

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * getModelTags
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelTags()
    {
        if (null === $this->objModelTags) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Tags.php';
            $this->objModelTags = new Model_Tags();
            $this->objModelTags->setLanguageId(1); // TODO : get language id
        }

        return $this->objModelTags;
    }


    /**
     * listAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function listAction(){
        $this->core->logger->debug('properties->controllers->TagController->listAction()');

        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');
        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'title');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

        $objSelect = $this->getModelTags()->getTagsTable()->select();
        $objSelect->setIntegrityCheck(false);
        $objSelect->from($this->getModelTags()->getTagsTable(), array('id', 'title'));

        if($strSearchValue != ''){
            $objSelect->where('tags.title LIKE ?', '%'.$strSearchValue.'%');
        }

        $objSelect->order($strOrderColumn.' '.strtoupper($strSortOrder));

        $intCountResult = 0;
        if($strSearchValue != ''){
            $intCountResult = count($this->getModelTags()->getTagsTable()->fetchAll($objSelect));
        }

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
        $objTagsPaginator = new Zend_Paginator($objAdapter);
        $objTagsPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', 20));
        $objTagsPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objTagsPaginator->setView($this->view);

        $this->view->assign('tagsPaginator', $objTagsPaginator);
        $this->view->assign('countResult', $intCountResult);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);
    }

    /**
     * editformAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function editformAction()
    {
        $this->core->logger->debug('properties->controllers->TagController->editformAction()');
        try {


            $this->initForm();
            $this->objForm->setAction('/zoolu/properties/tag/edit');

            $intTagId = $this->getRequest()->getParam('id');
            $objTag = $this->getModelTags()->loadTag($intTagId)->current();

            $strTagUsedIn = '';
            $intCounter = 0;
            $strSeperator = ', ';

            foreach ($this->getModelTags()->loadElementWithTag('file', $objTag->id, 1) as $objElement) {
                if ($intCounter === 0 && $objElement->title) {
                    $strTagUsedIn .= '<br /><b>Files:</b><br />';
                } else {
                    $strTagUsedIn .= $strSeperator;
                }
                $strTagUsedIn .= $objElement->title;
                $intCounter++;
            }

            $intCounter = 0;
            foreach ($this->getModelTags()->loadElementWithTag('page', $objTag->id, 1) as $objElement) {
                if ($intCounter === 0 && $objElement->title) {
                    $strTagUsedIn .= '<br /><br /><b>Pages:</b><br />';
                } else {
                    $strTagUsedIn .= $strSeperator;
                }
                $strTagUsedIn .= $objElement->title;
                $intCounter++;
            }

            $intCounter = 0;
            foreach ($this->getModelTags()->loadElementWithTag('global', $objTag->id, 1) as $objElement) {
                if ($intCounter === 0 && $objElement->title) {
                    $strTagUsedIn .= '<br /><br /><b>Globals:</b><br />';
                } else {
                    $strTagUsedIn .= $strSeperator;
                }
                $strTagUsedIn .= $objElement->title;
                $intCounter++;
            }

            $intCounter = 0;

            foreach ($this->getModelTags()->loadElementWithTag('folder', $objTag->id, 1) as $objElement) {
                if ($intCounter === 0 && $objElement->title) {
                    $strTagUsedIn .= '<br /><br /><b>Folders:</b><br />';
                } else {
                    $strTagUsedIn .= $strSeperator;
                }
                $strTagUsedIn .= $objElement->title;
                $intCounter++;
            }

            if ($strTagUsedIn == '') {
                $strTagUsedIn = $this->core->translate->_('tag_not_used');
            }

            foreach ($this->objForm->getElements() as $objElement) {
                $name = $objElement->getName();
                if (isset($objTag->$name)) {
                    $objElement->setValue($objTag->$name);
                }

                if ($name === 'usedTitle') {
                    $objElement->setValue($strTagUsedIn);
                }
            }

            $this->view->form = $this->objForm;
            $this->view->formTitle = $this->core->translate->_('Edit_Tag');

            $this->renderScript('tag/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }


    public function editAction()
    {
        $this->core->logger->debug('contacts->controllers->TagController->editAction()');

        try {
            $intTagId = $this->getRequest()->getParam('id');

            $this->initForm();

            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $arrFormData = $this->getRequest()->getPost();

                $objTag = $this->getModelTags()->loadTag($arrFormData['id']);
                $blnIsOwnTagName = false;

                //check if tagname changed
                foreach($objTag as $value) {
                    if ($arrFormData['title'] == $value->title) {
                        $blnIsOwnTagName = true;
                    }
                }

                if ($this->objForm->isValid($arrFormData) && (count($this->getModelTags()->loadTagByName($arrFormData['title'])) == 0 || $blnIsOwnTagName == true) && $arrFormData['title'] !== '') {
                    $this->objForm->setAction('/zoolu/properties/tag/edit');

                    //Edit tag
                    $arrData = array(
                        'title' => $arrFormData['title']
                    );

                    $this->getModelTags()->editTag($intTagId, $arrData);

                    $this->view->assign('blnShowFormAlert', true);
                    $this->_forward('list', 'tag', 'properties');
                } else {

                    if (count($this->getModelTags()->loadTagByName($arrFormData['title'])) > 0) {
                        $this->objForm->getElement('title')->addError('Tagname is already used');
                    }

                    if ($arrFormData['title'] == '' > 0) {
                        $this->objForm->getElement('title')->addError('Please insert tagname');
                    }

                    $this->objForm->setAction('/zoolu/properties/tag/edit');
                    $this->view->assign('blnShowFormAlert', false);

                    $this->view->form = $this->objForm;
                    $this->view->formTitle = $this->core->translate->_('Edit_Tag');

                    $this->renderScript('tag/form.phtml');
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     *
     */
    public function mergeAction()
    {
        $this->core->logger->debug('contacts->controllers->TagController->mergeAction()');
        $mergeTagIds = $this->getParam('tags');

        $status = 0;
        $message = '';
        try {
            if (!empty($mergeTagIds)) {
                if (!is_array($mergeTagIds)) {
                    $mergeTagIds = explode(',', $mergeTagIds);
                }
                if (count($mergeTagIds) > 1) {
                    $defaultId = $mergeTagIds[0];
                    unset($mergeTagIds[0]);

                    foreach ($mergeTagIds as $mergeTagId) {
                        $this->merge($defaultId, $mergeTagId);
                    }

                    $status = 1;
                    $message = $this->core->translate->_('merge_complete');
                } else {
                    $this->core->logger->warn('No merge able Tags');
                    throw new Exception ('nothing to merge');
                }
            } else {
                $this->core->logger->warn('No Tags');
                throw new Exception ('nothing to merge');
            }
        } catch (Exception $e) {
            $status = 0;
            $message = $this->core->translate->_('merge_not_calculated');
            $this->core->logger->warn($this->core->translate->_('merge_not_calculated'));
        }
        $this->view->assign('status', $status);
        $this->view->assign('message', $message);
    }

    /**
     * merge
     * @param int $defaultId
     * @param int $mergeTagId
     */
    protected function merge ($defaultId, $mergeTagId)
    {
        $mergeTagId = intval($mergeTagId);
        $this->core->logger->debug('contacts->controllers->TagController->merge('.$defaultId.', '.$mergeTagId.')');

        $tables = $this->getMergeTables();
        $hasErrors = false;
        foreach ($tables as $table => $values) {
            try {
                $this->getModelTags()->mergeTags($defaultId, $mergeTagId, $table, $values);
            } catch (Exception $e) {
                $hasErrors = true;
                $this->core->logger->err($e->getMessage());
            }
        }
        // Do not delete
        if (!$hasErrors) {
            $this->objModelTags->deleteTag($mergeTagId);
        }
    }

    /**
     * getMergeTables
     * @return array
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     */
    protected function getMergeTables()
    {
        return array_merge($this->getMergeDynTables(), $this->getMergeStaticTables());
    }

    /**
     * getMergeDynTables
     * @return array
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     */
    protected function getMergeDynTables()
    {
        $dynTableDatas = $this->getModelTags()->getFieldGenericForm();

        $dynTables = array();
        if (count($dynTableDatas)) {
            foreach($dynTableDatas as $tableData) {
                $tableName = '';
                $tableName .= $tableData->title . '-' . $tableData->genericFormId . '-' . $tableData->version;
                if ($tableData->isMultiply == 1) {
                    $tableName .= '-Region'.$tableData->regionId;
                }
                $tableName .= '-InstanceFileFilters';

                $dynTables[$tableName] = array(
                    'has4References'   => true,
                    'referenceColumn'  => $tableData->title  . 'Id',
                    'referenceColumn2' => 'idLanguages',
                    'referenceColumn3' => 'idRegionInstances',
                    'referenceColumn4' => 'idFields',
                    'placerColumn'     => 'referenceId'
                );
            }
        }

        return $dynTables;
    }

    /**
     * getMergeStaticTables
     * @return array
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     */
    protected function getMergeStaticTables()
    {
        return array(
            'tagPages' => array(
                'has4References'   => false,
                'referenceColumn'  => 'pageId',
                'referenceColumn2' => 'idLanguages',
                'placerColumn'     => 'idTags'
            ),
            'tagGlobals' => array(
                'has4References'   => false,
                'referenceColumn'  => 'globalId',
                'referenceColumn2' => 'idLanguages',
                'placerColumn'     => 'idTags'
            ),
            'tagFiles' => array(
                'has4References'   => false,
                'referenceColumn'  => 'fileId',
                'referenceColumn2' => 'idLanguages',
                'placerColumn'     => 'idTags'
            ),
        );
    }

    /**
     * mergeformAction
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     */
    public function mergeformAction()
    {
        $this->core->logger->debug('contacts->controllers->TagController->mergeformAction()');

        try {
            $tags = array();
            $mergeTagIds = $this->getParam('tags');
            if (!empty($mergeTagIds)) {
                $objSelect = $this->getModelTags()->getTagsTable()->select();
                $objSelect->setIntegrityCheck(false);
                $objSelect->from($this->getModelTags()->getTagsTable(), array('id', 'title'));
                $objSelect->where('tags.id IN (?)', $mergeTagIds);
                $objTags = $this->getModelTags()->getTagsTable()->fetchAll($objSelect);
                if (count($objTags) > 0) {
                    $tags = $objTags;
                }
            }

            $this->view->assign('core', $this->core);
            $this->view->assign('tags', $tags);
            $this->view->assign('allTags', $this->getAllTagsForAutocompleter($tags));
            $this->renderScript('tag/merge-form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }
    /**
     * getAllTagsForAutocompleter
     * @return Zend_Db_Table_Rowset $objAllTags
     * @return string $strElementId
     * @param array $filterTags
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getAllTagsForAutocompleter($filterTags = array())
    {
        $filterTagsId = array();
        foreach ($filterTags as $filterTag) {
            $filterTagsId[] = $filterTag->id;
        }
        $objSelect = $this->getModelTags()->getTagsTable()->select();
        $objSelect->setIntegrityCheck(false);
        $objSelect->from($this->getModelTags()->getTagsTable(), array('id', 'title'));
        $objAllTags = $this->getModelTags()->getTagsTable()->fetchAll($objSelect);
        $core = Zend_Registry::get('Core');
        $strAllTags = '[';
        if (count($objAllTags) > 0) {
            foreach ($objAllTags as $objTag) {
                if (!in_array($objTag->id, $filterTagsId)) {
                    $strAllTags .= '{"caption":"' . htmlentities($objTag->title, ENT_COMPAT, $core->sysConfig->encoding->default) . '","value":' . $objTag->id . '},';
                }
            }
            $strAllTags = trim($strAllTags, ',');
        }
        $strAllTags .= ']';
        return $strAllTags;
    }

    /**
     * deleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteAction(){
        $this->core->logger->debug('partners->controllers->TagController->deleteAction()');
        if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            $this->getModelTags();
            $objRequest = $this->getRequest();
            $this->objModelTags->deleteTag($objRequest->getParam("id"));
            $this->view->blnShowFormAlert = true;
            $this->_forward('list', 'tag', 'properties');
        } else {
            $this->renderScript('tag/form.phtml');
        }
    }

    /**
     * listdeleteAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function listdeleteAction(){
        $this->core->logger->debug('members->controllers->TagController->listdeleteAction()');

        try{
            if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $strTmpTagIds = trim($this->getRequest()->getParam('values'), '[]');
                $arrTagIds = array();
                $arrTagIds = explode('][', $strTmpTagIds);

                if(count($arrTagIds) > 1){
                    foreach ($arrTagIds as $value) {
                        $this->getModelTags()->deleteTag($value);
                    }
                }else{
                    $this->getModelTags()->deleteTag($arrTagIds[0]);
                }
            }
            $this->_forward('list', 'tag', 'properties');

        }catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function addformAction()
    {
        $this->core->logger->debug('contacts->controllers->TagController->addformAction()');

        try {
            $this->arrAddresses = array();
            $this->initForm();
            $this->objForm->setAction('/zoolu/properties/tag/add');

            $this->view->form = $this->objForm;
            $this->view->formTitle = $this->core->translate->_('New_Tag');

            $this->renderScript('tag/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function addAction()
    {
        $this->core->logger->debug('contacts->controllers->TagController->addAction()');

        try {

            $this->initForm(false);
            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $arrFormData = $this->getRequest()->getPost();
                if ($this->objForm->isValid($arrFormData) && count($this->getModelTags()->loadTagByName($arrFormData['title'])) == 0 && $arrFormData['title'] !== '') {
                    //Set Action
                    $this->objForm->setAction('/zoolu/properties/tag/add');
                    //Add Tag
                    $intTagId = $this->getModelTags()->addTag($arrFormData['title']);

                    $this->view->assign('blnShowFormAlert', true);
                    $this->_forward('list', 'tag', 'properties');
                } else {
                    if (count($this->getModelTags()->loadTagByName($arrFormData['title'])) > 0) {
                        $this->objForm->getElement('title')->addError('Tagname is already used');
                    }

                    if ($arrFormData['title'] == '' > 0) {
                        $this->objForm->getElement('title')->addError('Please insert tagname');
                    }

                    $this->objForm->setAction('/zoolu/properties/tag/add');
                    $this->view->assign('blnShowFormAlert', false);

                    $this->view->form = $this->objForm;
                    $this->view->formTitle = $this->core->translate->_('New_Tag');

                    $this->renderScript('tag/form.phtml');
                }

            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    protected function initForm($showUsed = true)
    {
        $this->objForm = new GenericForm();



        $this->objForm->addElement('hidden', 'id', array('decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'formType', array('value' => 'tag', 'decorators' => array('Hidden')));

        $this->objForm->addElement('text', 'title', array('label' => $this->core->translate->_('Tagname', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'select'));

        if (!$showUsed) {
            $this->objForm->addElement('freeText', 'usedTitle', array('label' => $this->core->translate->_('tag_used_in', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'select'));

        }
        $this->objForm->addDisplayGroup(array('title', 'usedTitle'), 'main-group');
        $this->objForm->getDisplayGroup('main-group')->setLegend($this->core->translate->_('General_information', false));
        $this->objForm->getDisplayGroup('main-group')->setDecorators(array('FormElements', 'Region'));
    }
}
