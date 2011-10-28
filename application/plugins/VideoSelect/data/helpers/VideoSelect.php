<?php
/**
 * GenericDataHelperVideoSelect
 *
 * Helper to save and load the "VideoSelect" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-06: Thomas Schedler
 * 1.1, 2009-06-05: Thomas Schedler
 *                  add multi video channel clients/users
 *                  ALTER TABLE `pageVideos` ADD `userId` VARCHAR( 32 ) NOT NULL AFTER `idLanguages`
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.helpers
 * @subpackage GenericDataHelper_VideoSelect
 */

require_once(dirname(__FILE__).'/../../../../../library/massiveart/generic/data/helpers/Abstract.php');

class Plugin_DataHelper_VideoSelect extends GenericDataHelperAbstract  {

  /**
   * @var Model_Pages
   */
  private $objModel;

  private $strType;

  /**
   * save()
   * @param integer $intElementId
   * @param string $strType
   * @param string $strElementId
   * @param integet $intVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function save($intElementId, $strType, $strElementId = null, $intVersion = null){
    try{
      $this->strType = $strType;

      $this->getModel();

      $intVideoTypeId = 0;
      if(array_key_exists($this->objElement->name.'TypeCur', $_POST)){
        $intVideoTypeId = $_POST[$this->objElement->name.'TypeCur'];
      }

      $strVideoUserId = '';
      if(array_key_exists($this->objElement->name.'UserCur', $_POST)){
        $strVideoUserId = $_POST[$this->objElement->name.'UserCur'];
      }

      $strVideoThumb = '';
      if(array_key_exists($this->objElement->name.'Thumb', $_POST)){
        $strVideoThumb = $_POST[$this->objElement->name.'Thumb'];
      }
      
      $strVideoTitle = '';
      if(array_key_exists($this->objElement->name.'Title', $_POST)){
        $strVideoTitle = $_POST[$this->objElement->name.'Title'];
      }

      if($intVideoTypeId > 0 && $strVideoThumb != '' && $strVideoTitle != ''){
        $this->objModel->addVideo($intElementId, $this->objElement->getValue(), $intVideoTypeId, $strVideoUserId, $strVideoThumb, $strVideoTitle);
        $this->objElement->intVideoTypeId = $intVideoTypeId;
        $this->objElement->strVideoUserId = $strVideoUserId;
        $this->objElement->strVideoThumb = $strVideoThumb;
        $this->objElement->strVideoTitle = $strVideoTitle;
      }else{
        $this->objModel->removeVideo($intElementId);
      }

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * load()
   * @param integer $intElementId
   * @param string $strType
   * @param string $strElementId
   * @param integet $intVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function load($intElementId, $strType, $strElementId = null, $intVersion = null){
    try{
      $this->strType = $strType;

      $this->getModel();

      $elementId = $this->strType.'Id';
      $objVideoSelectData = $this->objModel->loadVideo($intElementId);
      
      if(count($objVideoSelectData) > 0){
        $objVideoSelect = $objVideoSelectData->current();
        $this->objElement->setValue($objVideoSelect->videoId);
        $this->objElement->intVideoTypeId = $objVideoSelect->idVideoTypes;
        $this->objElement->strVideoUserId = $objVideoSelect->userId;
        $this->objElement->strVideoThumb = $objVideoSelect->thumb;
        $this->objElement->strVideoTitle = $objVideoSelect->title;
      }

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * getModel
   * @return type Model
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModel(){
    if($this->objModel === null) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      $strModelFilePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.$this->objElement->Setup()->getModelSubPath().((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s').'.php';
      if(file_exists($strModelFilePath)){
        require_once $strModelFilePath;
        $strModel = 'Model_'.((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s');
        $this->objModel = new $strModel();
        $this->objModel->setLanguageId($this->objElement->Setup()->getLanguageId());
      }else{
        throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "'.$this->strType.'"');
      }
    }
    return $this->objModel;
  }
}
?>