/**
 * search.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-08: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

Massiveart.Filter = Class.create({
  initialize: function(){
    this.constSelectOperator = 'operator';
    this.constSelectValue = 'value';
    this.constEditForm = 'editForm';
    
    this.constRequestSubscriberFilter = '/zoolu/contacts/subscriber/listfilter';
  },

  /**
   * toggleSelects
   */
  toggleSelects: function(lineId, filterId){
    this.showOptionsByFilterId(this.constSelectOperator + '_' + lineId, filterId);
    this.showOptionsByFilterId(this.constSelectValue + '_' + lineId, filterId);
  },
  
  /**
   * showOptionsByFilterId
   */
  showOptionsByFilterId: function(id, filterId){
    $(id).childElements().each(function(element){
      if(element.readAttribute('filterId') == filterId || element.readAttribute('filterId') == null){
        element.show();
      }else{
        element.hide();
        element.selected = '';
      }
    });
  },
  
  /**
   * loadLines
   */
  loadLines: function(){
    var arrLines = [];
    $('lineInstances').value.scan(/\[\d*\]/, function(lines){arrLines.push(lines[0].gsub(/\[/, '').gsub(/\]/, ''))});
    return arrLines;
  },
  
  /**
   * addLine
   */
  addLine: function(){
    var arrLines = this.loadLines();
    lineId = Number(arrLines[arrLines.length - 1]) + 1;
    
    var emptyLine = $('line_REPLACE_n');
    var newLine = new Element(emptyLine.tagName, {'class': 'field', 'id': 'line_'+lineId});
    newLine.update(emptyLine.innerHTML.gsub(/REPLACE_n/, lineId));
    new Insertion.Before(emptyLine, newLine);
    arrLines.push(lineId);
    
    arrLines.each(function(i){
      $('minus_'+i).show();
    });
    
    $('lineInstances').value = $('lineInstances').value + '['+lineId+']';
    if(arrLines.length >= 5){
      $$('div.plus').each(function(element){
        element.hide();
      });
    }
  },
  
  /**
   * removeLine
   */
  removeLine: function(lineId){
    if($('line_'+lineId)){
      $('lineInstances').value = $('lineInstances').value.replace('['+lineId+']', '');
      
      var arrLines = this.loadLines();
      
      if(arrLines.length <= 1){
        $('minus_'+arrLines[arrLines.length -1]).hide();
      }
      
      $('line_'+lineId).remove();
      
      if(arrLines.length < 5){
        $$('div.plus').each(function(element){
          element.show();
        });
      }
    }
  },
  
  /**
   * updateNavigation
   */
  updateNavigation: function(){
    new Ajax.Updater('naviitem'+myNavigation.rootLevelId+'menu', '/zoolu/contacts/subscriber/listfilter', {
      parameters: {
        rootLevelId: myNavigation.rootLevelId
      },
      evalScripts: true,
      onComplete: function(){
        
      }.bind(this)
    });
  },
  
  /**
   * checkFilter
   */
  checkFilter: function(){
    var arrLines = this.loadLines();
    var blnCheck = true;
    arrLines.each(function(line){
      if($('filter_'+line).value.trim() != '' && ($('operator_'+line).value.trim() == '' || $('value_'+line).value.trim() == '')){
        blnCheck = false;
        $('error_'+line).update(myCore.translate['Error_filter']);
      }
    });
    return blnCheck;
  },
  
  /**
   * saveFilter
   */
  saveFilter: function(){
    if($(this.constEditForm)){
      if(this.checkFilter()){
        var serializedForm = $(this.constEditForm).serialize();
        new Ajax.Request('/zoolu/contacts/overlay/savefilter', {
          parameters: serializedForm,
          onComplete: function(){
            this.updateNavigation();
            myOverlay.closeFilterOverlay();
          }.bind(this)
        });
      }
    }
  },
  
  /**
   * deleteFilter
   */
  deleteFilter: function(){
    myCore.deleteAlertSingleMessage = myCore.translate['Delete_filter'];
    myCore.showDeleteAlertMessage(1);
    
    $('buttonOk').observe('click', function(event){
      myCore.hideDeleteAlertMessage();
      new Ajax.Request('/zoolu/contacts/overlay/deletefilter', {
        parameters: {
          rootLevelFilterId: $('rootLevelFilterEditId').getValue()
        },
        evalScripts: true,
        onComplete: function(){
          //FIXME: Use updateNavigation
          //BEGIN
          this.updateNavigation();
          //END
          myOverlay.closeFilterOverlay();
        }.bind(this)
      });
    }.bind(this));
    
    $('buttonCancel').observe('click', function(event){
      myCore.hideDeleteAlertMessage();
    }.bind(this));
  }
});