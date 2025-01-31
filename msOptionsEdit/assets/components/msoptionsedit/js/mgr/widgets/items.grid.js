msOptionsEdit.grid.Items = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-grid-items';
    }
    Ext.applyIf(config, {
        url: msOptionsEdit.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/item/getlist'
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.showItem(grid, e, row);
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
    msOptionsEdit.grid.Items.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(msOptionsEdit.grid.Items, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = msOptionsEdit.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    showItem: function (btn, e, row) {
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
                action: 'mgr/item/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'msoptionsedit-item-window-show',
                            id: Ext.id(),
                            record: r,
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('msoptionsedit_items_remove')
                : _('msoptionsedit_item_remove'),
            text: ids.length > 1
                ? _('msoptionsedit_items_remove_confirm')
                : _('msoptionsedit_item_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/item/remove',
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
        return ['id', 'product_id', 'option_key', 'old_value', 'new_value', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('msoptionsedit_option_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('msoptionsedit_item_product_id'),
            dataIndex: 'product_id',
            sortable: true,
            width: 100,
        }, {
            header: _('msoptionsedit_item_option_key'),
            dataIndex: 'option_key',
            sortable: true,
            width: 300,
        }, {
            header: _('msoptionsedit_item_old_value'),
            dataIndex: 'old_value',
            sortable: true,
            width: 200,
        }, {
            header: _('msoptionsedit_item_new_value'),
            dataIndex: 'new_value',
            sortable: true,
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
        return [
        //     {
        //     text: '<i class="icon icon-plus"></i>&nbsp;' + _('msoptionsedit_item_create'),
        //     handler: this.createItem,
        //     scope: this
        // },
            '->', {
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

    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof(row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                }
                else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },

    removeOptionEdit: function () {
        if (!this.menu.record) {
            return false;
        }

        MODx.msg.confirm({
            title: _('msoptionsedit_item_remove') + '"' + this.menu.record.key + '"',
            text: _('msoptionsedit_item_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/item/remove',
                method: 'remove',
                ids: Ext.util.JSON.encode(this._getSelectedIds()),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
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
Ext.reg('msoptionsedit-grid-items', msOptionsEdit.grid.Items);
