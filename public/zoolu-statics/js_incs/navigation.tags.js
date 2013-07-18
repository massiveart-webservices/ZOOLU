/**
 * navigation.members.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-03-02: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation.Tags = Class.create(Massiveart.Navigation, {

    initialize: function ($super) {
        // initialize superclass
        $super();

        this.constBasePath = '/zoolu/tags';
        this.rootLevelType = '';
    },

    /**
     * getModuleRootLevelList
     * @param integer rootLevelId
     */
    getModuleRootLevelList: function (rootLevelId, rootLevelType) {

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
        myList.getListPage();
    },

    /**
     * selectTags
     */
    selectTags: function (rootLevelId, rootLevelGroupId, url, viewType, rootLevelType) {
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
     * getAddForm
     */
    getAddForm: function () {

        $(this.genListContainer).hide();
        $(this.genListFunctions).hide();

        if ($('buttondelete')) $('buttondelete').hide();

        new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/addform', {
            parameters: {
                rootLevelId: this.rootLevelId
            },
            evalScripts: true,
            onComplete: function () {
                $(this.genFormContainer).show();
                $(this.genFormFunctions).show();
                $(this.genFormContainer).scrollTo($('widgetfunctions'));
            }.bind(this)
        });
    },

    /**
     * getEditForm
     */
    getEditForm: function (itemId) {

        $(this.genListContainer).hide();
        $(this.genListFunctions).hide();

        if ($('buttondelete')) $('buttondelete').show();

        new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/editform', {
            parameters: {
                rootLevelId: this.rootLevelId,
                id: itemId
            },
            evalScripts: true,
            onComplete: function () {
                $(this.genFormContainer).show();
                $(this.genFormFunctions).show();
                $(this.genFormContainer).scrollTo($('widgetfunctions'));

                // load medias
                myForm.loadFileFieldsContent('media');
            }.bind(this)
        });
    }
});