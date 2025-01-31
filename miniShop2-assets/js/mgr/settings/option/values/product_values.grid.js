miniShop2.grid.OptionValues = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-grid-product-options';
    }
    Ext.applyIf(config, {
        url: miniShop2.config.connector_url,
        baseParams: {
            action: 'mgr/product/get_options',
            // option_id: config.baseParams.option_id || 0
        },
        fields: ['product_id', 'key', 'value'],
        columns: [{
            header: _('msoptionsedit_option_product_id'),
            dataIndex: 'product_id',
            sortable: true,
            width: 100,
        }, {
            header: _('msoptionsedit_option_key'),
            dataIndex: 'key',
            sortable: true,
            width: 300,
        }, {
            header: _('msoptionsedit_option_value'),
            dataIndex: 'value',
            sortable: true,
            width: 200,
        }],
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    miniShop2.grid.OptionValues.superclass.constructor.call(this, config);
};
Ext.extend(miniShop2.grid.OptionValues, MODx.grid.Grid);
Ext.reg('msoptionsedit-grid-product-options', miniShop2.grid.OptionValues);