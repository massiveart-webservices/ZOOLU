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

        $name = $this->view->escape($name);

        if( array_key_exists('fieldOptions', $attribs) && !empty($attribs['fieldOptions']) ) {
            $fieldOptions = json_decode( $attribs['fieldOptions'] );
        } else {
            $fieldOptions = new stdClass();
            $fieldOptions->textbox = 'text';
        }

        if( $fieldOptions->textbox == 'text' ) {

            $strOutput .= '<input type="text" name="' . $name . '" value="' . $this->view->escape($value) . '" />';

        } elseif( $fieldOptions->textbox == 'textarea' ) {

            $maxChars = $fieldOptions->charslimit;
            $fieldLength = strlen( $this->view->escape($value) );
            $chars_left = $maxChars - $fieldLength;

            $counter_id = "chars_count_" . $name;

            $strOutput .= '<div id="wrapper_'.$name.'">';

            $strOutput .= '<textarea rows="80" cols="24" name="'.$name.'" id="'.$name.'"
                                onKeyDown="myForm.countChars(\''.$name.'\', '.$maxChars.')"
                                onKeyUp="myForm.countChars(\''.$name.'\', '.$maxChars.')"
                           >'
                                . $this->view->escape($value) .
                          '</textarea>';

            $strOutput .= '<p>The ' . $fieldOptions->seoname . ' will be limited to ' . $maxChars . ' chars
                                <span id="'.$counter_id.'">' . $chars_left . '</span> chars left.
                           </p>';
            $strOutput .= '</div>';
        }

        return $strOutput;
    }

}