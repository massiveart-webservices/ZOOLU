<?php

/**
 * Form_Helper_FormVideoSelect
 *
 * Helper to generate a "tag" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-27: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormVideoSelect
 */

class Plugin_FormHelper_FormVideoSelect extends Zend_View_Helper_FormElement {

  /**
   * formVideoSelect
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param string $name
   * @param string $value
   * @param array $attribs
   * @param mixed $options
   * @param integer $intVideoTypeId
   * @param string $strVideoThumb
   * @param string $strVideoTitle
   * @version 1.0
   */
  public function formVideoSelect($name, $value = null, $attribs = null, $options = null, $intVideoTypeId, $strVideoUserId, $strVideoThumb, $strVideoTitle){
    $info = $this->_getInfo($name, $value, $attribs);
    $core = Zend_Registry::get('Core');
    extract($info); // name, id, value, attribs, options, listsep, disable

    // XHTML or HTML end tag
    $endTag = ' />';
    if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
      $endTag= '>';
    }

    $xhtml = '
    <input type="hidden" value="'.$this->view->escape($value).'" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" '.$endTag.'
    <input type="hidden" value="'.$strVideoThumb.'" id="'.$this->view->escape($id).'Thumb" name="'.$this->view->escape($name).'Thumb" '.$endTag.'
    <input type="hidden" value="'.$strVideoTitle.'" id="'.$this->view->escape($id).'Title" name="'.$this->view->escape($name).'Title" '.$endTag;

    // force $value to array so we can compare multiple values to multiple
    // options; also ensure it's a string for comparison purposes.
    $value = array_map('strval', (array) $intVideoTypeId);

    // Build the surrounding select element first.
    $xhtml .= '<select'
              . ' name="' . $this->view->escape($name.'TypeId') . '"'
              . ' id="' . $this->view->escape($id.'TypeId') . '"'
              . $this->_htmlAttribs($attribs)
              . ">\n    ";

    // build the list of options
    $list = array();
    $list[] = '<option label="'.$core->translate->_('Please_choose', false).'" value="" selected="selected">'.$core->translate->_('Please_choose', false).'</option>';
           
    foreach ($attribs['MultiOptions'] as $opt_value => $opt_label) {
      if (is_array($opt_label)) {
        $opt_disable = '';
        if (is_array($disable) && in_array($opt_value, $disable)) {
          $opt_disable = ' disabled="disabled"';
        }
        $list[] = '<optgroup'
                  . $opt_disable
                  . ' label="' . $this->view->escape($opt_value) .'">';
        foreach ($opt_label as $val => $lab) {
          $list[] = $this->optBuild($val, $lab, $value, $disable);
        }
        $list[] = '</optgroup>';
      } else {
        $list[] = $this->optBuild($opt_value, $opt_label, $value, $disable);
      }
    }

    // add the options to the xhtml and close the select
    $xhtml .= implode("\n    ", $list) . "\n</select>";

    // XHTML or HTML end tag
    $endTag = ' />';
    if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
      $endTag= '>';
    }

    // javascript observer
    $xhtml .= '
    <script type="text/javascript" language="javascript">
      myForm.initVideoChannelObserver(\''.$this->view->escape($id).'\');
      myForm.getSelectedVideo(\''.$this->view->escape($id).'\');
    </script>';

    return $xhtml;
  }

  /**
   * Builds the actual <option> tag
   *
   * @param string $value Options Value
   * @param string $label Options Label
   * @param array  $selected The option value(s) to mark as 'selected'
   * @param array|bool $disable Whether the select is disabled, or individual options are
   * @return string Option Tag XHTML
   */
  protected function optBuild($value, $label, $selected, $disable){
    if (is_bool($disable)) {
      $disable = array();
    }

    $opt = '<option'
           . ' value="' . $this->view->escape($value) . '"'
           . ' label="' . $this->view->escape($label) . '"';

    // disabled?
    if (in_array($value, $disable)) {
     $opt .= ' disabled="disabled"';
    }

    $opt .= '>' . $this->view->escape($label) . "</option>";

    return $opt;
  }
}

?>