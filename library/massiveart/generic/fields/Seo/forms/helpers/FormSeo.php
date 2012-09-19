<?php

class Form_Helper_FormSeo extends Zend_View_Helper_FormElement
{

    /**
     * The default number of rows for a textarea.
     *
     * @access public
     *
     * @var int
     */
    public $rows = 24;

    /**
     * The default number of columns for a textarea.
     *
     * @access public
     *
     * @var int
     */
    public $cols = 80;


    function formSeo($name, $value = null, $attribs = null, $options = null)
    {
        $strOutput = '';

        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, value, attribs, options, listsep, disable

        if( array_key_exists('fieldOptions', $attribs) && !empty($attribs['fieldOptions']) ) {
            $fieldOptions = json_decode($attribs['fieldOptions']);
        } else {
            $fieldOptions = new stdClass();
            $fieldOptions->textbox = 'text';
        }

        if( $fieldOptions->textbox == 'text' ) {
            $strOutput .= '<input type="text" name="" value="" />';

        } elseif( $fieldOptions->textbox == 'textarea' ) {

            $chars_max = 156;
            $fieldLength = strlen( $this->view->escape($value) );
            $chars_left = $chars_max - $fieldLength;

            $strOutput .= '<textarea rows="80" cols="24" name="' . $this->view->escape($name) . '" id="' . $this->view->escape($name) . '"
                                    onKeyDown="myForm.countChars('.$this->view->escape($name).')"
                                    onKeyUp="myForm.countChars('.$this->view->escape($name).')"
                           >'
                                . $this->view->escape($value) .
                          '</textarea>';

            $strOutput .= '<p>The ' . $fieldOptions->seoname . ' will be limited to ' . $chars_max . ' chars
                                <span id="chars_count_' . $this->view->escape($name) . '">' . $chars_left . '</span> chars left.
                           </p>';
        }

        return $strOutput;
    }

}