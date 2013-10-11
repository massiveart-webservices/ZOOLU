<?php

/**
 * Form_Helper_FormFreeText
 *
 * Helper to generate a "tag" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-19: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormFreeText
 */

class Form_Helper_FormFreeText extends Zend_View_Helper_FormElement
{

    /**
     * formFreeText
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param string $name
     * @param string $value
     * @param array $attribs
     * @param mixed $options
     * @version 1.0
     */
    public function formFreeText($name, $value = null, $attribs = null, $options = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // XHTML or HTML end tag
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag = '>';
        }

        $strOutput = '';
        if ($value != '') {
            // build the element
            $strOutput = '
                  <div class="textdisplaywrapper">
                    ' . html_entity_decode($this->view->escape($value)) . '
                    <input type="hidden" value="' . $this->view->escape($value) . '" id="' . $this->view->escape($id) . '" name="' . $this->view->escape($name) . '" ' . $endTag . '
                  </div>';
        }

        return $strOutput;
    }
}

?>