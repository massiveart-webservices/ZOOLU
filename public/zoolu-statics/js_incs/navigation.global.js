/**
 * navigation.global.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-03-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation.Global = Class.create(Massiveart.Navigation, {

  initialize: function($super) {
    // initialize superclass
    $super();

    this.constBasePath = '/zoolu/global';

    this.rootLevelType = 'folder';
    this.genListContainer = 'genTableListContainer';
  },
  
  /**
   * initModuleGlobal
   */
  initModuleGlobal: function(rootLevelId, rootLevelGroupId){
    if(typeof(rootLevelId) == 'undefined' && rootLevelId == '') rootLevelId = 0;
    if(typeof(rootLevelGroupId) == 'undefined' && rootLevelGroupId == '') rootLevelGroupId = 0;
    
    if(rootLevelGroupId > 0){
      if($('naviitem'+rootLevelGroupId)){
        $('naviitem'+rootLevelGroupId).onclick();
        if($('subnaviitem'+rootLevelId)){        
          $('subnaviitem'+rootLevelId).down('div.menutitle', 0).down(0).onclick();
        }
      }else{
        if($('naviitem'+rootLevelId)) $('naviitem'+rootLevelId).onclick();
      }
    }else{      
      if($('naviitem'+rootLevelId)) $('naviitem'+rootLevelId).onclick();
    }
  },
  
  /**
   * addElement
   */
  addElement: function(currLevel){
    $('buttondelete').hide();
    this.showFormContainer();
        
    $(this.genFormContainer).innerHTML = '';
    $('divWidgetMetaInfos').innerHTML = '';
    
    if($(this.genTableListContainer)) $(this.genTableListContainer).hide();
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();
        
    myCore.addBusyClass(this.genFormContainer);
    myCore.addBusyClass('divWidgetMetaInfos');
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater('genFormContainer', '/zoolu/global/element/getaddform', {
      parameters: {
        templateId: elementTemplateDefaultId,
        rootLevelId: this.rootLevelId,
        rootLevelGroupId: this.rootLevelGroupId,
        rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
        parentFolderId: $('navlevel'+currLevel).readAttribute('parentid'),
        currLevel: currLevel,
        elementTypeId: elementTypeDefaultId,
        elementType: this.constGlobal,
        isStartElement: 0
      },      
      evalScripts: true,     
      onComplete: function() {
        myForm.writeMetaInfos();
        
        $('levelmenu'+currLevel).hide();
        $('addmenu'+currLevel).fade({duration: 0.5});
        myCore.removeBusyClass('divWidgetMetaInfos');
        myCore.removeBusyClass(this.genFormContainer);              
      }.bind(this)
    });    
  },
  
  /**
   * getElementLinkChooser
   */
  getElementLinkChooser: function(currLevel){
    $(myForm.updateOverlayContainer).innerHTML = '';
    
    myCore.putCenter(myForm.updateOverlayContainer+'Wrapper');
    $(myForm.updateOverlayContainer+'Wrapper').show();
  
    this.folderId = myNavigation.folderId;
  
    new Ajax.Updater(myForm.updateOverlayContainer, '/zoolu/global/element/getoverlaysearch', {
      parameters: { currLevel: currLevel },
      evalScripts: true,
      onComplete: function(){
        myCore.putOverlayCenter(myForm.updateOverlayContainer+'Wrapper');
      } 
    });
  },
  
  /**
   * loadDashboard
   */
  loadDashboard: function(){
    $(this.genFormContainer).show();
    $(this.genTableListContainer).hide();
    myCore.addBusyClass(this.genFormContainer);
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, '/zoolu/global/element/dashboard', {
      parameters: { 
        rootLevelId: this.rootLevelId
      },
      evalScripts: true,     
      onComplete: function() {
        myCore.removeBusyClass(this.genFormContainer);
        myCore.initListHover(false);
      }.bind(this)
    });
  },
  
  /**
   * getEdit
   */
  getEdit: function(itemId, parentId){
    if(parentId == 'undefined') parentId = null;
    if(itemId != 'undefined'){
      
      this.intTreeItemId = itemId;
      this.strTreeItemType = 'element';
      
      new Ajax.Request('/zoolu/global/navigation/parent-folders', {
        parameters: { 
          id: itemId, 
          parentId: parentId 
        },      
        evalScripts: true,     
        onComplete: function(transport) {
          var response = transport.responseText.evalJSON();          
          if(typeof(response.folders) != 'undefined' && response.folders.length > 0){
            // load all folders
            this.arrTreeToLoad = response.folders; 
            if($('divNavigationTitle_folder'+this.arrTreeToLoad.first())) $('divNavigationTitle_folder'+this.arrTreeToLoad.first()).onclick();
          }else{
            this.arrTreeToLoad = [];
            if($('divNavigationTitle_folder'+parentId)) $('divNavigationTitle_folder'+parentId).onclick();
          }
        }.bind(this)
      });
    }
  },
  
  /**
   * getListEditForm
   */
  getListEditForm: function(itemId, languageCodes){
    
    if($(this.genTableListContainer)) $(this.genTableListContainer).hide();
    if($(this.genListContainer)) $(this.genListContainer).hide();
    if($(this.genListFunctions)) $(this.genListFunctions).hide();
    
    if(typeof(languageCodes) == 'undefined'){       
      languageCodes = null;
    }
    
    if($('buttondelete')) $('buttondelete').hide(); 
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/geteditform', {
      parameters: { rootLevelId: this.rootLevelId, id: itemId, languageCodes: languageCodes, sourceView: 'list' },      
      evalScripts: true,     
      onComplete: function() {        
        $(this.genFormContainer).show();
        $(this.genFormFunctions).show();
        $(this.genFormContainer).scrollTo($('widgetfunctions'));
      }.bind(this)
    });
  }

});