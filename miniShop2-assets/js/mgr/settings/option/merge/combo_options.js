miniShop2.combo.ComboOptions = function(config) {
    config = config || {};
    if (!config.id) {
        config.id = 'minishop2-combo-options';
    }

    Ext.applyIf(config, {
        store: new Ext.data.JsonStore({
            url: miniShop2.config.connector_url,
            baseParams: {
                action: 'mgr/settings/option/getlist',
                limit: -1,
            },
            root: 'results',
            fields: ['key', 'caption'],
            autoLoad: true
        }),
        displayField: 'caption',
        valueField: 'key',
        mode: 'remote',
        triggerAction: 'all',
        editable: false,
        multiSelect: true,
    });
    miniShop2.combo.ComboOptions.superclass.constructor.call(this, config);
};
Ext.extend(miniShop2.combo.ComboOptions, MODx.combo.ComboBox);
Ext.reg('minishop2-combo-options',miniShop2.combo.ComboOptions);