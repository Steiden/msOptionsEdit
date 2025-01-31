<?php
/** @var modX $modx */
switch ($modx->event->name) {
    case 'OnDocFormPrerender':
        $assetsUrl = $this->modx->getOption('msoptionsedit_assets_url', $config, $this->modx->getOption('assets_url') . 'components/msoptionsedit/');
        $modx->regClientStartupScript($assetsUrl . 'js/mgr/custom/custom_options.js');
        break;
}