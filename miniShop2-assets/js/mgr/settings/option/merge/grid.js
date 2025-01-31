miniShop2.grid.OptionValueMerge = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'minishop2-grid-option-value-merge';
    }

    this.selectedValues = []; // Хранение выбранных значений

    Ext.applyIf(config, {
        url: miniShop2.config.connector_url,
        tbar: this.getTopBar(config),
        baseParams: {
            action: 'mgr/settings/option/values/getlist',
            option_key: config.optionKey || '',
        },
        fields: ['id', 'value'],
        columns: [{
            header: _('msoptionsedit_option_value'),
            dataIndex: 'value',
            sortable: true,
            width: 200,
        }, {
            header: _('msoptionsedit_option_checked'),
            dataIndex: 'checked',
            sortable: false,
            width: 50,
            renderer: (value, metadata, record) => {
                const id = record.get('value'); // Уникальный идентификатор строки
                const checked = this.selectedValues.includes(id); // Проверяем сохраненное состояние
                return `<input type="checkbox" class="grid-checkbox" data-value="${id}" ${checked ? 'checked' : ''}>`;
            },
        }],
        listeners: {
            afterrender: (grid) => {
                grid.getEl().on('click', (event, target) => {
                    if (target.classList.contains('grid-checkbox')) {
                        const value = target.getAttribute('data-value');
                        const checked = target.checked;
                        if (checked) {
                            this.addValue(value);
                        } else {
                            this.removeValue(value);
                        }
                    }
                });
            },
            load: () => {
                // Применяем состояния после загрузки данных
                this.applyCheckboxStates();
            },
        },
        paging: true,
        remoteSort: true,
        height: 400,
        autoHeight: false,
    });

    miniShop2.grid.OptionValueMerge.superclass.constructor.call(this, config);
};

Ext.extend(miniShop2.grid.OptionValueMerge, MODx.grid.Grid, {
    addValue: function (value) {
        if (!this.selectedValues.includes(value)) {
            this.selectedValues.push(value);
        }
    },
    removeValue: function (value) {
        this.selectedValues = this.selectedValues.filter((item) => item !== value);
    },
    getSelectedValues: function () {
        return this.selectedValues;
    },
    applyCheckboxStates: function () {
        // Повторное применение состояний чекбоксов после загрузки данных
        const gridEl = this.getEl();
        if (gridEl) {
            const checkboxes = gridEl.query('.grid-checkbox');
            checkboxes.forEach((checkbox) => {
                const value = checkbox.getAttribute('data-value');
                checkbox.checked = this.selectedValues.includes(value);
            });
        }
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

Ext.reg('minishop2-grid-option-value-merge', miniShop2.grid.OptionValueMerge);
