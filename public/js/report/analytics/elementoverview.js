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

opendxp.registerNS("opendxp.bundle.googlemarketing.report.analytics.elementoverview");
/**
 * @private
 */
opendxp.bundle.googlemarketing.report.analytics.elementoverview = Class.create(opendxp.bundle.customreports.abstract, {

    matchType: function (type) {
        var types = ["document_page","global"];
        if (opendxp.bundle.customreports.abstract.prototype.matchTypeValidate(type, types)
            && opendxp.settings.google_analytics_enabled) {
            return true;
        }
        return false;
    },

    getName: function () {
        return "overview";
    },

    getIconCls: function () {
        return "opendxp_icon_analytics";
    },

    getPanel: function () {

        this.loadCounter = 0;
        this.initStores();

        var panel = new Ext.Panel({
            title: t("visitor_overview"),
            layout: "border",
            height: 680,
            border: false,
            items: [this.getFilterPanel(),this.getContentPanel()]
        });

        panel.on("afterrender", function (panel) {
            this.loadMask = new Ext.LoadMask({
                target: panel,
                msg: t("please_wait")
            });
            this.loadMask.show();

            this.sourceStore.load();
            this.summaryStore.load();
            this.chartStore.load({
                params: {
                    metric: "pageviews"
                }
            });

        }.bind(this));

        return panel;
    },

    getContentPanel: function () {
        var self = this;

        var summary = new Ext.grid.GridPanel({
            store: this.summaryStore,
            flex: 1,
            manageHeight: false,
            autoScroll: true,
            hideHeaders: true,
            columns: [
                {dataIndex: 'chart', sortable: false, renderer: function (d) {
                        return '<img src="' + d + '" />';
                    }},
                {dataIndex: 'value', sortable: false, renderer: function (d) {
                        return '<span class="opendxp_analytics_gridvalue">' + d + '</span>';
                    }},
                {flex: 1, dataIndex: 'label', sortable: false, renderer: function (d) {
                        return '<span class="opendxp_analytics_gridlabel">' + t(d) + '</span>';
                    }}
            ],
            stripeRows: true
        });

        summary.on("rowclick", function (grid, record, tr, rowIndex, e, eOpts ) {
            var record = grid.getStore().getAt(rowIndex);

            var values = this.filterPanel.getForm().getFieldValues();
            values.metric = record.data.metric;

            this.chartStore.load({
                params: values
            });
        }.bind(this));


        var panel = new Ext.Panel({
            region: "center",
            autoScroll: true,
            items: [{
                height: 350,
                items: [{
                    xtype: 'cartesian',
                    store: this.chartStore,
                    height: 350,
                    interactions: 'itemhighlight',
                    axes: [{
                        type: 'numeric',
                        fields: ['data' ],
                        position: 'left',
                        grid: true,
                        minimum: 0
                    }
                        , {
                            type: 'category',
                            fields: 'datetext',
                            position: 'bottom',
                            grid: true,
                            label: {
                                rotate: {
                                    degrees: -45
                                }
                            }
                        }
                    ],
                    series: [
                        {
                            type: 'line',
                            xField: 'datetext',
                            yField: 'data',
                            marker: {
                                radius: 4
                            },
                            style: {
                                lineWidth: 2,
                                strokeStyle: "#01841c"
                            },
                            tooltip: {
                                trackMouse: true,
                                style: 'background: #00bfff',
                                renderer: function(tooltip, storeItem, item) {
                                    tooltip.setHtml(storeItem.get('datetext') + ': ' + storeItem.get(item.series.getYField()));
                                }
                            }
                        }
                    ]
                }]
            },{
                autoScroll: true,
                items: [{
                    layout:'hbox',
                    border: false,
                    items: [summary,
                        {
                            xtype: 'polar',
                            width: '100%',
                            height: 300,
                            store: this.sourceStore,
                            flex: 1,
                            scrollable: false,
                            series: [{
                                type: 'pie',
                                angleField: 'pageviews',
                                label: {
                                    field: 'source',
                                    display: 'source',
                                    calloutLine: {
                                        length: 60,
                                        width: 3
                                    }
                                },
                                highlight: true,
                                tooltip: {
                                    trackMouse: true,
                                    renderer: function(tooltip, storeItem, item) {
                                        var views = storeItem.get('pageviews');
                                        var total = self.sourceStore.sum('pageviews');
                                        var percent = Math.round(views / total * 1000) / 10;
                                        tooltip.setHtml(storeItem.get('source') + ' ' + views + ' (' + percent + '%)');
                                    }
                                }
                            }],
                            legend: {
                                docked: 'bottom',
                                border: 0
                            }
                        }
                    ]
                }]
            }]
        });

        return panel;
    },

    getFilterPanel: function () {

        if (!this.filterPanel) {


            var today = new Date();
            var fromDate = new Date(today.getTime() - (86400000 * 31));

            this.filterPanel = new Ext.FormPanel({
                region: 'north',
                defaults: {
                    labelWidth: 40
                },
                height: 40,
                layout: 'hbox',
                bodyStyle: 'padding:7px 0 0 5px',
                items: [
                    {
                        xtype: "datefield",
                        fieldLabel: t('from'),
                        name: 'dateFrom',
                        value: fromDate,
                        cls: "opendxp_analytics_filter_form_item"
                    }
                    ,
                    {
                        xtype: "datefield",
                        fieldLabel: t('to'),
                        name: 'dateTo',
                        value: today,
                        cls: "opendxp_analytics_filter_form_item"
                    },{
                        xtype: "combo",
                        store: opendxp.globalmanager.get("sites"),
                        valueField: "id",
                        displayField: "domain",
                        triggerAction: "all",
                        name: "site",
                        fieldLabel: t("site"),
                        cls: "opendxp_analytics_filter_form_item"
                    },{
                        xtype: "button",
                        text: t("apply"),
                        iconCls: "opendxp_icon_save",
                        cls: "opendxp_analytics_filter_form_item",
                        handler: function () {

                            var values = this.filterPanel.getForm().getFieldValues();

                            this.sourceStore.load({
                                params: values
                            });
                            this.summaryStore.load({
                                params: values
                            });

                            values.metric = "pageviews";
                            this.chartStore.load({
                                params: values
                            });
                        }.bind(this)
                    }
                ]
            });
        }

        return this.filterPanel;
    },

    initStores: function () {

        var path = "";
        var id = "";
        var type = "";
        if (this.type == "document_page") {
            id = this.reference.id;
            path = this.reference.data.path + this.reference.data.key;
            type = "document";
        }

        this.chartStore = new Ext.data.JsonStore({
            proxy: {
                type: 'ajax',
                url: Routing.generate('opendxp_bundle_googlemarketing_reports_analytics_chartmetricdata'),
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                },
                extraParams: {
                    type: type,
                    id: id,
                    path: path,
                    dataField: "data"
                }
            },
            fields: ["timestamp","datetext","data"],
            listeners: {
                load: this.storeFinished.bind(this),
                beforeload: this.storeStart.bind(this)
            }
        });

        this.summaryStore = new Ext.data.Store({
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: Routing.generate('opendxp_bundle_googlemarketing_reports_analytics_summary'),
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                },
                extraParams: {
                    type: type,
                    id: id,
                    path: path
                }
            },
            fields: ['chart','value',"label","metric"],
            listeners: {
                load: this.storeFinished.bind(this),
                beforeload: this.storeStart.bind(this)
            }
        });

        this.sourceStore = new Ext.data.Store({
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: Routing.generate('opendxp_bundle_googlemarketing_reports_analytics_source'),
                reader: {
                    type: 'json',
                    rootProperty: 'data'
                },
                extraParams: {
                    type: type,
                    id: id,
                    path: path
                }
            },
            fields: ['source',{name:'pageviews',type:'integer'}],
            listeners: {
                load: this.storeFinished.bind(this),
                beforeload: this.storeStart.bind(this)
            }
        });
    },

    storeFinished: function () {
        this.loadCounter--;
        if(this.loadCounter < 1) {
            this.loadMask.hide();
        }
    },

    storeStart: function () {
        if(this.loadCounter < 1) {
            this.loadMask.show();
        }
        this.loadCounter++;
    }
});

// add to report broker
if (opendxp.bundle.customreports && opendxp.bundle.customreports.broker) {
    opendxp.bundle.customreports.broker.addGroup("analytics", "google_analytics", "opendxp_icon_analytics");
    opendxp.bundle.customreports.broker.addReport(opendxp.bundle.googlemarketing.report.analytics.elementoverview, "analytics");
}
