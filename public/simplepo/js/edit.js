/* MASSIVEART Enhancements */
(function($, window, document, undefined) {   
    //Copy
    function copyTranslation(e) {
        if($('#msgstrref').text() != '') {
            $('#msgstr').val($('#msgstrref').text());
        }
    }

    // set the Suggestion Title
    function setSuggestionLanguage() {
        $.messageService('getSuggestionLanguage',[],function(e) {
            $('.suggestion-language').text(e);
        });
    }

    var msgsRef = [];
    function addRefTranslation() {
        $.messageService('getMessages',[null],function(msgs) { 
            $.each(msgs,function(i,e){
                var keyName = replaceSpecificChars(e.msgid);
                $('tr.' + keyName + ' td.ref').html('<div>' + $.escape(e.msgstr) + '</div>');
                msgsRef[e.msgid] = e.msgstr;
            }) 
        });
    }

    function replaceSpecificChars(str) {
        return str.replace(/\s/g, '--20--').replace(/\//g, '--2F--').replace(/\?/g, '--3F--').replace(/\:/g, '--3A--').replace(/\./g, '--46--').replace(/\'/g, '--39--').replace(/\*/g, '--42--').replace(/\,/g, '--44--');
    }

    /* MASSIVEART Enhancements end*/

    $.escape = function(str) {
      return str.replace(/&/g,'&amp;').replace(/>/g,'&gt;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
    }
    $.nl2br = function(str) {
      return str.replace("\n",'<br />');
    }
    // jQuery function that send Post request to json rpc
    $.messageService = function(method, params,callback,error_handler) {
      $.post('message_service.php',
          {request:JSON.stringify(
            {"method":method,
             "params":params,
              "id":Math.random()
              }
          )},
          function(obj) {
            if(obj.error) {
              error_handler ? error_handler(obj.error) : NotificationObj.showError(obj.error.message + "  " + method);
            } else {
              callback(obj.result);
            }
          },
          "json"
      );     
    };

    // Object that handles Error Notifications and Display Messages
    var NotificationObj = (function() {
      var _init = function(){
          $('#errors #hideBtn').live('click', function(){ hideError(); });
          $('#messages #hideBtn').live('click', function(){ hideMessage();  });
      };
      var hideAll = function(){
          hideError();
          hideMessage();
      };
      var showError = function(msg){
          $('<span />').html("<br />" + msg).appendTo('#errors p');
          $('#errors').fadeIn();
          $('#next').hide();
      };
      var hideError = function(){
          $('#errors').fadeOut();
      };
      var showMessage = function(msg){
          $('#messages').stop().css({opacity:0});
          $('#messages span').text(msg);
          $('#messages').animate({opacity:1},300, function(){ $('#messages').animate({opacity:0}, 3000); });
      };
      var hideMessage = function(){
          $('#messages').css({opacity:0});
      };

      return {
        init: function(){
          _init();
        },
        hideAll: function(){
          hideAll();
        },
        showError: function(msg){
            showError(msg);
        },
        hideError: function(){
          hideError();
        },
        showMessage: function(msg){
            showMessage(msg);
        },
        hideMessage: function(){
          hideMessage();
        }
      };
    })();
    // GLOBAL 


    var appController = (function() {
      var msgs;
      var cat_id = parseInt(window.location.href.match(/cat_id=(\d+)/)[1]);
      
      var initEvents = function() {
        // Add click event to message table entries
        $('#msg_table td').live("click",function() {
          selectMessage($(this).parent('tr').prevAll().length );
        });

        // Add click event to next button in the edit bar
        $('#next').live("click", function(e){
          e.preventDefault();
          moveBy(1);
          $('#msgstr').focus();
        });
        
        $('#msgstr').focus(function(e){
          $('#fuzz').attr('checked',false);
        });

        $(document).keydown( function(e) {
          
          var nt = e.target.type,code;
          if( !(nt == 'input' || nt == 'textarea') ) {
            code = e.which || e.keyCode;
            switch(e.which || e.keyCode) {
              case 37:
              case 38:
                moveBy(-1);
                e.preventDefault();
                break;
              case 39:
              case 40:
                moveBy(1);
                e.preventDefault();
                break;
            }
          }
        }).keypress(function(e) {
          
        }).keyup(function() {
          
        });
      }

      var moveBy = function(num) {
        var moveTo = getCurrentMessage() + num;
        moveTo < msgs.length && moveTo >= 0 && selectMessage(moveTo);
      }

      var selectMessage = function (index) {
        beforeBlur();
        $('#msg_table').find('tbody tr.selected').removeClass('selected');
        var arr_index = $('#msg_table').find('tbody tr:eq(' +(index)+ ')').addClass('selected').data('index');
        fillEditBar(arr_index);
        setScroll();
      }

      var getCurrentMessage = function() {
        return $('#msg_table tr.selected').prevAll().length;
      }
      
      var beforeBlur = function(){
        if ( !$('#msg_table tr.selected').length ) return;
        var $row = $('#msg_table tr:eq(' + getCurrentMessage() + ')');    

        var msg = msgs[$row.data('index')];
        var dirty = $('#msgstr').val() != msg.msgstr || 
                    $('#comments').val() != msg.comments || 
                    $('#fuzz').is(':checked') != msg.fuzzy;
                    
        if (dirty) {
          msg.msgstr = $('#msgstr').val().replace(/\n+$/,'');
          msg.fuzzy = $('#fuzz').is(':checked');
          msg.comments = $('#comments').val();
          $row.trigger('sync');
          $.messageService('updateMessage', 
                          [msg.id, msg.comments, msg.msgstr, msg.fuzzy], 
                          function() {} 
                          );
        }
      };
      
      var setScroll = function() {
        var ot = $('#msg_table tr.selected').position().top - $('#msg_table').position().top;
        var rh = $('#msg_table tr.selected').height();
        var sh = $('#scroll_container').height();
        var st = $('#scroll_container').scrollTop();
        if(ot < st) {
          $('#scroll_container').scrollTop(ot);
        }
        if(ot + rh - sh > st) {
          $('#scroll_container').scrollTop(ot+rh-sh);
        }
      };
      var sync = function () {

        var $row2 = $(this);
        var msg2 = msgs[$row2.data("index")];

        $row2.find('td.msgid div').text(msg2.msgid).end()
             .find('td.msgstr:not(.ref) div').text(msg2.msgstr);
        msg2.msgstr == "" ? $row2.addClass('empty') : $row2.removeClass('empty');
        msg2.fuzzy == 1 ? $row2.addClass('f').find('.fuzzy').text('F') : $row2.removeClass('f').find('.fuzzy').text('');
        msg2.is_obsolete && $row2.addClass('d').find('.depr').text('D');
      };
      // Fill the table with all of the messages
      var fillMsgTable = function(catalogue_id) {
        $('#loading_indicator').show();
        if ($('#errors span').text() != "") return;
        setSuggestionLanguage();
        $.messageService('getMessages',[catalogue_id],function(d){
          msgs = d; // save data to global messages
          if (!(msgs && msgs.length) ) {
            NotificationObj.showError("No Messages Found");
          } else {
            var $tbody = $('#msg_table tbody').empty(), html="";
            
            $.each(msgs,function(i,e){
              html += renderRowAsString(e);
            })
            addRefTranslation();
            $tbody.append(html)
              .find('tr')
              .each(function(i,e){
                $(e).data('index',i)
                .bind('sync',sync);
              });
            selectMessage(0);
          }
          $('#loading_indicator').hide();
        });
      };
      var renderRowAsString = function(obj) {
        var tr_class = ""
                      + (!obj.msgstr.length ? 'empty ' : '')
                      + (obj.fuzzy == 1 ? 'f ' : '')
                      + (obj.is_obsolete ? 'd ' : '');

        // MA FIX: replace for keys with spaces and specialchars
        var cleanMsgId = replaceSpecificChars(obj.msgid);

        return  ''
                +  '<tr class="' + tr_class + cleanMsgId + '"><td class="msgid"><div><span>'
                + $.escape(obj.msgid) + '</span></div></td>'
                + '<td class="msgstr"><div>'
                + $.escape(obj.msgstr) + '</div></td>'
                + '<td class="msgstr ref"><div></div></td>'
                + '<td class="fuzzy">'
                + (obj.fuzzy == 1 ? 'F' : '')
                + '</td>'
                + '<td class="depr">'
                + (obj.is_obsolete ? 'D' : '')
                + '</td>';
      }

      // Fill the Edit Bar with the selected message
      var fillEditBar = function(index){
        var msg = msgs[index];
        var msgRef = msgsRef[msg.msgid];
        $('#ref_data').html( $.nl2br($.escape(msg.reference)) || '-' );
        $('#com_data').html( $.nl2br($.escape(msg.extracted_comments)) || '-' );
        $('#update_data').html( msg.updated_at || '-' );
        $('#comments').val(msg.comments);
        $('#msgid').html( $.nl2br($.escape(msg.msgid)) || "-" );
        $('#msgstr').val(msg.msgstr);
        if(msgRef != undefined) {
            $('#msgstrref').text(msgRef);
        } else {
            $('#msgstrref').text('');
        }
        ( msg.fuzzy == 1 ) ? $('#fuzz').attr('checked',true) : $('#fuzz').attr('checked',false);
        $('#edit_id').attr( 'value', msg.id );
      };
      
      var _init = function() {
        getCatalogues(cat_id);
         // Sort Table by the different headers
        sortController.init();
          
        initPanels();
        fillMsgTable(cat_id);
        NotificationObj.init();
        initEvents();
      }
      return {
        init: function() {
          _init();
        }
      };
    })();
    $(appController.init);      

    var sortController = {
      init: function() {
        $('#msg_table_head thead th').click(function() {
          var column_index = $(this).closest('thead')
                              .find('th')
                              .index(this);
          var direction = !!$(this).hasClass('sort-desc') || !$(this).hasClass('sort-asc');
          
          $(this).siblings().andSelf().removeClass('sort-asc').removeClass('sort-desc');
          
          $(this).addClass(direction ? 'sort-asc' : 'sort-desc');
          
          $('#msg_table').tsort(column_index,direction);
        });
      }
    };

    // Get the stored catalogues
    var getCatalogues = function( catId ){
      $.messageService('getCatalogues', [], function(data){
        if (data.length == 0){
          NotificationObj.showError("No PO Catalalogues Found.");
          return;
        }
        for (var i = 0; i<data.length; ++i){
            $("<option />").attr('id',data[i]['id']).text(data[i]['name'])
                           .appendTo($('#catalogue_list'))
                           .attr('selected',data[i]['id'] == catId ? 'selected' : '');
            if (data[i]['id'] == catId) {
                $('#cat_name').text(data[i]['name']);
            }
        }
      });
    };

    var initPanels = function(){
      // Jump to other Catalogues through drop down menu
      $('#catalogue_list').change( function(){
        window.location.href = "edit.html?cat_id=" + ( $('#catalogue_list option:selected').attr('id') );
      });
      
      // Add toggle effect
      $('#edit_bar .block h3 a.expand').click(function(){
        $(this).parents('.block').find('.data').toggle('fast');
      }).parents('.block').find('.data').toggle();
      
      // Add rollover effect to Side Bar
      var left = $('div.block').offset().left;
      var top = $('div.block').offset().top + $('div.block').height();
      $('#ref_head').live("mouseover", function(){
        $('#ref_data').css({'left':left, 'top':top}).fadeIn('fast');
      }).live("mouseout", function(){
        $('#ref_data').fadeOut();
      });
      $('#update_head').live("mouseover", function(){
        $('#update_data').css({'left':left, 'top':top}).fadeIn('fast');
      }).live("mouseout", function(){
        $('#update_data').fadeOut();
      });
      $('#src_com_head').live("mouseover", function(){
        $('#com_data').css({'left':left, 'top':top}).fadeIn('fast');
      }).live("mouseout", function(){
        $('#com_data').fadeOut();
      });
      
      $(this).resize(function() {
        var w = $(this).width();
        var h = $(this).height();
        var scroll_height = h - $('#scroll_container').offset().top - ( $('#foot').outerHeight());
        var l = $('#message_container').offset().left;
        $('#message_container').css({width:w-l});
        $('#msg_table,#msg_table_head').css({width:w-l-20});
        $('#scroll_container').css('height',scroll_height);
      }).resize();
      
    };
})(window.jQuery, window, document);