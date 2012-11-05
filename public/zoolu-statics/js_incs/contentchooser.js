/**
 * dashboard.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-11-05: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

Massiveart.Contentchooser = Class.create({
    initialize: function() {
        this.olCurrContainerId = '';
        this.olNewContainerId = '';

        this.updateOverlayContainer = 'overlayGenContent';
        this.folderUpdateContainer = 'olFolderContent';
        this.areaId = '';
    },

    /**
     * getModuleOverlay
     * @param string areaId
     */
    getModuleOverlay: function(areaId){
        $(this.updateOverlayContainer).innerHTML = '';
        myCore.putCenter('overlayGenContentWrapper');
        myCore.addBusyClass(this.updateOverlayContainer);
        $('overlayGenContentWrapper').show();

        if($(areaId)){
            this.areaId = areaId;
            var fieldname = 'dbrd-'+this.areaId.substring(this.areaId.indexOf('_')+1);
            new Ajax.Updater(this.updateOverlayContainer, '/zoolu/core/dashboard/overlay-modules', {
                parameters: { relationIds: $F(fieldname) },
                evalScripts: true,
                onComplete: function(){
                    myCore.putOverlayCenter('overlayGenContentWrapper');
                    myCore.removeBusyClass(this.updateOverlayContainer);
                }.bind(this)
            });
        }
    },

    /**
     * getModule
     */
    getModule: function(moduleId){
        if($('olModules')){
            this.olCurrContainerId = 'olModules';
            this.olNewContainerId = 'olRootLevels';
            this.createContainer();

            myCore.addBusyClass(this.olNewContainerId);
            this.moveContainers(this.olCurrContainerId, this.olNewContainerId);

            if($('olModuleId')) $('olModuleId').setValue(moduleId);
            new Ajax.Updater(this.olNewContainerId, '/zoolu/core/dashboard/overlay-rootlevels', {
                parameters: { moduleId: moduleId },
                evalScripts: true,
                onComplete: function(){
                    if($('olBack')) $('olBack').show();
                    if($(this.olNewContainerId+'_title')){
                        if($('dbrdOverlayTitle')) $('dbrdOverlayTitle').update($(this.olNewContainerId+'_title').innerHTML);
                    }
                    myCore.removeBusyClass(this.olNewContainerId);

                    $(this.olCurrContainerId).removeClassName('active');
                    $(this.olNewContainerId).addClassName('active');

                    this.olCurrContainerId = this.olNewContainerId;
                }.bind(this)
            });
        }
    },

    /**
     * getRootLevel
     */
    getRootLevel: function(rootLevelId, rootLevelTypeId, rootLevelGroupId, rootLevelLanguageId){
        if($(this.olCurrContainerId)){
            if(typeof(rootLevelTypeId) == 'undefined') rootLevelTypeId = '';
            if(typeof(rootLevelGroupId) == 'undefined') rootLevelGroupId = '';
            if(typeof(rootLevelLanguageId) == 'undefined') rootLevelLanguageId = '';

            this.olNewContainerId = 'olContentItems';
            this.createContainer();

            myCore.addBusyClass(this.olNewContainerId);
            this.moveContainers(this.olCurrContainerId, this.olNewContainerId);
            this.toggleContainerStatus('active');

            if($('olRootLevelId')) $('olRootLevelId').setValue(rootLevelId);
            new Ajax.Updater(this.olNewContainerId, '/zoolu/core/dashboard/overlay-content', {
                parameters: {
                    rootLevelId: rootLevelId,
                    rootLevelTypeId: rootLevelTypeId,
                    rootLevelGroupId: rootLevelGroupId,
                    rootLevelLanguageId: rootLevelLanguageId,
                    moduleId: $F('olModuleId')
                },
                evalScripts: true,
                onComplete: function(){
                    if($('olBack')) $('olBack').show();
                    if($(this.olNewContainerId+'_title')){
                        if($('dbrdOverlayTitle')) $('dbrdOverlayTitle').update($(this.olNewContainerId+'_title').innerHTML);
                    }
                    myCore.removeBusyClass(this.olNewContainerId);

                    $(this.olCurrContainerId).removeClassName('active');
                    $(this.olNewContainerId).addClassName('active');

                    if(rootLevelLanguageId != ''){
                        if($(this.languageField)) $(this.languageField).setValue(rootLevelLanguageId);
                    }

                    this.olCurrContainerId = this.olNewContainerId;
                }.bind(this)
            });
        }
    },

    /**
     * getNavItem
     * @param integer folderId, integer viewtype
     */
    getNavItem: function(folderId, rootLevelTypeId, rootLevelGroupId, viewtype, contenttype){
        this.resetNavItems();

        $('olnavitemtitle'+folderId).addClassName('selected');

        if($('olsubnav'+folderId)){
            this.toggleSubNavItem(folderId);
            // if mediaFilter is active
            /*if($('mediaFilter_Folders')){
             $('mediaFilter_Folders').value = folderId;
             this.loadFileFilterContent(viewtype, contenttype);
             }else{*/
            if(typeof(contenttype) != 'undefined'){
                this.getFolderContent(folderId, rootLevelTypeId, rootLevelGroupId, contenttype);
            }
            //}
        }else{
            if(folderId != ''){
                var subNavContainer = '<div id="olsubnav'+folderId+'" class="olsubnav" style="display:none;"></div>';
                new Insertion.Bottom('olnavitem'+folderId, subNavContainer);

                var blnVisible = this.toggleSubNavItem(folderId);
                myCore.addBusyClass('olsubnav'+folderId);

                var languageId = null;
                if($('languageId')) {
                    languageId = $F('languageId');
                }

                var languageCode = null;
                if($('languageCode')) {
                    languageCode = $F('languageCode');
                }

                new Ajax.Updater('olsubnav'+folderId, '/zoolu/core/dashboard/overlay-childnavigation', {
                    parameters: {
                        folderId: folderId,
                        viewtype: viewtype,
                        languageId: languageId,
                        languageCode: languageCode,
                        contenttype: contenttype,
                        rootLevelTypeId: rootLevelTypeId,
                        rootLevelGroupId: rootLevelGroupId
                    },
                    evalScripts: true,
                    onComplete: function() {
                        // if mediaFilter is active
                        /*if($('mediaFilter_Folders')){
                         $('mediaFilter_Folders').value = folderId;
                         this.loadFileFilterContent(viewtype, contenttype);
                         }else{*/
                        if(typeof(contenttype) != 'undefined'){
                            this.getFolderContent(folderId, rootLevelTypeId, rootLevelGroupId, contenttype);
                        }
                        //}
                        myCore.removeBusyClass('olsubnav'+folderId);
                    }.bind(this)
                });
            }
        }
    },

    /**
     * addItemToListArea
     */
    addItemToListArea: function(itemId, linkId){
        if(typeof(linkId) == 'undefined') linkId = null;
        if($(this.areaId) && $('olItem'+itemId)){
            var moduleId = 0;
            if($('olModuleId') && $F('olModuleId') != '') moduleId = $F('olModuleId');
            var rootLevelId = 0;
            if($('olRootLevelId') && $F('olRootLevelId') != '') rootLevelId = $F('olRootLevelId');

            var fieldId = 'dbrd-'+this.areaId.substring(this.areaId.indexOf('_')+1);
            var iconRemoveId = fieldId+'_remove'+itemId;

            // create new item container
            var itemContainer = '<div id="'+fieldId+'_item'+itemId+'" moduleid="'+moduleId+'" rootlevelid="'+rootLevelId+'" relationid="'+itemId+'" class="elementitem" style="display:none;">' + $('olItem'+itemId).innerHTML + '</div>';
            if($('divClear_'+fieldId)) $('divClear_'+fieldId).remove();
            new Insertion.Bottom(this.areaId, itemContainer + '<div id="divClear_'+fieldId+'" class="clear"></div>');

            if($('Remove'+itemId)) $('Remove'+itemId).writeAttribute('id', iconRemoveId);

            // insert file id to hidden field - only 1 insert is possible
            var addToField = '{"moduleId":'+moduleId+',"rootLevelId":'+rootLevelId+',"relationId":'+itemId;
            if(linkId != null && linkId != itemId){
                addToField += ',"linkId":'+linkId+'}';
            }else{
                addToField += '}';
            }

            if(addToField.isJSON()){
                if($(fieldId).value.indexOf(addToField) == -1){
                    if($F(fieldId) == ''){
                        $(fieldId).setValue('[' + addToField + ']');
                    }else if($(fieldId).value.indexOf('}]') != -1){
                        $(fieldId).setValue($F(fieldId).replace('}]', '},'+addToField+']'));
                    }
                }
            }

            $(fieldId+'_item'+itemId).appear({duration: 0.5});
            $('olItem'+itemId).fade({duration: 0.5});

            // add remove method to remove icon
            if($(iconRemoveId)){
                $(iconRemoveId).show();
                $(iconRemoveId).onclick = function(){
                    myDashboard.removeRelationItem(fieldId, fieldId+'_item'+itemId, itemId, linkId);
                }
            }
        }
    },

    /**
     * getFolderContent
     */
    getFolderContent: function(folderId, rootLevelTypeId, rootLevelGroupId, contenttype){
        $(this.folderUpdateContainer).innerHTML = '';
        myCore.addBusyClass(this.folderUpdateContainer);

        var languageId = null;
        if($('languageId')){
            languageId = $F('languageId');
        }
        var languageCode = null;
        if($('languageCode')){
            languageCode = $F('languageCode');
        }

        var fieldname = 'dbrd-'+this.areaId.substring(this.areaId.indexOf('_')+1);
        new Ajax.Updater(this.folderUpdateContainer, '/zoolu/core/dashboard/overlay-list', {
            parameters: {
                folderId: folderId,
                relation: $(fieldname).value,
                languageId: languageId,
                languageCode: languageCode,
                contenttype: contenttype,
                rootLevelTypeId: rootLevelTypeId,
                rootLevelGroupId: rootLevelGroupId
            },
            evalScripts: true,
            onComplete: function(){
                myCore.removeBusyClass(this.folderUpdateContainer);
            }.bind(this)
        });
    },

    /**
     * createContainer
     */
    createContainer: function(){
        if(!$(this.olNewContainerId)){
            $(this.olCurrContainerId).insert({
                after: '<div id="'+this.olNewContainerId+'" style="left: '+this.offsetX+'px;"></div>'
            });
        }else{
            $(this.olNewContainerId).update('');
        }
    },

    /**
     * moveContainers
     */
    moveContainers: function(currContainer, newContainer, direction){
        if(typeof(direction) == 'undefined') direction = 'NEXT';

        var offsetX = this.offsetX;
        if(direction == 'NEXT'){
            offsetX = -430;
        }
        if($(currContainer)) new Effect.Move(currContainer, { x: offsetX, y: 0, mode: 'absolute', duration: 0.3, transition: Effect.Transitions.linear });
        if($(newContainer)) new Effect.Move(newContainer, { x: 0, y: 0, mode: 'absolute', duration: 0.3, transition: Effect.Transitions.linear });
    },

    /**
     * toggleContainerStatus
     */
    toggleContainerStatus: function(cssClassName){
        if($(this.olCurrContainerId)) $(this.olCurrContainerId).removeClassName(cssClassName);
        if($(this.olNewContainerId)) $(this.olNewContainerId).addClassName(cssClassName);
    },

    /**
     * resetNavItems
     */
    resetNavItems: function(){
        $$('.olnavigationwrapper .olnavchilditem span.selected').each(function(element, index){
            element.removeClassName('selected');
        });
        $$('.olnavigationwrapper .olnavrootitem span.selected').each(function(element, index){
            element.removeClassName('selected');
        });
    },

    /**
     * toggleSubNavItem
     * @param integer itemId
     */
    toggleSubNavItem: function(itemId){
        if($('olsubnav'+itemId)){
            $('olsubnav'+itemId).toggle();

            if($('olnavitem'+itemId)){
                if($('olnavitem'+itemId).down('.icon').hasClassName('img_folder_on_open')){
                    $('olnavitem'+itemId).down('.icon').removeClassName('img_folder_on_open');
                    return false;
                }else{
                    $('olnavitem'+itemId).down('.icon').addClassName('img_folder_on_open');
                    return true;
                }
            }
        }
    }
});