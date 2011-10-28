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
 * @package    library.massiveart.generic.fields.SelectTree.forms.elements
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Element_SelectTree
 * 
 * MultiCheckboxTree form element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-19: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.elements
 * @subpackage Form_Element_SelectTree
 */

class Form_Element_SelectTree extends FormElementMultiAbstract {
  
  /**
   * Use formSelectTree view helper by default
   * @var string
   */
  public $helper = 'formSelectTree';
  
  /**
   * Is the value provided valid?
   *
   * Autoregisters InArray validator if necessary.
   *
   * @param  string $value
   * @param  mixed $context
   * @return bool
   */
  public function isValid($value, $context = null){
    if($this->registerInArrayValidator()){
      if (!$this->getValidator('InArray')){
        $multiOptions = $this->getMultiOptions();
        $options      = array();

        foreach($multiOptions as $opt_value => $opt_label){
          $options[] = $opt_value;
        }

        $this->addValidator('InArray', true, array($options));
      }
    }
    return parent::isValid($value, $context);
  }
}

?>