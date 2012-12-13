/**
 * global.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-11-12: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Global = Class.create({

  initialize: function() {
    this.isStartGlobal = false;
  },
  
    /**
   * selectParentFolder
   */
  selectParentFolder: function(parentFolderId){
    if($('id')){
      this.linkId = $F('linkId'); //assume that linkId == id if the element is no link
      this.elementId = $F('id');
      myCore.addBusyClass('overlayGenContent');
      
      new Ajax.Request('/zoolu/global/element/changeparentfolder', {
        parameters: { 
         elementId: this.linkId,
         parentFolderId: parentFolderId
        },      
        evalScripts: true,     
        onComplete: function() {  
          $('overlayGenContentWrapper').hide(); 
          $('overlayBlack75').hide();
          
          new Effect.Highlight('global'+this.elementId, {startcolor: '#ffd300', endcolor: '#ffffff'});
          $('global'+this.elementId).fade({duration: 0.5});
          $('global'+this.elementId).remove();
                  
          myCore.removeBusyClass('overlayGenContent');
        }.bind(this)
      });
    }
  },
  
  /**
   * selectParentRootFolder
   */
  selectParentRootFolder: function(rootFolderId){
    if($('id')){
      this.elementId = $F('linkId'); //assume that linkId == id if the element is no link
      myCore.addBusyClass('overlayGenContent');
      
      new Ajax.Request('/zoolu/global/element/changeparentrootfolder', {
        parameters: { 
         elementId: this.elementId,
         rootFolderId: rootFolderId
        },      
        evalScripts: true,     
        onComplete: function() {  
          $('overlayGenContentWrapper').hide(); 
          $('overlayBlack75').hide();
          
          new Effect.Highlight('global'+this.elementId, {startcolor: '#ffd300', endcolor: '#ffffff'});
          $('global'+this.elementId).fade({duration: 0.5});
          $('global'+this.elementId).remove();        
                  
          myCore.removeBusyClass('overlayGenContent');
        }.bind(this)
      });
    }
  },
  
  /**
   * changeType
   */
  changeType: function(typeId, backLink){
    //check if backLink is assigned
    backLink = (typeof(backLink) != 'undefined' || backLink == null) ? backLink : false;
	  
    params = $H({elementTypeId: typeId,
                 templateId: $F('templateId'),
                 formId: $F('formId'),
                 formVersion: $F('formVersion'),
                 formTypeId: $F('formTypeId'),
                 id: $F('id'),
                 linkId: ($('linkId')) ? $F('linkId') : -1,
                 languageId: $F('languageId'),
                 languageCode: (($('languageCode')) ? $F('languageCode') : ''),
                 currLevel: $F('currLevel'),
                 rootLevelId: $F('rootLevelId'),
                 rootLevelGroupId: $F('rootLevelGroupId'),
                 rootLevelGroupKey: ($('rootLevelGroupKey'+$F('rootLevelGroupId'))) ? $F('rootLevelGroupKey'+$F('rootLevelGroupId')) : '',
                 parentFolderId: $F('parentFolderId'),
                 parentTypeId: $F('parentTypeId'),
                 elementType: $F('elementType'),
                 isStartGlobal: this.isStartGlobal,
                 backLink: backLink
                 });
    
    $(myNavigation.genFormContainer).innerHTML = '';
    
    // loader
    myCore.addBusyClass(myNavigation.genFormContainer);
    myCore.addBusyClass('tdChangeType');    
    myForm.getFormSaveLoader();
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(myForm.updateContainer, '/zoolu/global/element/changeType', {
      parameters: params,
      evalScripts: true,
      onComplete: function() { 
        // load medias
        myForm.loadFileFieldsContent('media');
        // load documents
        myForm.loadFileFieldsContent('document');
        // load videos
        myForm.loadFileFieldsContent('video');
        
        $('divMetaInfos').innerHTML = '';
        myCore.removeBusyClass(myNavigation.genFormContainer);
        myCore.removeBusyClass('tdChangeType');
        myForm.cancleFormSaveLoader();
      }.bind(this)
    });
  },

  /**
   * resetSearch
   */
  resetSearch: function(){
    if($('elementSearchResult') && $('elementSearchValue')){
      $('elementSearchResult').innerHTML = '';
      $('elementSearchValue').value = '';
    }
  },

  /**
   * search
   */
  search: function(){
    if($('elementSearchResult') && $('elementSearchValue') && !$F('elementSearchValue').blank()){
      $('elementSearchResult').innerHTML = '';
      myCore.addBusyClass('elementSearchResult')
      new Ajax.Updater('elementSearchResult', '/zoolu/global/element/overlaysearch', {
        parameters: {searchValue: $F('elementSearchValue'), rootLevelId: myNavigation.rootLevelId },
        evalScripts: true,
        onComplete: function() {
          myCore.removeBusyClass('elementSearchResult');
        }.bind(this)
      });
    }
  },

  /**
   * addElementLink
   */
  addElementLink: function(elementId){
    if($('elementSearchCurrLevel')){
      currLevel = $F('elementSearchCurrLevel');

      myCore.resetTinyMCE(true);
      
      new Ajax.Request('/zoolu/global/element/addelementlink', {
        parameters: {
            templateId: elementTemplateDefaultId,
            rootLevelId: myNavigation.rootLevelId,
            rootLevelGroupId: myNavigation.rootLevelGroupId,
            parentFolderId: $('navlevel'+currLevel).readAttribute('parentid'),
            currLevel: currLevel,
            elementTypeId: elementTypeDefaultId,
            elementType: myNavigation.constGlobal,
            isStartGlobal: 0,
            linkId: elementId
        },
        evalScripts: true,
        onComplete: function() {
          myNavigation.updateNavigationLevel(currLevel, $('navlevel'+currLevel).readAttribute('parentid'));
        }.bind(this)
      });
    }
  },

  copyElement: function(folderId) {
      alert('copy element');
  }
});