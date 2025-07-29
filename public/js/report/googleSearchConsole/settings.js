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

opendxp.registerNS("opendxp.bundle.googlemarketing.report.google_search_console.settings");

/**
 * @private
 */
opendxp.bundle.googlemarketing.report.google_search_console.settings = Class.create({

    initialize: function (parent) {
        this.parent = parent;
    },

    getKey: function () {
        return "google_search_console";
    },

    getLayout: function () {

        this.panel = new Ext.FormPanel({
            title: "Google Search Console",
            bodyStyle: "padding: 10px;",
            autoScroll: true,
            items: [
                {
                    xtype: "displayfield",
                    width: 670,
                    hideLabel: true,
                    value: "&nbsp;<br />" + t("search_console_settings_description"),
                    cls: "opendxp_extra_label"
                },
                {
                    xtype: "panel",
                    style: "padding:30px 0 0 0;",
                    border: false,
                    items: this.getConfigurations()
                }
            ]
        });

        return this.panel;
    },

    getConfigurations: function () {

        this.configCount = 0;
        var configs = [];
        var sites = opendxp.globalmanager.get("sites");

        sites.each(function (record) {
            var id = record.data.id;
            if (id == "default") {
                key = "default";
            } else {
                key = "site_" + id;
            }
            configs.push(this.getConfiguration(key, record.data.domain, id));
        }, this);


        return configs;
    },

    getConfiguration: function (key, name, id) {
        var config = {
            xtype: "fieldset",
            title: name,
            items: [
                {
                    xtype: "textfield",
                    fieldLabel: t("verification_filename_text") + " (google1d765d927ceexxxx.html)",
                    name: "verification",
                    labelWidth: 250,
                    width: 650,
                    value: this.parent.getValue("google_search_console.sites." + key + ".verification"),
                    id: "report_settings_google_search_console_verification_" + id
                }
            ]
        };

        return config;
    },

    getValues: function () {

        var sites = opendxp.globalmanager.get("sites");
        var sitesData = {};

        sites.each(function (record) {
            var id = record.get("id");
            if (id == "default") {
                key = "default";
            } else {
                key = "site_" + id;
            }

            sitesData[key] = {
                verification: Ext.getCmp("report_settings_google_search_console_verification_" + id).getValue()
            };
        }, this);

        var values = {
            sites: sitesData
        };

        return values;
    }
});


opendxp.bundle.googlemarketing.settings.broker.push("opendxp.bundle.googlemarketing.report.google_search_console.settings");
