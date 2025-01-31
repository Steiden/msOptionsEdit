msOptionsEdit.window.ShowItem = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        title: _('msoptionsedit_item_show'),
        width: 800,
        autoHeight: true,
        baseParams: {},
        layout: 'anchor',
        fields: this.getFields(config),
        buttons: [{
            text: _('close'),
            handler: function () {
                this.close();
            },
            scope: this
        }]
    });
    msOptionsEdit.window.ShowItem.superclass.constructor.call(this, config);
};
Ext.extend(msOptionsEdit.window.ShowItem, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('msoptionsedit_item_product_id'),
            name: 'product_id',
            id: config.id + '-product_id',
            anchor: '100%',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('msoptionsedit_item_option_key'),
            name: 'option_key',
            id: config.id + '-option_key',
            anchor: '100%',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('msoptionsedit_item_old_value'),
            name: 'old_value',
            id: config.id + '-old_value',
            anchor: '100%',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('msoptionsedit_item_new_value'),
            name: 'new_value',
            id: config.id + '-new_value',
            anchor: '100%',
        }, {
            xtype: 'statictextfield',
            fieldLabel: _('msoptionsedit_item_createdon'),
            name: 'createdon',
            id: config.id + '-createdon',
            anchor: '100%',
        }];
    },

    loadDropZones: function () {
    }
});
Ext.reg('msoptionsedit-item-window-show', msOptionsEdit.window.ShowItem);