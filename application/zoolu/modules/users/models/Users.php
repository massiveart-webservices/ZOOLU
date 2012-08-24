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
 * @package    application.zoolu.modules.users.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Users
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Users
{

    /**
     * @var Model_Table_Users
     */
    protected $objUserTable;

    /**
     * @var Model_Table_UserGroups
     */
    protected $objUserGroupTable;

    /**
     * @var Model_Table_Groups
     */
    protected $objGroupTable;

    /**
     * @var Model_Table_GroupPermissions
     */
    protected $objGroupPermissionTable;

    /**
     * @var Model_Table_GroupGroupTypes
     */
    protected $objGroupGroupTypeTable;

    /**
     * @var Model_Table_Resources
     */
    protected $objResourceTable;

    /**
     * @var Model_Table_ResourceGroups
     */
    protected $objResourceGroupTable;

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
     * getUserTable
     * @return Zend_Db_Table_Rowset_Abstract
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadUsers()
    {
        $this->core->logger->debug('users->models->Model_Users->loadUsers()');

        $objSelect = $this->getUserTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objUserTable, array('id', 'idLanguages', 'username', 'fname', 'sname', 'email'));

        return $this->objUserTable->fetchAll($objSelect);
    }

    /**
     * addUser
     * @param array $arrData
     * @return integer user id
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addUser($arrData)
    {
        try {
            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $arrData['idUsers'] = $intUserId;
            $arrData['creator'] = $intUserId;
            $arrData['created'] = date('Y-m-d H:i:s');
            $arrData['password'] = md5($arrData['password']);

            $this->getUserTable()->insert($arrData);

            return $this->objUserTable->getAdapter()->lastInsertId();
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * editUser
     * @param integer $intUserId
     * @param array $arrData
     * @return integer the number of rows updated
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editUser($intUserId, $arrData)
    {
        try {
            $this->getUserTable();
            $strWhere = $this->objUserTable->getAdapter()->quoteInto('id = ?', $intUserId);

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $arrData['idUsers'] = $intUserId;
            $arrData['changed'] = date('Y-m-d H:i:s');

            if ($arrData['password'] != '') {
                $arrData['password'] = md5($arrData['password']);
            } else {
                unset($arrData['password']);
            }

            return $this->objUserTable->update($arrData, $strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * deleteUser
     * @param integer $intUserId
     * @return integer the number of rows deleted
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteUser($intUserId)
    {
        try {
            $strWhere = $this->getUserTable()->getAdapter()->quoteInto('id = ?', $intUserId);
            return $this->objUserTable->delete($strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * deleteUsers
     * @param array $arrUserIds
     * @return integer the number of rows deleted
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function deleteUsers($arrUserIds)
    {
        try {
            $strWhere = '';
            $intCounter = 0;
            if (count($arrUserIds) > 0) {
                foreach ($arrUserIds as $intUserId) {
                    if ($intUserId != '') {
                        if ($intCounter == 0) {
                            $strWhere .= $this->getUserTable()->getAdapter()->quoteInto('id = ?', $intUserId);
                        } else {
                            $strWhere .= $this->getUserTable()->getAdapter()->quoteInto(' OR id = ?', $intUserId);
                        }
                        $intCounter++;
                    }
                }
            }
            return $this->objUserTable->delete($strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * updateUserGroups
     * @param integer $intUserId
     * @param array $arrGroups
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function updateUserGroups($intUserId, $arrGroups)
    {
        try {
            if (count($arrGroups) > 0) {
                $this->getUserGroupTable();

                /**
                 * delete data
                 */
                $strWhere = $this->objUserGroupTable->getAdapter()->quoteInto('idUsers = ?', $intUserId);
                $this->objUserGroupTable->delete($strWhere);

                foreach ($arrGroups as $intGroupId) {
                    $arrData = array(
                        'idUsers'  => $intUserId,
                        'idGroups' => $intGroupId
                    );

                    $this->objUserGroupTable->insert($arrData);
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getUserGroups
     * @param integer $intUserId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getUserGroups($intUserId)
    {
        try {
            $objSelect = $this->getUserGroupTable()->select();

            $objSelect->setIntegrityCheck(false);
            $objSelect->from($this->objUserGroupTable, array('idUsers', 'idGroups'))
                ->joinInner('groups', 'groups.id = userGroups.idGroups', array('key'))
                ->where('userGroups.idUsers = ?', $intUserId);
            return $this->objUserGroupTable->fetchAll($objSelect);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * addGroup
     * @param array $arrData
     * @return integer group id
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addGroup($arrData)
    {
        try {
            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $arrData['idUsers'] = $intUserId;
            $arrData['creator'] = $intUserId;
            $arrData['created'] = date('Y-m-d H:i:s');

            return $this->getGroupTable()->insert($arrData);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * editGroup
     * @param integer $intGroupId
     * @param array $arrData
     * @return integer the number of rows updated
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editGroup($intGroupId, $arrData)
    {
        try {
            $this->getGroupTable();
            $strWhere = $this->objGroupTable->getAdapter()->quoteInto('id = ?', $intGroupId);

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $arrData['idUsers'] = $intUserId;
            $arrData['changed'] = date('Y-m-d H:i:s');

            return $this->objGroupTable->update($arrData, $strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * deleteGroup
     * @param integer $intGroupId
     * @return integer the number of rows deleted
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteGroup($intGroupId)
    {
        try {
            $strWhere = $this->getGroupTable()->getAdapter()->quoteInto('id = ?', $intGroupId);
            return $this->objGroupTable->delete($strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * updateGroupPermissions
     * @param integer $intGroupId
     * @param array $arrPermissions
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function updateGroupPermissions($intGroupId, $arrPermissions)
    {
        try {
            $this->getGroupPermissionTable();

            /**
             * delete data
             */
            $strWhere = $this->objGroupPermissionTable->getAdapter()->quoteInto('idGroups = ?', $intGroupId);
            $this->objGroupPermissionTable->delete($strWhere);

            if (count($arrPermissions) > 0) {
                foreach ($arrPermissions as $arrPermissionData) {

                    if (isset($arrPermissionData['permissions']) && is_array($arrPermissionData['permissions'])) {
                        $intLanguageId = $arrPermissionData['language'];
                        foreach ($arrPermissionData['permissions'] as $intPermissionId) {
                            $arrData = array(
                                'idGroups'       => $intGroupId,
                                'idLanguages'    => $intLanguageId,
                                'idPermissions'  => $intPermissionId
                            );

                            $this->objGroupPermissionTable->insert($arrData);
                        }
                    }
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * updateGroupGroupTypes
     * @param integer $intGroupId
     * @param array $arrGroupTypes
     * @return void
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function updateGroupGroupTypes($intGroupId, $arrGroupTypes)
    {
        try {
            $this->getGroupGroupTypeTable();

            /**
             * delete data
             */
            $strWhere = $this->objGroupGroupTypeTable->getAdapter()->quoteInto('idGroups = ?', $intGroupId);
            $this->objGroupGroupTypeTable->delete($strWhere);

            if (count($arrGroupTypes) > 0) {
                if (is_array($arrGroupTypes)) {
                    foreach ($arrGroupTypes as $intGroupTypeId) {
                        $arrData = array(
                            'idGroups'      => $intGroupId,
                            'idGroupTypes'  => $intGroupTypeId
                        );

                        $this->objGroupGroupTypeTable->insert($arrData);
                    }
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadGroupsById
     * @param mixed $mixedGroupIds
     * @return Zend_Db_Table_Rowset
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadGroupsById($mixedGroupIds)
    {
        $this->core->logger->debug('users->models->Users->loadGroupsById(' . $mixedGroupIds . ')');
        try {
            $this->getGroupTable();

            $arrGroupIds = array();
            if (is_array($mixedGroupIds)) {
                $arrGroupIds = $mixedGroupIds;
            } else if (isset($mixedGroupIds) && $mixedGroupIds != '') {
                $strTmpGroupIds = trim($mixedGroupIds, '[]');
                $arrGroupIds = explode('][', $strTmpGroupIds);
            }

            $objSelect = $this->objGroupTable->select();
            $objSelect->setIntegrityCheck(false);

            if (count($arrGroupIds) > 0) {
                $strIds = '';
                foreach ($arrGroupIds as $intGroupId) {
                    $strIds .= $intGroupId . ',';
                }

                $objSelect->from('groups', array('id', 'title'));
                $objSelect->where('groups.id IN (' . trim($strIds, ',') . ')');
                $objSelect->order('FIND_IN_SET(groups.id,\'' . trim($strIds, ',') . '\')');

                return $this->objGroupTable->fetchAll($objSelect);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }


    /**
     * getGroups
     * @param integer $intGroupId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     * @version 1.0
     */
    public function getGroupsPermissions()
    {
        try {
            $objSelect = $this->getGroupTable()->select();

            $objSelect->setIntegrityCheck(false);
            $objSelect->from($this->objGroupTable, array('id', 'title', 'key'))
                ->joinInner('groupPermissions', 'groupPermissions.idGroups = groups.id', array())
                ->joinInner('permissions', 'permissions.id = groupPermissions.idPermissions', array('id AS permissionId', 'title AS permissionTitle'));

            return $this->objGroupTable->fetchAll($objSelect);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getGroups
     * @param integer $intGroupId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     * @version 1.0
     */
    public function getGroups()
    {
        try {
            return $this->getGroupTable()->fetchAll();
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getGroupsWithFilter
     * @param integer $intGroupId
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     * @version 1.0
     */
    public function getGroupsWithFilter($objFilters = null, $strSearchValue = '')
    {
        try {
            $objSelect = $this->getGroupTable()->select();
            $objSelect->setIntegrityCheck(false);
            $objSelect->from('groups');
            $objSelect->joinLeft('groupGroupTypes', 'groups.id = groupGroupTypes.idGroups');
            if ($objFilters != null) {
                foreach ($objFilters as $objFilter) {
                    $objSelect->where($objFilter->key . ' = ?', $objFilter->value);
                }
            }
            if ($strSearchValue != '') {
                $objSelect->where('(groups.title LIKE ?', '%' . $strSearchValue . '%');
                $objSelect->orWhere('groups.description LIKE ?)', '%' . $strSearchValue . '%');
            }
            return $this->getGroupTable()->fetchAll($objSelect);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getGroupPermissions
     * @param integer $intGroupId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     * @version 1.0
     */
    public function getGroupPermissions($intGroupId)
    {
        try {
            $objSelect = $this->getGroupPermissionTable()->select()->where('idGroups = ?', $intGroupId);
            return $this->objGroupPermissionTable->fetchAll($objSelect);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getGroupGroupTypes
     * @param integer $intGroupId
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     * @version 1.0
     */
    public function getGroupGroupTypes($intGroupId)
    {
        try {
            $objSelect = $this->getGroupGroupTypeTable()->select()->where('idGroups = ?', $intGroupId);
            return $this->objGroupGroupTypeTable->fetchAll($objSelect);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * addResource
     * @param array $arrData
     * @return integer resource id
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addResource($arrData)
    {
        try {
            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $arrData['idUsers'] = $intUserId;
            $arrData['creator'] = $intUserId;
            $arrData['created'] = date('Y-m-d H:i:s');

            $this->getResourceTable()->insert($arrData);

            return $this->objResourceTable->getAdapter()->lastInsertId();
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * editResource
     * @param integer $intResourceId
     * @param array $arrData
     * @return integer the number of rows updated
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editResource($intResourceId, $arrData)
    {
        try {
            $this->getResourceTable();
            $strWhere = $this->objResourceTable->getAdapter()->quoteInto('id = ?', $intResourceId);

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $arrData['idUsers'] = $intUserId;
            $arrData['changed'] = date('Y-m-d H:i:s');

            return $this->objResourceTable->update($arrData, $strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * deleteResource
     * @param integer $intResourceId
     * @return integer the number of rows deleted
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteResource($intResourceId)
    {
        try {
            $strWhere = $this->getResourceTable()->getAdapter()->quoteInto('id = ?', $intResourceId);
            return $this->objResourceTable->delete($strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * updateResourceGroups
     * @param integer $intResourceId
     * @param array $arrGroups
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function updateResourceGroups($intResourceId, $arrGroups)
    {
        try {
            $this->getResourceGroupTable();

            /**
             * delete data
             */
            $strWhere = $this->objResourceGroupTable->getAdapter()->quoteInto('idResources = ?', $intResourceId);
            $this->objResourceGroupTable->delete($strWhere);

            if (count($arrGroups) > 0) {
                foreach ($arrGroups as $intGroupId) {
                    $arrData = array(
                        'idResources'  => $intResourceId,
                        'idGroups'     => $intGroupId
                    );

                    $this->objResourceGroupTable->insert($arrData);
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getResources
     * @param integer $intGroupId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     * @version 1.0
     */
    public function getResources()
    {
        try {
            return $this->getResourceTable()->fetchAll();
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getResourceGroups
     * @param integer $intResourceId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getResourceGroups($intResourceId)
    {
        try {
            $objSelect = $this->getResourceGroupTable()->select()->where('idResources = ?', $intResourceId);
            return $this->objResourceGroupTable->fetchAll($objSelect);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getResourcesGroups
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getResourcesGroups()
    {
        try {
            $objSelect = $this->getResourceTable()->select();

            $objSelect->setIntegrityCheck(false);
            $objSelect->from($this->objResourceTable, array('id', 'title', 'key'))
                ->joinInner('resourceGroups', 'resourceGroups.idResources = resources.id', array())
                ->joinInner('groups', 'groups.id = resourceGroups.idGroups', array('id AS groupId', 'title AS groupTitle', 'key AS groupKey'))
                ->joinInner('groupPermissions', 'groupPermissions.idGroups = groups.id', array())
                ->joinInner('permissions', 'permissions.id = groupPermissions.idPermissions', array('id AS permissionId', 'title AS permissionTitle'));

            return $this->objResourceTable->fetchAll($objSelect);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getUserTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getUserTable()
    {

        if ($this->objUserTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/tables/Users.php';
            $this->objUserTable = new Model_Table_Users();
        }

        return $this->objUserTable;
    }

    /**
     * getUserGroupTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getUserGroupTable()
    {

        if ($this->objUserGroupTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/tables/UserGroups.php';
            $this->objUserGroupTable = new Model_Table_UserGroups();
        }

        return $this->objUserGroupTable;
    }

    /**
     * getGroupTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGroupTable()
    {

        if ($this->objGroupTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/tables/Groups.php';
            $this->objGroupTable = new Model_Table_Groups();
        }

        return $this->objGroupTable;
    }

    /**
     * getGroupPermissionTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGroupPermissionTable()
    {

        if ($this->objGroupPermissionTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/tables/GroupPermissions.php';
            $this->objGroupPermissionTable = new Model_Table_GroupPermissions();
        }

        return $this->objGroupPermissionTable;
    }

    /**
     * getGroupGroupTypeTable
     * @return Zend_Db_Table_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getGroupGroupTypeTable()
    {

        if ($this->objGroupGroupTypeTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/tables/GroupGroupTypes.php';
            $this->objGroupGroupTypeTable = new Model_Table_GroupGroupTypes();
        }

        return $this->objGroupGroupTypeTable;
    }

    /**
     * getResourceTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getResourceTable()
    {

        if ($this->objResourceTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/tables/Resources.php';
            $this->objResourceTable = new Model_Table_Resources();
        }

        return $this->objResourceTable;
    }

    /**
     * getResourceGroupTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getResourceGroupTable()
    {

        if ($this->objResourceGroupTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/tables/ResourceGroups.php';
            $this->objResourceGroupTable = new Model_Table_ResourceGroups();
        }

        return $this->objResourceGroupTable;
    }
}

?>