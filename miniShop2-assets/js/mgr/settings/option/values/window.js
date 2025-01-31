var topic = '/msoptionsedit/';
var register = 'mgr';

let options = [];
let optionsIsLoaded = false;

miniShop2.window.MergeOptionValue = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-window-update-option-value';
    }

    Ext.applyIf(config, {
        title: _('miniShop2_update_option_value'),
        width: 600,
        autoHeight: true,
        url: miniShop2.config.connector_url,
        baseParams: {
            // action: 'mgr/settings/option/values/update',
        },
        layout: 'anchor',
        fields: this.getFields(config),
    });
    miniShop2.window.MergeOptionValue.superclass.constructor.call(this, config);

    this.on('success', function () {
        var grid = Ext.getCmp('msoptionsedit-grid-option-values');
        if (grid) {
            grid.getStore().load();
        }
    });
};

Ext.extend(miniShop2.window.MergeOptionValue, MODx.Window, {
    getFields: function (config) {
        // console.log(config);
        return [{
            xtype: 'hidden', name: 'option_key', id: config.id + '-option_key'
        }, {
            xtype: 'textfield',
            fieldLabel: "Исходное значение",
            name: 'current_value',
            id: config.id + '-current_value',
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
            xtype: 'minishop2-grid-option-value-merge',
            fieldLabel: "Значения для слияния",
            id: config.id + '-merge-grid',
            optionKey: config.record ? config.record.object.option_key : '',
            hidden: true,
        }, {
            xtype: 'textfield',
            fieldLabel: "Новое значение",
            name: 'new_value',
            id: config.id + '-new_value',
            anchor: '100%',
        }, {
            xtype: 'textfield',
            fieldLabel: "Маска",
            name: 'regex_mask',
            id: config.id + '-regex_mask',
            anchor: '100%',
            hidden: true,
        }, {
            // xtype: 'textfield',
            // fieldLabel: "Делитель",
            // name: 'regex_delimiter',
            // id: config.id + '-regex_delimiter',
            // anchor: '100%',
            // hidden: true,
        }, {
            xtype: 'numberfield',
            defaultValue: 1,
            fieldLabel: "Коэффициент",
            name: 'coefficient',
            id: config.id + '-coefficient',
            anchor: '100%',
            hidden: true,
        }, {
            xtype: 'combo',
            fieldLabel: "Опции",
            name: 'option_keys',
            id: config.id + '-option_keys',
            anchor: '100%',
            hidden: true,
            store: new Ext.data.JsonStore({
                url: miniShop2.config.connector_url,
                baseParams: {
                    action: 'mgr/settings/option/getlist',
                    limit: 0,
                },
                root: 'results',
                fields: ['key', 'caption'],
                autoLoad: true,
                listeners: {
                    load: function (combo, records) {
                        if (!optionsIsLoaded) {
                            options = records.map(el => el?.data);
                            optionsIsLoaded = true;
                        }
                    }
                }
            }),
            displayField: 'caption',
            valueField: 'key',
            mode: 'remote',
            triggerAction: 'all',
            editable: true,
            pageSize: 10,
            enableKeyEvents: true,
            listeners: {
                render: function (combo) {
                    combo.selectedValues = [];
                    combo.selectedKeys = [];
                },
                beforequery: function (queryEvent) {
                    const values = queryEvent.query.split(new RegExp(";\s*"));
                    queryEvent.combo.store.baseParams.query = values[values.length - 1].trim();
                    queryEvent.combo.store.load();
                    return false;
                },
                select: function (combo, record) {
                    let key = record.get(combo.valueField);
                    let value = record.get(combo.displayField);

                    if (!combo.selectedKeys.includes(key)) {
                        combo.selectedKeys[combo.selectedKeys.length - 1] = key;
                        combo.selectedValues[combo.selectedValues.length - 1] = value;
                    }

                    combo.setValue(combo.selectedValues.join('; '));
                },
                change: {
                    fn: function (combo, newValue, oldValue) {
                        this.changeOptions(combo, newValue);
                    },
                    scope: this
                },
                keypress: {
                    fn: function (textfield, event) {
                        const combo = Ext.getCmp(config.id + '-option_keys');
                        const value = event.getTarget().value;

                        this.changeOptions(combo, value);
                    },
                    scope: this
                }
            }
        }, {
            xtype: 'textfield',
            fieldLabel: "Исключить слова",
            name: 'exclude_words',
            id: config.id + '-exclude_words',
            anchor: '100%',
            hidden: true,
        }, {
            xtype: 'xcheckbox',
            fieldLabel: "Применить ко всем",
            name: 'apply_to_all',
            id: config.id + '-apply_to_all',
            hidden: true,
        }];
    },

    changeOptions: function (combo, value) {
        combo.selectedKeys = [];
        combo.selectedValues = [];

        const currentValues = value.split(new RegExp(';\s*'));

        currentValues.forEach(val => {
            const searchResult = options.find(option => option?.caption === val.trim());
            if (!searchResult) {
                combo.selectedKeys.push("");
                combo.selectedValues.push(val.trim());
                return;
            }
            combo.selectedKeys.push(searchResult?.key);
            combo.selectedValues.push(searchResult?.caption);
        });
    },

    onActionSelect: function (combo, record) {
        const actionKey = record.get('key');
        const regexFields = ['regex_mask', // 'regex_delimiter',
            'coefficient', 'option_keys', 'exclude_words', 'apply_to_all',];
        const mergeGrid = Ext.getCmp(this.config.id + '-merge-grid');
        const newValueField = Ext.getCmp(this.config.id + '-new_value');

        const isSplit = actionKey === 'split';
        const isMerge = actionKey === 'merge';

        regexFields.forEach(fieldId => {
            const field = Ext.getCmp(this.config.id + '-' + fieldId);
            field.setVisible(isSplit);
            field.setWidth(field.ownerCt.getWidth() - 10);
            field.ownerCt.doLayout();
        });

        mergeGrid.setVisible(isMerge);
        mergeGrid.ownerCt.doLayout();

        newValueField.setVisible(!isSplit);
        newValueField.reset();
        newValueField.ownerCt.doLayout();
    },

    submit: function () {
        const mergeGrid = Ext.getCmp(this.config.id + '-merge-grid');
        const mergeValues = mergeGrid.getSelectedValues();

        const optionKeysCombo = Ext.getCmp(this.config.id + '-option_keys');
        const optionKeys = optionKeysCombo.selectedKeys.join(',');

        const optionKey = Ext.getCmp(this.config.id + '-option_key').getValue();
        const currentValue = Ext.getCmp(this.config.id + '-current_value').getValue();
        const actionKey = Ext.getCmp(this.config.id + '-action_key').getValue();
        const newValue = Ext.getCmp(this.config.id + '-new_value').getValue();
        const regexMask = Ext.getCmp(this.config.id + '-regex_mask').getValue();
        // const regexDelimiter = Ext.getCmp(this.config.id + '-regex_delimiter').getValue();
        const coef = Ext.getCmp(this.config.id + '-coefficient').getValue();
        const excludeWords = Ext.getCmp(this.config.id + '-exclude_words').getValue();
        const applyToAll = Ext.getCmp(this.config.id + '-apply_to_all').getValue();

        this.baseParams = Ext.apply(this.baseParams, {
            optionKey, currentValue, actionKey, newValue, mergeValues: mergeValues.join(','), regexMask, // regexDelimiter,
            coef, optionKeys, excludeWords, applyToAll, productId: this.config.record?.object?.product_id
        });

        var console = MODx.load({
            xtype: 'modx-console', register: register, topic: topic, show_filename: 0, listeners: {
                'shutdown': {
                    fn: function () {
                        this.close();
                    }, scope: this
                }, 'complete': {
                    fn: function () {
                        this.close();
                    }, scope: this
                }
            }
        });
        console.show(Ext.getBody());

        this.getTotalProductsAndProcess(this.baseParams);

        // miniShop2.window.MergeOptionValue.superclass.submit.call(this);
    },

    processBatch: function (batchSize, offset, total, baseParams, iteration = 1) {
        MODx.Ajax.request({
            url: this.config.url, params: {
                action: 'mgr/settings/option/values/update',
                register: register,
                topic: topic,
                actionType: 'process_batch',
                limit: batchSize,
                offset: offset,
                iteration: iteration,
                ...baseParams
            }, listeners: {
                'success': {
                    fn: function (response) {
                        iteration++;
                        const processedCount = offset + batchSize;

                        if (processedCount < total) {
                            this.processBatch(batchSize, processedCount, total, baseParams, iteration);
                        } else {
                            var grid = Ext.getCmp('msoptionsedit-grid-option-values');
                            if (grid) {
                                grid.getStore().load();
                            }

                            // alert("Обновлений опций завершено!");
                        }
                    }, scope: this
                }, 'failure': {
                    fn: function () {
                        this.close();
                    }, scope: this
                }
            }
        });
    },

    getTotalProductsAndProcess: function (baseParams) {
        MODx.Ajax.request({
            url: this.config.url, params: {
                action: 'mgr/settings/option/values/update',
                register: register,
                topic: topic,
                actionType: 'count', ...baseParams
            }, listeners: {
                'success': {
                    fn: function (response) {
                        const total = response.object.count;
                        const batchSize = 50;  // Размер партии
                        if (total > 0) {
                            this.processBatch(batchSize, 0, total, baseParams);
                        } else {
                            // alert("Количество найденных продуктов: 0");
                        }
                    }, scope: this
                }, 'failure': {
                    fn: function () {
                        this.close();
                    }, scope: this
                }
            }
        });
    },
});

Ext.reg('msoptionsedit-window-update-option-value', miniShop2.window.MergeOptionValue);
