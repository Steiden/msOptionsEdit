msOptionsEdit.window.CreateOption = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-option-window-create';
    }
    Ext.applyIf(config, {
        title: _('msoptionsedit_option_create'),
        width: 770,
        height: 500,
        autoHeight: false,
        url: msOptionsEdit.config.connector_url,
        action: 'mgr/option/create',
        // layout: 'anchor',
        items: this.getTabs(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    msOptionsEdit.window.CreateOption.superclass.constructor.call(this, config);
};
Ext.extend(msOptionsEdit.window.CreateOption, MODx.Window, {

    getTabs: function (config) {
        return [{
            xtype: 'modx-tabs',
            defaults: { border: false, autoHeight: true },
            activeTab: 0,
            items: [{
                title: _('msoptionsedit_option_main'),
                layout: 'form',
                items: this.getFields(config),
            }, {
                title: _('msoptionsedit_option_products'),
                layout: 'anchor',
                items: [{
                    xtype: 'msoptionsedit-grid-product-options',
                    id: config.id + '-product-options',
                    anchor: '99% 99%',
                    // baseParams: {
                    //     option_id: config.record ? config.record.id : 0
                    // }
                }]
            }]
        }];
    },

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('msoptionsedit_option_key'),
            name: 'key',
            id: config.id + '-key',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: _('msoptionsedit_option_caption'),
            name: 'caption',
            id: config.id + '-caption',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('msoptionsedit_option_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%',
            allowBlank: true,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('msoptionsedit-option-window-create', msOptionsEdit.window.CreateOption);

msOptionsEdit.window.UpdateOption = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-option-window-update';
    }
    Ext.applyIf(config, {
        title: _('msoptionsedit_option_update'),
        width: 750, // Увеличили ширину для удобства
        height: 500, // Добавили высоту для второй вкладки
        autoHeight: false,
        url: msOptionsEdit.config.connector_url,
        action: 'mgr/option/update',
        layout: 'anchor',
        items: this.getTabs(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    msOptionsEdit.window.UpdateOption.superclass.constructor.call(this, config);
};
Ext.extend(msOptionsEdit.window.UpdateOption, MODx.Window, {

    getTabs: function (config) {
        return [{
            xtype: 'modx-tabs',
            defaults: { border: false, autoHeight: true },
            activeTab: 0,
            items: [
                {
                    title: _('msoptionsedit_option_main'),
                    layout: 'form',
                    items: this.getFields(config),
                },
                {
                    title: _('msoptionsedit_option_products'),
                    layout: 'anchor',
                    items: [{
                        xtype: 'msoptionsedit-grid-product-options',
                        id: config.id + '-product-options',
                        anchor: '99% 99%',
                        baseParams: {
                            id: config?.record?.id
                        }
                    }]
                }
            ]
        }];
    },

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('msoptionsedit_option_key'),
            name: 'key',
            id: config.id + '-key',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: _('msoptionsedit_option_caption'),
            name: 'caption',
            id: config.id + '-caption',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('msoptionsedit_option_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%',
            allowBlank: true,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('msoptionsedit-option-window-update', msOptionsEdit.window.UpdateOption);
