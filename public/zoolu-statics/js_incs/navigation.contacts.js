/**
 * navigation.members.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-01-05: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation.Contacts = Class.create(Massiveart.Navigation, {
    initialize: function($super) {
        // initialize superclass
        $super();

        this.constBasePath = '/zoolu/contacts';
        this.navigationPath = '';

        this.constUnit = 'unit';
        this.constContact = 'contact';
        this.constLocation = 'location';
        this.constMember = 'member';
        this.constCompany = 'company';

        this.currLevel = 0;

        this.constRequestSubscriberFilter = '/zoolu/contacts/subscriber/listfilter';
        this.constRequestSubscriberList = '/zoolu/contacts/subscriber/list';

        this.isList = ((location.href.endsWith('/list')) ? true : false);
    },
    /**
     * initItemHover
     */
    initItemHover: function() {
        $$('div.hoveritem').each(function(elDiv) {
            elDiv.observe('mouseover', function(event) {
                el = Event.element(event);
                if (el.hasClassName('hoveritem')) {
                    el.addClassName('hover');
                } else {
                    el.up('.hoveritem').addClassName('hover');
                }
            }.bind(this));

            elDiv.observe('mouseout', function(event) {
                el = Event.element(event);
                if (el.hasClassName('hoveritem')) {
                    el.removeClassName('hover');
                } else {
                    el.up('.hoveritem').removeClassName('hover');
                }
            }.bind(this));
        }.bind(this));
    },
    /**
     * getRootLevelTreeStart
     */
    getRootLevelTreeStart: function() {
        if ($('subnaviitem' + this.rootLevelId + '_link')) {
            $('subnaviitem' + this.rootLevelId + '_link').onclick();
        } else if ($('naviitem' + this.rootLevelId)) {
            $('naviitem' + this.rootLevelId).onclick();
        }

    },
    /**
     * getRootLevelList
     */
    getRootLevelList: function() {

        // set root level type if undefined
        if (typeof(this.rootLevelType) == 'undefined') {
            if ($('rootLevelType' + this.rootLevelId))
                this.rootLevelType = $F('rootLevelType' + this.rootLevelId);
        }

        // load list
        if ((typeof(myList) != 'undefined')) {
            //myList.getListPage();
            myList.sortColumn = '';
            myList.sortOrder = '';
            myList.resetSearch();
        }
    },
    /**
     * getModuleRootLevelList
     * @param integer rootLevelId
     */
    getModuleRootLevelList: function(rootLevelId, rootLevelType) {

        this.rootLevelId = rootLevelId;
        this.rootLevelType = rootLevelType;

        $(this.genFormContainer).hide();
        $(this.genFormFunctions).hide();

        this.makeSelected('naviitem' + rootLevelId);
        if ($(this.preSelectedPortal) && ('naviitem' + rootLevelId) != this.preSelectedPortal) {
            this.makeDeselected(this.preSelectedPortal);
        }

        this.preSelectedPortal = 'naviitem' + rootLevelId;

        myList.sortColumn = '';
        myList.sortOrder = '';
        myList.resetSearch();
    },
    /**
     * getAddForm
     */
    getAddForm: function() {
        this.resetGenContainer();

        if ($('buttondelete'))
            $('buttondelete').hide();

        myCore.addBusyClass(this.genFormContainer);
        myCore.resetTinyMCE(true);

        var parentId = 0;
        if ($('navlevel' + this.currLevel)) {
            parentId = $('navlevel' + this.currLevel).readAttribute('parentid')
        }

        new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/addform', {
            parameters: {
                rootLevelId: this.rootLevelId,
                parentId: parentId,
                currLevel: this.currLevel
            },
            evalScripts: true,
            onComplete: function() {
                if ($(this.genFormContainer))
                    $(this.genFormContainer).show();
                if ($(this.genFormFunctions))
                    $(this.genFormFunctions).show();
                if ($(this.genFormSaveContainer))
                    $(this.genFormSaveContainer).show();
                if ($('widgetfunctions'))
                    $(this.genFormContainer).scrollTo($('widgetfunctions'));
                myCore.removeBusyClass(this.genFormContainer);
            }.bind(this)
        });
    },
    /**
     * getAddFormList
     */
    getAddFormList: function() {
        $(this.genListContainer).hide();
        $(this.genListFunctions).hide();
        if ($('sendDataLink'))
            $('sendDataLink').hide();
        if ($('buttondelete'))
            $('buttondelete').hide();

        myCore.resetTinyMCE(true);

        new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/addform', {
            parameters: {
                rootLevelId: this.rootLevelId
            },
            evalScripts: true,
            onComplete: function() {
                $(this.genFormContainer).show();
                $(this.genFormFunctions).show();
                $(this.genFormContainer).scrollTo($('widgetfunctions'));
            }.bind(this)
        });
    },
    /**
     * addUnit
     * @param integer currLevel
     */
    addUnit: function(currLevel) {
        if ($('buttondelete'))
            $('buttondelete').hide();
        myNavigation.showFormContainer();
        $(this.genListContainer).hide();
        $(this.genListFunctions).hide();
        $(this.genFormContainer).innerHTML = '';
        $(this.genFormContainer).show();
        $(this.genFormSaveContainer).show();

        myCore.addBusyClass(this.genFormContainer);
        myCore.resetTinyMCE(true);

        new Ajax.Updater(this.genFormContainer, '/zoolu/contacts/contact/unit-addform', {
            parameters: {
                formId: 'DEFAULT_UNIT',
                rootLevelId: this.rootLevelId,
                parentId: $('navlevel' + currLevel).readAttribute('parentid'),
                currLevel: currLevel
            },
            evalScripts: true,
            onComplete: function() {
                $('levelmenu' + currLevel).hide();
                $('addmenu' + currLevel).fade({duration: 0.5});
                myCore.removeBusyClass(this.genFormContainer);
            }.bind(this)
        });
    },
    /**
     * addContact
     * @param integer currLevel
     */
    addContact: function(currLevel) {
        if ($('buttondelete'))
            $('buttondelete').hide();
        myNavigation.showFormContainer();

        $(this.genFormContainer).innerHTML = '';
        $(this.genFormContainer).show();
        $(this.genFormSaveContainer).show();

        myCore.addBusyClass(this.genFormContainer);
        myCore.resetTinyMCE(true);

        new Ajax.Updater(this.genFormContainer, '/zoolu/contacts/contact/addform', {
            parameters: {
                formId: contactFormDefaultId,
                rootLevelId: this.rootLevelId,
                parentId: $('navlevel' + currLevel).readAttribute('parentid'),
                currLevel: currLevel
            },
            evalScripts: true,
            onComplete: function() {
                $('levelmenu' + currLevel).hide();
                $('addmenu' + currLevel).fade({duration: 0.5});
                myCore.removeBusyClass(this.genFormContainer);
            }.bind(this)
        });
    },
    /**
     * getEditForm
     * @param integer itemId
     */
    getEditForm: function(itemId, elType, formId, version) {
        $(this.genFormContainer).innerHTML = '';

        this.resetGenContainer();

        var element = elType + itemId;
        if ($(element))
            this.currItemId = itemId;

        var typeEditPath = '';
        switch (elType) {
            case this.constUnit:
                formDefaultId = ''; //unitFormDefaultId;
                typeEditPath = '/contact/unit-editform';
                break;
            case this.constContact:
                formDefaultId = ''; //contactFormDefaultId;
                typeEditPath = '/contact/editform';
                break;
            case this.constLocation:
                formDefaultId = ''; //locationFormDefaultId;
                typeEditPath = '/location/editform';
                break;
        }

        formId = (formId == null) ? formDefaultId : formId;
        version = (version == null) ? 1 : version;

        var currLevel = 0;
        currLevel = ($(element)) ? parseInt($(element).up().id.substr(5)) : this.currLevel;

        if (this.navigation[currLevel]) {
            this.makeDeselected(this.navigation[currLevel]);
        }
        this.navigation[currLevel] = element;

        if (this.navigation.length > 0) {
            for (var i = 1; i <= this.navigation.length - 1; i++) {
                if (this.navigation[i] != element) {
                    if (currLevel < i) {
                        this.makeDeselected(this.navigation[i]);
                    } else {
                        this.makeParentSelected(this.navigation[i]);
                    }
                } else {
                    this.makeSelected(this.navigation[currLevel]);
                }
            }
        }

        if (this.levelArray.indexOf(currLevel) != -1 && elType == this.constContact) {
            var levelPos = this.levelArray.indexOf(currLevel) + 1;
            for (var i = levelPos; i < this.levelArray.length; i++) {
                if ($('navlevel' + this.levelArray[i]))
                    $('navlevel' + this.levelArray[i]).innerHTML = '';
            }
        }

        this.showFormContainer();

        if ($('buttondelete'))
            $('buttondelete').show();
        if ($(this.genFormContainer))
            $(this.genFormContainer).show();
        if ($(this.genFormSaveContainer))
            $(this.genFormSaveContainer).show();

        myCore.addBusyClass(this.genFormContainer);
        myCore.resetTinyMCE(true);

        var parentId = 0;
        if ($('navlevel' + currLevel)) {
            parentId = $('navlevel' + currLevel).readAttribute('parentid');
        }

        new Ajax.Updater(this.genFormContainer, this.constBasePath + typeEditPath, {
            parameters: {
                id: itemId,
                formId: formId,
                formVersion: version,
                currLevel: currLevel,
                rootLevelId: this.rootLevelId,
                parentId: parentId
            },
            evalScripts: true,
            onComplete: function() {
                myCore.removeBusyClass(this.genFormContainer);
                myForm.loadFileFieldsContent('media');
                myForm.loadFileFieldsContent('document');
            }.bind(this)
        });
    },
    /**
     * getEditFormList
     * @param integer itemId
     */
    getEditFormList: function(itemId, elType, formId, version) {
        version = (version == null) ? 1 : version;

        $(this.genListContainer).hide();
        $(this.genListFunctions).hide();
        if ($('sendDataLink'))
            $('sendDataLink').hide();

        myCore.resetTinyMCE(true);

        if ($('buttondelete'))
            $('buttondelete').show();

        new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + elType + '/editform', {
            parameters: {
                id: itemId,
                formId: formId,
                formVersion: version,
                rootLevelId: this.rootLevelId,
                rootLevelFilterId: $('rootLevelFilterListId') ? $('rootLevelFilterListId').getValue() : null
            },
            evalScripts: true,
            onComplete: function() {
                $(this.genFormContainer).show();
                $(this.genFormFunctions).show();
                $(this.genFormContainer).scrollTo($('widgetfunctions'));

                myForm.loadFileFieldsContent('media');
                myForm.loadFileFieldsContent('document');

                // show special link to send data to user per click
                if (elType == 'member' || elType == 'company') {
                    if ($('id') && $F('id') > 0) {
                        if ($('sendDataLink'))
                            $('sendDataLink').show();
                    }
                }
            }.bind(this)
        });
    },
    /**
     * selectContacts
     */
    selectContacts: function(rootLevelId, rootLevelGroupId, url, makeRequest, viewType, rootLevelType) {
        if ($('importList'))
            $('importList').hide();
        if ($('exportList'))
            $('exportList').hide();

        if (typeof(viewType) == 'undefined') {
            viewType = 'tree';
        }

        if (typeof(url) != 'undefined' && url != '' && (!location.href.endsWith(url) || viewType == 'list')) {
            this.changeViewType(rootLevelId, rootLevelGroupId, url);
        } else {
            if (typeof(makeRequest) == 'undefined') {
                makeRequest = true;
            }

            this.rootLevelId = rootLevelId;
            this.rootLevelGroupId = rootLevelGroupId;
            this.rootLevelType = rootLevelType;

            this.resetGenContainer();
            this.hideCurrentFolder();

            this.currLevel = 1;
            this.navigationItemType = this.constUnit;

            if ($('naviitem' + rootLevelId)) {
                this.makeSelected('naviitem' + rootLevelId);
                if ($(this.preSelectedNaviItem) && ('naviitem' + rootLevelId) != this.preSelectedNaviItem) {
                    this.makeDeselected(this.preSelectedNaviItem);
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedNaviItem = 'naviitem' + rootLevelId;
            } else if ($('subnaviitem' + rootLevelId)) {
                this.makeSelected('subnaviitem' + rootLevelId);
                if ($(this.preSelectedSubNaviItem) && ('subnaviitem' + rootLevelId) != this.preSelectedSubNaviItem) {
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedSubNaviItem = 'subnaviitem' + rootLevelId;
            }

            if ($('divNaviCenterInner'))
                $('divNaviCenterInner').innerHTML = '';
            this.levelArray = [];

            if (makeRequest == true) {
                var levelContainer = '<div id="navlevel' + this.currLevel + '" rootlevelid="' + this.rootLevelId + '" parentid="" class="navlevel busy" style="left: ' + (201 * this.currLevel - 201) + 'px"></div>';
                new Insertion.Bottom('divNaviCenterInner', levelContainer);

                if (Prototype.Browser.IE) {
                    newNavHeight = $('divNaviCenter').getHeight();
                    $$('.navlevel').each(function(elDiv) {
                        if ((newNavHeight - 42) > 0)
                            $(elDiv).setStyle({height: (newNavHeight - 42) + 'px'});
                    });
                }
                else if (Prototype.Browser.WebKit) {
                    newNavHeight = $('divNaviCenter').getHeight();
                    $$('.navlevel').each(function(elDiv) {
                        if ((newNavHeight - 40) > 0)
                            $(elDiv).setStyle({height: (newNavHeight - 40) + 'px'});
                    });
                }

                this.navigationPath = '/navigation/' + this.constContact + 'navigation';
                new Ajax.Updater('navlevel' + this.currLevel, this.constBasePath + this.navigationPath, {
                    parameters: {
                        rootLevelId: this.rootLevelId,
                        rootLevelLanguageId: ($('rootLevelLanguageId' + this.rootLevelId)) ? $F('rootLevelLanguageId' + this.rootLevelId) : '',
                        rootLevelGroupId: this.rootLevelGroupId,
                        rootLevelGroupKey: ($('rootLevelGroupKey' + this.rootLevelGroupId)) ? $F('rootLevelGroupKey' + this.rootLevelGroupId) : '',
                        rootLevelTypeId: this.rootLevelTypeId,
                        currLevel: this.currLevel
                    },
                    evalScripts: true,
                    onComplete: function() {
                        myCore.removeBusyClass('navlevel' + this.currLevel);
                        this.initItemHover();
                        this.initAddMenuHover();
                        this.levelArray.push(this.currLevel);
                    }.bind(this)
                });
            }
        }
    },
    /**
     * selectLocations
     */
    selectLocations: function(rootLevelId, rootLevelGroupId, url, makeRequest, viewType, rootLevelType) {
        if ($('importList'))
            $('importList').hide();
        if ($('exportList'))
            $('exportList').hide();

        if (typeof(viewType) == 'undefined') {
            viewType = 'tree';
        }

        if (typeof(url) != 'undefined' && url != '' && (!location.href.endsWith(url) || viewType == 'list')) {
            this.changeViewType(rootLevelId, rootLevelGroupId, url);
        } else {
            if (typeof(makeRequest) == 'undefined') {
                makeRequest = true;
            }

            this.resetGenContainer();
            this.hideCurrentFolder();

            this.currLevel = 1;
            this.navigationItemType = this.constUnit;

            if ($('naviitem' + rootLevelId)) {
                this.makeSelected('naviitem' + rootLevelId);
                if ($(this.preSelectedNaviItem) && ('naviitem' + rootLevelId) != this.preSelectedNaviItem) {
                    this.makeDeselected(this.preSelectedNaviItem);
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedNaviItem = 'naviitem' + rootLevelId;
            } else if ($('subnaviitem' + rootLevelId)) {
                this.makeSelected('subnaviitem' + rootLevelId);
                if ($(this.preSelectedSubNaviItem) && ('subnaviitem' + rootLevelId) != this.preSelectedSubNaviItem) {
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedSubNaviItem = 'subnaviitem' + rootLevelId;
            }

            this.rootLevelId = rootLevelId;
            this.rootLevelGroupId = rootLevelGroupId;
            this.rootLevelType = rootLevelType;

            if ($('divNaviCenterInner'))
                $('divNaviCenterInner').innerHTML = '';
            this.levelArray = [];

            if (makeRequest == true) {
                var levelContainer = '<div id="navlevel' + this.currLevel + '" rootlevelid="' + this.rootLevelId + '" parentid="" class="navlevel busy" style="left: ' + (201 * this.currLevel - 201) + 'px"></div>';
                new Insertion.Bottom('divNaviCenterInner', levelContainer);

                if (Prototype.Browser.IE) {
                    newNavHeight = $('divNaviCenter').getHeight();
                    $$('.navlevel').each(function(elDiv) {
                        if ((newNavHeight - 42) > 0)
                            $(elDiv).setStyle({height: (newNavHeight - 42) + 'px'});
                    });
                }
                else if (Prototype.Browser.WebKit) {
                    newNavHeight = $('divNaviCenter').getHeight();
                    $$('.navlevel').each(function(elDiv) {
                        if ((newNavHeight - 40) > 0)
                            $(elDiv).setStyle({height: (newNavHeight - 40) + 'px'});
                    });
                }

                this.navigationPath = '/navigation/' + this.constLocation + 'navigation';
                new Ajax.Updater('navlevel' + this.currLevel, this.constBasePath + this.navigationPath, {
                    parameters: {
                        rootLevelId: this.rootLevelId,
                        rootLevelLanguageId: ($('rootLevelLanguageId' + this.rootLevelId)) ? $F('rootLevelLanguageId' + this.rootLevelId) : '',
                        rootLevelGroupId: this.rootLevelGroupId,
                        rootLevelGroupKey: ($('rootLevelGroupKey' + this.rootLevelGroupId)) ? $F('rootLevelGroupKey' + this.rootLevelGroupId) : '',
                        rootLevelTypeId: this.rootLevelTypeId,
                        currLevel: this.currLevel},
                    evalScripts: true,
                    onComplete: function() {
                        myCore.removeBusyClass('navlevel' + this.currLevel);
                        this.initItemHover();
                        this.initAddMenuHover();
                        this.levelArray.push(this.currLevel);
                    }.bind(this)
                });
            }
        }
    },
    /**
     * selectMembers
     */
    selectMembers: function(rootLevelId, rootLevelGroupId, url, viewType, rootLevelType) {
        if (typeof(viewType) == 'undefined') {
            viewType = 'tree';
        }

        if (typeof(url) != 'undefined' && url != '' && (!location.href.endsWith(url) || viewType != 'list')) {
            this.changeViewType(rootLevelId, rootLevelGroupId, url);
        } else {
            this.rootLevelId = rootLevelId;
            this.rootLevelGroupId = rootLevelGroupId;
            this.rootLevelType = rootLevelType;

            $(this.genFormContainer).hide();
            $(this.genFormFunctions).hide();

            if ($('naviitem' + rootLevelId)) {
                this.makeSelected('naviitem' + rootLevelId);
                if ($(this.preSelectedNaviItem) && ('naviitem' + rootLevelId) != this.preSelectedNaviItem) {
                    this.makeDeselected(this.preSelectedNaviItem);
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedNaviItem = 'naviitem' + rootLevelId;
            } else if ($('subnaviitem' + rootLevelId)) {
                this.makeSelected('subnaviitem' + rootLevelId);
                if ($(this.preSelectedSubNaviItem) && ('subnaviitem' + rootLevelId) != this.preSelectedSubNaviItem) {
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedSubNaviItem = 'subnaviitem' + rootLevelId;
            }

            myList.sortColumn = '';
            myList.sortOrder = '';
            myList.resetSearch();
        }
    },
    /**
     * selectCompanies
     */
    selectCompanies: function(rootLevelId, rootLevelGroupId, url, viewType, rootLevelType) {
        if (typeof(viewType) == 'undefined') {
            viewType = 'tree';
        }

        if (typeof(url) != 'undefined' && url != '' && (!location.href.endsWith(url) && viewType != 'list')) {
            this.changeViewType(rootLevelId, rootLevelGroupId, url);
        } else {
            this.rootLevelId = rootLevelId;
            this.rootLevelGroupId = rootLevelGroupId;
            this.rootLevelType = rootLevelType;

            $(this.genFormContainer).hide();
            $(this.genFormFunctions).hide();

            if ($('naviitem' + rootLevelId)) {
                this.makeSelected('naviitem' + rootLevelId);
                if ($(this.preSelectedNaviItem) && ('naviitem' + rootLevelId) != this.preSelectedNaviItem) {
                    this.makeDeselected(this.preSelectedNaviItem);
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedNaviItem = 'naviitem' + rootLevelId;
            } else if ($('subnaviitem' + rootLevelId)) {
                this.makeSelected('subnaviitem' + rootLevelId);
                if ($(this.preSelectedSubNaviItem) && ('subnaviitem' + rootLevelId) != this.preSelectedSubNaviItem) {
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedSubNaviItem = 'subnaviitem' + rootLevelId;
            }

            myList.sortColumn = '';
            myList.sortOrder = '';
            myList.resetSearch();
        }
    },
    /**
     * selectSubscribers
     */
    selectSubscribers: function(rootLevelId, rootLevelGroupId, url, viewType, rootLevelType, rootLevelFilter) {
        if (!this.actionMenu || this.actionMenu.isOpen == false) {
            if ($('importList'))
                $('importList').show();
            if ($('exportList'))
                $('exportList').show();

            if (typeof(viewType) == 'undefined') {
                viewType = 'list';
            }

            if (typeof(rootLevelFilter) == 'undefined') {
                rootLevelFilter = null;
            }

            if (typeof(url) != 'undefined' && url != '' && (!location.href.replace(/#/, '').endsWith(url) || viewType != 'list')) {
                this.changeViewType(rootLevelId, rootLevelGroupId, url);
            } else {
                this.rootLevelId = rootLevelId;
                this.rootLevelGroupId = rootLevelGroupId;
                this.rootLevelType = rootLevelType;

                $(this.genFormContainer).hide();
                $(this.genFormFunctions).hide();

                if ($('naviitem' + rootLevelId)) {
                    this.makeSelected('naviitem' + rootLevelId);
                    if ($(this.preSelectedNaviItem) && ('naviitem' + rootLevelId) != this.preSelectedNaviItem) {
                        this.makeDeselected(this.preSelectedNaviItem);
                        this.makeDeselected(this.preSelectedSubNaviItem);
                    }
                    this.preSelectedNaviItem = 'naviitem' + rootLevelId;
                } else if ($('subnaviitem' + rootLevelId)) {
                    this.makeSelected('subnaviitem' + rootLevelId);
                    if ($(this.preSelectedSubNaviItem) && ('subnaviitem' + rootLevelId) != this.preSelectedSubNaviItem) {
                        this.makeDeselected(this.preSelectedSubNaviItem);
                    }
                    this.preSelectedSubNaviItem = 'subnaviitem' + rootLevelId;
                }

                new Ajax.Updater('naviitem' + this.rootLevelId + 'menu', this.constRequestSubscriberFilter, {
                    parameters: {
                        rootLevelId: this.rootLevelId
                    },
                    evalScripts: true,
                    onComplete: function() {

                    }.bind(this)
                });

                myList.sortColumn = '';
                myList.sortOrder = '';
                if (rootLevelFilter != null) {
                    myList.getListPage(0, rootLevelFilter);
                } else {
                    myList.getListPage();
                }
            }
        }
    },
    /**
     * selectCustomers
     */
    selectCustomers: function(rootLevelId, rootLevelGroupId, url, viewType, rootLevelType) {
        if (typeof(viewType) == 'undefined') {
            viewType = 'tree';
        }
        if (typeof(url) != 'undefined' && url != '' && (!location.href.endsWith(url) || viewType != 'list')) {
            this.changeViewType(rootLevelId, rootLevelGroupId, url);
        } else {
            this.rootLevelId = rootLevelId;
            this.rootLevelGroupId = rootLevelGroupId;
            this.rootLevelType = rootLevelType;

            $(this.genFormContainer).hide();
            $(this.genFormFunctions).hide();

            if ($('naviitem' + rootLevelId)) {
                this.makeSelected('naviitem' + rootLevelId);
                if ($(this.preSelectedNaviItem) && ('naviitem' + rootLevelId) != this.preSelectedNaviItem) {
                    this.makeDeselected(this.preSelectedNaviItem);
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedNaviItem = 'naviitem' + rootLevelId;
            } else if ($('subnaviitem' + rootLevelId)) {
                this.makeSelected('subnaviitem' + rootLevelId);
                if ($(this.preSelectedSubNaviItem) && ('subnaviitem' + rootLevelId) != this.preSelectedSubNaviItem) {
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedSubNaviItem = 'subnaviitem' + rootLevelId;
            }

            myList.sortColumn = '';
            myList.sortOrder = '';
            myList.resetSearch();
        }
    },
    /**
     * selectSubscribers
     */
    selectBounces: function(type, rootLevelId) {
        if (!this.actionMenu || this.actionMenu.isOpen == false) {
            if ($('importList'))
                $('importList').show();
            if ($('exportList'))
                $('exportList').show();

            if (typeof(viewType) == 'undefined') {
                viewType = 'list';
            }

            if (typeof(rootLevelFilter) == 'undefined') {
                rootLevelFilter = null;
            }

            this.rootLevelId = rootLevelId;

            $(this.genFormContainer).hide();
            $(this.genFormFunctions).hide();

            if ($('naviitem' + rootLevelId)) {
                this.makeSelected('naviitem' + rootLevelId);
                if ($(this.preSelectedNaviItem) && ('naviitem' + rootLevelId) != this.preSelectedNaviItem) {
                    this.makeDeselected(this.preSelectedNaviItem);
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedNaviItem = 'naviitem' + rootLevelId;
            } else if ($('subnaviitem' + rootLevelId)) {
                this.makeSelected('subnaviitem' + rootLevelId);
                if ($(this.preSelectedSubNaviItem) && ('subnaviitem' + rootLevelId) != this.preSelectedSubNaviItem) {
                    this.makeDeselected(this.preSelectedSubNaviItem);
                }
                this.preSelectedSubNaviItem = 'subnaviitem' + rootLevelId;
            }

            new Ajax.Updater('naviitem' + this.rootLevelId + 'menu', this.constRequestSubscriberFilter, {
                parameters: {
                    rootLevelId: this.rootLevelId
                },
                evalScripts: true,
                onComplete: function() {

                }.bind(this)
            });
            
            myList.sortColumn = '';
            myList.sortOrder = '';
            myList.getListPage(1, 0, type);
        }
    },
    /**
     * changeViewType
     */
    changeViewType: function(rootLevelId, rootLevelGroupId, url) {
        // select root level with layout change

        var myForm = document.createElement('form');
        myForm.method = 'post';
        myForm.action = url;

        var myRootLevelIdInput = document.createElement("input");
        myRootLevelIdInput.setAttribute('name', 'rootLevelId');
        myRootLevelIdInput.setAttribute('value', rootLevelId);
        myRootLevelIdInput.setAttribute('type', 'hidden');
        myForm.appendChild(myRootLevelIdInput);

        var myRootLevelGroupIdInput = document.createElement("input");
        myRootLevelGroupIdInput.setAttribute('name', 'rootLevelGroupId');
        myRootLevelGroupIdInput.setAttribute('value', rootLevelGroupId);
        myRootLevelGroupIdInput.setAttribute('type', 'hidden');
        myForm.appendChild(myRootLevelGroupIdInput);

        document.body.appendChild(myForm);
        myForm.submit();
    },
    /**
     * selectNavigationItem
     */
    selectNavigationItem: function(parentLevel, elType, itemId, categoryTypeId) {
        $(this.genFormContainer).hide();
        $(this.genFormSaveContainer).hide();

        this.categoryTypeId = (typeof(categoryTypeId) != 'undefined') ? categoryTypeId : -1;

        var level = parentLevel + 1;
        var element = elType + itemId;

        this.currLevel = level;
        this.currItemId = itemId;

        if (this.navigation[parentLevel]) {
            this.makeDeselected(this.navigation[parentLevel]);
        }

        this.navigation[parentLevel] = element;

        if (this.navigation.length > 0) {
            for (var i = 1; i <= this.navigation.length - 1; i++) {
                if (this.navigation[i] != element) {
                    this.makeParentSelected(this.navigation[i]);
                } else {
                    this.makeSelected(this.navigation[parentLevel]);
                }
            }
        }

        this.setParentFolderId(itemId);

        if (this.levelArray.indexOf(this.currLevel) == -1) {
            this.levelArray.push(this.currLevel);

            var levelContainer = '<div id="navlevel' + this.currLevel + '" rootlevelid="' + this.rootLevelId + '" parentid="' + this.getParentFolderId() + '" class="navlevel busy" style="left: ' + (201 * this.currLevel - 201) + 'px"></div>';
            new Insertion.Bottom('divNaviCenterInner', levelContainer);

        } else {

            myCore.addBusyClass('navlevel' + this.currLevel);
            $('navlevel' + this.currLevel).writeAttribute('parentid', this.getParentFolderId());

            var levelPos = this.levelArray.indexOf(this.currLevel);
            for (var i = levelPos; i < this.levelArray.length; i++) {
                if ($('navlevel' + this.levelArray[i]))
                    $('navlevel' + this.levelArray[i]).innerHTML = '';
            }

        }

        if (Prototype.Browser.IE) {
            newNavHeight = $('divNaviCenter').getHeight();
            $$('.navlevel').each(function(elDiv) {
                $(elDiv).setStyle({height: (newNavHeight - 42) + 'px'});
            });
        }
        else if (Prototype.Browser.WebKit) {
            newNavHeight = $('divNaviCenter').getHeight();
            $$('.navlevel').each(function(elDiv) {
                $(elDiv).setStyle({height: (newNavHeight - 40) + 'px'});
            });
        }

        new Ajax.Updater('navlevel' + this.currLevel, this.constBasePath + this.navigationPath, {
            parameters: {
                itemId: itemId,
                rootLevelId: this.rootLevelId,
                currLevel: this.currLevel,
                categoryTypeId: categoryTypeId
            },
            evalScripts: true,
            onComplete: function() {
                this.initItemHover();
                this.initAddMenuHover();
                myCore.removeBusyClass('navlevel' + this.currLevel);
                this.scrollNavigationBar();
            }.bind(this)
        });
    },
    /**
     * updateNavigationLevel
     * @param integer level, integer parentItemId
     */
    updateNavigationLevel: function(level, parentItemId) {

        var elementId;
        var currLevel;
        var parentId;
        var elementType = '';

        if (typeof(level) != 'undefined' && level != '') {
            currLevel = level;
        } else {
            if ($('currLevel'))
                currLevel = $F('currLevel');
        }
        this.currLevel = currLevel;

        if (typeof(parentItemId) != 'undefined' && parentItemId != '') {
            parentId = parentItemId;
        } else {
            if ($('parentId'))
                parentId = $F('parentId');
        }

        if ($('elementType') && $F('elementType') != '')
            elementType = $F('elementType');
        if ($('id') && $F('id'))
            elementId = $F('id');

        var strAjaxAction = '';
        var strParams = '';

        strAjaxAction = this.constBasePath + this.navigationPath;
        if (parentId != '' && parentId > 0) {
            strParams = 'currLevel=' + currLevel + '&rootLevelId=' + this.rootLevelId + '&itemId=' + parentId;
        } else {
            strParams = 'currLevel=' + currLevel + '&rootLevelId=' + this.rootLevelId;
        }

        if (strParams != '' && strAjaxAction != '') {
            new Ajax.Updater('navlevel' + currLevel, strAjaxAction, {
                parameters: strParams,
                evalScripts: true,
                onComplete: function() {
                    new Effect.Highlight('navlevel' + currLevel, {startcolor: '#ffd300', endcolor: '#ffffff'});

                    if (elementType != '' && elementId != '' && $(elementType + elementId)) {
                        if (this.navigation[currLevel]) {
                            this.makeDeselected(this.navigation[currLevel]);
                        }
                        this.navigation[currLevel] = elementType + elementId;

                        if (this.navigation.length > 0) {
                            for (var i = 1; i <= this.navigation.length - 1; i++) {
                                if (this.navigation[i] != elementType + elementId) {
                                    if (currLevel < i) {
                                        this.makeDeselected(this.navigation[i]);
                                    } else {
                                        this.makeParentSelected(this.navigation[i]);
                                    }
                                } else {
                                    this.makeSelected(this.navigation[currLevel]);
                                }
                            }
                        }
                        if (this.levelArray.indexOf(currLevel) != -1 && elType == this.constPage) {
                            var levelPos = this.levelArray.indexOf(currLevel) + 1;
                            for (var i = levelPos; i < this.levelArray.length; i++) {
                                if ($('navlevel' + this.levelArray[i]))
                                    $('navlevel' + this.levelArray[i]).innerHTML = '';
                            }
                        }
                        if (elementType == this.constFolder) {
                            this.selectItem();
                        }
                    }
                    this.initItemHover();
                    this.initAddMenuHover();
                }.bind(this)
            });
        }
    },
    /**
     * getRootLevelActions
     */
    getRootLevelActions: function(rootLevelId, rootLevelTypeId, gear) {
        if (!this.actionMenu) {
            this.actionMenu = new Massiveart.UI.Menu.Overlap({items: ['<a href="#" onclick="myNavigation.getFilterAddOverlay(' + rootLevelId + ', ' + rootLevelTypeId + '); return false;">' + myCore.translate.Add_new_Filter + '</a>',
                    '<a href="#" onclick="myNavigation.getFilterOverviewOverlay(' + rootLevelId + ', ' + rootLevelTypeId + '); return false;">' + myCore.translate.Edit_Filter + '</a>']});
        }

        this.actionMenu.open(gear.viewportOffset());
    },
    /**
     * getFilterAddOverlay
     */
    getFilterAddOverlay: function(rootLevelId, rootLevelTypeId) {
        if ($('overlayBlack75'))
            $('overlayBlack75').show();
        if ($('overlayFilterWrapper')) {
            myCore.putCenter('overlayFilterWrapper');
            $('overlayFilterWrapper').show();
            $('buttonfilterdelete').hide();

            if ($('overlayFilterContent')) {
                myCore.addBusyClass('overlayFilterContent');
                new Ajax.Updater('overlayFilterContent', '/zoolu/contacts/overlay/listfilter', {
                    parameters: {
                        rootLevelId: rootLevelId,
                        rootLevelTypeId: rootLevelTypeId
                    },
                    evalScripts: true,
                    onComplete: function() {
                        myCore.removeBusyClass('overlayFilterContent');
                    }.bind(this)
                });
            }
        }
    },
    /**
     * getFilterOverviewOverlay
     */
    getFilterOverviewOverlay: function(rootLevelId) {
        if ($('overlayBlack75'))
            $('overlayBlack75').show();
        if ($('overlayGenContentWrapper')) {
            myCore.putCenter('overlayGenContentWrapper');
            $('overlayGenContentWrapper').show();

            if ($('overlayGenContent')) {
                myCore.addBusyClass('overlayGenContent');
                new Ajax.Updater('overlayGenContent', '/zoolu/contacts/overlay/overviewfilter', {
                    parameters: {
                        rootLevelId: rootLevelId
                    },
                    evalScripts: true,
                    onComplete: function() {
                        myCore.putCenter('overlayGenContentWrapper');
                        myCore.removeBusyClass('overlayGenContent');
                    }.bind(this)
                });
            }
        }
    },
    /**
     * getFilterEditOverlay
     */
    getFilterEditOverlay: function(rootLevelId, rootLevelTypeId, rootLevelFilterId) {
        $('overlayGenContentWrapper').hide();
        if ($('overlayBlack75'))
            $('overlayBlack75').show();
        if ($('overlayFilterWrapper')) {
            myCore.putCenter('overlayFilterWrapper');
            $('overlayFilterWrapper').show();
            $('buttonfilterdelete').show();

            if ($('overlayFilterContent')) {
                myCore.addBusyClass('overlayFilterContent');
                new Ajax.Updater('overlayFilterContent', '/zoolu/contacts/overlay/listfilter', {
                    parameters: {
                        rootLevelId: rootLevelId,
                        rootLevelTypeId: rootLevelTypeId,
                        rootLevelFilterId: rootLevelFilterId
                    },
                    evalScripts: true,
                    onComplete: function() {
                        myCore.removeBusyClass('overlayFilterContent');
                        myFilter.addLine();
                    }.bind(this)
                });
            }
        }
    }
});
