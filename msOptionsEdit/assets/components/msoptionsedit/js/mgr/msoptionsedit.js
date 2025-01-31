var msOptionsEdit = function (config) {
    config = config || {};
    msOptionsEdit.superclass.constructor.call(this, config);
};
Ext.extend(msOptionsEdit, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('msoptionsedit', msOptionsEdit);

msOptionsEdit = new msOptionsEdit();