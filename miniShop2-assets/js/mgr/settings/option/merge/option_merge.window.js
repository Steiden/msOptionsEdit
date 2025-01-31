miniShop2.window.MergeOption = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-window-merge-option';
    }

    Ext.applyIf(config, {
        title: _('miniShop2_update_option'),
        width: 600,
        autoHeight: true,
        url: miniShop2.config.connector_url,
        baseParams: {
            action: 'mgr/settings/option/merge',
        },
        layout: 'anchor',
        fields: this.getFields(config),
    });
    miniShop2.window.MergeOption.superclass.constructor.call(this, config);

    this.on('success', function () {
        var grid = Ext.getCmp('msoptionsedit-grid-option-merge');
        if (grid) {
            grid.getStore().load();
        }
    });
};

Ext.extend(miniShop2.window.MergeOption, MODx.Window, {
    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: "Текущий key",
            name: 'key',
            id: config.id + '-current_key',
            anchor: '100%',
            readOnly: true,
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: "Текущий caption",
            name: 'caption',
            id: config.id + '-current_caption',
            anchor: '100%',
            readOnly: true,
            allowBlank: false,
        }, {
            xtype: 'combo',
            fieldLabel: "Действие",
            name: 'action_key',
            id: config.id + '-action_key',
            anchor: '100%',
            store: new Ext.data.JsonStore({
                url: miniShop2.config.connector_url, baseParams: {
                    action: 'mgr/settings/option/action/getlist',
                }, root: 'results', fields: ['key', 'name'], autoLoad: true
            }),
            displayField: 'name',
            valueField: 'key',
            mode: 'local',
            triggerAction: 'all',
            editable: false,
            allowBlank: false,
            listeners: {
                select: {
                    fn: this.onActionSelect, scope: this
                }
            }
        }, {
            xtype: 'msoptionsedit-grid-option-merge',
            fieldLabel: "Опции для слияния",
            id: config.id + '-merge-grid',
            optionKey: config.record ? config.record.object.option_key : '',
            hidden: true,
        }, {
            xtype: 'textfield',
            fieldLabel: "Новый key",
            name: 'new_key',
            id: config.id + '-new_key',
            anchor: '100%',
            allowBlank: false,
        }, {
            xtype: 'textfield',
            fieldLabel: "Новый caption",
            name: 'new_caption',
            id: config.id + '-new_caption',
            anchor: '100%',
            allowBlank: false,
        }];
    },

    onActionSelect: function (combo, record) {
        const actionKey = record.get('key');
        const mergeGrid = Ext.getCmp(this.config.id + '-merge-grid');

        const isMerge = actionKey === 'merge';

        mergeGrid.setVisible(isMerge);
        mergeGrid.ownerCt.doLayout();
    },

    submit: function () {
        const formValues = this.fp.getForm().getValues();
        const mergeGrid = Ext.getCmp(this.config.id + '-merge-grid');
        const mergeValues = mergeGrid.getSelectedValues();

        this.baseParams = Ext.apply(this.baseParams, {
            currentKey: formValues['current_key'],
            currentCaption: formValues['current_caption'],
            newKey: formValues['new_key'],
            newCaption: formValues['new_caption'],
            mergeValues: mergeValues.join(','),
        });

        miniShop2.window.MergeOption.superclass.submit.call(this);
    }
});

Ext.reg('msoptionsedit-window-merge-option', miniShop2.window.MergeOption);
