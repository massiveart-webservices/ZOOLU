/**
 * navigation.users.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-05: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation.Users = Class.create(Massiveart.Navigation, {

  initialize: function($super) {
    // initialize superclass
    $super();
    
    this.constBasePath = '/zoolu/users';
    
    this.rootLevelType = '';
  },
  
  /**
   * getModuleRootLevelList
   * @param integer rootLevelId
   */
  getModuleRootLevelList: function(rootLevelId, rootLevelType){
    
    this.rootLevelId = rootLevelId;
    this.rootLevelType = rootLevelType;
    
    $(this.genFormContainer).hide();
    $(this.genFormFunctions).hide();
    
    this.makeSelected('naviitem'+rootLevelId);
    if($(this.preSelectedPortal) && ('naviitem'+rootLevelId) != this.preSelectedPortal){ 
      this.makeDeselected(this.preSelectedPortal);
    }  
            
    this.preSelectedPortal = 'naviitem'+rootLevelId;
    
    myList.sortColumn = '';
    myList.sortOrder = '';
    myList.resetSearch();
  },
  
  /**
   * getAddForm
   */
  getAddForm: function(){
    
    $(this.genListContainer).hide();
    $(this.genListFunctions).hide();
    
    if($('buttondelete')) $('buttondelete').hide();
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/addform', {
      parameters: { rootLevelId: this.rootLevelId },      
      evalScripts: true,     
      onComplete: function() {        
        $(this.genFormContainer).show();
        $(this.genFormFunctions).show();
        $(this.genFormContainer).scrollTo($('widgetfunctions'));        
      }.bind(this)
    });
  },
  
  /**
   * getEditForm
   */
  getEditForm: function(itemId){
    
    $(this.genListContainer).hide();
    $(this.genListFunctions).hide();
    
    if($('buttondelete')) $('buttondelete').show();
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/editform', {
      parameters: { rootLevelId: this.rootLevelId, id: itemId },      
      evalScripts: true,     
      onComplete: function() {
        $(this.genFormContainer).show();
        $(this.genFormFunctions).show();
        $(this.genFormContainer).scrollTo($('widgetfunctions'));
        // load medias
        myForm.loadFileFieldsContent('media');
      }.bind(this)
    });
  }
});