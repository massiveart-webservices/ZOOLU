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
 * @package    application.zoolu.modules.core.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */


/**
 * Model_Templates
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-14: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Templates
{

    private $intLanguageId;

    /**
     * @var Model_Table_Templates
     */
    protected $objTemplateTable;

    /**
     * @var Model_Table_TemplateExcludedFields
     */
    protected $objTemplateExcludedFieldsTable;

    /**
     * @var Model_Table_TemplateExcludedRegions
     */
    protected $objTemplateExcludedRegionsTable;

    /**
     * @var Model_Table_TemplateRegionProperties
     */
    protected $objTemplateRegionPropertiesTable;

    /**
     * @var Core
     */
    private $core;

    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * loadTemplateById
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset
     * @version 1.0
     */
    public function loadTemplateById($intTemplateId)
    {
        $this->core->logger->debug('core->models->Model_Templates->loadTemplateById(' . $intTemplateId . ')');

        $objSelect = $this->getTemplateTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('templates', array(
                                           'id',
                                           'genericFormId',
                                           'filename',
                                           '(SELECT version FROM genericForms WHERE genericForms.genericFormId = templates.genericFormId ORDER BY version DESC LIMIT 1) AS version',
                                           '(SELECT idGenericFormTypes FROM genericForms WHERE genericForms.genericFormId = templates.genericFormId ORDER BY version DESC LIMIT 1) AS formTypeId'
                                      ));
        $objSelect->where('templates.id = ?', $intTemplateId);

        return $this->getTemplateTable()->fetchAll($objSelect);
    }

    /**
     * loadTemplateExcludedRegions   *
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadTemplateExcludedRegions($intTemplateId)
    {
        $this->core->logger->debug('core->models->Model_Templates->loadTemplateExcludedRegions(' . $intTemplateId . ')');

        $objSelect = $this->getTemplateExcludedRegionsTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objTemplateExcludedRegionsTable, array('idRegions'));
        $objSelect->where('idTemplates = ?', $intTemplateId);

        return $this->objTemplateExcludedRegionsTable->fetchAll($objSelect);
    }

    /**
     * loadTemplateExcludedFields
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset
     * @version 1.0
     */
    public function loadTemplateExcludedFields($intTemplateId)
    {
        $this->core->logger->debug('core->models->Model_Templates->loadTemplateExcludedFields(' . $intTemplateId . ')');

        $objSelect = $this->getTemplateExcludedFieldsTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('templateExcludedFields', array('idFields'));
        $objSelect->where('idTemplates = ?', $intTemplateId);

        return $this->objTemplateExcludedFieldsTable->fetchAll($objSelect);
    }

    /**
     * loadTemplateRegionProperties
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadTemplateRegionProperties($intTemplateId)
    {
        $this->core->logger->debug('core->models->Model_Templates->loadTemplateRegionProperties(' . $intTemplateId . ')');

        $objSelect = $this->getTemplateRegionPropertiesTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objTemplateRegionPropertiesTable, array('idRegions', 'order', 'collapsable', 'isCollapsed'));
        $objSelect->where('idTemplates = ?', $intTemplateId);

        return $this->objTemplateRegionPropertiesTable->fetchAll($objSelect);
    }

    /**
     * loadActiveTemplates
     * @param boolean $blnIsStartPage
     * @param integer $intPageTypeId
     * @paran integer $intParentTypeId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @return Zend_Db_Table_Rowset
     * @version 1.0
     */
    public function loadActiveTemplates($blnIsStartElement = false, $intElementTypeId, $intParentTypeId, $intFormTypeId, $intRootLevelId)
    {
        $this->core->logger->debug('core->models->Model_Templates->loadActiveTemplates(' . var_export($blnIsStartElement, true) . ', ' . $intElementTypeId . ', ' . $intParentTypeId . ', ' . $intFormTypeId . ', ' . $intRootLevelId . ')');

        $objSelect = $this->getTemplateTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('templates', array(
                                           'id',
                                           'genericFormId',
                                           '(SELECT version FROM genericForms WHERE genericForms.genericFormId = templates.genericFormId ORDER BY version DESC LIMIT 1) AS version',
                                           'filename',
                                           'thumbnail'
                                      ));
        $objSelect->join('templateTypes', 'templateTypes.idTemplates = templates.id', array());
        $objSelect->join('rootLevelTemplates', 'rootLevelTemplates.idTemplates = templates.id', array());
        $objSelect->join('types', 'types.id = templateTypes.idTypes', array());
        $objSelect->joinLeft('templateTitles', 'templateTitles.idTemplates = templates.id AND templateTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->where('templates.active = ?', 1);
        $objSelect->where('rootLevelTemplates.idRootLevels = ?', $intRootLevelId);
        $objSelect->order('templateTitles.title');

        switch ($intFormTypeId) {
            case $this->core->sysConfig->form->types->page:
                switch ($intElementTypeId) {
                    case $this->core->sysConfig->page_types->page->id:
                        if ($blnIsStartElement && $intParentTypeId == $this->core->sysConfig->parent_types->rootlevel) {
                            $objSelect->where('types.id = ?', $this->core->sysConfig->types->portal_startpage);
                        } else if ($blnIsStartElement) {
                            $objSelect->where('types.id = ?', $this->core->sysConfig->types->startpage);
                        } else {
                            $objSelect->where('types.id = ?', $this->core->sysConfig->types->page);
                        }
                        break;
                    case $this->core->sysConfig->page_types->overview->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->overview);
                        break;
                    case $this->core->sysConfig->page_types->collection->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->collection);
                        break;
                    case $this->core->sysConfig->page_types->product_tree->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->product_tree);
                        break;
                    case $this->core->sysConfig->page_types->press_area->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->press_area);
                        break;
                    case $this->core->sysConfig->page_types->courses->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->courses);
                        break;
                    case $this->core->sysConfig->page_types->events->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->events);
                        break;
                    case $this->core->sysConfig->page_types->download_center->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->download_center);
                        break;
                    case $this->core->sysConfig->page_types->sitemap->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->sitemap);
                        break;
                    case $this->core->sysConfig->page_types->service->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->service);
                        break;
                }
                break;
            case $this->core->sysConfig->form->types->global:
                switch ($intElementTypeId) {
                    case $this->core->sysConfig->global_types->content->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->content);
                        break;
                    case $this->core->sysConfig->global_types->content_overview->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->content_overview);
                        break;
                    case $this->core->sysConfig->global_types->product->id:
                    case $this->core->sysConfig->global_types->product_link->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->product);
                        break;
                    case $this->core->sysConfig->global_types->product_overview->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->product_overview);
                        break;
                    case $this->core->sysConfig->global_types->press->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->press);
                        break;
                    case $this->core->sysConfig->global_types->press_overview->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->press_overview);
                        break;
                    case $this->core->sysConfig->global_types->course->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->course);
                        break;
                    case $this->core->sysConfig->global_types->course_overview->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->course_overview);
                        break;
                    case $this->core->sysConfig->global_types->event->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->event);
                        break;
                    case $this->core->sysConfig->global_types->event_overview->id:
                        $objSelect->where('types.id = ?', $this->core->sysConfig->types->event_overview);
                        break;
                }
                break;
            case $this->core->sysConfig->form->types->newsletter:
                $objSelect->where('types.id = ?', $this->core->sysConfig->types->newsletter);
                break;
        }

        return $this->getTemplateTable()->fetchAll($objSelect);
    }

    /**
     * getTemplateTable
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getTemplateTable()
    {

        if ($this->objTemplateTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/Templates.php';
            $this->objTemplateTable = new Model_Table_Templates();
        }

        return $this->objTemplateTable;
    }

    /**
     * getTemplateExcludedRegionsTable
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTemplateExcludedRegionsTable()
    {

        if ($this->objTemplateExcludedRegionsTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/TemplateExcludedRegions.php';
            $this->objTemplateExcludedRegionsTable = new Model_Table_TemplateExcludedRegions();
        }

        return $this->objTemplateExcludedRegionsTable;
    }

    /**
     * getTemplateExcludedFieldsTable
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getTemplateExcludedFieldsTable()
    {

        if ($this->objTemplateExcludedFieldsTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/TemplateExcludedFields.php';
            $this->objTemplateExcludedFieldsTable = new Model_Table_TemplateExcludedFields();
        }

        return $this->objTemplateExcludedFieldsTable;
    }

    /**
     * getTemplateRegionPropertiesTable
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTemplateRegionPropertiesTable()
    {

        if ($this->objTemplateRegionPropertiesTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/TemplateRegionProperties.php';
            $this->objTemplateRegionPropertiesTable = new Model_Table_TemplateRegionProperties();
        }

        return $this->objTemplateRegionPropertiesTable;
    }

    /**
     * setLanguageId
     * @param integer $intLanguageId
     */
    public function setLanguageId($intLanguageId)
    {
        $this->intLanguageId = $intLanguageId;
    }

    /**
     * getLanguageId
     * @param integer $intLanguageId
     */
    public function getLanguageId()
    {
        return $this->intLanguageId;
    }


}

?>