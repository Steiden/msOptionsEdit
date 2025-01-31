<?php
/** @var modX $modx */
switch ($modx->event->name) {
    case 'OnDocFormPrerender':
        $modx->sendForward(404);
        break;
}