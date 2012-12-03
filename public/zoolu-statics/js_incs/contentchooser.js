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
    initialize:function () {
        this.olCurrContainerId = '';
        this.olNewContainerId = '';

        this.updateOverlayContainer = 'overlayGenContent';
        this.folderUpdateContainer = 'olFolderContent';
        this.areaId = '';
        this.start = '';
        this.type = '';

        this.offsetX = 430;

        this.callback = function () {
        };
    },

    removeUnusedContainer:function () {
        if (this.start == 'rootLevel' || this.start == 'content') {
            if ($('olModules')) {
                $('olModules').remove();
            }
        }
    },

    /**
     * getContentchooser
     * @param string areaId
     */
    getContentchooser:function (start, type, callback, areaId) {
        if (typeof(start) == 'undefined') {
            start = 'modules';
        }
        if (typeof(type) == 'undefined') {
            type = 'content';
        }
        if (typeof(areaId) == 'undefined') {
            areaId = null;
        }
        this.start = start;
        this.type = type;
        this.callback = callback;

        $(this.updateOverlayContainer).innerHTML = '';
        myCore.putCenter('overlayGenContentWrapper');
        myCore.addBusyClass(this.updateOverlayContainer);
        $('overlayGenContentWrapper').show();

        this.areaId = areaId;

        var parameters = {};
        if (this.areaId != null) {
            var fieldname = 'dbrd-' + this.areaId.substring(this.areaId.indexOf('_') + 1);
            parameters = { relationIds:$F(fieldname) };
        }

        new Ajax.Updater(this.updateOverlayContainer, '/zoolu/core/contentchooser/overlay-modules', {
            parameters:parameters,
            evalScripts:true,
            onComplete:function () {
                myCore.putOverlayCenter('overlayGenContentWrapper');
                myCore.removeBusyClass(this.updateOverlayContainer);
                this.olCurrContainerId = 'olModules';
                //go to desired start location
                if (this.start != 'modules') {
                    switch (this.start) {
                        case 'rootLevel':
                            this.getModule(myNavigation.module);
                            break;
                        case 'content':
                            $('olModuleId').setValue(myNavigation.module);
                            this.getRootLevel(myNavigation.rootLevelId, myNavigation.rootLevelTypeId, myNavigation.rootLevelGroupId); //TODO Add rootLevelLanguage
                            break;
                    }
                }
            }.bind(this)
        });
    },

    /**
     * getModule
     */
    getModule:function (moduleId) {
        if ($('olModules')) {
            this.olCurrContainerId = 'olModules';
            this.olNewContainerId = 'olRootLevels';
            this.createContainer();

            myCore.addBusyClass(this.olNewContainerId);
            this.moveContainers(this.olCurrContainerId, this.olNewContainerId);

            if ($('olModuleId')) $('olModuleId').setValue(moduleId);
            new Ajax.Updater(this.olNewContainerId, '/zoolu/core/contentchooser/overlay-rootlevels', {
                parameters:{ moduleId:moduleId },
                evalScripts:true,
                onComplete:function () {
                    if ($('olBack')) $('olBack').show();
                    if ($(this.olNewContainerId + '_title')) {
                        if ($('dbrdOverlayTitle')) $('dbrdOverlayTitle').update($(this.olNewContainerId + '_title').innerHTML);
                    }
                    myCore.removeBusyClass(this.olNewContainerId);

                    $(this.olCurrContainerId).removeClassName('active');
                    $(this.olNewContainerId).addClassName('active');

                    this.olCurrContainerId = this.olNewContainerId;
                    this.removeUnusedContainer();
                }.bind(this)
            });
        }
    },

    /**
     * getRootLevel
     */
    getRootLevel:function (rootLevelId, rootLevelTypeId, rootLevelGroupId, rootLevelLanguageId) {
        if ($(this.olCurrContainerId)) {
            if (typeof(rootLevelTypeId) == 'undefined') rootLevelTypeId = '';
            if (typeof(rootLevelGroupId) == 'undefined') rootLevelGroupId = '';
            if (typeof(rootLevelLanguageId) == 'undefined') rootLevelLanguageId = '';

            this.olNewContainerId = 'olContentItems';
            this.createContainer();

            myCore.addBusyClass(this.olNewContainerId);
            this.moveContainers(this.olCurrContainerId, this.olNewContainerId);
            this.toggleContainerStatus('active');

            if ($('olRootLevelId')) $('olRootLevelId').setValue(rootLevelId);
            new Ajax.Updater(this.olNewContainerId, '/zoolu/core/contentchooser/overlay-content', {
                parameters:{
                    rootLevelId:rootLevelId,
                    rootLevelTypeId:rootLevelTypeId,
                    rootLevelGroupId:rootLevelGroupId,
                    rootLevelLanguageId:rootLevelLanguageId,
                    moduleId:$F('olModuleId'),
                    type:this.type
                },
                evalScripts:true,
                onComplete:function () {
                    if ($('olBack')) $('olBack').show();
                    if ($(this.olNewContainerId + '_title')) {
                        if ($('dbrdOverlayTitle')) $('dbrdOverlayTitle').update($(this.olNewContainerId + '_title').innerHTML);
                    }
                    myCore.removeBusyClass(this.olNewContainerId);

                    $(this.olCurrContainerId).removeClassName('active');
                    $(this.olNewContainerId).addClassName('active');

                    if (rootLevelLanguageId != '') {
                        if ($(this.languageField)) $(this.languageField).setValue(rootLevelLanguageId);
                    }

                    this.olCurrContainerId = this.olNewContainerId;

                    this.removeUnusedContainer();

                    $('buttonCancel').observe('click', function () {
                        myOverlay.close('overlayGenContentWrapper');
                    });
                }.bind(this)
            });
        }
    },

    /**
     * getNavItem
     * @param integer folderId, integer viewtype
     */
    getNavItem:function (folderId, rootLevelTypeId, rootLevelGroupId, viewtype, contenttype) {
        if (this.type == 'folder') {
            //set selected class only if folder type is active
            this.resetNavItems();
            $('olnavitemtitle' + folderId).addClassName('selected');

            //Set Click Handler for OK Button
            $('buttonOk').stopObserving('click');
            $('buttonOk').observe('click', function(event){
                $('buttonOk').addClassName('busy');
                this.callback(folderId);
            }.bind(this));
        }

        if ($('olsubnav' + folderId)) {
            this.toggleSubNavItem(folderId);
            if (typeof(contenttype) != 'undefined' && this.type != 'folder') {
                this.getFolderContent(folderId, rootLevelTypeId, rootLevelGroupId, contenttype);
            }
        } else {
            if (folderId != '') {
                var subNavContainer = '<div id="olsubnav' + folderId + '" class="olsubnav" style="display:none;"></div>';
                new Insertion.Bottom('olnavitem' + folderId, subNavContainer);

                var blnVisible = this.toggleSubNavItem(folderId);
                myCore.addBusyClass('olsubnav' + folderId);

                var languageId = null;
                if ($('languageId')) {
                    languageId = $F('languageId');
                }

                var languageCode = null;
                if ($('languageCode')) {
                    languageCode = $F('languageCode');
                }

                new Ajax.Updater('olsubnav' + folderId, '/zoolu/core/contentchooser/overlay-childnavigation', {
                    parameters:{
                        folderId:folderId,
                        viewtype:viewtype,
                        languageId:languageId,
                        languageCode:languageCode,
                        contenttype:contenttype,
                        rootLevelTypeId:rootLevelTypeId,
                        rootLevelGroupId:rootLevelGroupId
                    },
                    evalScripts:true,
                    onComplete:function () {
                        if (typeof(contenttype) != 'undefined' && this.type != 'folder') {
                            this.getFolderContent(folderId, rootLevelTypeId, rootLevelGroupId, contenttype);
                        }
                        myCore.removeBusyClass('olsubnav' + folderId);
                    }.bind(this)
                });
            }
        }
    },

    /**
     * getFolderContent
     */
    getFolderContent:function (folderId, rootLevelTypeId, rootLevelGroupId, contenttype) {
        $(this.folderUpdateContainer).innerHTML = '';
        myCore.addBusyClass(this.folderUpdateContainer);

        var languageId = null;
        if ($('languageId')) {
            languageId = $F('languageId');
        }
        var languageCode = null;
        if ($('languageCode')) {
            languageCode = $F('languageCode');
        }

        var relation;
        if (this.areaId != null) {
            var fieldname = 'dbrd-' + this.areaId.substring(this.areaId.indexOf('_') + 1);
            relation = $(fieldname).value;
        } else {
            relation = null;
        }
        new Ajax.Updater(this.folderUpdateContainer, '/zoolu/core/contentchooser/overlay-list', {
            parameters:{
                folderId:folderId,
                relation:relation,
                languageId:languageId,
                languageCode:languageCode,
                contenttype:contenttype,
                rootLevelTypeId:rootLevelTypeId,
                rootLevelGroupId:rootLevelGroupId
            },
            evalScripts:true,
            onComplete:function () {
                myCore.removeBusyClass(this.folderUpdateContainer);
            }.bind(this)
        });
    },

    /**
     * createContainer
     */
    createContainer:function () {
        if (!$(this.olNewContainerId)) {
            $(this.olCurrContainerId).insert({
                after:'<div id="' + this.olNewContainerId + '" style="left: ' + this.offsetX + 'px;"></div>'
            });
        } else {
            $(this.olNewContainerId).update('');
        }
    },

    /**
     * moveContainers
     */
    moveContainers:function (currContainer, newContainer, direction) {
        if (typeof(direction) == 'undefined') direction = 'NEXT';

        var offsetX = this.offsetX;
        if (direction == 'NEXT') {
            offsetX = -430;
        }
        if ($(currContainer)) new Effect.Move(currContainer, { x:offsetX, y:0, mode:'absolute', duration:0.3, transition:Effect.Transitions.linear });
        if ($(newContainer)) new Effect.Move(newContainer, { x:0, y:0, mode:'absolute', duration:0.3, transition:Effect.Transitions.linear });
    },

    /**
     * toggleContainerStatus
     */
    toggleContainerStatus:function (cssClassName) {
        if ($(this.olCurrContainerId)) $(this.olCurrContainerId).removeClassName(cssClassName);
        if ($(this.olNewContainerId)) $(this.olNewContainerId).addClassName(cssClassName);
    },

    /**
     * resetNavItems
     */
    resetNavItems:function () {
        $$('.olnavigationwrappercontent .olnavchilditem span.selected').each(function (element, index) {
            element.removeClassName('selected');
        });
        $$('.olnavigationwrappercontent .olnavrootitem span.selected').each(function (element, index) {
            element.removeClassName('selected');
        });
    },

    /**
     * toggleSubNavItem
     * @param integer itemId
     */
    toggleSubNavItem:function (itemId) {
        if ($('olsubnav' + itemId)) {
            $('olsubnav' + itemId).toggle();

            if ($('olnavitem' + itemId)) {
                if ($('olnavitem' + itemId).down('.icon').hasClassName('img_folder_on_open')) {
                    $('olnavitem' + itemId).down('.icon').removeClassName('img_folder_on_open');
                    return false;
                } else {
                    $('olnavitem' + itemId).down('.icon').addClassName('img_folder_on_open');
                    return true;
                }
            }
        }
    },

    /**
     * stepBack
     */
    stepBack:function () {
        $$('#olContent .active').each(function (element) {
            if ($(element.id)) {
                this.olCurrContainerId = element.id;
                var prevElement = $(element.id).previous();
                this.olNewContainerId = prevElement.id;

                this.moveContainers(this.olCurrContainerId, this.olNewContainerId, 'PREV');
                this.toggleContainerStatus('active');

                if ($(this.olNewContainerId + '_title')) {
                    if ($('dbrdOverlayTitle')) $('dbrdOverlayTitle').update($(this.olNewContainerId + '_title').innerHTML);
                }

                if ($(prevElement.id).previous() == null) {
                    if ($('olBack')) $('olBack').hide();
                }

                this.olCurrContainerId = this.olNewContainerId;
            }
        }.bind(this));
    }
});