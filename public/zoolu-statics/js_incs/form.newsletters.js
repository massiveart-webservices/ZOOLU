/**
 * form.members.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-05: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.Form.Newsletters = Class.create(Massiveart.Form, {
  
  initialize: function($super) {
    // initialize superclass
    $super();
    
    this.constRequestSendMessage = '/zoolu/newsletters/newsletter/sendmessage';
    this.templateClickStatisticLine2 = new Template('<tr><td>#{0}</td><td>#{1}</td></tr>');
    this.templateClickStatisticHeader2 = new Template('<tr><th>#{0}</th><th>#{1}</th></tr>');
    this.templateClickStatisticLine3 = new Template('<tr><td>#{0}</td><td>#{1}</td><td>#{2}</td></tr>');
    this.templateClickStatisticHeader3 = new Template('<tr><th>#{0}</th><th>#{1}</th><th>#{2}</th></tr>');
    this.constStatisticClass = 'newsletterStatistics';
    
    this.constTypeClicks = 'clicks';
    this.constTypeUnsubscribes = 'unsubscribes';
    this.constTypeComplaints = 'complaints';
    this.constTypeBounces = 'bounces';
    this.constStatisticTab = 'tabNavItem_43';
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
      
      new Ajax.Updater(myNavigation.genFormContainer, $(this.formId).readAttribute('action'), {
        parameters: serializedForm,
        evalScripts: false,
        onComplete: function(transport) {
          //problem: ajax.updater evalScripts = true was too late
          transport.responseText.evalScripts();
          
          if(this.blnShowFormAlert){
            //saved
            this.getFormSaveSucces();
                       
            $('buttondelete').show();
            $('buttonsend').show();
            $('buttontestsend').show();
            
            // load medias
            this.loadFileFieldsContent('media');
            // load documents
            this.loadFileFieldsContent('document');
            
          }else{
            this.getFormSaveError();
            
            // load medias
            this.loadFileFieldsContent('media');
            // load documents
            this.loadFileFieldsContent('document');
          }
          $(this.constStatisticTab).hide();
        }.bind(this)
      });
    }
  },
  
  /**
   * deleteElement
   */
  deleteElement: function(){
    if($(this.formId)){
      
      var tmpKey = 'Delete_newsletter';
      if(myCore.translate[tmpKey]){
        var key = tmpKey;
      }else{
        var key = 'Delete_';
      }
      
      myCore.deleteAlertSingleMessage = myCore.translate[key];
      myCore.showDeleteAlertMessage(1);

      $('buttonOk').observe('click', function(event){
        var strAjaxActionBase = $(this.formId).readAttribute('action').replace('edit', 'delete');
        var elementId = $('id').getValue();
        
        // loader
        this.getFormSaveLoader();
        myCore.resetTinyMCE(true);
        
        if($('formType')){
          navItemId = $F('formType')+elementId;
        }

        new Ajax.Updater(myNavigation.genListContainer, strAjaxActionBase, {
          parameters: { 
            id: elementId,
            rootLevelId: myNavigation.rootLevelId
          },
          evalScripts: true,
          onComplete: function() {
            //deleted
            this.getFormDeleteSucces();
            
            $(myNavigation.genFormContainer).hide();
            $(myNavigation.genFormFunctions).hide();
            
            $(myNavigation.genListContainer).show();
            $(myNavigation.genListFunctions).show();   
            
            myCore.hideDeleteAlertMessage();
            
            myList.getListPage();

          }.bind(this)
        });
      }.bind(this));

      $('buttonCancel').observe('click', function(event){
        myCore.hideDeleteAlertMessage();
      }.bind(this));
    }
  },
  
  /**
   * showAlertMessage
   */
  showSendAlertMessage: function(test){
    if($('overlayGenContentWrapper')){
      $('overlayGenContent').innerHTML = '';       
      
      if(typeof(test) == 'undefined') test = false;

      if($('overlayBlack75')) $('overlayBlack75').show();
      
      new Ajax.Updater('overlayGenContent', this.constRequestSendMessage, {
        parameters: {
          id: $('id').getValue(),
          test: test
        },
        onComplete: function(){
          $('overlayButtons').show();
          myCore.putOverlayCenter('overlayGenContentWrapper');
          $('overlayGenContentWrapper').show(); 
          myOverlay.overlayCounter++;
        }.bind(this)
      }); 
    }   
  },
  
  /**
   * hideAlertMessage
   */
  hideSendAlertMessage: function(){
    if($('buttonOk')) $('buttonOk').stopObserving();
    if($('buttonCancel')) $('buttonCancel').stopObserving();
    if($('overlayGenContentWrapper')) $('overlayGenContentWrapper').hide();
    if($('overlayGenContent')) $('overlayGenContent').innerHTML = '';
    if($('overlayButtons')) $('overlayButtons').hide();
    if($('overlayBlack75')) $('overlayBlack75').hide();
  },
  
  /**
   * showNewsletterInIframe
   */
  showNewsletterInIframe: function(){
    var iframe = new Element('iframe', {id: this.updateNewsletterPreview, src: this.requestNewsletterPreview+'?id='+$('id').value});
    $(this.updateNewsletterContainer).insert(iframe);
    $('overlayNewsletterWrapper').setStyle({width: '80%'});
    $('newsletterPreview').setStyle({backgroundColor: '#fff', height: document.viewport.getDimensions().height * 0.7 + 'px', width: '100%'});
    myCore.putCenter('overlayNewsletterWrapper');
    if($('sent').value == 1){
      $('overlayNewsletterButtons').hide();
    }else{
      $('overlayNewsletterButtons').show();
    }
    $('overlayBlack75').show();
    $('overlayNewsletterWrapper').show();  
    myCore.putCenter('overlayNewsletterWrapper');
  },
  
  /**
   * closeNewsletterInIframe
   */
  closeNewsletterOverlay: function(){
    $('overlayNewsletterWrapper').hide();
    $('overlayBlack75').hide();
  },
  
  /**
   * showStatistic
   */
  showStatisticTable: function(type, divData, readHeader){
    $('overlayNewsletterContent').update();
    myCore.putCenter('overlayNewsletterWrapper');
    $('overlayNewsletterWrapper').show();
    $('overlayNewsletterContent').setStyle({'maxHeight': myCore.calcMaxOverlayHeight('overlayNewsletterContent', false)+'px'});
    
    var data = $(divData).innerHTML != '' ? $(divData).innerHTML.evalJSON() : null;
    var output = '<table class="'+this.constStatisticClass+'">';
    //Headline
    var dataHeader;
    var columns;
    switch(type){
      case this.constTypeClicks:
        dataHeader = {
          0: myCore.translate.Link,
          1: myCore.translate.Clicks,
          2: myCore.translate.Unique_clicks
        };
        columns = 3;
        break;
      case this.constTypeUnsubscribes:
        dataHeader = {
          0: myCore.translate.Email,
          1: myCore.translate.Reason,
          2: myCore.translate.Text
        };
        columns = 3;
        break;
      case this.constTypeComplaints:
        dataHeader = {
          0: myCore.translate.Date,
          1: myCore.translate.Email,
          2: myCore.translate.Type
        };
        columns = 3;
        break;
      case this.constTypeBounces:
        dataHeader = {
          0: myCore.translate.Date,
          1: myCore.translate.Email
        };
        columns = 2;
        break;
    }
    
    if(columns == 3){
      output += this.templateClickStatisticHeader3.evaluate(dataHeader);
    }else if(columns == 2){
      output += this.templateClickStatisticHeader2.evaluate(dataHeader);
    }
    
    //Entries
    for(var d in data){
      var i = 0;
      var templateData = [];
      if(readHeader){
        templateData[i++] = d;
      }
      for(var e in data[d]){
        templateData[i++] = data[d][e];
      }
      if(columns == 3){
        output += this.templateClickStatisticLine3.evaluate(templateData);
      }else if(columns == 2){
        output += this.templateClickStatisticLine2.evaluate(templateData);
      }
    }
    output += '</table>';
    $('overlayNewsletterContent').update(output);
    myCore.putCenter('overlayNewsletterWrapper');
  },
  
  /**
   * sendNewsletter
   */
  sendNewsletter: function(test){    
    var recipient;
    
    this.showSendAlertMessage(test);
    
    $('buttonOk').observe('click', function(event){
      if($('testemail')) {
        recipient = $('testemail').getValue()
      }else{
        recipient = null;
      }
      this.hideSendAlertMessage();
      $('divFormSaveLoader').show();
      new Ajax.Request(this.requestNewsletterSend, {
        parameters: {
          newsletterId: $('id').value,
          test: test,
          recipient: recipient
        },
        evalScripts: true,
        onComplete: function(){
          if(!test){
            $('buttonsend').hide();
            $('buttontestsend').hide();
            $('buttonsave').hide();
            $('sent').value = 1;
          }
          $('divFormSaveLoader').hide();
        }.bind(this)
      });
    }.bind(this));
    
    $('buttonCancel').observe('click', function(event){
      this.hideSendAlertMessage();
    }.bind(this));
  },
  
  print: function(type){
    var id = $F('id');
    window.open('/zoolu/newsletters/newsletter/printstats?id='+id+'&type='+type);
  }
});