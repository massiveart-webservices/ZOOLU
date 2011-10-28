<?php
/**
 * GenericDataHelperTag
 *
 * Helper to save and load the "tag" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-29: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.helpers
 * @subpackage GenericDataHelper_Tag
 */

require_once(dirname(__FILE__).'/../../../../data/helpers/Abstract.php');

class GenericDataHelper_Tag extends GenericDataHelperAbstract  {

  /**
   * @var Model_Tags
   */
  private $objModelTags;

  protected $strTags;
  protected $arrTagIds = array();
  protected $arrTags = array();
  protected $arrNewTagIds = array();
  protected $arrNewTags = array();

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
      $this->validateTags();

      $this->getModelTags();

      $this->objModelTags->deletTypeTags($strType, $strElementId, $intVersion);

      $this->objModelTags->addTypeTags($strType, $this->arrNewTagIds, $strElementId, $intVersion);

      $this->load($intElementId, $strType, $strElementId, $intVersion);
            
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

      $this->getModelTags();
      $this->arrTagIds = array();

      $objTagData = $this->objModelTags->loadTypeTags($strType, $strElementId, $intVersion);
      
      $this->objElement->setValue($objTagData);

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * validateTags
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  private function validateTags(){

    $this->getModelTags();

    $this->arrNewTagIds = array();
    
    if($this->objElement->getValue() instanceof Zend_Db_Table_Rowset_Abstract){
      foreach($this->objElement->getValue() as $objTag){
        $this->arrNewTagIds[] = $objTag->id;  
      }
    }elseif(is_string($this->objElement->getValue())){
      
      $this->arrNewTags = split(',', $this->objElement->getValue());
  
      /**
       * get tag ids
       */
      foreach($this->arrNewTags as $mixedTag){
        $mixedTag = trim($mixedTag);
        if($mixedTag != ''){
          try{
          	if(is_numeric($mixedTag)){
          		$objTagData = $this->objModelTags->loadTag($mixedTag);
          	}else{
              $objTagData = $this->objModelTags->loadTagByName($mixedTag);	
          	}          
  
            /**
             * if the tag exists
             */
            if(count($objTagData) > 0){
              $objTag = $objTagData->current();
  
              /**
               * fill in tagIds array
               */
              if(!in_array($objTag->id, $this->arrNewTagIds)) {
                $this->arrNewTagIds[] = $objTag->id;
              }
  
            }else{
              /**
               * else, insert new tag
               */
              $this->arrNewTagIds[] = $this->objModelTags->addTag($mixedTag);
            }
          }catch (PDOException $exc) {
            $this->core->logger->logException($exc);
          }
        }
      }
    }
  }

  /**
   * getModelTags
   * @return Model_Tags
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelTags(){
    if (null === $this->objModelTags) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Tags.php';
      $this->objModelTags = new Model_Tags();
      $this->objModelTags->setLanguageId($this->objElement->Setup()->getLanguageId());
    }

    return $this->objModelTags;
  }
}
?>