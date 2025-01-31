<?php
class msOptionsEditOptionsGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'msOption';
    public $defaultSortField = 'key';
    public $defaultSortDirection = 'ASC';
    public $languageTopics = ['minishop2:default'];

    public function prepareQueryBeforeCount(xPDOQuery $c) {
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where([
                'key:LIKE' => "%{$query}%",
                'OR:name:LIKE' => "%{$query}%"
            ]);
        }
        return $c;
    }

    public function prepareRow(xPDOObject $object) {
        $array = $object->toArray();
        // Добавьте дополнительные данные, если нужно
        return $array;
    }
}
return 'msOptionsEditOptionsGetListProcessor';