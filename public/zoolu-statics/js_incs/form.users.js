/**
 * form.users.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-16: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Form.Users = Class.create(Massiveart.Form, {
  
  initialize: function($super) {
    // initialize superclass
    $super();
  },
  
  /**
   * save
   */
  save: function(){
   if($(this.formId)){      
      
     /**
      * write/save texteditor content to generic form
      */
      if($$('.texteditor')){
        tinyMCE.triggerSave();
        myCore.resetTinyMCE(true);
      }
     
      /**
       * serialize generic form
       */
      var serializedForm = $(this.formId).serialize();
      
      // loader
      this.getFormSaveLoader();
      
      new Ajax.Updater(myNavigation.genTmpContainer, $(this.formId).readAttribute('action'), {
        parameters: serializedForm,
        evalScripts: true,
        onComplete: function(transport) {
          //problem: ajax.updater evalScripts = true was too late
          transport.responseText.evalScripts();
          
          if(this.blnShowFormAlert){
            //saved
            this.getFormSaveSucces();
            
            $(myNavigation.genListContainer).update($(myNavigation.genTmpContainer).innerHTML);
            
            $(myNavigation.genFormContainer).hide();
            $(myNavigation.genFormFunctions).hide();
            
            $(myNavigation.genListContainer).show();
            $(myNavigation.genListFunctions).show();
          }else{
            this.getFormSaveError();
            
            $(myNavigation.genFormContainer).update($(myNavigation.genTmpContainer).innerHTML);            
          }
          myCore.initSelectAll();
          myCore.initListHover();
        }.bind(this)
      });
    }
  },
  
  /**
   * deleteElement
   */
  deleteElement: function(){
    if($(this.formId)){

      if($('rootLevelGroupKey' + myNavigation.rootLevelGroupId)){
        tmpKey = 'Delete_' + $('rootLevelGroupKey' + myNavigation.rootLevelGroupId).getValue();
        var key = (myCore.translate[tmpKey]) ? tmpKey : 'Delete_';
      }else{
        var key = 'Delete_';
      }

      myCore.deleteAlertSingleMessage = myCore.translate[key];
      myCore.showDeleteAlertMessage(1);

      $('buttonOk').observe('click', function(event){
        myCore.hideDeleteAlertMessage();
        var intPosLastSlash = $(this.formId).readAttribute('action').lastIndexOf('/');
        var strAjaxActionBase = $(this.formId).readAttribute('action').substring(0, intPosLastSlash + 1);
        var elementId = $('id').getValue();

        // loader
        this.getFormSaveLoader();

        new Ajax.Updater(myNavigation.genListContainer, strAjaxActionBase + 'delete', {
          parameters: { id: elementId },
          evalScripts: true,
          onComplete: function() {
            //deleted
            this.getFormDeleteSucces();

            $(myNavigation.genFormContainer).hide();
            $(myNavigation.genFormFunctions).hide();

            $(myNavigation.genListContainer).show();
            $(myNavigation.genListFunctions).show();

            myCore.initSelectAll();
            myCore.initListHover();
          }.bind(this)
        });
      }.bind(this));

      $('buttonCancel').observe('click', function(event){
        myCore.hideDeleteAlertMessage();
      }.bind(this));
    }
  }    
});