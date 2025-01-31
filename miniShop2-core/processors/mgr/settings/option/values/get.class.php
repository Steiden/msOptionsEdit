<?php

class msOptionValueGetProcessor extends modProcessor
{
    public $classKey = 'msProductOption';
    public $objectType = 'msProductOption';

    /**
     * Process the request.
     *
     * @return array|string
     */
    public function process()
    {
//         if (!$this->checkPermissions()) {
//             return $this->failure($this->modx->lexicon('access_denied'));
//         }

        // Получаем входные параметры
        $key = $this->getProperty('option_key');
        $value = $this->getProperty('current_value');
        $productId = $this->getProperty('product_id');

        // Проверяем наличие обязательных параметров
        if (empty($key) || empty($value) || empty($productId)) {
            return $this->failure($this->modx->lexicon('msoptionsedit_item_err_ns'));
        }

        // Выполняем запрос для поиска записи
        $query = $this->modx->newQuery($this->classKey);
        $query->where([
            'key' => $key,
            'value' => $value,
            'product_id' => $productId,
        ]);

        /** @var msProductOption $object */
        $object = $this->modx->getObject($this->classKey, $query);

        if (!$object) {
            return $this->failure($this->modx->lexicon('msoptionsedit_item_err_nf'));
        }

        // Возвращаем данные объекта
        return $this->success('', [
            'option_key' => $object->get('key'),
            'current_value' => $object->get('value'),
            'product_id' => $object->get('product_id'),
        ]);
    }
}

return 'msOptionValueGetProcessor';
