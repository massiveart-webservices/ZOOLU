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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ContentController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2010-04-15: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

require_once(GLOBAL_ROOT_PATH.'/library/IP2Location/ip2location.class.php');

class NewsletterController extends Zend_Controller_Action {

  /**
   * @var Core
   */
  protected $core; 
  
  /**
   * @var Model_Newsletter
   */
  protected $objModelNewsletters;
  
  /**
   * @var Model_Templates
   */
  protected $objModelTemplates;
  
  /**
   * preDispatch
   * Called before action method.
   * 
   * @return void  
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function preDispatch(){
    $this->core = Zend_Registry::get('Core');    
    $this->request = $this->getRequest();
  }
  
  /**
   * previewAction
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function previewAction() {
    //Load the newsletter with the given Id
    $intNewsletterId = $this->getRequest()->getParam('id');
    $objNewsletters = $this->getModelNewsletters()->load($intNewsletterId);
    if(count($objNewsletters) > 0) {
      $objNewsletter = $objNewsletters->current();
      $this->renderNewsletter($objNewsletter);
      $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/newsletter/'.$this->core->sysConfig->newsletter->theme);
      $this->renderScript('/master.php');
    }
  }
    
  /**
   * renderNewsletter
   * @param Zend_Db_Table_Row $objNewsletter
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  private function renderNewsletter($objNewsletter){
    $objGenericData = $this->getModelNewsletters()->loadGenericForm($objNewsletter);
    
    //Load Template
    $objTemplate = $this->getModelTemplates()->loadTemplateById($objGenericData->Setup()->getTemplateId());
    
    //Assign the values to the template
    $this->view->assign('setup', $objGenericData->Setup());
    if(count($objTemplate) > 0) {
      $this->view->assign('template_file', $objTemplate->current()->filename);
    }
    
    $this->view->assign('extended', true);
    $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/newsletter/'.$this->core->sysConfig->newsletter->theme);
  }
  
  /**
   * getModelNewsletters
   * @return Model_Newsletters
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelNewsletters(){
    if (null === $this->objModelNewsletters) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'newsletters/models/Newsletters.php';
      $this->objModelNewsletters = new Model_Newsletters();
    }

    return $this->objModelNewsletters;
  }
  
  /**
   * getModelTemplates
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelTemplates(){
    if (null === $this->objModelTemplates) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Templates.php';
      $this->objModelTemplates = new Model_Templates();
    }

    return $this->objModelTemplates;
  }
}
?>