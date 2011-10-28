<?php

/**
 * Form_Decorator_VideoSelect
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-27: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Plugin_FormDecorator_VideoSelect extends Zend_Form_Decorator_Abstract {

  /**
   * @var Core
   */
  private $core;

  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct($options = null){
    $this->core = Zend_Registry::get('Core');
    parent::__construct($options);
  }

  /**
   * buildLabel
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function buildLabel(){

    $element = $this->getElement();
    $label = $element->getLabel();

    if (empty($label)){
      return '';
    }

    if ($element->isRequired()) {
      $label .= ' *';
    }

    return $element->getView()->formLabel($element->getName(), $label, array('class' => 'fieldtitle')).'<br/>';
  }

  /**
   * buildDescription
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function buildDescription(){
    $element = $this->getElement();
    $desc    = $element->getDescription();

    if (empty($desc)){
      return '';
    }

    return '<div class="description">'.$desc.'</div>';
  }

  /**
   * buildVideoSelect
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.1
   */
  public function buildVideoSelect(){

    $element = $this->getElement();
    $helper  = $element->helper;

    $strOutput = $element->getView()->$helper($element->getName(), $element->getValue(), $element->getAttribs(), $element->options, $element->intVideoTypeId, $element->strVideoUserId, $element->strVideoThumb, $element->strVideoTitle);

    return $strOutput;
  }

  /**
   * buildErrors
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function buildErrors(){

    $element  = $this->getElement();
    $messages = $element->getMessages();

    if (empty($messages)){
      return '';
    }

    return '<div class="errors">'.$element->getView()->formErrors($messages).'</div>';
  }

  /**
   * render
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function render($content){

    $element = $this->getElement();

    if (!$element instanceof Zend_Form_Element) {
      return $content;
    }

    if (null === $element->getView()) {
      return $content;
    }

    $separator    = $this->getSeparator();
    $placement    = $this->getPlacement();
    $label        = $this->buildLabel();
    $videoSelect  = $this->buildVideoSelect();
    $errors       = $this->buildErrors();
    $desc         = $this->buildDescription();
    
    $userSelect = $this->buildVideoSelect();
    
    $strOutput = '<div class="field-12">
                    <input type="hidden" id="'.$element->getName().'TypeCur" name="'.$element->getName().'TypeCur" value="'.$element->intVideoTypeId.'">
                    <input type="hidden" id="'.$element->getName().'UserCur" name="'.$element->getName().'UserCur" value="'.$element->strVideoUserId.'">
                    <div id="'.$element->getName().'SelectedContainer" class="field ">
                    
                      <div id="'.$element->getName().'SelectedService" class="field-12"></div>
                      <div class="selectedVideo bg2">
                        <div id="div_selected'.$element->getName().'"></div>
                        <div class="videoItem bg1">
                          <div class="videoThumb"><img src="/zoolu-statics/images/icons/icon_novideo.png" witdh="100" style="border-right:1px solid #ccc;"/></div>
                          <div class="videoInfos"><strong>Kein Video</strong></div>   
                        </div>
                      </div>
                    </div>
                    <div class="field-3">
                   ' .$label
                     .$desc
                     .$videoSelect
                     .$errors
                     .'
                    </div>
                    <div id="div_'.$element->getName().'_users" >
                     
                    </div>
                  </div>
                  <div class="field-'.($element->getAttrib('columns')).'">
                    <div class="field videoContainer" id="div_'.$element->getName().'">&nbsp;<br/></div>
                  </div>';

    switch ($placement) {
      case (self::PREPEND):
        return $strOutput . $separator . $content;
      case (self::APPEND):
      default:
        return $content . $separator . $strOutput;
    }
  }
}

?>