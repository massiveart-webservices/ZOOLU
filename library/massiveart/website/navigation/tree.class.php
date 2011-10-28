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
 * @package    library.massiveart.website.cache.navigation
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * NavigationTree
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-17: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website.navigation
 * @subpackage NavigationTree
 */

require_once(dirname(__FILE__).'/item.class.php');

class NavigationTree extends NavigationItem implements Iterator, Countable {

  private $blnOrderUpdated = false;

  private $arrItems = array();
  private $arrTrees = array();

  private $arrOrder = array();

  /**
   * construct
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct() { }

  /**
   * addItem
   * @param NavigationItem $objItem
   * @param string $strName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addItem(NavigationItem $objItem, $strName = null){
    $this->arrItems[$strName] = $objItem;
    $this->arrOrder[$strName] = $this->arrItems[$strName]->getOrder();
    $this->blnOrderUpdated = true;
  }

  /**
   * hasSubTrees
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function hasSubTrees(){
    return (count($this->arrTrees) > 0) ? true : false;
  }
  
  /**
   * hasSubTree
   * @param string $strName
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function hasSubTree($strName){
    return (array_key_exists($strName, $this->arrTrees)) ? true : false;
  }

  /**
   * addTree
   * @param NavigationTree $objTree
   * @param string $strName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addTree(NavigationTree $objTree, $strName = null){
    $this->arrTrees[$strName] = $objTree;
    $this->arrOrder[$strName] = $this->arrTrees[$strName]->getOrder();
    $this->blnOrderUpdated = true;
  }

  /**
   * addToParentTree
   * @param NavigationTree|NavigationItem $objNav
   * @param string $strName
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function addToParentTree($objNav, $strName = null){
    if($this->intId == $objNav->getParentId()){
      if($objNav instanceof NavigationTree){
        $this->addTree($objNav, $strName);
      }else if($objNav instanceof NavigationItem){
        $this->addItem($objNav, $strName);        
      }
      return true;
    }else{
      foreach($this->arrTrees as $objSubTree){
        if($objSubTree->addToParentTree($objNav, $strName)){
          break;
        }
      }
    }
  }

  /**
   * sort()
   * Sort elements according to their order
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function sort(){
    if($this->blnOrderUpdated){
      $arrItems = array();
      $intIndex = 0;

      foreach ($this->arrOrder as $strKey => $intOrder){
        if($intOrder === null){
          if(($intOrder = $this->{$strKey}->getOrder()) === null) {
            while(array_search($intIndex, $this->arrOrder, true)) {
              $intIndex++;
            }
            $arrItems[$intIndex] = $strKey;
            $intIndex++;
          }else{
            $arrItems[$intOrder] = $strKey;
          }
        }else{
          $arrItems[$intOrder] = $strKey;
        }
      }

      $arrItems = array_flip($arrItems);
      asort($arrItems);
      $this->arrOrder = $arrItems;
      $this->blnOrderUpdated = false;
    }
  }

  /**
   * Overloading: access to navigation items and trees
   * @param  string $strName
   * @return NavigationItem|NavigationTree|null
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __get($strName){
    if(isset($this->arrItems[$strName])){
      return $this->arrItems[$strName];
    }elseif (isset($this->arrTrees[$strName])){
      return $this->arrTrees[$strName];
    }

    return null;
  }

  /**
   * Overloading: access to navigation items and trees
   * @param  string $strName
   * @param  NavigationItem|NavigationTree $obj
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __set($strName, $obj){
    if($value instanceof NavigationItem){
      $this->addtem($obj, $strName);
      return;
    }elseif($obj instanceof NavigationTree){
      $this->addTree($obj, $strName);
      return;
    }

    if(is_object($obj)){
      $strType = get_class($obj);
    }else{
      $strType = gettype($obj);
    }
    throw new Zend_Form_Exception('Only navigation items and trees may be overloaded; variable of type "'.$strType.'" provided');
  }


  /**
   * rewind
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function rewind() {
    $this->sort();
    reset($this->arrOrder);
  }

  /**
   * current
   * @return NavigationItem|NavigationTree
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function current() {
    $this->sort();
    current($this->arrOrder);
    $strKey = key($this->arrOrder);

    if(isset($this->arrItems[$strKey])){
      return $this->arrItems[$strKey];
    }elseif (isset($this->arrTrees[$strKey])){
      return $this->arrTrees[$strKey];
    } else{
      throw new Exception('Corruption detected in navigation tree; invalid key ("'.$strKey.'") found in internal iterator');
    }
  }

  /**
   * key
   * @return string
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function key() {
    $this->sort();
    return key($this->arrOrder);
  }

  /**
   * next
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function next() {
    $this->sort();
    next($this->arrOrder);
  }

  /**
   * valid
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function valid() {
    $this->sort();
    return (current($this->arrOrder) !== false);
  }

  /**
   * count
   * @return integer
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function count(){
    return count($this->arrOrder);
  }
}
?>