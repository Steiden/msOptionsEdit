msOptionEdit.window.CreateItem = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsmergeaction-item-window-create';
    }
    Ext.applyIf(config, {
        title: _('msoptionsmergeaction_item_create'),
        width: 550,
        autoHeight: true,
        url: msOptionEdit.config.connector_url,
        action: 'mgr/option/action/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    msOptionEdit.window.CreateAction.superclass.constructor.call(this, config);
};
Ext.extend(msOptionEdit.window.CreateAction, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('msoptionsmergeaction_item_key'),
            name: 'key',
            id: config.id + '-key',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: _('msoptionsmergeaction_item_caption'),
            name: 'caption',
            id: config.id + '-caption',
            anchor: '99%',
            allowBlank: false,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('msoptionsmergeaction-item-window-create', msOptionEdit.window.CreateAction);


msOptionEdit.window.UpdateAction = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsmergeaction-item-window-update';
    }
    Ext.applyIf(config, {
        title: _('msoptionsmergeaction_item_update'),
        width: 550,
        autoHeight: true,
        url: msOptionEdit.config.connector_url,
        action: 'mgr/option/action/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    msOptionEdit.window.UpdateAction.superclass.constructor.call(this, config);
};
Ext.extend(msOptionEdit.window.UpdateAction, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('msoptionsmergeaction_item_key'),
            name: 'key',
            id: config.id + '-key',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: _('msoptionsmergeaction_item_caption'),
            name: 'caption',
            id: config.id + '-caption',
            anchor: '99%',
            allowBlank: false,
        }];
    },

    loadDropZones: function () {
    }
});

Ext.reg('msoptionsmergeaction-item-window-update', msOptionEdit.window.UpdateAction);