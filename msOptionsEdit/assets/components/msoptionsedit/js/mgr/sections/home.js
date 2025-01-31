msOptionsEdit.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'msoptionsedit-panel-home',
            renderTo: 'msoptionsedit-panel-home-div'
        }]
    });
    msOptionsEdit.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(msOptionsEdit.page.Home, MODx.Component);
Ext.reg('msoptionsedit-page-home', msOptionsEdit.page.Home);