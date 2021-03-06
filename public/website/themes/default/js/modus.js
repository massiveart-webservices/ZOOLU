var Web = Web || {};

(function(window, Web) {
    Web.Modus = {
        showModusContainer: function() {
            var toolbar = document.getElementById('zoolu-modus-toolbar');
            var show_toolbar = document.getElementById('zoolu-show-modus-toolbar');
            toolbar.style.top = '0px';
            show_toolbar.style.top = '-40px';
        },

        hideModusContainer: function() {
            var toolbar = document.getElementById('zoolu-modus-toolbar');
            var show_toolbar = document.getElementById('zoolu-show-modus-toolbar');
            toolbar.style.top = '-40px';
            show_toolbar.style.top = '0px';
        },
            
        /**
         * expireCache
         */
        expireCache: function(elId) {
            this.addBusyClass(elId, true);
            $.ajax({
                url: "/zoolu-website/content/expire-cache",
                type: "GET",
                success: function() {
                    this.removeBusyClass(elId, true);
                    window.location.reload();
                }.bind(this)
            });
        },

        /**
         * changeTestMode
         */
        changeTestMode: function(status) {
            $.ajax({
                url: "/zoolu-website/testmode/change",
                type: "GET",
                data: {TestMode: status},
                success: function() {
                    window.location.reload();
                }.bind(this)
            });
        },

        /**
         * addBusyClass
         */
        addBusyClass: function(busyElement, blnDisplay) {
            if ($(busyElement)) {
                if (blnDisplay) {
                    $(busyElement).addClass('busy').show();
                }
            }
        },

        /**
         * removeBusyClass
         */
        removeBusyClass: function(busyElement, blnDisplay) {
            if ($(busyElement)) {
                if (blnDisplay) {
                    $(busyElement).removeClass('busy').hide();
                }
            }
        }
    };
})(window, Web);
