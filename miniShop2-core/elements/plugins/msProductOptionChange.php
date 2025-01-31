<?php
/** @var modX $modx */

$corePath = $modx->getOption('msoptionsedit_core_path', $modx->getOption('core_path') . 'components/msoptionsedit/');
$modelPath = $corePath . 'model/';
$modx->addPackage('msoptionsedit', $modelPath);

switch ($modx->event->name) {
    case 'OnBeforeDocFormSave':
        // Проверяем, является ли сохраняемый ресурс продуктом MiniShop2
        if ($resource->get('class_key') !== 'msProduct') {
            return;
        }

        $productId = $resource->get('id');

        // Получаем текущие опции продукта
        $currentOptions = $modx->getCollection('msProductOption', ['product_id' => $productId], false);

        $currentOptionsMap = [];
        foreach ($currentOptions as $option) {
            $currentOptionsMap[$option->get('key')] = $option->get('value');
        }

        // Сохраняем старые опции в реестр MODX
        $modx->setPlaceholder('msProductOptions_old_' . $productId, $currentOptionsMap);
        break;

    case 'OnDocFormSave':
        // Проверяем, является ли сохраняемый ресурс продуктом MiniShop2
        if ($resource->get('class_key') !== 'msProduct') {
            return;
        }

        $productId = $resource->get('id');
        $newOptions = $resource->get('options'); // Новые значения опций продукта

        if (empty($newOptions)) {
            return;
        }

        // Получаем старые значения из реестра
        $currentOptionsMap = $modx->getPlaceholder('msProductOptions_old_' . $productId) ?? [];

        // Обрабатываем измененные и новые опции
        foreach ($newOptions as $key => $newValue) {
            $newValue = $newValue[0];
            $oldValue = $currentOptionsMap[$key] ?? null;

            if ($oldValue != $newValue) {
                $modx->log(1, 'Key: ' . $key);

                // Записываем изменения в таблицу истории
                $history = $modx->newObject('msOptionsEditItem');
                $history->fromArray([
                    'product_id' => $productId,
                    'option_key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'createdon' => date('Y-m-d H:i:s'),
                    'createdby' => $modx->user->get('id'),
                    'editedon' => date('Y-m-d H:i:s'),
                    'editedby' => $modx->user->get('id'),
                ]);
                $history->save();
            }

            // Удаляем ключ из currentOptionsMap, чтобы позже обработать оставшиеся как удаленные
            unset($currentOptionsMap[$key]);
        }

        // Обрабатываем удаленные опции
        foreach ($currentOptionsMap as $key => $oldValue) {

//            if ($key != 'width' && $key != 'height' & $key != 'length') {
                // Если ключ есть в $currentOptionsMap, но отсутствует в $newOptions, опция была удалена
                $history = $modx->newObject('msOptionsEditItem');
                $history->fromArray([
                    'product_id' => $productId,
                    'option_key' => $key,
                    'old_value' => $oldValue,
                    'new_value' => '', // Пустое значение для удаленных опций
                    'createdon' => date('Y-m-d H:i:s'),
                    'createdby' => $modx->user->get('id'),
                    'editedon' => date('Y-m-d H:i:s'),
                    'editedby' => $modx->user->get('id'),
                ]);
                $history->save();
//            }
        }
        break;
}