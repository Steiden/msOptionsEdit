<?php
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
} else {
    require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var msOptionsEdit $msOptionsEdit */
$corePath = $modx->getOption('msoptionsedit_core_path', null, $modx->getOption('core_path') . 'components/msoptionsedit/');
$msOptionsEdit = $modx->getService('msOptionsEdit', 'msOptionsEdit', MODX_CORE_PATH . 'components/msoptionsedit/model/');
$modx->lexicon->load('msoptionsedit:default');

// handle request

$path = $modx->getOption('processorsPath', $msOptionsEdit->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);