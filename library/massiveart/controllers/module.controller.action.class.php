<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
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
 * @package    library.massiveart.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ModuleControllerAction
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-08-23: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class ModuleControllerAction extends AuthControllerAction {

  /**
   * init
   */
  public function init(){
    parent::init();
  }

	/**
   * preDispatch
   */
  public function preDispatch(){
  	parent::preDispatch();        
  	
  	/**
     * load item if the defined params are posted
     */
    if($this->getRequest()->isPost()){
      $intRootLevelId = $this->getRequest()->getParam('rootLevelId', 0);
      $intRootLevelGroupId = $this->getRequest()->getParam('rootLevelGroupId', 0);
      $intRelationId = $this->getRequest()->getParam('relationId', 0);
      $intParentId = $this->getRequest()->getParam('parentId', 0);
      $intParentTypeId = $this->getRequest()->getParam('parentTypeId', 0);
      
      if($intRootLevelId > 0 && $intRelationId > 0 && $intParentId > 0 && $intParentTypeId > 0){
        $objSelectItem = new stdClass();
        $objSelectItem->rootLevelId = $intRootLevelId;
        $objSelectItem->rootLevelGroupId = $intRootLevelGroupId;
        $objSelectItem->relationId = $intRelationId;
        $objSelectItem->parentId = $intParentId;
        $objSelectItem->parentTypeId = $intParentTypeId;
        $this->core->objCoreSession->selectItem = $objSelectItem;
      }
    }
  }
}
?>