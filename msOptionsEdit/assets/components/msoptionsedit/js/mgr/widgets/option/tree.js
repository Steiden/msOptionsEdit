msOptionsEdit.tree.OptionCategories = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'minishop2-option-categories-tree';
    }

    Ext.applyIf(config, {
        url: msOptionsEdit.config.connector_url,
        title: '',
        anchor: '100%',
        autoHeight: false,
        rootVisible: false,
        expandFirst: true,
        enableDD: false,
        remoteToolbar: false,
        action: 'mgr/settings/option/getnodes',
        baseParams: {
            categories: config['categories'] || '',
            options: config['options'] || '',
        },
        stateful: false,
        listeners: this.getListeners(config)
    });
    msOptionsEdit.tree.OptionCategories.superclass.constructor.call(this, config);

    this.on('afterrender', function () {
        this.maxHeight = this.maxHeight || Ext.getBody().getViewSize().height  * 0.75;
        this.setSize('', this.maxHeight);
    });
};
Ext.extend(msOptionsEdit.tree.OptionCategories, MODx.tree.Tree, {

    getListeners: function () {
        return {
            checkchange: function () {
                var grid = Ext.getCmp(this.optionGrid);
                if (grid) {
                    var checkedNodes = this.getChecked();
                    var categories = [];
                    for (var i = 0; i < checkedNodes.length; i++) {
                        categories.push(checkedNodes[i].attributes.pk);
                    }

                    var s = grid.getStore();
                    s.baseParams.categories = Ext.util.JSON.encode(categories);
                    grid.getBottomToolbar().changePage(1);
                }
            }
        };
    },

    _showContextMenu: function (n, e) {
        n.select();
        this.cm.activeNode = n;
        this.cm.removeAll();
        var m = [];
        m.push({
            text: '<i class="x-menu-item-icon icon icon-refresh"></i> ' + _('directory_refresh'),
            handler: function () {
                this.refreshNode(this.cm.activeNode.id, true);
            }
        },{
            text: '<i class="x-menu-item-icon icon icon-level-down"></i> ' + _('expand_tree'),
            handler: function () {
                this.cm.activeNode.expand(true);
            }
        },{
            text: '<i class="x-menu-item-icon icon icon-level-up"></i> ' + _('collapse_tree'),
            handler: function () {
                this.cm.activeNode.collapse(true);
            }
        },{
            text: '<i class="x-menu-item-icon icon icon-check-square-o"></i> ' + _('ms2_menu_select_all'),
            handler: function () {
                var activeNode = this.cm.activeNode;
                var checkchange = this.getListeners().checkchange;

                function massCheck(node)
                {
                    node.getUI().toggleCheck(true);
                    node.expand(false,false,function (node) {
                        node.eachChild(massCheck);
                        if (node == activeNode) {
                            checkchange();
                        }
                    });
                }
                massCheck(activeNode);
            }
        },{
            text: '<i class="x-menu-item-icon icon icon-square-o"></i> ' + _('ms2_menu_clear_all'),
            handler: function () {
                var activeNode = this.cm.activeNode;
                var checkchange = this.getListeners().checkchange;

                function massUncheck(node)
                {
                    node.getUI().toggleCheck(false);
                    node.eachChild(massUncheck);
                    if (node == activeNode) {
                        checkchange();
                    }
                }
                massUncheck(activeNode);
            }
        });
        this.addContextMenuItem(m);
        this.cm.showAt(e.xy);
        e.stopEvent();
    },

    remove: function () {

    },

});
Ext.reg('minishop2-tree-option-categories', msOptionsEdit.tree.OptionCategories);

/*
msOptionsEdit.tree.ModalCategories = function (config) {
config = config || {};
if (!config.id) {
config.id = 'minishop2-modal-categories-tree';
}

//Ext.applyIf(config, {});
msOptionsEdit.tree.ModalCategories.superclass.constructor.call(this, config);
};
Ext.extend(msOptionsEdit.tree.ModalCategories, msOptionsEdit.tree.OptionCategories, {

getListeners: function () {
return {
checkchange: function () {
var checkedNodes = this.getChecked();
var categories = [];
for (var i = 0; i < checkedNodes.length; i++) {
categories.push(checkedNodes[i].attributes.pk);
}

var catField = Ext.getCmp(this.categories);
if (!catField) {
return false;
}
catField.setValue(Ext.util.JSON.encode(categories));
},
afterrender: function () {
this.mask = new Ext.LoadMask(this.getEl());
}
};
}

});
Ext.reg('minishop2-tree-modal-categories', msOptionsEdit.tree.ModalCategories);
*/
