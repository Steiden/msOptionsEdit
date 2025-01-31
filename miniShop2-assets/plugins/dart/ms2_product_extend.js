Ext.override(miniShop2.panel.Product, {
    getProductFields: function (config) {
        // Поля, которыми мы будем управлять вручную
        var override_fields = ['new', 'favorite', 'popular', 'fixprice', 'check_sizes', 'weight_netto', 'weight_brutto', 'length', 'width', 'height', 'places', 'volume', 'b24id', 'available'];
        // var override_fields = ['new', 'favorite', 'popular', 'fixprice', 'check_sizes', 'weight_netto', 'weight_brutto', 'places', 'volume', 'b24id', 'available']; // Удалены length, width и height

        var enabled = miniShop2.config.data_fields;
        var available = miniShop2.config.extra_fields;

        var product_fields = this.getAllProductFields(config);
        var col1 = [];
        var col2 = [];
        var tmp;
        for (var i = 0; i < available.length; i++) {
            var field = available[i];
            this.active_fields = [];
            if ((enabled.length > 0 && enabled.indexOf(field) === -1)
                || this.active_fields.indexOf(field) !== -1) {
                continue;
            }

            // Если это поле, поведение которого мы переопределили, то просто добавляем его в active_fields
            // но не выводим
            if (override_fields.indexOf(field) !== -1) {
                this.active_fields.push(field);
                continue;
            }

            if (tmp = product_fields[field]) {
                this.active_fields.push(field);
                tmp = this.getExtField(config, field, tmp);

                if (i % 2) {
                    col2.push(tmp);
                } else {
                    col1.push(tmp);
                }
            }
        }

        console.log("Product fields: ", product_fields, this.getExtField(config, 'length', product_fields['length']));


        return {
            title: _('ms2_tab_product_data'),
            id: 'minishop2-product-data',
            bodyCssClass: 'main-wrapper',
            items: [{
                layout: 'column',
                items: [{
                    columnWidth: .5,
                    layout: 'form',
                    id: 'minishop2-product-data-left',
                    labelAlign: 'top',
                    items: col1,
                }, {
                    columnWidth: .5,
                    layout: 'form',
                    id: 'minishop2-product-data-right',
                    labelAlign: 'top',
                    items: col2,
                }],
            }, {
                html: '<h3>Метки к товару</h3>',
                style: 'margin-top: 30px;border-bottom: solid 1px #e4e4e4;',
                border: false
            }, {
                layout: 'column',
                items: [{
                    columnWidth: .5,
                    layout: 'form',
                    id: 'minishop2-product-data-checkboxes',
                    labelAlign: 'top',
                    items: [
                        this.getExtField(config, 'new', product_fields['new']),
                        this.getExtField(config, 'favorite', product_fields['favorite']),
                        this.getExtField(config, 'popular', product_fields['popular']),
						this.getExtField(config, 'fixprice', product_fields['fixprice']),
						this.getExtField(config, 'check_sizes', product_fields['check_sizes'])
                    ],
                },{
                    columnWidth: .5,
                    layout: 'form',
                    id: 'minishop2-product-data-labels',
                    labelAlign: 'top',
                    items: [
						this.getExtField(config, 'b24id', product_fields['b24id']),
                        this.getExtField(config, 'source_url', product_fields['source_url']),
						this.getExtField(config, 'available', product_fields['available']),
                    ],
                }],
            }, {
                html: '<h3>Информация для расчета доставки</h3>',
                style: 'margin-top: 30px;border-bottom: solid 1px #e4e4e4;',
                border: false
            }, {
                layout: 'column',
                items: [{
                    columnWidth: .35,
                    layout: 'form',
                    id: 'minishop2-product-data-supplier-left',
                    labelAlign: 'top',
                    items: [
                        this.getExtField(config, 'weight_netto', product_fields['weight_netto']),
                        this.getExtField(config, 'weight_brutto', product_fields['weight_brutto']),
						this.getExtField(config, 'measure', product_fields['measure'])
                    ],
                },{
                    columnWidth: .65,
                    layout: 'form',
                    id: 'minishop2-product-data-supplier-right',
                    labelAlign: 'top',
                    items: [
                        this.getExtField(config, 'length', product_fields['length']),
                        this.getExtField(config, 'width', product_fields['width']),
						this.getExtField(config, 'height', product_fields['height']),
						this.getExtField(config, 'places', product_fields['places']),
						this.getExtField(config, 'volume', product_fields['volume'])
                    ],
                }],
            }
            ],
            listeners: {},
        };
    }
});