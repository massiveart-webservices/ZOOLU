var Web = Web || {};

(function(window, document, Web, undefined) {
    Web.selectionCount = 0;
    Web.formInputs = [];

    $.extend(Web, {
        
        /**
         * updateLiveSearch
         */
        updateLiveSearch: function(selectedElement){
            if($('#searchField') && selectedElement.childNodes[0] && selectedElement.childElements().length > 0){
                var link = selectedElement.childNodes[0].readAttribute('href');
                if(link != ''){
                    return self.location.href = link;  
                }
            }
        },

        /**
         * search
         */
        search: function(){
            if($('#searchField')){
                if(($('#searchField').value != '')){
                    $('#searchForm').submit();
                }
            } 
        },
          
        /**
         * eventSearch
         */
        eventSearch: function(formId){
            if($('#'+formId)){
                $('#'+formId).submit(function(){
                    if(($('#f') && $('#f').val() == '') || ($('#t') && $('#t').val() == '')){
                        $('#submit-button').attr('disabled',false);
                        return false;
                    }else{
                        $('#submit-button').attr('disabled',true);
                        return true;
                    }
                });
            }
        },

        /**
         * serializeAndSubmitForm
         */
        serializeAndSubmitForm: function (formId, displaydiv) {
            //validation
            this.retValue = true;  
  
            this.validateForm();

            if (this.retValue == true) {
                var form = $('#'+formId);
                var serializedForm = form.serialize();
                var action = form.attr('action');
                var method = form.attr('method');
                //console.log('ACTION und METHOD:' + action + method);
                $.ajax({
                    url: action,
                    type: method,
                    data: serializedForm,
                    success: function(data) {
                        form.html(data);
                        if (typeof displaydiv == "string") {
                            $('#'+displaydiv).fadeIn(0);    
                            console.log(displaydiv)
                        }
                    }
                });
            }
        },
          
        /**
         * submitForm
         */
        submitForm: function(formId){
            if($('#'+formId)){
                //validation
                this.retValue = true;      

                var parent = this;

                this.validateForm(formId);
                /**
                 * check captcha
                 */ 
                if(this.retValue && $('#recaptcha_response_field').length > 0 && $('#recaptcha_challenge_field').length > 0){
                    if($('#recaptcha_response_field').val != ''){
                        $.ajax({
                            url: '/zoolu-website/datareceiver/check-recaptcha',
                            type: 'post',
                            data: {
                                recaptcha_response_field: $('#recaptcha_response_field').val(),
                                recaptcha_challenge_field: $('#recaptcha_challenge_field').val()
                            },
                            dataType: 'json',
                            accepts: 'application/json',
                            type: 'POST',
                            success: function(transport){
                                if(transport.status == 'ok'){

                                    $('#recaptcha_response_field').removeClass('missinginput');
                                    this.validForm = true;
                                    $('#'+formId)[0].submit();
                                }else{
                                    Recaptcha.reload();
                                    this.validForm = false;
                                    $('#recaptcha_response_field').addClass('missinginput');
                                    window.scrollTo($('.missinginput'),0);
                                }
                            }
                        });
                    }else{
                        if($('#lbl_recaptcha_response_field')) $('#lbl_recaptcha_response_field').addClass('missing');
                        this.retValue = false;
                    }
                }else{
                    if(this.retValue == true) {
                        $('#'+formId)[0].submit();
                    }
                }
            }
        },

        /**
         * validateForm
         */
        validateForm: function(formId){
            if(typeof(formId) == 'undefined'){
                formId = '';
            }else{
                formId = '#' + formId + ' ';
            }

            $(formId + '.mandatory').each(function(element){
                Web.validateInput($(this).attr('id'));
            });

            $(formId + '.val_email').each(function(element){
                Web.validateInputEmail($(this).attr('id'));
            });

            $(formId + '.val_type_alphabethic').each(function(element){
                Web.validateAlphabethic($(this).attr('id'));
            });

            $(formId + '.val_type_numeric').each(function(element){
                Web.validateNumeric($(this).attr('id'));
            });
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
                    if($('#lbl_'+elementname)) {
                        $('#lbl_'+elementname).addClass('missing');
                        $('#'+elementname).addClass('error');
                        window.scrollTo($('.error'),0);
                        $('#empty-field').show();
                    }
                    this.retValue = false;
                }else{
                    if($(element).type == 'checkbox'){
                        elementname = elementname.substr(0,elementname.length - 2);
                    }
                    if($('#lbl_'+elementname)) {
                        $('#lbl_'+elementname).removeClass('missing');
                        $('#'+elementname).removeClass('error');
                    }
                }
            }
            //Everything else
            else if(($('#'+element) && $('#'+element).val() == '') || $('#'+element).val() == baseValue){
                if($('#lbl_'+element)) {
                    $('#lbl_'+element).addClass('missing');
                    $('#'+element).addClass('error');
                    window.scrollTo($('.error'),0);
                    $('#empty-field').show();
                }
                this.retValue = false;
            }else{
                if($('#lbl_'+element)) {
                    $('#lbl_'+element).removeClass('missing');
                    $('#'+element).removeClass('error');
                }
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
            if($('#'+element) && $('#'+element).val() != ''){
                if(!filter.test($('#'+element).val())){
                    if($('#lbl_'+element)) $('#lbl_'+element).addClass('missing');
                    $('#'+element).addClass('error');
                    this.retValue = false;
                    window.scrollTo($('.missing'),0);
                    $('#empty-field').show();
                }else{
                    if($('#lbl_'+element)) $('#lbl_'+element).removeClass('missing');
                    $('#'+element).removeClass('error');
                }
            }
        },
    });

})(window, document, Web);