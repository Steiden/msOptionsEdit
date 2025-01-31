<?php

class msOptionsEdit
{
    /** @var modX $modx */
    public $modx;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
		$corePath = $this->modx->getOption('msoptionsedit_core_path', $config, $this->modx->getOption('core_path') . 'components/msoptionsedit/');
		$assetsUrl = $this->modx->getOption('msoptionsedit_assets_url', $config, $this->modx->getOption('assets_url') . 'components/msoptionsedit/');
		$assetsPath = $this->modx->getOption('msoptionsedit_assets_path', $config, $this->modx->getOption('base_path') . 'assets/components/msoptionsedit/');

		$this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('msoptionsedit', $this->config['modelPath']);
        $this->modx->lexicon->load('msoptionsedit:default');
    }
}