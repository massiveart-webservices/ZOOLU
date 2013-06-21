/**
 * navigation.cms.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-03-09: Daniel.Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation.Landingpages = Class.create(Massiveart.Navigation.Cms, {

  initialize: function($super) {
    // initialize superclass
    $super();
    this.constBasePath = '/zoolu/core';
    this.rootLevelType = 'landingpage';
  },
  
  /**
   * getEditForm
   */
  getEditForm: function(itemId){
    
    $(this.genListContainer).hide();
    $(this.genListFunctions).hide();
    
    if($('buttondelete')) $('buttondelete').show();
    
    myCore.resetTinyMCE(true);
    
    var languageId = null;
    var rootLevelId = myNavigation.rootLevelId;
    if($('rootLevelLanguageId'+rootLevelId)){
      languageId = $F('rootLevelLanguageId'+rootLevelId)
    }
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/editform', {
      parameters: { 
        rootLevelId: this.rootLevelId, 
        id: itemId,
        languageId: languageId },
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