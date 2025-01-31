<?php

class msProductOptionUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'msProductOption';
    public $languageTopics = ['yourcomponent:default'];
    public $objectType = 'msproductoption';

    public function beforeSet()
    {
        $newValue = $this->getProperty('value');
        $oldValue = $this->object->get('value');

        if ($newValue === $oldValue) {
//            return $this->modx->lexicon('yourcomponent.option_no_changes');
            return;
        }

        $this->setProperty('old_value', $oldValue);
        return parent::beforeSet();
    }

    public function afterSave()
    {
        $productId = $this->object->get('product_id');
        $optionKey = $this->object->get('key');
        $oldValue = $this->getProperty('old_value');
        $newValue = $this->getProperty('value');
        $userId = $this->modx->user->get('id');

        // Запись изменений в историю
        $history = $this->modx->newObject('msOptionsEditItem');
        $history->fromArray([
            'product_id' => $productId,
            'option_key' => $optionKey,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'createdon' => date('Y-m-d H:i:s'),
            'createdby' => $userId,
        ]);

        if (!$history->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Failed to save change history for msProductOption.');
        }

        return parent::afterSave();
    }
}

return 'msProductOptionUpdateProcessor';
