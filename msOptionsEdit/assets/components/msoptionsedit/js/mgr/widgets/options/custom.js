Ext.override(miniShop2.window.CreateOption, {
    getParentFields: miniShop2.window.CreateOption.prototype.getFields,

    getFields: function (config) {
        var parentFields = this.getParentFields.call(this, config);

        return [{
            xtype: 'modx-tabs',
            defaults: { border: false, autoHeight: true },
            activeTab: 0,
            items: [{
                title: _('msoptionsedit_option_main'),
                layout: 'form',
                items: parentFields,
            }, {
                title: _('msoptionsedit_option_values'),
                layout: 'anchor',
                items: [{
                    xtype:'msoptionsedit-grid-product-options',
                    anchor: '100%',
                    baseParams: {
                        option_id: config.record.id
                    }
                }]
            }]
        }];
    }
});
