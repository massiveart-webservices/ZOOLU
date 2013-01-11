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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * LandingpageController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-02-14: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class Core_LandingpageController extends AuthControllerAction
{

    /**
     * @var GenericForm
     */
    protected $objForm;

    /**
     * @var Model_Urls
     */
    protected $objModelUrls;

    /**
     * @var Model_Languages
     */
    protected $objModelLanguages;

    /**
     * @var Model_Pages
     */
    protected $objModelPages;

    /**
     * @var Model_Globals
     */
    protected $objModelGlobals;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * initForm
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function initForm($blnSingleEdit = false)
    {

        $this->objForm = new Zend_Form();

        /**
         * Use our own PluginLoader
         */
        $objLoader = new PluginLoader();
        $objLoader->setPluginLoader($this->objForm->getPluginLoader(PluginLoader::TYPE_FORM_ELEMENT));
        $objLoader->setPluginType(PluginLoader::TYPE_FORM_ELEMENT);
        $this->objForm->setPluginLoader($objLoader, PluginLoader::TYPE_FORM_ELEMENT);

        /**
         * clear all decorators
         */
        $this->objForm->clearDecorators();

        /**
         * add standard decorators
         */
        $this->objForm->addDecorator('TabContainer');
        $this->objForm->addDecorator('FormElements');
        $this->objForm->addDecorator('Form');

        /**
         * add form prefix path
         */
        $this->objForm->addPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/', 'decorator');

        /**
         * elements prefixes
         */
        $this->objForm->addElementPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/', 'decorator');

        /**
         * regions prefixes
         */
        $this->objForm->addDisplayGroupPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/');

        $this->objForm->setAttrib('id', 'genForm');
        $this->objForm->setAttrib('onsubmit', 'return false;');
        $this->objForm->addELement('hidden', 'rootLevelId', array('decorators' => array('Hidden'), 'value' => $this->getRequest()->getParam('rootLevelId')));
        $this->objForm->addElement('hidden', 'languageId', array('decorators' => array('Hidden'), 'value' => $this->core->intZooluLanguageId));
        $this->objForm->addElement('hidden', 'id', array('decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'relationId', array('decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'version', array('decorators' => array('Hidden'), 'value' => 1)); //TODO Version should not be hardcoded
        $this->objForm->addElement('hidden', 'isLandingpage', array('decorators' => array('Hidden'), 'value' => 1));
        $this->objForm->addElement('hidden', 'idParent', array('decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'idParentTypes', array('decorators' => array('Hidden'), 'value' => 2)); //TODO ParentType should not be hardcoded

        $arrLanguageOptions = array();
        $arrLanguageOptions[''] = $this->core->translate->_('Please_choose', false);
        $sqlStmt = $this->getModelLanguages()->loadLanguages($this->getRequest()->getParam('rootLevelId'));
        foreach ($sqlStmt as $arrSql) {
            $arrLanguageOptions[$arrSql['id']] = $arrSql['title'];
        }

        $this->objForm->addElement('text', 'url', array('label' => $this->core->translate->_('Landingpage_url', false), 'description' => $this->core->translate->_('Landingpage_url_desc', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text', 'required' => true));
        $this->objForm->addElement('text', 'external', array('label' => $this->core->translate->_('External_url', false), 'description' => $this->core->translate->_('External_url_desc', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('sitemapLink', 'link', array('label' => $this->core->translate->_('Landingpage_link', false), 'decorators' => array('Input'), 'columns' => 12, 'class' => 'text'));
        $this->objForm->addElement('select', 'idLanguages', array('label' => $this->core->translate->_('Landingpage_language', false), 'description' => $this->core->translate->_('Landingpage_language_desc'), 'decorators' => array('Input'), 'columns' => 3, 'class' => 'select', 'required' => true, 'MultiOptions' => $arrLanguageOptions));
        $this->objForm->addElement('checkbox', 'isMain', array('decorators' => array('Input'), 'columns' => 12, 'class' => 'checkbox', 'label' => $this->core->translate->_('Landingpage_redirect', false), 'description' => $this->core->translate->_('Landingpage_redirect_desc')));

        $this->objForm->addDisplayGroup(array('link'), 'link-group');
        $this->objForm->getDisplayGroup('link-group')->setLegend($this->core->translate->_('Contentpage', false));
        $this->objForm->getDisplayGroup('link-group')->setDecorators(array('FormElements', 'Region'));

        $this->objForm->addDisplayGroup(array('external'), 'external-group');
        $this->objForm->getDisplayGroup('external-group')->setLegend($this->core->translate->_('External_page', false));
        $this->objForm->getDisplayGroup('external-group')->setDecorators(array('FormElements', 'Region'));

        $this->objForm->addDisplayGroup(array('url', 'isMain', 'idLanguages'), 'main-group');
        $this->objForm->getDisplayGroup('main-group')->setLegend($this->core->translate->_('General_information_landingpage', false));
        $this->objForm->getDisplayGroup('main-group')->setDecorators(array('FormElements', 'Region'));
    }

    /**
     * Initializes the sitemap field
     * @param string $strRelationId
     */
    private function initSitemap($strRelationId, $intUrlTypeId)
    {

        //Initialize Sitemap field
        $strType = '';
        if ($intUrlTypeId == 1) {
            $strType = 'page';
        } else {
            $strType = 'global';
        }

        $objLinkedElement = null;
        if (!empty($strRelationId)) {
            if ($strType == 'page') {
                $objLinkedElement = $this->getModelPages()->loadByPageId($strRelationId);
            } elseif ($strType == 'global') {
                $objLinkedElement = $this->getModelGlobals()->loadLinkByGlobalId($strRelationId);
            }

            $arrData = $this->buildSitemapFieldData($objLinkedElement->current()->id, $strType);

            $this->objForm->getElement('link')->setOptions(array(
                'label' => $this->core->translate->_('Link', false),
                'decorators' => array('Input'),
                'columns' => 12,
                'class' => 'text',
                'strLinkedPageBreadcrumb' => ltrim($arrData['breadcrumb'], ' » ') . ' » ',
                'strLinkedPageTitle' => $arrData['title'],
                'strLinkedPageUrl' => $arrData['url'],
                'intParentId' => $arrData['parentId'],
                'relationId' => $arrData['relationId'],
                'strType' => $strType
            ));
        }
    }

    /**
     * addformAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function addformAction()
    {
        $this->core->logger->debug('core->controllers->LandingpageController->addformAction()');
        try {

            $this->initForm();
            $this->objForm->setAction('/zoolu/core/landingpage/add');

            $this->view->form = $this->objForm;
            $this->view->formTitle = $this->core->translate->_('New_Landingpage');

            $this->renderScript('form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function addAction()
    {
        $this->core->logger->debug('core->controllers->LandingpageController->addAction()');
        try {
            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $this->initForm();
                $arrFormData = $this->getRequest()->getPost();
                $intRootLevelId = $arrFormData['rootLevelId'];
                unset($arrFormData['rootLevelId']);
                unset($arrFormData['languageId']);
                $arrFormData['relationId'] = $arrFormData['sitemapLinkRelation_link'];
                unset($arrFormData['sitemapLinkRelation_link']);
                $arrFormData['idUrlTypes'] = $this->core->sysConfig->url_types->$arrFormData['sitemapLinkType_link'];
                if ($arrFormData['sitemapLinkType_link'] == 'global') {
                    $arrFormData['idParent'] = $arrFormData['sitemapLinkParent_link'];
                } else {
                    $arrFormData['idParent'] = null;
                }
                //Apply url type
                if (isset($arrFormData['sitemapLinkType_link']) && $arrFormData['sitemapLinkType_link'] != '') {
                    $arrFormData['idUrlTypes'] = $this->core->sysConfig->url_types->$arrFormData['sitemapLinkType_link'];
                } else {
                    //FIXME Not a very nice solution
                    $arrFormData['idUrlTypes'] = $this->core->sysConfig->url_types->external;
                    $arrFormData['idParent'] = $intRootLevelId;
                    $arrFormData['idParentTypes'] = $this->core->sysConfig->parent_types->rootlevel;
                }
                unset($arrFormData['sitemapLinkType_link']);
                unset($arrFormData['sitemapLinkParent_link']);
                if ($this->objForm->isValid($arrFormData) && $this->checkUnqiueUrl($arrFormData['url'], $intRootLevelId)) {
                    //Save and show list again
                    $intUrlId = $this->getRequest()->getParam('id');

                    $this->getModelUrls()->addUrl($arrFormData);

                    $this->_forward('list', 'landingpage', 'core');
                    $this->view->assign('blnShowFormAlert', true);
                } else {
                    //Show Form with errors
                    $this->objForm->setAction('/zoolu/core/landingpage/add');
                    $this->view->assign('blnShowFormAlert', false);

                    $this->initSitemap($arrFormData['relationId'], $arrFormData['idUrlTypes']);

                    $this->view->form = $this->objForm;
                    $this->view->formTitle = $this->core->translate->_('New_Landingpage');

                    $this->renderScript('form.phtml');
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * editformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editformAction()
    {
        $this->core->logger->debug('core->controllers->LandingpageController->editformAction()');

        try {

            $this->initForm();
            $this->objForm->setAction('/zoolu/core/landingpage/edit');

            $objLandingPage = $this->getModelUrls()->loadUrlById($this->getRequest()->getParam('id'));

            foreach ($this->objForm->getElements() as $objElement) {
                $name = $objElement->getName();
                if (isset($objLandingPage->$name)) {
                    $objElement->setValue($objLandingPage->$name);
                }
            }

            $this->objForm->getElement('idLanguages')->setValue($objLandingPage->idLanguages);

            if (isset($objLandingPage->relationId) && $objLandingPage->relationId != '') {
                $this->initSitemap($objLandingPage->relationId, $objLandingPage->idUrlTypes, $objLandingPage->idParent);
            }

            $objRootLevelUrl = $this->getModelRootLevels()->loadRootLevelUrl($this->getRequest()->getParam('rootLevelId'));

            $this->view->assign('url', 'http://' . $objRootLevelUrl->url . '/' . $objLandingPage->url);

            $this->view->form = $this->objForm;
            $this->view->formTitle = $this->core->translate->_('Edit_Landingpage');

            $this->renderScript('form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * editAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function editAction()
    {
        $this->core->logger->debug('core->controllers->LandingpageController->editAction()');

        try {
            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $this->initForm();
                $intUrlId = $this->getRequest()->getParam('id');
                $arrFormData = $this->getRequest()->getPost();
                $intRootLevelId = $arrFormData['rootLevelId'];
                unset($arrFormData['rootLevelId']);
                unset($arrFormData['languageId']);
                $arrFormData['relationId'] = $arrFormData['sitemapLinkRelation_link'];
                unset($arrFormData['sitemapLinkRelation_link']);
                if ($arrFormData['sitemapLinkType_link'] == 'global') {
                    $arrFormData['idParent'] = $arrFormData['sitemapLinkParent_link'];
                } else {
                    $arrFormData['idParent'] = null;
                }
                unset($arrFormData['sitemapLinkParent_link']);
                //Apply url type
                if (isset($arrFormData['sitemapLinkType_link']) && $arrFormData['sitemapLinkType_link'] != '') {
                    $arrFormData['idUrlTypes'] = $this->core->sysConfig->url_types->$arrFormData['sitemapLinkType_link'];
                } else {
                    //FIXME Not a very nice solution
                    $arrFormData['idUrlTypes'] = $this->core->sysConfig->url_types->external;
                    $arrFormData['idParent'] = $intRootLevelId;
                    $arrFormData['idParentTypes'] = $this->core->sysConfig->parent_types->rootlevel;
                }
                unset($arrFormData['sitemapLinkType_link']);
                if ($this->objForm->isValid($arrFormData) && $this->checkUnqiueUrl($arrFormData['url'], $intRootLevelId, $intUrlId)) {
                    //Save and show list again

                    $this->getModelUrls()->editUrl($intUrlId, $arrFormData);

                    $this->_forward('list', 'landingpage', 'core');
                    $this->view->assign('blnShowFormAlert', true);
                } else {
                    //Show Form with errors
                    $this->objForm->setAction('/zoolu/core/landingpage/edit');
                    $this->view->assign('blnShowFormAlert', false);

                    $this->initSitemap($arrFormData['relationId'], $arrFormData['idUrlTypes']);

                    $this->view->form = $this->objForm;
                    $this->view->formTitle = $this->core->translate->_('Edit_Landingpage');

                    $this->renderScript('form.phtml');
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function deleteAction()
    {
        $this->core->logger->debug('core->controllers->LandingpageController->deleteAction()');

        try {
            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $this->getModelUrls()->deleteUrl($this->getRequest()->getParam('id'));
            }
            $this->_forward('list', 'landingpage', 'core');
            $this->view->assign('blnShowFormAlert', true);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function listdeleteAction()
    {
        $this->core->logger->debug('core->controllers->LandingpageController->listdeleteAction()');

        try {
            $strLandingpages = $this->getRequest()->getParam('values', null);
            if ($strLandingpages != null) {
                $arrLandingpageIds = explode('][', trim($strLandingpages, '[]'));
                foreach ($arrLandingpageIds as $intLandingpageId) {
                    $this->getModelUrls()->deleteUrl($intLandingpageId);
                }
            }
            $this->_forward('list', 'landingpage', 'core');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * listAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->core->logger->debug('core->controllers->LandingpageController->listAction()');

        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');
        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'sname');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

        $objSelect = $this->getModelUrls()->loadUrlsByRootLevelForSitemapList($intRootLevelId, true, true);

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
        $objLandingPagesPaginator = new Zend_Paginator($objAdapter);
        $objLandingPagesPaginator->setItemCountPerPage((int)$this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objLandingPagesPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objLandingPagesPaginator->setView($this->view);

        $this->view->assign('paginator', $objLandingPagesPaginator);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);
    }

    /**
     * sitemapfieldAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function sitemapfieldAction()
    {
        $this->core->logger->debug('core->controllers->LandingpageController->sitemapfieldAction()');

        $strFieldId = $this->getRequest()->getParam('fieldId');
        $strElementId = $this->getRequest()->getParam('elementId');
        $intElementId = $this->getRequest()->getParam('idElement');
        $strType = $this->getRequest()->getParam('type');

        $arrData = $this->buildSitemapFieldData($intElementId, $strType);

        require_once(GLOBAL_ROOT_PATH . 'library/massiveart/generic/fields/SitemapLink/forms/elements/SitemapLink.php');
        $objElement = new Form_Element_SitemapLink($strFieldId);
        $objElement->addPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/decorators/', 'decorator');
        $objElement->setOptions(array(
            'label' => $this->core->translate->_('Link', false),
            'decorators' => array('Input'),
            'columns' => 12,
            'class' => 'text',
            'strLinkedPageBreadcrumb' => ltrim($arrData['breadcrumb'], ' » ') . ' » ',
            'strLinkedPageTitle' => $arrData['title'],
            'strLinkedPageUrl' => $arrData['url'],
            'intParentId' => $arrData['parentId'],
            'relationId' => $arrData['relationId'],
            'strType' => $strType
        ));

        $objDecorator = $objElement->getDecorator('Input');
        $objDecorator->setElement($objElement);
        echo $objDecorator->buildInput();

        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * Builds all the string, which are needed for the sitemap field
     * @param integer $intElementId
     */
    private function buildSitemapFieldData($intElementId, $strType)
    {
        $this->core->logger->debug('core->controllers->landingpage->buildSitemapFieldData(' . $intElementId . ', ' . $strType . ')');
        $arrData = array();

        if ($strType == 'global') {
            $objLinkedGlobalData = $this->getModelGlobals()->loadLinkGlobal($intElementId);
            if (count($objLinkedGlobalData) > 0) {
                $objLinkedGlobal = $objLinkedGlobalData->current();
                $objParentFoldersData = $this->getModelGlobals()->loadParentFolders($objLinkedGlobal->originId);
                if (count($objParentFoldersData) > 0) {
                    $arrData['breadcrumb'] = '';
                    foreach ($objParentFoldersData as $objParentFolder) {
                        $arrData['breadcrumb'] = ' » ' . $objParentFolder->title . $arrData['breadcrumb'];
                    }
                }
                $arrData['title'] = $objLinkedGlobal->title;
                $arrData['url'] = '/' . strtolower($objLinkedGlobal->languageCode) . '/' . $objLinkedGlobal->url;
                $arrData['relationId'] = $objLinkedGlobal->globalId;
                $arrData['parentId'] = $this->getRequest()->getParam('idParent');
            }
        } else {
            $objLinkedPageData = $this->getModelPages()->loadLinkPage($intElementId);
            if (count($objLinkedPageData) > 0) {
                $objLinkedPage = $objLinkedPageData->current();
                $objParentFoldersData = $this->getModelPages()->loadParentFolders($objLinkedPage->id);
                if (count($objParentFoldersData) > 0) {
                    $arrData['breadcrumb'] = '';
                    foreach ($objParentFoldersData as $objParentFolder) {
                        $arrData['breadcrumb'] = ' » ' . $objParentFolder->title . $arrData['breadcrumb'];
                    }
                }
                $arrData['title'] = $objLinkedPage->title;
                $arrData['url'] = '/' . strtolower($objLinkedPage->languageCode) . '/' . $objLinkedPage->url;
                $arrData['relationId'] = $objLinkedPage->pageId;
                $arrData['parentId'] = null;
            }
        }

        return $arrData;
    }

    /**
     * Checks if the given URL is unique
     * @param string $strUrl
     * @param integer $intRootLevelId
     * @param integer $intElementId
     * @return True if the url is already existing, otherwise false
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    private function checkUnqiueUrl($strUrl, $intRootLevelId, $intElementId = null)
    {
        $objLandingPageUrls = $this->getModelUrls()->loadUrlByUrlAndRootLevel($strUrl, $intRootLevelId, true);
        if (count($objLandingPageUrls) == 0) {
            //URL is unique
            return true;
        }
        if (count($objLandingPageUrls) <= 1) {
            //Check if the url belongs to the given one
            if ($objLandingPageUrls->current()->id == $intElementId) {
                return true;
            }
        }
        return false;
    }

    /**
     * getModelUrls
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelUrls()
    {
        if (null === $this->objModelUrls) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Urls.php';
            $this->objModelUrls = new Model_Urls();
            $this->objModelUrls->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelUrls;
    }

    /**
     * getModelLanguages
     * @return Model_Languages
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelLanguages()
    {
        if (null === $this->objModelLanguages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Languages.php';
            $this->objModelLanguages = new Model_Languages();
        }

        return $this->objModelLanguages;
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
            $this->objModelPages->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelPages;
    }

    /**
     * getModelGenericForm
     * @return Model_GenericForms
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function getModelGlobals()
    {
        if (null === $this->objModelGlobals) {
            /**
             * autoload only handles "library" components.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/Globals.php';
            $this->objModelGlobals = new Model_Globals();
            $this->objModelGlobals->setLanguageId($this->getRequest()->getParam("languageId", $this->core->intZooluLanguageId));
        }

        return $this->objModelGlobals;
    }

    /**
     * getModelRootLevels
     * @author Cornelius Hansjakob <cha@massiveart.com>
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
            $this->objModelRootLevels->setLanguageId(1); // TODO Language from user
        }

        return $this->objModelRootLevels;
    }
}