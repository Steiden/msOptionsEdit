Ext.override(miniShop2.window.CreateOption, {
    getFields: function (config) {
        const originalFields = miniShop2.window.CreateOption.prototype.getFields.call(this, config);

        // Добавляем новую вкладку в TabPanel
        originalFields.push({
            xtype: 'tabpanel',
            activeTab: 0,
            items: [
                {
                    title: _('all_product_options'),
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'minishop2-grid-product-options',
                            anchor: '100%',
                            cls: 'main-wrapper',
                        }
                    ]
                }
            ]
        });

        return originalFields;
    }
});

Ext.override(miniShop2.window.UpdateOption, {
    getFields: function (config) {
        const originalFields = miniShop2.window.UpdateOption.prototype.getFields.call(this, config);

        // Добавляем новую вкладку в TabPanel
        originalFields.push({
            xtype: 'tabpanel',
            activeTab: 0,
            items: [
                {
                    title: _('all_product_options'),
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'minishop2-grid-product-options',
                            anchor: '100%',
                            cls: 'main-wrapper',
                        }
                    ]
                }
            ]
        });

        return originalFields;
    }
});