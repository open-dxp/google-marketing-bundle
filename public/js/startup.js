opendxp.registerNS('opendxp.bundle.googlemarketing.startup');

opendxp.bundle.googlemarketing.startup = Class.create({
    initialize: function () {
        document.addEventListener(opendxp.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (event) {
        const menu = event.detail.menu;
        const user = opendxp.globalmanager.get('user');
        const perspectiveCfg = opendxp.globalmanager.get("perspective");

        if (menu.marketing && perspectiveCfg.inToolbar("settings.marketingReports")
            && user.isAllowed("google_marketing")) {
            menu.marketing.items.push({
                text: t("marketing_settings"),
                iconCls: "opendxp_nav_icon_marketing_settings",
                itemId: 'opendxp_menu_marketing_settings',
                handler: this.marketingSettings,
                priority: 30
            });
        }
    },

    marketingSettings: function () {
        try {
            opendxp.globalmanager.get("bundle_marketing_settings").activate();
        }
        catch (e) {
            opendxp.globalmanager.add("bundle_marketing_settings", new opendxp.bundle.googlemarketing.settings());
        }
    }
});

var opendxpBundleGoogleMarketing = new opendxp.bundle.googlemarketing.startup();
