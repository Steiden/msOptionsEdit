<?php

class msOptionValueUpdateProcessor extends modProcessor
{
    public $classKey = 'msProductOption';
    public $objectType = 'msProductOption';

    public function escapeRegex($pattern)
    {
        // Найдем все части строки, заключенные в скобки
        $matches = [];
        preg_match_all('/\((.*?)\)/', $pattern, $matches);

        // Сохраним эти части, чтобы потом вернуть их обратно в строку
        $preservedParts = $matches[0];

        // Удалим все части, заключенные в скобки, чтобы они не подвергались экранированию
        $patternWithoutBrackets = preg_replace('/\((.*?)\)/', '{BRACKET_CONTENT}', $pattern);

        // Экранируем все символы, кроме символов в скобках
        $escapedPattern = preg_replace_callback('/[^\(\)\{BRACKET_CONTENT}]/', function ($match) {
            $specialChars = ['*', '+', '?', '.', '[', ']', '{', '}', '^', '$', '\\', '|', '/', '-', '=', '!', ':'];
            return in_array($match[0], $specialChars) ? '\\' . $match[0] : $match[0];
        }, $patternWithoutBrackets);

        // Восстанавливаем части строки, которые были в скобках
        foreach ($preservedParts as $index => $part) {
            $escapedPattern = preg_replace('/\{BRACKET_CONTENT\}/', $part, $escapedPattern, 1);
        }

        return $escapedPattern;
    }

    function convertMaskToRegex($mask): string
    {
        $pattern = '/\(000\)/';
        $regex = preg_replace($pattern, '(.+)', $mask);

        // Экранируем все специальные символы, кроме скобок
        $escapedRegex = $this->escapeRegex($regex);

        return '/' . $escapedRegex . '/';
    }


    public function convertRegexForMySQL($regex): string
    {
        // Удаляем начальные и конечные символы "/"
        return trim($regex, '/');
    }

    public function process()
    {
        try {
            // Проверка прав доступа
            if (!$this->checkPermissions()) {
                return $this->failure($this->modx->lexicon('access_denied'));
            }

            // Получаем данные из запроса
            $currentValue = $this->getProperty('currentValue');
            $action = $this->getProperty('actionKey');
            $mergeValues = $this->getProperty('mergeValues');
            $newValue = $this->getProperty('newValue');
            $optionKey = $this->getProperty('optionKey');
            $mask = $this->getProperty('regexMask');
//            $delimiter = $this->getProperty('regexDelimiter');
            $coefficient = $this->getProperty('coef');
            $optionKeys = $this->getProperty('optionKeys');
            $excludeWords = $this->getProperty('excludeWords');
            $applyToAll = $this->getProperty('applyToAll');
            $productId = $this->getProperty('productId');

            $actionType = $this->getProperty('actionType');
            $limit = $this->getProperty('limit');
            $offset = $this->getProperty('offset');
            $iteration = $this->getProperty('iteration');

            $applyToAll = $applyToAll === 'true';
            $excludeWordsArray = array_map('trim', preg_split('/,\s*/', $excludeWords));
            $optionKeysArray = explode(',', $optionKeys);
            $tableName = $this->modx->getTableName('msProductOption');
            $mask = $this->convertMaskToRegex($mask);
            $regex = $this->convertRegexForMySQL($mask);

//            $this->modx->log(3, "----------------------------------------------------------------");
//            $this->modx->log(3, "Data: " . print_r([
//                    'action' => $action,
//                    'actionType' => $actionType,
//                    'limit' => $limit,
//                    'offset' => $offset,
//                ], 1));
//            $this->modx->log(3, "----------------------------------------------------------------");

            // Проверка на обязательные поля
            if (empty($currentValue) || empty($action) || empty($optionKey)) {
                return $this->failure($this->modx->lexicon('msoptionsedit_item_err_ns'));
            }

            switch ($action) {
                case 'rename':
                    if ($actionType == 'count') {
                        $criteria = [
                            'value' => $currentValue,
                            'key' => $optionKey,
                            'action' => $action
                        ];

                        $count = $this->getProductCount($criteria);

                        $this->modx->log(3, "Количество продуктов для переименования: " . $count);
                        $this->modx->log(3, "\n");
                        $this->modx->log(3, "================================================");
                        $this->modx->log(3, "\n");

                        return $this->success("Количество опций на переименование получено", ['count' => $count]);
                    }
                    if ($actionType == 'process_batch') {
                        $this->modx->log(3, "Выбрано действие \"Переименование\"");

                        $affectedRows = $this->renameValues($optionKey, $currentValue, $newValue, $limit, $iteration);
                        if($affectedRows < $limit) {
                            $this->modx->log(modX::LOG_LEVEL_INFO, 'COMPLETED');
                        }
                        return $this->success("Значения опций успешно переименованы", ['affectedOptions' => $affectedRows]);
                    }

                case 'merge':
                    if ($actionType == 'count') {
                        $criteria = [
                            'key' => $optionKey,
                            'mergeValues' => $mergeValues,
                            'action' => $action
                        ];

                        $count = $this->getProductCount($criteria);

                        $this->modx->log(3, "Количество продуктов для слияния: " . $count);
                        $this->modx->log(3, "\n");
                        $this->modx->log(3, "================================================");
                        $this->modx->log(3, "\n");

                        return $this->success("Количество опций на слияние получено", ['count' => $count]);
                    }
                    if ($actionType == 'process_batch') {
                        $this->modx->log(3, "Выбрано действие \"Слияние\"");

                        $affectedRows = $this->mergeValues($optionKey, $mergeValues, $newValue, $limit, $iteration);
                        if($affectedRows < $limit) {
                            $this->modx->log(modX::LOG_LEVEL_INFO, 'COMPLETED');
                        }
                        return $this->success("Значения опций успешно слиялись", ['affectedOptions' => $affectedRows]);
                    }

                case 'split':
                    if ($actionType == 'count') {
                        $criteria = [
                            'regex' => $regex,
                            'key' => $optionKey,
                            'action' => $action,
                        ];

                        $count = 1;
                        if ($applyToAll) {
                            $count = $this->getProductCount($criteria);
                        }

                        $this->modx->log(3, "Количество продуктов для разделения: " . $count);
                        $this->modx->log(3, "\n");
                        $this->modx->log(3, "================================================");
                        $this->modx->log(3, "\n");

                        return $this->success($this->modx->lexicon('msoptionsedit_item_split_success'), ['count' => $count]);
                    }
                    if ($actionType == 'process_batch') {
                        $this->modx->log(3, "Выбрано действие \"Разделение\"");

                        $criteria = [
                            'regex' => $regex,
                            'key' => $optionKey,
                            'applyToAll' => $applyToAll,
                            'productId' => $productId,
                            'value' => $currentValue
                        ];
                        $affectedRows = $this->processBatch($criteria, $limit, $offset, $mask, $coefficient, $excludeWordsArray, $optionKeysArray, $tableName, $iteration);
                        if($affectedRows < $limit) {
                            $this->modx->log(modX::LOG_LEVEL_INFO, 'COMPLETED');
                        }
                        return $this->success("Значения опций успешно разделены", ['affectedOptions' => $affectedRows]);
                    }

                default:
                    $this->modx->log(1, 'Неизвестное действие: ' . $action);
                    $this->modx->log(3, "COMPLETED");
            }
        } catch (Exception $e) {
            $this->modx->log(1, "Ошибка обновления опций: " . $e->getMessage());
            $this->modx->log(modX::LOG_LEVEL_INFO, 'COMPLETED');
            return $this->failure($e->getMessage());
        }
    }

    private function renameValues($optionKey, $currentValue, $newValue, $limit, $iteration)
    {
        try {
            $this->modx->log(3, "Процесс обновления опций начался!");
            $this->modx->log(3, "\n");
            $this->modx->log(3, "================================================");
            $this->modx->log(3, "\n");
            $this->modx->log(3, "Итерация №" . $iteration);

            $this->modx->beginTransaction();

            $tableName = $this->modx->getTableName('msProductOption');

            $sql = "UPDATE {$tableName} SET value = :newValue WHERE `key` = :key AND `value` = :value LIMIT :limit";
            $sqlStmt = $this->modx->prepare($sql);
            $sqlStmt->bindValue(':newValue', $newValue);
            $sqlStmt->bindValue(':key', $optionKey);
            $sqlStmt->bindValue(':value', $currentValue);
            $sqlStmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            if (!$sqlStmt->execute()) {
                $this->modx->rollBack();
                return $this->failure($this->modx->lexicon('msoptionsedit_item_err_save'));
            }

            $this->modx->commit();
            return $sqlStmt->rowCount();
        } catch (Exception $e) {
            $this->modx->rollback();
            $this->modx->log("Ошибка переименования опций: " . $e->getMessage());
            return $this->failure($e->getMessage());
        }
    }

    private function mergeValues($optionKey, $mergeValues, $newValue, $limit, $iteration)
    {
        try {
            $this->modx->log(3, "Процесс обновления опций начался!");
            $this->modx->log(3, "\n");
            $this->modx->log(3, "================================================");
            $this->modx->log(3, "\n");
            $this->modx->log(3, "Итерация №" . $iteration);

            $this->modx->beginTransaction();

            $tableName = $this->modx->getTableName('msProductOption');

            $mergeValues = explode(',', $mergeValues);
            $mergeValues = array_map(function($value) {
                return "'" . $value . "'";
            }, $mergeValues);
            $mergeValues = implode(',', $mergeValues);

            // Выполняем обновление
            $sql = "UPDATE {$tableName} SET value = :newValue WHERE `key` = :key AND value IN ($mergeValues) LIMIT :limit";
            $sqlStmt = $this->modx->prepare($sql);
            $sqlStmt->bindValue(':newValue', $newValue);
            $sqlStmt->bindValue(':key', $optionKey);
            $sqlStmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            if (!$sqlStmt->execute()) {
                $this->modx->rollBack();
                return $this->failure($this->modx->lexicon('msoptionsedit_item_merge_failure'));
            }

            $this->modx->commit();
            return $sqlStmt->rowCount();
        } catch (Exception $e) {
            $this->modx->rollBack();
            $this->modx->log("Ошибка слияния опций: " . $e->getMessage());
            return $this->failure($e->getMessage());
        }
    }

    public function getProductCount($criteria)
    {
        $action = $criteria['action'];
        $tableName = $this->modx->getTableName('msProductOption');
        $options = [];

        if ($action == 'rename') {
            $options = $this->modx->getCollection('msProductOption', [
                'key' => $criteria['key'],
                'value' => $criteria['value'],
            ]);
        } else if ($action == 'merge') {
            $mergeValuesArray = explode(',', $criteria['mergeValues']);
            $options = $this->modx->getCollection('msProductOption', [
                'key' => $criteria['key'],
                'value:IN' => $mergeValuesArray
            ]);
        } else if ($action == 'split') {
            $options = $this->modx->getCollection('msProductOption', [
                'key' => $criteria['key'],
                'value:REGEXP' => $criteria['regex'],
            ]);
        }

        return count($options);
    }

    public function processBatch($criteria, $limit, $offset, $mask, $coefficient, $excludeWordsArray, $optionKeysArray, $tableName, $iteration)
    {
        $this->modx->log(3, "Процесс обновления опций начался!");
        $this->modx->log(3, "\n");
        $this->modx->log(3, "================================================");
        $this->modx->log(3, "\n");
        $this->modx->log(3, "Итерация №" . $iteration);

        $productsOptions = [];

        if ($criteria['applyToAll']) {
            $sql = "SELECT * FROM {$tableName} WHERE `key` = :optionKey AND `value` REGEXP :regex LIMIT :limit";
            $stmt = $this->modx->prepare($sql);
            $stmt->bindValue(':optionKey', $criteria['key']);
            $stmt->bindValue(':regex', $criteria['regex']);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $productsOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            $sql = "SELECT * FROM {$tableName} WHERE `key` = :optionKey AND `product_id` = :productId LIMIT 1";
            $stmt = $this->modx->prepare($sql);
            $stmt->bindValue(':optionKey', $criteria['key']);
            $stmt->bindValue(':productId', $criteria['productId']);
            if ($stmt->execute()) {
                $productsOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $this->modx->log(3, "Найдено продуктов: " . count($productsOptions));

        foreach ($productsOptions as $productOption) {
            $this->splitValue($productOption, $mask, $coefficient, $excludeWordsArray, $optionKeysArray, $tableName, $criteria['key']);
        }

        return count($productsOptions);
    }

    public function splitValue($productOption, $mask, $coefficient, $excludeWordsArray, $optionKeysArray, $tableName, $optionKey)
    {
        try {
            $this->modx->exec("START TRANSACTION");

//            $this->modx->log(3, "Start split next value");
//            $this->modx->log(3, "productOption = " . print_r($productOption, 1));
//            $this->modx->log(3, "product_id (1) = " . $productOption['value']);

            $corePath = $this->modx->getOption('msoptionsedit_core_path', $this->modx->getOption('core_path') . 'components/msoptionsedit/');
            $modelPath = $corePath . 'model/';
            $this->modx->addPackage('msoptionsedit', $modelPath);

            $splitIsSuccess = true;
            $value = $productOption['value'];

//            $this->modx->log(3, "Edit product option: mask = " . $mask);

            // Применяем маску для получения значений
            preg_match($mask, $value, $matches);
            $value = $matches[0] ?? '';

//            $this->modx->log(3, "Edit product option: matches = " . print_r($matches, 1));

            if (!$value) {
                return;
            }

            $value = trim($value);
            $splitValues = array_slice($matches, 1);

            // Удаляем слова из массива $excludeWordsArray в каждом значении $splitValues
            foreach ($splitValues as &$value) {
                foreach ($excludeWordsArray as $word) {
                    $value = preg_replace('/' . $word . '/u', '', $value);
                }
                $value = trim($value);
            }
            unset($value);

//            $this->modx->log(3, "Edit product option: splitValues = " . print_r($splitValues, 1));

            // Применяем коэффициент
            $splitValues = array_map(function ($val) use ($coefficient) {
                return $val * $coefficient;
            }, $splitValues);

            // Записываем значения в соответствующие опции
            foreach ($optionKeysArray as $index => $key) {
                // Если ключ равен '*', пропускаем
                if ($key === '*') continue;

                if ($splitValues[$index]) {
                    // Проверяем, существует ли уже запись для указанного ключа и product_id
                    $checkSql = "SELECT COUNT(*) FROM {$tableName} WHERE `key` = :key AND product_id = :productId";
                    $checkStmt = $this->modx->prepare($checkSql);
                    $checkStmt->bindValue(':key', $key);
                    $checkStmt->bindValue(':productId', $productOption['product_id']);
                    $checkStmt->execute();
                    $exists = (int)$checkStmt->fetchColumn();

//                    $this->modx->log(3, "Edit product option: product_id = " . $productOption['product_id'] . ", key = " . $key . ", value = " . $splitValues[$index]);

                    if ($exists) {
                        // Обновляем значение, если запись уже существует
                        $updateSql = "UPDATE {$tableName} SET value = :value WHERE `key` = :key AND product_id = :productId";
                        $updateStmt = $this->modx->prepare($updateSql);
                        $updateStmt->bindValue(':value', $splitValues[$index]);
                        $updateStmt->bindValue(':key', $key);
                        $updateStmt->bindValue(':productId', $productOption['product_id']);

                        $oldOption = $this->modx->getObject('msProductOption', array(
                            'key' => $key,
                            'product_id' => $productOption['product_id'],
                        ), false);


                        if ($updateStmt->execute()) {
                            $this->modx->log(3, "✔ Опция с ключом " . $key . " успешно обновлена у продукта " . $productOption['product_id'] . ", значение - " . $splitValues[$index]);

//                            $this->modx->log(MODX_LOG_LEVEL_ERROR, 'Тип $oldOption: ' . gettype($oldOption));
//                            $this->modx->log(3, "Edit product option: old value = " . $oldValue);
                            $oldValue = isset($oldOption) ? $oldOption->get('value') : '';

                            // Записываем изменения в таблицу истории
                            $this->saveOptionHistory($productOption['product_id'], $key, $oldValue, $splitValues[$index]);
                        } else {
                            $splitIsSuccess = false;
                            $this->modx->log(1, "✖ Не получилось обновить опцию с ключом " . $key . " у продукта " . $productOption['product_id'] . ", значение - " . $splitValues[$index]);
                            break;
                        }
                    } else {
                        // Вставляем новую запись, если её ещё нет
                        $insertSql = "INSERT INTO {$tableName} (`key`, value, product_id) VALUES (:key, :value, :productId)";
                        $insertStmt = $this->modx->prepare($insertSql);
                        $insertStmt->bindValue(':key', $key);
                        $insertStmt->bindValue(':value', $splitValues[$index]);
                        $insertStmt->bindValue(':productId', $productOption['product_id']);

                        $oldOption = $this->modx->getObject('msProductOption', array(
                            'key' => $key,
                            'product_id' => $productOption['product_id'],
                        ), false);

                        if ($insertStmt->execute()) {
                            $this->modx->log(3, "✔ Опция с ключом " . $key . " успешно добавлена к продукту " . $productOption['product_id'] . ", значение - " . $splitValues[$index]);

//                            $this->modx->log(MODX_LOG_LEVEL_ERROR, 'Тип $oldOption: ' . gettype($oldOption));
//                            $this->modx->log(3, "Edit product option: old value = " . $oldValue);
                            $oldValue = isset($oldOption) ? $oldOption->get('value') : '';

                            // Записываем изменения в таблицу истории
                            $this->saveOptionHistory($productOption['product_id'], $key, $oldValue, $splitValues[$index]);
                        } else {
                            $splitIsSuccess = false;
                            $this->modx->log(1, "✖ Не получилось обновить опцию с ключом " . $key . " у продукта " . $productOption['product_id'] . ", значение - " . $splitValues[$index]);
                            break;
                        }
                    }

                } else {
                    $splitIsSuccess = false;
                    $this->modx->log(3, "✔ Обработка ключа " . $key . " у продукта " . $productOption['product_id'] . " пропущена (пустое значение)");
                    break;
                }

                $this->modx->log(3, "\n");
            }

            if (!$splitIsSuccess) {
                return;
            }

            // Удаление исходной опции после успешного разделения
            $deleteSql = "DELETE FROM {$tableName} WHERE `key` = :key AND product_id = :productId";
            $deleteStmt = $this->modx->prepare($deleteSql);
            $deleteStmt->bindValue(':key', $optionKey);
            $deleteStmt->bindValue(':productId', $productOption['product_id']);
            $deleteStmt->execute();

            $this->modx->log(3, "");
            $this->modx->log(3, "✔ Опция с ключом " . $optionKey . " у продукта " . $productOption['product_id']. " успешно удалена");
            $this->modx->log(3, "");
            $this->modx->log(3, "--------------------------------------");
            $this->modx->log(3, "");

            $this->modx->exec("COMMIT");
        } catch (\Exception $e) {
            $this->modx->exec("ROLLBACK");
            $this->modx->log(1, "Ошибка разделения опции: " . $e->getMessage());
        }
    }

    /**
     * @param $productId
     * @param $key
     * @param $oldValue
     * @param $newValue
     * @return void
     */
    public function saveOptionHistory($productId, $key, $oldValue, $newValue)
    {
        $history = $this->modx->newObject('msOptionsEditItem');
        $history->fromArray([
            'product_id' => $productId,
            'option_key' => $key,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'createdon' => date('Y-m-d H:i:s'),
            'createdby' => $this->modx->user->get('id'),
            'editedon' => date('Y-m-d H:i:s'),
            'editedby' => $this->modx->user->get('id'),
        ]);
        if($history->save()) {
            $this->modx->log(3, "✔ Запись об обновлении опции с ключом " . $key . " у продукта " . $productId . " успешно создана");
        }
        else {
            $this->modx->log(1, "Не получилось создать запись об обновлении опции с ключом " . $key . " у продукта " . $productId);
        }
    }
}

return 'msOptionValueUpdateProcessor';
