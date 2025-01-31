msOptionsEdit.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
            baseCls: 'modx-formpanel',
            layout: 'anchor',
            /*
             stateful: true,
             stateId: 'msoptionsedit-panel-home',
             stateEvents: ['tabchange'],
             getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
             */
            hideMode: 'offsets',
            items: [{
                html: '<h2>' + _('msoptionsedit') + '</h2>',
                cls: '',
                style: {margin: '15px 0'}
            }, {
                xtype: 'modx-tabs',
                defaults: {border: false, autoHeight: true},
                border: true,
                hideMode: 'offsets',
                items: [
                    {
                        title: _('msoptionsedit_items'),
                        layout: 'anchor',
                        items: [{
                            xtype: 'msoptionsedit-grid-items',
                            anchor: '100%',
                            cls: 'main-wrapper',
                        }]
                    },
                    {
                        title: _('msoptionsedit_actions'),
                        layout: 'anchor',
                        items: [
                            {
                                html: _('msoptionsedit_intro_msg'),
                                cls: 'panel-desc',
                            },
                            {
                                xtype: 'msoptionsedit-grid-actions',
                                cls: 'main-wrapper',
                            }
                        ]
                    }
                ]
            }
            ]
        }
    );
    msOptionsEdit.panel.Home.superclass.constructor.call(this, config);
}
;
Ext.extend(msOptionsEdit.panel.Home, MODx.Panel);
Ext.reg('msoptionsedit-panel-home', msOptionsEdit.panel.Home);
