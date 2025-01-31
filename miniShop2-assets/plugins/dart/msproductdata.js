miniShop2.plugin.dart = {
    getFields: function(config) {
        return {
			vendor_article: {
                xtype: 'textfield'
				,name: 'vendor_article'
				,hiddenName: 'vendor_article'
                ,description: '<b>[[+vendor_article]]</b><br />' + _('ms2_product_vendor_article_help')
            },
			barcode: {
                xtype: 'textfield'
				,name: 'barcode'
				,hiddenName: 'barcode'
                ,description: '<b>[[+barcode]]</b><br />' + _('ms2_product_barcode_help')
            },
			price_rrc: {
                xtype: 'numberfield',
                decimalPrecision: 2,
                description: '<b>[[+price_rrc]]</b><br />' + _('ms2_product_price_rrc_help'),
				defaultValue: 0.0
            },
			price_mrc: {
                xtype: 'numberfield',
                decimalPrecision: 2,
                description: '<b>[[+price_mrc]]</b><br />' + _('ms2_product_price_mrc_help'),
				defaultValue: 0.0
            },
			available: {
                xtype: 'combo-available'
				,name: 'available'
				,hiddenName: 'available'
                ,description: '<b>[[+available]]</b><br />' + _('ms2_product_available_help'),
				defaultValue: 99
            },
			fixprice: {
                xtype: 'xcheckbox'
				,name: 'fixprice'
				,hiddenName: 'fixprice'
                ,description: '<b>[[+fixprice]]</b><br />' + _('ms2_product_fixprice_help')
            },
			check_sizes: {
                xtype: 'xcheckbox'
				,name: 'check_sizes'
				,hiddenName: 'check_sizes'
                ,description: '<b>[[+check_sizes]]</b><br />' + _('ms2_product_check_sizes_help')
            },
			weight_netto: {
                xtype: 'numberfield',
                decimalPrecision: 3,
                description: '<b>[[+weight_netto]]</b><br />' + _('ms2_product_weight_netto_help'),
				defaultValue: 0.0
            },
			weight_brutto: {
                xtype: 'numberfield',
                decimalPrecision: 3,
                description: '<b>[[+weight_brutto]]</b><br />' + _('ms2_product_weight_brutto_help'),
				defaultValue: 0.0
            },
			// length: {
            //     xtype: 'numberfield',
            //     decimalPrecision: 2,
            //     description: '<b>[[+length]]</b><br />' + _('ms2_product_length_help'),
			// 	defaultValue: 0.0
            // },
			// width: {
            //     xtype: 'numberfield',
            //     decimalPrecision: 2,
            //     description: '<b>[[+width]]</b><br />' + _('ms2_product_width_help'),
			// 	defaultValue: 0.0
            // },
			// height: {
            //     xtype: 'numberfield',
            //     decimalPrecision: 2,
            //     description: '<b>[[+height]]</b><br />' + _('ms2_product_height_help'),
			// 	defaultValue: 0.0
            // },
			places: {
                xtype: 'numberfield',
                description: '<b>[[+places]]</b><br />' + _('ms2_product_places_help'),
				defaultValue: 1
            },
			volume: {
                xtype: 'numberfield',
                decimalPrecision: 3,
                description: '<b>[[+volume]]</b><br />' + _('ms2_product_volume_help'),
				defaultValue: 0.0
            },
			b24id: {
                xtype: 'textfield',
                description: '<b>[[+b24id]]</b><br />' + _('ms2_product_b24id_help')
            },
			source_url: {
                xtype: 'textfield',
                description: '<b>[[+source_url]]</b><br />' + _('ms2_product_source_url_help')
            },
			measure: {
                xtype: 'textfield',
                description: '<b>[[+measure]]</b><br />' + _('ms2_product_measure_help')
            }
        }
    }
    ,getColumns: function() {
        return {
			available: {width:50, sortable:true, editor: {xtype:'combo-available', renderer: 'true'}},
            fixprice: {width:50, sortable:true, editor: {xtype:'xcheckbox', renderer: 'true'}},
			check_sizes: {width:50, sortable:true, editor: {xtype:'xcheckbox', renderer: 'true'}},
			price_rrc: {width:50, sortable:true, editor: {xtype:'numberfield', decimalPrecision: 2, renderer: 'true'}},
			price_mrc: {width:50, sortable:true, editor: {xtype:'numberfield', decimalPrecision: 2, renderer: 'true'}},
			weight_netto: {width:50, sortable:true, editor: {xtype:'numberfield', decimalPrecision: 3, renderer: 'true'}},
			weight_brutto: {width:50, sortable:true, editor: {xtype:'numberfield', decimalPrecision: 3, renderer: 'true'}},
			// length: {width:50, sortable:true, editor: {xtype:'numberfield', decimalPrecision: 2, renderer: 'true'}},
			// width: {width:50, sortable:true, editor: {xtype:'numberfield', decimalPrecision: 2, renderer: 'true'}},
			// height: {width:50, sortable:true, editor: {xtype:'numberfield', decimalPrecision: 2, renderer: 'true'}},
			places: {width:50, sortable:true, editor: {xtype:'numberfield', renderer: 'true'}},
			volume: {width:50, sortable:true, editor: {xtype:'numberfield', decimalPrecision: 3, renderer: 'true'}},
			b24id: {width:50, sortable:true, editor: {xtype:'textfield', renderer: 'true'}},
			source_url: {width:50, sortable:true, editor: {xtype:'textfield', renderer: 'true'}},
			measure: {width:50, sortable:true, editor: {xtype:'textfield', renderer: 'true'}},
			vendor_article: {width:50, sortable:true, editor: {xtype:'textfield', renderer: 'true'}},
			barcode: {width:50, sortable:true, editor: {xtype:'textfield', renderer: 'true'}}
		}
    }
};

miniShop2.combo.available = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        store: new Ext.data.ArrayStore({
            id: 0
            ,fields: ['available','display']
            ,data: [
                ['1','В наличии']
                ,['2','Под заказ']
                ,['99','Нет в наличии']
            ]
        })
        ,mode: 'local'
        ,displayField: 'display'
        ,valueField: 'available'
    });
    miniShop2.combo.available.superclass.constructor.call(this,config);
};
Ext.extend(miniShop2.combo.available,MODx.combo.ComboBox);
Ext.reg('combo-available',miniShop2.combo.available);