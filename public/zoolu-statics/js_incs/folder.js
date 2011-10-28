/**
 * folder.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-14: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Folder = Class.create({

  initialize: function() {
    this.folderId = 0;
  },
  
  /**
   * getCurrentFolderParentChooser
   */
  getCurrentFolderParentChooser: function(strActionKey){
    $(myForm.updateOverlayContainer).innerHTML = '';
    
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();    
  
    this.folderId = myNavigation.folderId;
    
    if(typeof(strActionKey) == 'undefined'){
      strActionKey = '';
    }
    
    new Ajax.Updater(myForm.updateOverlayContainer, '/zoolu/core/folder/foldertree', { 
      parameters: { 
        portalId: myNavigation.rootLevelId, 
        rootLevelId: myNavigation.rootLevelId,
        rootLevelLanguageId: ($('rootLevelLanguageId'+myNavigation.rootLevelId)) ? $F('rootLevelLanguageId'+myNavigation.rootLevelId) : '',
        folderId: this.folderId,
        key: strActionKey },
      evalScripts: true,
      onComplete: function(){
        myCore.putOverlayCenter('overlayGenContentWrapper');
        myCore.removeBusyClass('overlayGenContent');
      } 
    });
  },
  
  /**
   * selectParentFolder
   */
  selectParentFolder: function(parentFolderId){
    myCore.addBusyClass('overlayGenContent');
  
    new Ajax.Request('/zoolu/core/folder/changeparentfolder', {
      parameters: { 
       folderId: this.folderId,
       parentFolderId: parentFolderId
      },      
      evalScripts: true,     
      onComplete: function() {  
        $('overlayGenContentWrapper').hide(); 
        $('overlayBlack75').hide();                
        
        new Effect.Highlight('folder'+this.folderId, {startcolor: '#ffd300', endcolor: '#ffffff'});
        $('folder'+this.folderId).fade({duration: 0.5});
        $('folder'+this.folderId).remove();
        
        myNavigation.navigation[myNavigation.currLevel - 1] = null;
        
        if($('folder'+parentFolderId)){
          myNavigation.itemId = 'folder'+parentFolderId;            
          myNavigation.selectItem();
        }else{
          if($('navlevel'+myNavigation.currLevel)){
            $('navlevel'+myNavigation.currLevel).innerHTML = '';
          }
          myNavigation.hideCurrentFolder();
        }
        
        myCore.removeBusyClass('overlayGenContent');
      }.bind(this)
    });
  },
  
  /**
   * selectParentRootFolder
   */
  selectParentRootFolder: function(rootFolderId){
    myCore.addBusyClass('overlayGenContent');
      
    new Ajax.Request('/zoolu/core/folder/changeparentrootfolder', {
      parameters: { 
       folderId: this.folderId,
       rootFolderId: rootFolderId
      },      
      evalScripts: true,     
      onComplete: function() {  
        $('overlayGenContentWrapper').hide(); 
        $('overlayBlack75').hide();                
        
        new Effect.Highlight('folder'+this.folderId, {startcolor: '#ffd300', endcolor: '#ffffff'});
        $('folder'+this.folderId).fade({duration: 0.5});
        $('folder'+this.folderId).remove();
        
        myNavigation.navigation[myNavigation.currLevel - 1] = null;
        
        myNavigation.selectPortal(rootFolderId);
        
        myCore.removeBusyClass('overlayGenContent');
      }.bind(this)
    });
  },
  
  /**
   * getFolderContentList
   */
  getFolderContentList: function(){
    if($(myForm.updateTableListContainer)){
      $(myForm.updateTableListContainer).innerHTML = '';
      $(myForm.updateTableListContainer).show();
      myCore.addBusyClass(myForm.updateTableListContainer);
        
      this.folderId = myNavigation.folderId;
      
      //myList.resetSearch();   Is this neccessary?
      myList.sortColumn = '';
      myList.sortOrder = '';
      
      new Ajax.Updater(myForm.updateTableListContainer, myNavigation.constBasePath + '/' + myNavigation.rootLevelType + '/list', { 
        parameters: { 
          portalId: myNavigation.rootLevelId, 
          rootLevelId: myNavigation.rootLevelId,
          rootLevelGroupKey: ($('rootLevelGroupKey'+myNavigation.rootLevelGroupId)) ? $F('rootLevelGroupKey'+myNavigation.rootLevelGroupId) : '',
          folderId: this.folderId,
          currLevel: myNavigation.currLevel
        },
        evalScripts: true,
        onComplete: function(){
          myCore.removeBusyClass(myForm.updateTableListContainer);
        } 
      });
    }
  },
    
  /**
   * getCurrentFolderSecurity
   */
  getCurrentFolderSecurity: function(){
    $(myForm.updateOverlayContainer).innerHTML = '';
    
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();    
    
    new Ajax.Updater(myForm.updateOverlayContainer, '/zoolu/core/folder/security', { 
      parameters: { folderId: this.folderId },
      evalScripts: true,
      onComplete: function(){
        myCore.putOverlayCenter('overlayGenContentWrapper');
      } 
    });
  },
  
  /**
   * updateFolderSecurity
   */
  updateFolderSecurity: function(){
        
    if($('folderSecurityForm')){
      
      myCore.addBusyClass(myForm.updateOverlayContainer);
      
      /**
       * serialize generic form
       */
      var serializedForm = $('folderSecurityForm').serialize();
    
      new Ajax.Updater(myForm.updateOverlayContainer, '/zoolu/core/folder/securityupdate', {
        parameters: serializedForm,
        evalScripts: true,
        onComplete: function() {         
          myCore.removeBusyClass(myForm.updateOverlayContainer);
        }.bind(this)
      });
    }
  }  
});