<?php

class msOptionUpdateProcessor extends modProcessor
{
    public $classKey = 'msOption';
    public $objectType = 'msOption';

    public function process()
    {
        // Проверка прав доступа
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        // Получаем данные из запроса
        $currentKey = $this->getProperty('currentKey');
        $currentCaption = $this->getProperty('currentCaption');
        $newKey = $this->getProperty('newKey');
        $newCaption = $this->getProperty('newCaption');
        $mergeValues = $this->getProperty('mergeValues'); // Массив выбранных опций для слияния

        // Проверка обязательных данных
        if (empty($currentKey) || empty($currentCaption) || empty($newKey) || empty($newCaption)) {
            return $this->failure($this->modx->lexicon('msoptionsedit_item_err_ns'));
        }

        // Переименование
        if ($newKey !== $currentKey || $newCaption !== $currentCaption) {
            return $this->renameOption($currentKey, $newKey, $newCaption);
        }

        // Слияние
        if (!empty($mergeValues)) {
            return $this->mergeOptions($currentKey, $mergeValues, $newKey, $newCaption);
        }

        return $this->failure($this->modx->lexicon('msoptionsedit_item_err_invalid_action'));
    }

    private function renameOption($currentKey, $newKey, $newCaption)
    {
        // Создание новой опции с измененными key и caption
        $newOption = $this->modx->newObject('msOption');
        $newOption->set('key', $newKey);
        $newOption->set('caption', $newCaption);

        // Копируем другие свойства опции, если нужно
        // ...

        if ($newOption->save()) {
            // Переносим все связи с прежней опции на новую
            $this->replaceOptionLinks($currentKey, $newKey);

            return $this->success($this->modx->lexicon('msoptionsedit_item_update_success'));
        }

        return $this->failure($this->modx->lexicon('msoptionsedit_item_update_failure'));
    }

    private function mergeOptions($currentKey, $mergeValues, $newKey, $newCaption)
    {
        // Создаем новую опцию с объединенными значениями
        $newOption = $this->modx->newObject('msOption');
        $newOption->set('key', $newKey);
        $newOption->set('caption', $newCaption);

        if ($newOption->save()) {
            // Обновляем связи для всех опций, которые будут слиты
            foreach ($mergeValues as $mergeValue) {
                $this->replaceOptionLinks($mergeValue, $newKey);
            }

            return $this->success($this->modx->lexicon('msoptionsedit_item_merge_success'));
        }

        return $this->failure($this->modx->lexicon('msoptionsedit_item_merge_failure'));
    }

    private function replaceOptionLinks($oldKey, $newKey)
    {
        // Обновление всех связей с опциями
        $tableName = $this->modx->getTableName('msProductOption');
        $sql = "UPDATE {$tableName} SET `key` = :newKey WHERE `key` = :oldKey";
        $stmt = $this->modx->prepare($sql);
        $stmt->bindValue(':oldKey', $oldKey);
        $stmt->bindValue(':newKey', $newKey);
        $stmt->execute();
    }
}

return 'msOptionUpdateProcessor';
