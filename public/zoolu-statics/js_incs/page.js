/**
 * page.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-14: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Page = Class.create({

  initialize: function() {
    this.isStartPage = false;
    this.intPageId = 0;
  },
  
  /**
   * selectParentFolder
   */
  selectParentFolder: function(parentFolderId){
    if($('id')){
      this.pageId = $F('id');      
      myCore.addBusyClass('overlayGenContent');
      
      new Ajax.Request('/zoolu/cms/page/changeparentfolder', {
        parameters: { 
         pageId: this.pageId,
         parentFolderId: parentFolderId
        },      
        evalScripts: true,     
        onComplete: function() {  
          $('overlayGenContentWrapper').hide(); 
          $('overlayBlack75').hide();
          
          new Effect.Highlight('page'+this.pageId, {startcolor: '#ffd300', endcolor: '#ffffff'});
          $('page'+this.pageId).fade({duration: 0.5});
          $('page'+this.pageId).remove();        
                  
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
      this.pageId = $F('id');
      myCore.addBusyClass('overlayGenContent');
      
      new Ajax.Request('/zoolu/cms/page/changeparentrootfolder', {
        parameters: { 
         pageId: this.pageId,
         rootFolderId: rootFolderId
        },      
        evalScripts: true,     
        onComplete: function() {  
          $('overlayGenContentWrapper').hide(); 
          $('overlayBlack75').hide();
          
          new Effect.Highlight('page'+this.pageId, {startcolor: '#ffd300', endcolor: '#ffffff'});
          $('page'+this.pageId).fade({duration: 0.5});
          $('page'+this.pageId).remove();        
                  
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
	  
    params = $H({pageTypeId: typeId,
                 templateId: $F('templateId'),
                 formId: $F('formId'),
                 formVersion: $F('formVersion'),
                 formTypeId: $F('formTypeId'),
                 id: $F('id'),
                 languageId: $F('languageId'),
                 languageCode: (($('languageCode')) ? $F('languageCode') : ''),
                 currLevel: $F('currLevel'),
                 rootLevelId: $F('rootLevelId'),
                 parentFolderId: $F('parentFolderId'),
                 parentTypeId: $F('parentTypeId'),
                 elementType: $F('elementType'),
                 isStartPage: this.isStartPage,
                 backLink: backLink
                 });
    
    $(myNavigation.genFormContainer).innerHTML = '';
    
    // loader
    myCore.addBusyClass(myNavigation.genFormContainer);
    myCore.addBusyClass('tdChangeType');    
    myForm.getFormSaveLoader();
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(myForm.updateContainer, '/zoolu/cms/page/changeType', {
      parameters: params,
      evalScripts: true,
      onComplete: function() { 
        // load medias
        myForm.loadFileFieldsContent('media');
        // load documents
        myForm.loadFileFieldsContent('document');
        // load videos
        myForm.loadFileFieldsContent('video');
        // load filter documents
        myForm.loadFileFilterFieldsContent('documentFilter');
        // load contacts
        myForm.loadContactFieldsContent();
        
        $('divMetaInfos').innerHTML = '';
        myCore.removeBusyClass(myNavigation.genFormContainer);
        myCore.removeBusyClass('tdChangeType');
        myForm.cancleFormSaveLoader();
      }.bind(this)
    });
  },
  
  exportDynFormEntries: function(idPage, from, to, headline, startdate, enddate){
    var url = '/zoolu/cms/page/exportdynformentries?pageId='+idPage+'&from='+from+'&to='+to+'&headline='+headline+'&startdate='+startdate+'&enddate='+enddate;
    location.href = url;
  },

    /**
     * copyPage
     * @param folderId
     */
  copyPage: function(folderId) {
      if($F('isStartPage') == '1') {
          myCore.showDynamicMessage();
          new Ajax.Updater('overlayGenContent2', '/zoolu/cms/page/copy-text', {
              parameters: {
                  src: $F('id'),
                  dest: folderId
              },
              onComplete: function() {
                  $('btnYes').observe('click', function() {
                      new Ajax.Request('/zoolu/cms/page/copystartpage', {
                          parameters: {
                              src: $F('id'),
                              dest: folderId,
                              override: true
                          },
                          onComplete: function() {
                              myOverlay.close();
                          }
                      });
                      myCore.hideDynamicMessage();

                      $('btnYes').stopObserving();
                      $('btnNo').stopObserving();
                  });

                  $('btnNo').observe('click', function() {
                      new Ajax.Request('/zoolu/cms/page/copystartpage', {
                          parameters: {
                              src: $F('id'),
                              dest: folderId,
                              override: false
                          },
                          onComplete: function() {
                              myOverlay.close();
                          }
                      });
                      myCore.hideDynamicMessage();

                      $('btnYes').stopObserving();
                      $('btnNo').stopObserving();
                  });
              }
          });
      } else {
          new Ajax.Request('/zoolu/cms/page/copy', {
              parameters: {
                  id: $F('id'),
                  formId: $F('formId'),
                  templateId: $F('templateId'),
                  formVersion: $F('formVersion'),
                  languageId: $F('languageId'),
                  isStartPage: $F('isStartPage'),
                  parentFolderId: $F('parentFolderId'),
                  rootLevelId: $F('rootLevelId'),
                  newParentFolderId: folderId
              },
              onComplete: function() {
                  myOverlay.close('')
              }
          });
      }
  }
});