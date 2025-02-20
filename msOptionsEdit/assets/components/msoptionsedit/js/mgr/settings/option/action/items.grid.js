msOptionEdit.grid.Action = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsmergeaction-grid-items';
    }
    Ext.applyIf(config, {
        url: msOptionEdit.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        // tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/option/action/getlist'
        },
        // listeners: {
        //     rowDblClick: function (grid, rowIndex, e) {
        //         var row = grid.store.getAt(rowIndex);
        //         this.updateItem(grid, e, row);
        //     }
        // },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            // getRowClass: function (rec) {
            //     return !rec.data.active
            //         ? 'msoptionsmergeaction-grid-row-disabled'
            //         : '';
            // }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    msOptionEdit.grid.Action.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    // this.store.on('load', function () {
    //     if (this._getSelectedIds().length) {
    //         this.getSelectionModel().clearSelections();
    //     }
    // }, this);
};
Ext.extend(msOptionEdit.grid.Action, MODx.grid.Grid, {
    windows: {},

    // getMenu: function (grid, rowIndex) {
    //     var ids = this._getSelectedIds();
    //
    //     var row = grid.getStore().getAt(rowIndex);
    //     var menu = msOptionEdit.utils.getMenu(row.data['actions'], this, ids);
    //
    //     this.addContextMenuItem(menu);
    // },
    //
    // createItem: function (btn, e) {
    //     var w = MODx.load({
    //         xtype: 'msoptionsmergeaction-item-window-create',
    //         id: Ext.id(),
    //         listeners: {
    //             success: {
    //                 fn: function () {
    //                     this.refresh();
    //                 }, scope: this
    //             }
    //         }
    //     });
    //     w.reset();
    //     w.setValues({active: true});
    //     w.show(e.target);
    // },
    //
    // updateItem: function (btn, e, row) {
    //     if (typeof(row) != 'undefined') {
    //         this.menu.record = row.data;
    //     }
    //     else if (!this.menu.record) {
    //         return false;
    //     }
    //     var id = this.menu.record.id;
    //
    //     MODx.Ajax.request({
    //         url: this.config.url,
    //         params: {
    //             action: 'mgr/item/get',
    //             id: id
    //         },
    //         listeners: {
    //             success: {
    //                 fn: function (r) {
    //                     var w = MODx.load({
    //                         xtype: 'msoptionsmergeaction-item-window-update',
    //                         id: Ext.id(),
    //                         record: r,
    //                         listeners: {
    //                             success: {
    //                                 fn: function () {
    //                                     this.refresh();
    //                                 }, scope: this
    //                             }
    //                         }
    //                     });
    //                     w.reset();
    //                     w.setValues(r.object);
    //                     w.show(e.target);
    //                 }, scope: this
    //             }
    //         }
    //     });
    // },
    //
    // removeItem: function () {
    //     var ids = this._getSelectedIds();
    //     if (!ids.length) {
    //         return false;
    //     }
    //     MODx.msg.confirm({
    //         title: ids.length > 1
    //             ? _('msoptionsmergeaction_items_remove')
    //             : _('msoptionsmergeaction_item_remove'),
    //         text: ids.length > 1
    //             ? _('msoptionsmergeaction_items_remove_confirm')
    //             : _('msoptionsmergeaction_item_remove_confirm'),
    //         url: this.config.url,
    //         params: {
    //             action: 'mgr/item/remove',
    //             ids: Ext.util.JSON.encode(ids),
    //         },
    //         listeners: {
    //             success: {
    //                 fn: function () {
    //                     this.refresh();
    //                 }, scope: this
    //             }
    //         }
    //     });
    //     return true;
    // },
    //
    // disableItem: function () {
    //     var ids = this._getSelectedIds();
    //     if (!ids.length) {
    //         return false;
    //     }
    //     MODx.Ajax.request({
    //         url: this.config.url,
    //         params: {
    //             action: 'mgr/item/disable',
    //             ids: Ext.util.JSON.encode(ids),
    //         },
    //         listeners: {
    //             success: {
    //                 fn: function () {
    //                     this.refresh();
    //                 }, scope: this
    //             }
    //         }
    //     })
    // },
    //
    // enableItem: function () {
    //     var ids = this._getSelectedIds();
    //     if (!ids.length) {
    //         return false;
    //     }
    //     MODx.Ajax.request({
    //         url: this.config.url,
    //         params: {
    //             action: 'mgr/item/enable',
    //             ids: Ext.util.JSON.encode(ids),
    //         },
    //         listeners: {
    //             success: {
    //                 fn: function () {
    //                     this.refresh();
    //                 }, scope: this
    //             }
    //         }
    //     })
    // },

    getFields: function () {
        return ['id', 'key', 'caption'];
    },

    getColumns: function () {
        return [{
            header: _('msoptionsmergeaction_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('msoptionsmergeaction_item_key'),
            dataIndex: 'key',
            sortable: true,
            width: 200,
        }, {
            header: _('msoptionsmergeaction_item_caption'),
            dataIndex: 'caption',
            sortable: true,
            width: 250,
        }, {
            header: _('msoptionsmergeaction_grid_actions'),
            dataIndex: 'actions',
            renderer: msOptionEdit.utils.renderAction,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },

    // getTopBar: function () {
    //     return [{
    //         text: '<i class="icon icon-plus"></i>&nbsp;' + _('msoptionsmergeaction_item_create'),
    //         handler: this.createItem,
    //         scope: this
    //     }, '->', {
    //         xtype: 'msoptionsmergeaction-field-search',
    //         width: 250,
    //         listeners: {
    //             search: {
    //                 fn: function (field) {
    //                     this._doSearch(field);
    //                 }, scope: this
    //             },
    //             clear: {
    //                 fn: function (field) {
    //                     field.setValue('');
    //                     this._clearSearch();
    //                 }, scope: this
    //             },
    //         }
    //     }];
    // },

    // onClick: function (e) {
    //     var elem = e.getTarget();
    //     if (elem.nodeName == 'BUTTON') {
    //         var row = this.getSelectionModel().getSelected();
    //         if (typeof(row) != 'undefined') {
    //             var action = elem.getAttribute('action');
    //             if (action == 'showMenu') {
    //                 var ri = this.getStore().find('id', row.id);
    //                 return this._showMenu(this, ri, e);
    //             }
    //             else if (typeof this[action] === 'function') {
    //                 this.menu.record = row.data;
    //                 return this[action](this, e);
    //             }
    //         }
    //     }
    //     return this.processEvent('click', e);
    // },
    //
    // _getSelectedIds: function () {
    //     var ids = [];
    //     var selected = this.getSelectionModel().getSelections();
    //
    //     for (var i in selected) {
    //         if (!selected.hasOwnProperty(i)) {
    //             continue;
    //         }
    //         ids.push(selected[i]['id']);
    //     }
    //
    //     return ids;
    // },
    //
    // _doSearch: function (tf) {
    //     this.getStore().baseParams.query = tf.getValue();
    //     this.getBottomToolbar().changePage(1);
    // },
    //
    // _clearSearch: function () {
    //     this.getStore().baseParams.query = '';
    //     this.getBottomToolbar().changePage(1);
    // },
});

Ext.reg('msoptionsmergeaction-grid-items', msOptionEdit.grid.Action);