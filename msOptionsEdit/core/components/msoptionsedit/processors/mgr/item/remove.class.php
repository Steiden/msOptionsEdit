<?php

class msOptionsEditItemRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'msOptionsEditItem';
    public $classKey = 'msOptionsEditItem';
    public $languageTopics = ['msoptionsedit'];
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('msoptionsmergeaction_item_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var msOptionsMergeAction $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('msoptionsmergeaction_item_err_nf'));
            }

            $productId = $object->get('product_id');
            $optionKey = $object->get('option_key');
            $oldValue = $object->get('old_value');
            $newValue = $object->get('new_value');

            // Проверяем данные
            if (empty($productId) || empty($optionKey)) {
                $this->modx->log(1, "Invalid product_id or option_key for object ID {$id}.");
                continue;
            }

            // Удаление значения в таблице mw2_product_options, если $oldValue пустое
            $stmt = null;
            if (empty($oldValue)) {
                $sql = "DELETE FROM `gewn5fer4GqeR_ms2_product_options` WHERE `product_id` = :product_id AND `key` = :key AND `value` = :value";
                $stmt = $this->modx->prepare($sql);

                $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindValue(':key', $optionKey, PDO::PARAM_STR);
                $stmt->bindValue(':value', $newValue, PDO::PARAM_STR);
            } else if (empty($newValue)) {
                // Создать запись значения опции в таблице ms2_product_options
                $sql = "INSERT INTO `gewn5fer4GqeR_ms2_product_options` (`product_id`, `key`, `value`) VALUES (:product_id, :key, :value)";
                $stmt = $this->modx->prepare($sql);

                $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindValue(':key', $optionKey, PDO::PARAM_STR);
                $stmt->bindValue(':value', $oldValue, PDO::PARAM_STR);
            } else if (!empty($oldValue)) {
                // Прямое обновление значения в таблице ms2_product_options
                $sql = "UPDATE `gewn5fer4GqeR_ms2_product_options` SET `value` = :old_value WHERE `product_id` = :product_id AND `key` = :key AND `value` = :value";
                $stmt = $this->modx->prepare($sql);

                $stmt->bindValue(':old_value', $oldValue, PDO::PARAM_STR);
                $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
                $stmt->bindValue(':key', $optionKey, PDO::PARAM_STR);
                $stmt->bindValue(':value', $newValue, PDO::PARAM_STR);
            }

            if ($stmt->execute()) {
                $this->modx->log(3, "Updated option: product_id={$productId}, key={$optionKey}, value={$oldValue}");

                // Удаляем запись из истории
                $object->remove();

                // Обновляем кэш
                $this->modx->cacheManager->refresh([
                    'minishop2/product/options' => ['product_id' => $productId]
                ]);
            } else {
                $this->modx->log(1, "Failed to update option: product_id={$productId}, key={$optionKey}");
            }
        }
        return $this->success();
    }
}

return 'msOptionsEditItemRemoveProcessor';