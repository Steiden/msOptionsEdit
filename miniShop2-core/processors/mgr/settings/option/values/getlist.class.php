<?php
class msOptionValuesGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'msProductOption';
    public $defaultSortField = 'key';
    public $defaultSortDirection = 'asc';

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'value:LIKE' => "%{$query}%",
            ]);
        }

       $optionKey = $this->getProperty('option_key');

        $c->select([
            'product_id',
            'key',
            'value',
        ]);
        $c->where([
            'key' => $optionKey,
        ]);

        $c->sortby('product_id', 'ASC');
        $c->groupby('BINARY value');

        return $c;
    }

    /**
    * @param xPDOObject $object
    *
    * @return array
    */
    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $array['actions'] = [];

        // Edit
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('miniShop2_update_option_value'),
            'action' => 'updateOptionValue',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }
}
return 'msOptionValuesGetListProcessor';