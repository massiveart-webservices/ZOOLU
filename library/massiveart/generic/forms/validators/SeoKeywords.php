<?php

require_once(dirname(__FILE__) . '/Abstract.php');


class Form_Validator_SeoKeywords extends Form_Validator_Abstract
{

    /**
     * @var array
     */
    protected $_arrMessages;

    public function getMessages()
    {
        return $this->_arrMessages;
    }

    public function addMessage($strKey, $strMessage)
    {
        $this->_arrMessages[$strKey] = $strMessage;
    }

    public function isValid($value)
    {
        $max_keywords = 5;

        $is_valid = count( explode(',', $value) ) > $max_keywords ? false : true;
        if( !$is_valid ) {
            $this->addMessage('errMessage', $this->core->translate->_('Err_extra_keywords'));
        }

        return $is_valid;
    }
}
?>