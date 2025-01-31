miniShop2.grid.OptionMerge = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msoptionsedit-grid-option-merge';
    }

    this.selectedKeys = []; // Хранение выбранных значений

    Ext.applyIf(config, {
        url: miniShop2.config.connector_url,
        tbar: this.getTopBar(config),
        baseParams: {
            action: 'mgr/settings/option/getlist',
            option_key: config.optionKey || '',
        },
        fields: ['key', 'caption'],
        columns: [{
            header: _('msoptionsedit_option_key'),
            dataIndex: 'key',
            sortable: true,
            width: 150,
        }, {
            header: _('msoptionsedit_option_caption'),
            dataIndex: 'caption',
            sortable: true,
            width: 150,
        }, {
            header: _('msoptionsedit_option_checked'),
            dataIndex: 'checked',
            sortable: false,
            width: 50,
            renderer: (value, metadata, record) => {
                const id = record.get('key'); // Уникальный идентификатор строки
                const checked = this.selectedKeys.includes(id); // Проверяем сохраненное состояние
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

    miniShop2.grid.OptionMerge.superclass.constructor.call(this, config);
};

Ext.extend(miniShop2.grid.OptionMerge, MODx.grid.Grid, {
    addValue: function (value) {
        if (!this.selectedKeys.includes(value)) {
            this.selectedKeys.push(value);
        }
    },
    removeValue: function (value) {
        this.selectedKeys = this.selectedKeys.filter((item) => item !== value);
    },
    getselectedKeys: function () {
        return this.selectedKeys;
    },
    applyCheckboxStates: function () {
        // Повторное применение состояний чекбоксов после загрузки данных
        const gridEl = this.getEl();
        if (gridEl) {
            const checkboxes = gridEl.query('.grid-checkbox');
            checkboxes.forEach((checkbox) => {
                const value = checkbox.getAttribute('data-value');
                checkbox.checked = this.selectedKeys.includes(value);
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

Ext.reg('msoptionsedit-grid-option-merge', miniShop2.grid.OptionMerge);
