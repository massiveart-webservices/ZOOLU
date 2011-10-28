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
 * @package    application.zoolu.modules.cms.views
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * RssHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-08-25: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class RssHelper {
  
  /**
   * @var Core
   */
  protected $core;
  
  /**
   * @var Page
   */
  protected $objPage;

  /**
   * @var Zend_Translate
   */
  protected $objTranslate;
  
  /**
   * constructor
   * @author Cornelius Hansjakob <cha@massiveart.com>   
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * getTemplateFile
   * @return string 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function getTemplateFile(){
    return $this->objPage->getTemplateFile();
  }
  
  /**
   * getRootLevelTitle
   * @return string $strRootLevelTitle
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function getRootLevelTitle(){
    return $this->objPage->getRootLevelTitle();
  }
    
  /**
   * setPage    
   * @param Page $objPage   
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function setPage(Page $objPage){
    $this->objPage = $objPage;
  }
  
  /**
   * setTranslate    
   * @param Zend_Translate $objTranslate   
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function setTranslate(Zend_Translate $objTranslate){
    $this->objTranslate = $objTranslate;
  }
  
  /**
   * getTranslate    
   * @return Zend_Translate $objTranslate   
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function getTranslate(){
    return $this->objTranslate;
  }
}