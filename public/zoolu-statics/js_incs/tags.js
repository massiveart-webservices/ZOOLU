/**
 * tags.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-03-10: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */


var Tags = Class.create({

  initialize: function() {
	
	this.tagsEditField = 'TagsEditField';
    this.cssClassSelectedTag = 'selectedtag';
    this.FormId = '';
    this.blnIsValidateForm = true;    
    this.arrRenameFieldsToValidate = ['OldRenameTagId', 'NewRenameTag'];
    this.arrDeleteFieldsToValidate = ['TagId'];    
    this.tagCloudOnclick = '';
    this.tagCloudHref = '';
    this.mainSectionId = null;
    this.mainSectionTypeId = null;
    this.subSection = null;
    this.subSectionChild = null;    
    this.tagIds = [];
    this.tagNotSelected = null;
    this.viewSection = null;
    this.viewFilterOnlyMy = null;
  },
  
  /**
   * add a tag to the tagcloud
   */
  addTag: function(strElementId){
    if($(strElementId) && $F(strElementId+'_Inp') !== ''){
   	
      var strTagTitle = $F(strElementId+'_Inp');
   
      if(!Selector.findChildElements($(strElementId+'SelectedTags'),['div[id='+strElementId+'_'+strTagTitle+']'])[0]){
    	$(strElementId+'_WrapperClear').remove();
    	$(strElementId+'SelectedTags').innerHTML += '<div id="'+strElementId+'_'+strTagTitle+'" class="tagpill"><div class="tagdelete" onclick="myTags.removeTag(\''+strElementId+'\',\''+strTagTitle+'\')">[x]</div><div class="tagtitle">'+strTagTitle+'</div><div class="clear"></div></div>';
    	$(strElementId+'SelectedTags').innerHTML += '<div id="'+strElementId+'_WrapperClear" class="clear"></div>';
    	$(strElementId).value +='['+strTagTitle+']';
        $(strElementId+'_Inp').value = '';
      }else{
        new Effect.Highlight(strElementId+'_'+strTagTitle, {startcolor: '#ffd300', endcolor: '#E4E4E4'});
      }
    }
  },
  
  /**
   * remove a tag from the tagcloud
   */
  removeTag: function(strElementId,strTagTitle){
    if(strElementId !== '' && strTagTitle !== ''){
    	
      $(strElementId+'_'+strTagTitle).remove();

      var strTagCloud = $F(strElementId);
      var strNewTagCloud = strTagCloud.replace('['+strTagTitle+']','');
  
      $(strElementId).value = strNewTagCloud;
    }
  },
    
  /*addOrRemoveTag: function(tag, anchor, editField){
    if(editField != ''){
      this.tagsEditField  = editField;  
    }
    
    if($(this.tagsEditField)){
      var myRegExp = /(;|,)/;
      var blnHasTag = false;
      $F(this.tagsEditField).split(myRegExp).each(function(actualTag){
        if(actualTag != ',' && actualTag != ';'){
          if(actualTag.strip() == tag.strip()){
            blnHasTag = true;
          }
        }
      });
      
      if(!$(anchor).hasClassName(this.cssClassSelectedTag)){
       
         // try to add
        
        if(blnHasTag == false){
          if($F(this.tagsEditField).blank()){
            $(this.tagsEditField).value = tag;          
          }else{
            $(this.tagsEditField).value = $(this.tagsEditField).value + ', ' + tag;
          }
        }
        $(anchor).addClassName(this.cssClassSelectedTag);        
      }else{
        
         // try to remove
        
        if(blnHasTag == true){
          var actualTags = $(this.tagsEditField).value ;
          actualTags = ' ' + actualTags + ',';
          actualTags = actualTags.gsub(' ' + tag.strip() + ',', '');
          actualTags = actualTags.replace(/^,+|,+$/g,'');          
          actualTags = actualTags.strip();
          $(this.tagsEditField).value = actualTags;
        }
        
        $(anchor).removeClassName(this.cssClassSelectedTag);
      }
    }
  },
  
  resetSelectedTags: function(){
    $$('.'+this.cssClassSelectedTag).each(function(anchor){
      $(anchor).removeClassName(this.cssClassSelectedTag);
    }.bind(this));
  },*/
  
  /**
   * updateTagCloud
   */
  updateTagCloud: function(){ 
    myCore.addBusyClass('divTagCloud');
    
    new Ajax.Updater('divTagCloud', '/ajax_request', {
      parameters: { ajaxRequestClass: 'TagsControl',
                    ajaxRequestFunc: 'getTagCloud(\''+this.tagCloudOnclick+'\', \''+this.tagCloudHref+'\')',
                    MainSectionId: this.mainSectionId,
                    MainSectionTypeId:  this.mainSectionTypeId,
                    SubSection: this.subSection,
                    SubSectionChild: this.subSectionChild,
                    TagIds: this.tagIds.toJSON(),
                    TagNotSelected: this.tagNotSelected,
                    ViewSection: this.viewSection,
                    ViewFilterOnlyMy: this.viewFilterOnlyMy},
      evalScripts: true,
      onComplete: function() {
        myCore.removeBusyClass('divTagCloud');
      }      
    });
  },
  
  showRenameWidget: function(){
    if($('divTagsRenameWidget')) $('divTagsRenameWidget').show();
    this.cancleDeleteWidget();
  },
  
  renameTag: function(){
    myCore.addBusyClass('buttonRenameTag');
    
    /**
     * validate Form Fields
     */
    this.validateForm(this.arrRenameFieldsToValidate);
    if(!this.blnIsValidateForm){
      myCore.removeBusyClass('buttonRenameTag');
      return false;
    }
      
    new Ajax.Updater('divTagCloud', '/ajax_request', {
      parameters: { ajaxRequestClass: 'TagsControl',
                    ajaxRequestFunc: 'renameTag',                    
                    RenameTagId: $F('OldRenameTagId'),
                    RenameTag: $F('NewRenameTag'),
                    TagCloudOnclick: this.tagCloudOnclick,
                    TagCloudHref: this.tagCloudHref,
                    MainSectionId: this.mainSectionId,
                    MainSectionTypeId:  this.mainSectionTypeId,
                    SubSection: this.subSection,
                    SubSectionChild: this.subSectionChild,
                    TagIds: this.tagIds.toJSON(),
                    TagNotSelected: this.tagNotSelected,
                    ViewSection: this.viewSection,
                    ViewFilterOnlyMy: this.viewFilterOnlyMy },
      evalScripts: true,
      onComplete: function() {
        myCore.removeBusyClass('buttonRenameTag');
      }.bind(this)
    });
  },
  
  cancleRenameWidget: function(){
    if($('divTagsRenameWidget')){
      $('divTagsRenameWidget').hide();
      $('NewRenameTag').value = '';
      $('OldRenameTagId').options[0].selected = true;
    }
  },
  
  showDeleteWidget: function(){
    if($('divTagDeleteWidget')) $('divTagDeleteWidget').show();
    this.cancleRenameWidget();
  },
  
  deleteTag: function(){
    myCore.addBusyClass('buttonDeleteTag');
    
    /**
     * validate Form Fields
     */
    this.validateForm(this.arrDeleteFieldsToValidate);
    if(!this.blnIsValidateForm){
      myCore.removeBusyClass('buttonDeleteTag');
      return false;
    }
      
    new Ajax.Updater('divTagCloud', '/ajax_request', {
      parameters: { ajaxRequestClass: 'TagsControl',
                    ajaxRequestFunc: 'deleteTag',                    
                    TagId: $F('TagId'),
                    TagCloudOnclick: this.tagCloudOnclick,
                    TagCloudHref: this.tagCloudHref,
                    MainSectionId: this.mainSectionId,
                    MainSectionTypeId:  this.mainSectionTypeId,
                    SubSection: this.subSection,
                    SubSectionChild: this.subSectionChild,
                    TagIds: this.tagIds.toJSON(),
                    TagNotSelected: this.tagNotSelected,
                    ViewSection: this.viewSection,
                    ViewFilterOnlyMy: this.viewFilterOnlyMy },
      evalScripts: true,
      onComplete: function() {
        myCore.removeBusyClass('buttonDeleteTag');
      }.bind(this)
    });
  },
  
  cancleDeleteWidget: function(){
    if($('divTagDeleteWidget')){
      $('divTagDeleteWidget').hide();      
      $('TagId').options[0].selected = true;
    }
  },
  
  /**
   * validateForm
   */
  validateForm: function(arrFieldsToValidate) {
    this.blnIsValidateForm = true;
    arrFieldsToValidate.each(function(Field) {
      if($(this.FormId+Field)){
        if($F(this.FormId+Field).blank()){
          if($(this.FormId+Field+'Headline')) $(this.FormId+Field+'Headline').addClassName('missing');
          $(this.FormId+Field).addClassName('missinginput');
          $(this.FormId+Field).activate();
          this.blnIsValidateForm = false;
          this.blnIsValidateForm = false;
          throw $break;
        }else{
          if($(this.FormId+Field+'Headline')) $(this.FormId+Field+'Headline').removeClassName('missing');
          $(this.FormId+Field).removeClassName('missinginput');
        }
      }
    }.bind(this));
  }
});

/**
 * initialize Tags Object
 */
var myTags = new Tags();

