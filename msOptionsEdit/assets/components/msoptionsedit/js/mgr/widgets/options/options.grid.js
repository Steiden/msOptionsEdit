msOptionsEdit.grid.Options = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-grid-options';
    }
    Ext.applyIf(config, {
        url: msOptionsEdit.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/option/getlist'
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateOption(grid, e, row);
            }
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            getRowClass: function (rec) {
                return !rec.data.active
                    ? 'msoptionsedit-grid-row-disabled'
                    : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    msOptionsEdit.grid.Options.superclass.constructor.call(this, config);

    // Очистка выделений при обновлении грида
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(msOptionsEdit.grid.Options, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = msOptionsEdit.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createOption: function (btn, e) {
        var w = MODx.load({
            xtype: 'msoptionsedit-option-window-create',
            id: Ext.id(),
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({active: true});
        w.show(e.target);
    },

    updateOption: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/option/get',
                id: id,
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'msoptionsedit-option-window-update',
                            id: Ext.id(),
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeOption: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('msoptionsedit_options_remove')
                : _('msoptionsedit_option_remove'),
            text: ids.length > 1
                ? _('msoptionsedit_options_remove_confirm')
                : _('msoptionsedit_option_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/option/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    },

    getFields: function () {
        return ['id', 'key', 'caption', 'description', 'measure_unit', 'category', 'type', 'properties'];
    },

    getColumns: function () {
        return [{
            header: _('msoptionsedit_option_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('msoptionsedit_option_key'),
            dataIndex: 'key',
            sortable: true,
            width: 200,
        }, {
            header: _('msoptionsedit_option_caption'),
            dataIndex: 'caption',
            sortable: true,
            width: 250,
        }, {
            header: _('msoptionsedit_option_description'),
            dataIndex: 'description',
            sortable: false,
            width: 300,
        }, {
            header: _('msoptionsedit_option_measure_unit'),
            dataIndex:'measure_unit',
            sortable: true,
            width: 100,
        }, {
            header: _('msoptionsedit_option_category'),
            dataIndex: 'category',
            sortable: true,
            width: 150,
        }, {
            header: _('msoptionsedit_option_type'),
            dataIndex: 'type',
            sortable: true,
            width: 100,
        }, {
            header: _('msoptionsedit_option_properties'),
            dataIndex: 'properties',
            sortable: false,
            width: 200,
        }, {
            header: _('msoptionsedit_grid_actions'),
            dataIndex: 'actions',
            renderer: msOptionsEdit.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('msoptionsedit_option_create'),
            handler: this.createOption,
            scope: this
        }, '->', {
            xtype: 'msoptionsedit-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
    },

    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();

        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }

        return ids;
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
Ext.reg('msoptionsedit-grid-options', msOptionsEdit.grid.Options);
