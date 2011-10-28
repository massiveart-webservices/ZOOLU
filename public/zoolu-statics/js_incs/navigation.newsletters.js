/**
 * navigation.newsletter.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-03-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation.Newsletters = Class.create(Massiveart.Navigation, {

  initialize: function($super) {
    // initialize superclass
    $super();

    this.constBasePath = '/zoolu/newsletters';
    this.constNewsletterDefaultTemplateId = 40;
    this.constStatisticTab = 'tabNavItem_43';
    this.constTabContainer = 'tabNavContainer';
  },
  
  /**
   * getAddFormList
   */
  getAddFormList: function(){    
    $(this.genListContainer).hide();
    $(this.genListFunctions).hide();
    
    $('buttondelete').hide();
    $('buttonsend').hide();
    $('buttontestsend').hide();
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/addform', {
      parameters: {
        rootLevelId: this.rootLevelId,
        templateId: this.constNewsletterDefaultTemplateId
      },      
      evalScripts: true,     
      onComplete: function() {        
        $(this.genFormContainer).show();
        $(this.genFormFunctions).show();
        $('buttonprint').hide();
        $(this.constTabContainer).hide();
        if($('widgetfunctions')) $(this.genFormContainer).scrollTo($('widgetfunctions'));        
      }.bind(this)
    });
  },
  
  /**
   * getEditForm
   */
  getEditForm: function(itemId, templateId, sent){
    
    $(this.genListContainer).hide();
    $(this.genListFunctions).hide();
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/editform', {
      parameters: {
        rootLevelId: this.rootLevelId,
        id: itemId,
        templateId: templateId,
        sent: sent
      },      
      evalScripts: true,     
      onComplete: function() {
        if(sent){
          $('buttonsend').hide();
          $('buttontestsend').hide();
          $('buttonprint').show();
        }else{
          $('buttonprint').hide();
          $(this.constStatisticTab).hide();
        }
        $(this.genFormContainer).show();
        $(this.genFormFunctions).show();
        if($('widgetfunctions')) $(this.genFormContainer).scrollTo($('widgetfunctions'));
        
        // load medias
        myForm.loadFileFieldsContent('media');
        // load documents
        myForm.loadFileFieldsContent('document');
        // load videos
        myForm.loadFileFieldsContent('video');
      }.bind(this)
    });
  }

});