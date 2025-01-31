msOptionsEdit.window.CreateAction = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-action-window-create';
    }
    Ext.applyIf(config, {
        title: _('msoptionsedit_action_create'),
        width: 550,
        autoHeight: true,
        url: msOptionsEdit.config.connector_url,
        action: 'mgr/action/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    msOptionsEdit.window.CreateAction.superclass.constructor.call(this, config);
};
Ext.extend(msOptionsEdit.window.CreateAction, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('msoptionsedit_action_key'),
            name: 'key',
            id: config.id + '-key',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: _('msoptionsedit_action_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('msoptionsedit-action-window-create', msOptionsEdit.window.CreateAction);


msOptionsEdit.window.UpdateItem = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-action-window-update';
    }
    Ext.applyIf(config, {
        title: _('msoptionsedit_action_update'),
        width: 550,
        autoHeight: true,
        url: msOptionsEdit.config.connector_url,
        action: 'mgr/action/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    msOptionsEdit.window.UpdateItem.superclass.constructor.call(this, config);
};
Ext.extend(msOptionsEdit.window.UpdateItem, MODx.Window, {

    getFields: function (config) {
        return [
            {
                xtype: 'hidden',
                name: 'id',
                id: config.id + '-id',
            },
            {
                xtype: 'textfield',
                fieldLabel: _('msoptionsedit_action_key'),
                name: 'key',
                id: config.id + '-key',
                anchor: '99%',
                allowBlank: false,
            }, {
                xtype: 'textfield',
                fieldLabel: _('msoptionsedit_action_name'),
                name: 'name',
                id: config.id + '-name',
                anchor: '99%',
                allowBlank: false,
            }];
    },

    loadDropZones: function () {
    }
});

Ext.reg('msoptionsedit-action-window-update', msOptionsEdit.window.UpdateItem);
