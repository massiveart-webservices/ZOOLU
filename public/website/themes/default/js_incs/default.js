/**
 * default.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-26: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Default = Class.create({

  initialize: function() {
    this.retValue = false;
  },
  
  /**
   * submitForm
   */
  submitForm: function(formId){
    if($(formId)){      
      /**
       * validation
       */
      this.retValue = true;      
      
      $$('.mandatory').each(function(element){
        this.validateInput(element.id);
      }.bind(this));
      
      $$('.val_email').each(function(element){
        this.validateInputEmail(element.id)
      }.bind(this));
      
      $$('.val_type_alphabethic').each(function(element){
        this.validateAlphabethic(element.id);
      }.bind(this));
      
      $$('.val_type_numeric').each(function(element){
        this.validateNumeric(element.id);
      }.bind(this));
      
      /**
       * check captcha
       */
      if(this.retValue && $('recaptcha_response_field') && $('recaptcha_challenge_field')){
        if($F('recaptcha_response_field') != ''){          
          new Ajax.Request('/zoolu-website/datareceiver/check-recaptcha', {
            parameters: {
              recaptcha_response_field: $F('recaptcha_response_field'),
              recaptcha_challenge_field: $F('recaptcha_challenge_field')
            },
            evalScripts: true,
            onComplete: function(transport) {              
              if(transport.responseText.isJSON()){
                var responseData = transport.responseText.evalJSON();                
                if(responseData.status == 'ok'){
                  if($('lbl_recaptcha_response_field')) $('lbl_recaptcha_response_field').removeClassName('missing');
                  if(this.retValue == true) {
                    $(formId).submit();
                  }
                }else{
                  Recaptcha.reload();
                  if($('lbl_recaptcha_response_field')) $('lbl_recaptcha_response_field').addClassName('missing');
                  this.retValue = false;
                }
              }
            }.bind(this)
          });
        }else{
          if($('lbl_recaptcha_response_field')) $('lbl_recaptcha_response_field').addClassName('missing');
          this.retValue = false;
        }
      }else{
        if(this.retValue == true) {
          $(formId).submit();
        }
      }
    }
  },
  
  /**
   * validateInput
   */
  validateInput: function(element, baseValue) {
    //Radio buttons and checkboxes
    if($(element).type == 'radio' || $(element).type == 'checkbox'){
      var elementname = $(element).name;
      var form = $('contactForm'); //TODO Load correct form
      //checks if there is any item checked
      if(typeof(form.getInputs($(element).type,elementname).find(function(radio){return radio.checked})) == 'undefined'){
        //checkboxes have [] at the end of the name, but labels not
        if($(element).type == 'checkbox'){
          elementname = elementname.substr(0,elementname.length - 2);
        }
        if($('lbl_'+elementname)) $('lbl_'+elementname).addClassName('missing');
        this.retValue = false;
      }else{
        if($(element).type == 'checkbox'){
          elementname = elementname.substr(0,elementname.length - 2);
        }
        if($('lbl_'+elementname)) $('lbl_'+elementname).removeClassName('missing');
      }
    }
    //Everything else
    else if(($(element) && $F(element).blank()) || $F(element) == baseValue){
      if($('lbl_'+element)) $('lbl_'+element).addClassName('missing');
      this.retValue = false;
    }else{
      if($('lbl_'+element)) $('lbl_'+element).removeClassName('missing');
    }
  },
  
  /**
   * validateInputEmail
   */
  validateInputEmail: function(element){
    this.validateFilter(/^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/, element);
  },
  
  /**
   * validateAlphabethic
   */
  validateAlphabethic: function(element){
    this.validateFilter(/^[a-zA-Z äöüßÄÖÜ]+$/, element);
  },
  
  validateNumeric: function(element){
    this.validateFilter(/^\d+((.|,)\d+)?$/, element);
  },
  
  /**
   * validateFilter
   */
  validateFilter: function(filter, element){
    if($(element) && $F(element) != ''){
      if(!filter.test($F(element))){
        if($('lbl_'+element)) $('lbl_'+element).addClassName('missing');
        this.retValue = false;
      }else{
        if($('lbl_'+element)) $('lbl_'+element).removeClassName('missing');
      }
    }
  },
  

  
  /**
   * changeTestMode
   */
  changeTestMode: function(status){
    new Ajax.Request('/zoolu-website/testmode/change', {
      parameters: { TestMode: status },
      evalScripts: true,
      onComplete: function(transport) {         
        window.location.href = window.location.href;
      }.bind(this)
    });
  },
  
  /**
   * addBusyClass
   */
  addBusyClass: function(busyElement, blnDisplay) {
    if($(busyElement)){
      $(busyElement).addClassName('busy');
      if(blnDisplay) $(busyElement).show();
    }
  },

  /**
   * removeBusyClass
   */
  removeBusyClass: function(busyElement, blnDisplay) {
    if($(busyElement)){
      $(busyElement).removeClassName('busy');
      if(blnDisplay) $(busyElement).hide();
    }
  },

  /**
   * expireCache
   */
  expireCache: function(elId){
    this.addBusyClass(elId, false);
    new Ajax.Request('/zoolu-website/content/expire-cache', {
      method: 'get',
      evalScripts: true,
      onComplete: function(transport) {
        this.removeBusyClass(elId, false);
        window.location.href = window.location.href;
      }.bind(this)
    });
  } 
});