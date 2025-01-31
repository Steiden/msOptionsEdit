miniShop2.grid.OptionValues = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-grid-option-values';
    }
    Ext.applyIf(config, {
        url: miniShop2.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        baseParams: {
            action: 'mgr/settings/option/values/getlist', option_id: config.option_id, option_key: config.option_key,
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateOptionValue(grid, e, row);
            }
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active ? 'msoptionsedit-grid-row-disabled' : '';
            }
        },
        paging: true,
        remoteSort: true,
        height: 550,
        autoHeight: false,
    });
    miniShop2.grid.OptionValues.superclass.constructor.call(this, config);
};
Ext.extend(miniShop2.grid.OptionValues, MODx.grid.Grid, {
    windows: {},

    getFields: function (config) {
        return [{
            xtype: 'hidden', name: 'key', id: config.id + '-key',
        }, {
            xtype: 'hidden', name: 'product_id', id: config.id + '-product_id',
        }, 'value', 'actions'];
    },

    getColumns: function (config) {
        return [{
            header: _('msoptionsedit_product_id'), dataIndex: 'product_id', sortable: true, width: 50,
        }, {
            header: _('msoptionsedit_option_value'), dataIndex: 'value', sortable: true, width: 200,
        }, {
            header: _('ms2_actions'),
            dataIndex: 'actions',
            id: 'actions',
            width: 70,
            renderer: miniShop2.utils.renderActions
        }]
    },

    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof (row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                } else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },

    updateOptionValue: function (btn, e, row) {
        if (typeof (row) != 'undefined') {
            this.menu.record = row.data;
        } else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        var w = Ext.getCmp('msoptionsedit-window-update-option-value');
        if (w) {
            w.close();
        }

        // console.log("Record: ", this.menu.record);

        MODx.Ajax.request({
            url: this.config.url, params: {
                action: 'mgr/settings/option/values/get',
                option_key: this.menu.record.key,
                current_value: this.menu.record.value,
                product_id: this.menu.record.product_id,
                option_id: this.config.option_id,
            }, listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'msoptionsedit-window-update-option-value', id: Ext.id(), record: r,
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    getTopBar: function () {
        return [{
            xtype: 'minishop2-field-search', width: 250, listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                }, clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('msoptionsedit-grid-option-values', miniShop2.grid.OptionValues);