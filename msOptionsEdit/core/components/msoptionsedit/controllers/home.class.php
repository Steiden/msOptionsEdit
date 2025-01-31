<?php

/**
 * The home manager controller for msOptionsEdit.
 *
 */
class msOptionsEditHomeManagerController extends modExtraManagerController
{
    /** @var msOptionsEdit $msOptionsEdit */
    public $msOptionsEdit;


    /**
     *
     */
    public function initialize()
    {
        $corePath = $this->modx->getOption('msoptionsedit_core_path', array(), $this->modx->getOption('core_path') . 'components/msoptionsedit/');
        $this->msOptionsEdit = $this->modx->getService('msOptionsEdit', 'msOptionsEdit', $corePath . 'model/');

        $mainCorePath = MODX_CORE_PATH . 'components/minishop2/';
        $modelPath = $mainCorePath . 'model/';
        $this->modx->addPackage('minishop2', $modelPath);

        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['msoptionsedit:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('msoptionsedit');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->msOptionsEdit->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/msoptionsedit.js');
        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/action/items.grid.js');
        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/action/items.window.js');

//         $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/option/grid.js');
//         $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/option/window.js');
//         $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/option/tree.js');
//         $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/option/types/combobox.grid.js');

//        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/options/options.grid.js');
//        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/options/options.window.js');

//        $this->addJavascript($this->msOptionsEdit->config['jsUrl'] . 'mgr/widgets/product_options/productoptions.grid.js');


        $this->addHtml('<script type="text/javascript">
        msOptionsEdit.config = ' . json_encode($this->msOptionsEdit->config) . ';
        msOptionsEdit.config.connector_url = "' . $this->msOptionsEdit->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "msoptionsedit-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="msoptionsedit-panel-home-div"></div>';

        return '';
    }
}