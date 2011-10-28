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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * TagsController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-02-22: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Core_TagsController extends AuthControllerAction {

  /**
	 * The default action - show the home page
	 */
  public function indexAction(){

  	$this->_helper->viewRenderer->setNoRender();
  }
  
  /**
   * livesearchAction
   * @author Thomas Schedler <tsh@massiveart.com>   
   */
  public function livesearchAction(){
    $this->_helper->viewRenderer->setNoRender();
    
    require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Tags.php';
    $objModelTags = new Model_Tags();      
    $objAllTags = $objModelTags->loadAllTags();
    
    $strAllTags = '[';
    if(count($objAllTags) > 0){      
      foreach($objAllTags as $objTag){
        $strAllTags .= '{"caption":"'.htmlentities($objTag->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'","value":'.$objTag->id.'},';
      }
      $strAllTags = trim($strAllTags, ',');
    }
    $strAllTags .= ']';
    echo $strAllTags;
  }

}
