<?php
class msProductGetOptionsProcessor extends modObjectGetListProcessor {
    public $classKey = 'msProductOption';
    public $defaultSortField = 'key';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $c->select('product_id, key, value');
        return $c;
    }
}
return 'msProductGetOptionsProcessor';