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
 * Model_Categories
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-20: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Model_Categories
{

    private $intLanguageId;

    /**
     * @var Model_Table_Categories
     */
    protected $objCategoriesTable;

    /**
     * @var Model_Table_RootLevels
     */
    protected $objRootLevelTable;

    /**
     * @var Core
     */
    private $core;

    /**
     * Constructor
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * loadCatNavigation
     * @param integer $intItemId
     * @param integer $intCategoryTypeId
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadCatNavigation($intItemId, $intCategoryTypeId)
    {
        $this->core->logger->debug('core->models->Folders->loadCatNavigation(' . $intItemId . ',' . $intCategoryTypeId . ')');

        $objSelect = $this->getCategoriesTable()->select();
        $objSelect->setIntegrityCheck(false);

        /**
         * SELECT categories.*, categoryTitles.title
         * FROM categories
         * INNER JOIN categoryTitles ON categoryTitles.idCategories = categories.id
         *   AND categoryTitles.idLanguages = ?
         * WHERE categories.idCategoryTypes = ? AND
         *   categories.idParentCategory = ?
         * ORDER BY categories.lft
         */
        $objSelect->from('categories');
        $objSelect->join('categoryTitles', 'categoryTitles.idCategories = categories.id AND categoryTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->where('categories.idCategoryTypes = ' . $intCategoryTypeId);
        $objSelect->where('categories.idParentCategory = ' . $intItemId);
        $objSelect->order(array('categoryTitles.title', 'categories.lft'));

        return $this->getCategoriesTable()->fetchAll($objSelect);
    }

    /**
     * loadCategory
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function loadCategory($intElementId, $intLanguageId = null)
    {
        $this->core->logger->debug('core->models->Folders->loadCategory(' . $intElementId . ')');

        if ($intLanguageId == null) {
            $intLanguageId = $this->intLanguageId;
        }

        $objSelect = $this->getCategoriesTable()->select();
        $objSelect->setIntegrityCheck(false);

        /**
         * SELECT categories.*, categoryTitles.title
         * FROM categories
         * INNER JOIN categoryTitles ON categoryTitles.idCategories = categories.id AND
         *   categoryTitles.idLanguages = ?
         * WHERE categories.id = ?
         */
        $objSelect->from('categories');
        $objSelect->join('categoryTitles', 'categoryTitles.idCategories = categories.id AND categoryTitles.idLanguages = ' . $intLanguageId, array('title'));
        $objSelect->joinLeft('categoryCodes', 'categoryCodes.idCategories = categories.id AND categoryCodes.idLanguages = ' . $intLanguageId, array('code'));
        $objSelect->where('categories.id = ?', $intElementId);

        return $this->getCategoriesTable()->fetchAll($objSelect);
    }

    /**
     * loadCategoryTree
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function loadCategoryTree($intElementId, $blnFallbackCodes = false)
    {
        $this->core->logger->debug('core->models->Folders->loadCategory(' . $intElementId . ')');

        $objSelect = $this->getCategoriesTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('categories');
        $objSelect->join(array('rootCat' => 'categories'), 'rootCat.id = ' . $intElementId, array());
        $objSelect->join('categoryTitles', 'categoryTitles.idCategories = categories.id AND categoryTitles.idLanguages = ' . (($blnFallbackCodes == true) ? 1 : $this->intLanguageId), array('title'));
        $objSelect->joinLeft('categoryCodes', 'categoryCodes.idCategories = categories.id AND categoryCodes.idLanguages = ' . (($blnFallbackCodes == true) ? 1 : $this->intLanguageId), array('code'));
        $objSelect->where('categories.idRootCategory = rootCat.idRootCategory');
        $objSelect->where('categories.lft BETWEEN (rootCat.lft + 1) AND rootCat.rgt');

        return $this->getCategoriesTable()->fetchAll($objSelect);
    }

    /**
     * loadCategoriesMatchCode
     * @param integer|string $mixedIds
     * @param boolean $retAsArray = false
     * @return Zend_Db_Table_Rowset_Abstract|array
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadCategoriesMatchCode($mixedIds, $retAsArray = false)
    {
        $this->core->logger->debug('core->models->Folders->loadCategoriesMatchCode(' . $mixedIds . ',' . $retAsArray . ')');

        $objSelect = $this->getCategoriesTable()->select();
        $objSelect->setIntegrityCheck(false);

        /**
         * SELECT categories.id, categories.matchCode
         * FROM categories
         * WHERE categories.id = ? | WHERE categories.id IN (?,?)
         */
        $objSelect->from('categories', array('id', 'matchCode'));
        if (strpos($mixedIds, ',') === false) {
            $objSelect->where('categories.id = ?', $mixedIds);
        } else {
            $objSelect->where('categories.id IN (' . $mixedIds . ')');
        }

        $mixedCatMatchCodes = $this->getCategoriesTable()->fetchAll($objSelect);

        if ($retAsArray) {
            $mixedCatMatchCodes = $mixedCatMatchCodes->toArray();
        }
        return $mixedCatMatchCodes;
    }

    /**
     * deleteCategory
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function deleteCategory($intElementId)
    {
        $this->core->logger->debug('core->models->Folders->deleteCategory(' . $intElementId . ')');

        $this->getCategoriesTable();

        /**
         * delete categories
         */
        $strWhere = $this->objCategoriesTable->getAdapter()->quoteInto('id = ?', $intElementId);
        $strWhere .= $this->objCategoriesTable->getAdapter()->quoteInto('OR idParentCategory = ?', $intElementId);

        return $this->objCategoriesTable->delete($strWhere);
    }

    /**
     * addCategoryNode
     * @param integer $intParentId
     * @param array $arrData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addCategoryNode($intParentId, $arrData = array())
    {
        try {
            $intCategoryId = null;

            $this->getCategoriesTable();

            $objNestedSet = new NestedSet($this->objCategoriesTable);
            $objNestedSet->setDBFParent('idParentCategory');
            $objNestedSet->setDBFRoot('idRootCategory');

            /**
             * if $intParentId == 0, this is a root category node
             */
            if ($intParentId == 0) {
                $intCategoryId = $objNestedSet->newRootNode($arrData);
            } else {
                $intCategoryId = $objNestedSet->newLastChild($intParentId, $arrData);
            }

            return $intCategoryId;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * deleteCategoryNode
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function deleteCategoryNode($intCategoryId)
    {
        $this->core->logger->debug('core->models->Categories->deleteCategoryNode(' . $intCategoryId . ')');

        $this->getCategoriesTable();

        $objNestedSet = new NestedSet($this->objCategoriesTable);
        $objNestedSet->setDBFParent('idParentCategory');
        $objNestedSet->setDBFRoot('idRootCategory');

        $objNestedSet->deleteNode($intCategoryId);
    }

    /**
     * getCategoriesTable
     * @return Model_Table_Categories $objCategoriesTable
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getCategoriesTable()
    {

        if ($this->objCategoriesTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/Categories.php';
            $this->objCategoriesTable = new Model_Table_Categories();
        }

        return $this->objCategoriesTable;
    }

    /**
     * getRootLevelTable
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getRootLevelTable()
    {

        if ($this->objRootLevelTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/RootLevels.php';
            $this->objRootLevelTable = new Model_Table_RootLevels();
        }

        return $this->objRootLevelTable;
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