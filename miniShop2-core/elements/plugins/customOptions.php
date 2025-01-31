<?php
/** @var modX $modx */
switch ($modx->event->name) {
    case 'OnManagerPageBeforeRender':
        $modx->regClientStartupScript('/assets/components/minishop2/js/mgr/settings/option/custom.js');
        break;
}