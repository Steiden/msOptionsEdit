<?php

class msOptionsEditItemGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'msOptionsEditItem';
    public $classKey = 'msOptionsEditItem';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'DESC';
    //public $permission = 'list';


    /**
     * We do a special check of permissions
     * because our objects is not an instances of modAccessibleObject
     *
     * @return boolean|string
     */
    public function beforeQuery()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where([
                'name:LIKE' => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ]);
        }

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
            'icon' => 'icon icon-eye',
            'title' => $this->modx->lexicon('msoptionsedit_item_show'),
            //'multiple' => $this->modx->lexicon('msoptionsedit_items_update'),
            'action' => 'showItem',
            'button' => true,
            'menu' => true,
        ];

        // Remove
        $array['actions'][] = [
            'cls' => '',
            'icon' => 'icon icon-trash-o action-red',
            'title' => $this->modx->lexicon('msoptionsedit_item_remove'),
            'multiple' => $this->modx->lexicon('msoptionsedit_items_remove'),
            'action' => 'removeItem',
            'button' => true,
            'menu' => true,
        ];

        return $array;
    }
}

return 'msOptionsEditItemGetListProcessor';