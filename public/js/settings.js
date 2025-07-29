/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.ch)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

opendxp.registerNS("opendxp.bundle.googlemarketing.settings");
/**
 * @private
 */
opendxp.bundle.googlemarketing.settings = Class.create({

    initialize: function () {

        this.getData();
    },

    getData: function () {
        Ext.Ajax.request({
            url: Routing.generate('opendxp_bundle_googlemarketing_settings_get'),
            success: function (response) {

                this.data = Ext.decode(response.responseText);
                this.getTabPanel();

            }.bind(this)
        });
    },

    getValue: function (key) {

        var nk = key.split("\.");
        var current = this.data.values;

        for (var i = 0; i < nk.length; i++) {
            if (current[nk[i]] || 'boolean' === typeof current[nk[i]]) {
                current = current[nk[i]];
            }
        }

        if (typeof current != "object" && typeof current != "array" && typeof current != "function") {
            return current;
        }

        return "";
    },

    getTabPanel: function () {

        this.moduleSettings = [];

        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "opendxp_marketing_settings",
                title: t("marketing_settings"),
                iconCls: "opendxp_icon_system",
                border: false,
                layout: "fit",
                closable:true,
                bodyStyle: "padding: 10px;"

            });

            var tabPanel = Ext.getCmp("opendxp_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("opendxp_marketing_settings");


            this.panel.on("destroy", function () {
                opendxp.globalmanager.remove("bundle_marketing_settings");
            }.bind(this));

            try {
                var broker = opendxp.bundle.googlemarketing.settings.broker;
                var settingsContainerItems = [];
                var moduleSetting,moduleClass;

                for (var i = 0; i < broker.length; i++) {

                    moduleClass = eval(broker[i]);
                    moduleSetting = new moduleClass(this);

                    settingsContainerItems.push(moduleSetting.getLayout());
                    this.moduleSettings.push(moduleSetting);
                }

                this.settingsContainer = new Ext.TabPanel({
                    activeTab: 0,
                    deferredRender:false,
                    enableTabScroll:true,
                    items: settingsContainerItems,
                    buttons: [
                        {
                            text: t("save"),
                            handler: this.save.bind(this),
                            iconCls: "opendxp_icon_accept"
                        }
                    ]
                });

                this.panel.add(this.settingsContainer);

                this.panel.updateLayout();
                opendxp.layout.refresh();
            }
            catch (e) {
                console.log(e);
            }
        }

        return this.panel;
    },

    activate: function () {
        var tabPanel = Ext.getCmp("opendxp_panel_tabs");
        tabPanel.setActiveItem("opendxp_reports_settings");
    },

    save: function () {
        var values = {};

        for (var i = 0; i < this.moduleSettings.length; i++) {
            try {
                values[this.moduleSettings[i].getKey()] = this.moduleSettings[i].getValues();
            }
            catch (e) {
                console.log("unable to get configuration for report");
                console.log(e);
            }
        }

        Ext.Ajax.request({
            url: Routing.generate('opendxp_bundle_googlemarketing_settings_save'),
            method: "PUT",
            params: {
                data: Ext.encode(values)
            },
            success: function (response) {
                try{
                    var res = Ext.decode(response.responseText);
                    if (res.success) {
                        opendxp.helpers.showNotification(t("success"), t("saved_successfully"), "success");

                        Ext.MessageBox.confirm(t("info"), t("reload_opendxp_changes"), function (buttonValue) {
                            if (buttonValue == "yes") {
                                window.location.reload();
                            }
                        }.bind(this));
                    } else {
                        opendxp.helpers.showNotification(t("error"), t("saving_failed"), "error",t(res.message));
                    }
                } catch(e){
                    opendxp.helpers.showNotification(t("error"), t("saving_failed"), "error");
                }
            }.bind(this)
        });
    }

});

opendxp.bundle.googlemarketing.settings.broker = [];
