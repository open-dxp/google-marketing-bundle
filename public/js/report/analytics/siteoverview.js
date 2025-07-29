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

opendxp.registerNS("opendxp.bundle.googlemarketing.report.analytics.overview");
/**
 * @private
 */
opendxp.bundle.googlemarketing.report.analytics.overview = Class.create(opendxp.bundle.customreports.abstract, {

    matchType: function (type) {

        // deactivate temporary
        return;
// // commented this out, otherwise JSLint would complain
//
//        var types = ["global"];
//        if (opendxp.bundle.customreports.abstract.prototype.matchTypeValidate(type, types)
//                                                            && opendxp.settings.google_analytics_enabled) {
//            return true;
//        }
//        return false;
    },

    getName: function () {
        return "overview";
    },

    getIconCls: function () {
        return "opendxp_icon_analytics";
    },



    getPanel: function () {

        this.site = "default";
        this.iframeId = uniqid();


        var panel = new Ext.Panel({
            title: t("visitor_overview"),
            layout: "border",
            border: false,
            items: [this.getFilterPanel(),this.getFramePanel()]
        });

        var containerConfig = {
            border: false,
            layout: "fit",
            items: [panel]
        };

        // check for sites
        var sites = opendxp.globalmanager.get("sites");
        if (sites.getTotalCount() > 0) {
            containerConfig.tbar = ["->",{
                xtype: 'tbtext',
                text: t("select_site")
            },{
                xtype: "combo",
                store: sites,
                valueField: "id",
                displayField: "domain",
                triggerAction: "all",
                listeners: {
                    "select": function (el) {
                        this.site = el.getValue();
                        this.setFrameUrl();
                    }.bind(this)
                }
            }];
        }


        var container = new Ext.Panel(containerConfig);

        return container;
    },

    getFramePanel: function () {

        if (!this.framePanel) {
            this.framePanel = new Ext.Panel({
                listeners: {
                    "resize": this.framePanelResize.bind(this)
                },
                bodyCls: "opendxp_overflow_scrolling",
                html: '<iframe src="about:blank" frameborder="0" id="' + this.iframeId + '" style="width: 100%;"></iframe>',
                region: "center"
            });

            this.framePanel.on("afterrender", this.setFrameUrl.bind(this));
        }
        return this.framePanel;
    },

    framePanelResize: function (el, width, height, rWidth, rHeight) {
        Ext.get(this.iframeId).setStyle({
            height: (height) + "px"
        });
    },

    getFilterPanel: function () {

        if (!this.filterPanel) {


            var today = new Date();
            var fromDate = new Date(today.getTime() - (86400000 * 31));


            this.filterPanel = new Ext.FormPanel({
                region: 'north',
                labelWidth: 40,
                height: 40,
                layout: 'form',
                bodyStyle: 'padding:7px 0 0 5px',
                items: [
                    {
                        xtype: "datefield",
                        fieldLabel: t('from'),
                        name: 'datefrom',
                        value: fromDate,
                        cls: "opendxp_analytics_filter_form_item"
                    },
                    {
                        xtype: "datefield",
                        fieldLabel: t('to'),
                        name: 'dateto',
                        value: today,
                        cls: "opendxp_analytics_filter_form_item"
                    },
                    {
                        xtype: "button",
                        text: t("apply"),
                        cls: "opendxp_analytics_filter_form_item",
                        handler: this.setFrameUrl.bind(this)
                    }
                ]
            });
        }

        return this.filterPanel;
    },

    setFrameUrl: function () {
        var values = this.getFilterPanel().getForm().getFieldValues();

        var queryString = {};
        queryString.dateFrom = values.datefrom.getTime() / 1000;
        queryString.dateTo = values.dateto.getTime() / 1000;
        queryString.site = this.site;

        Ext.get(this.iframeId).dom.setAttribute("src",
            Routing.getBaseUrl() + "/admin/reports/analytics/siteoverview?" + Ext.Object.toQueryString(queryString));
    }
});

// add to report broker
if (opendxp.bundle.customreports && opendxp.bundle.customreports.broker) {
    opendxp.bundle.customreports.broker.addGroup("analytics", "google_analytics", "opendxp_icon_analytics");
    opendxp.bundle.customreports.broker.addReport(opendxp.bundle.googlemarketing.report.analytics.overview, "analytics");
}
