<?php

class msOptionEditItemPropertyGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'msOptionEditItemProperty';
    public $classKey = 'msOptionEditItemProperty';
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
        $optionEditId = $this->getProperty('option_edit_id');
        if ($optionEditId) {
            $c->where(['option_edit_id' => $optionEditId]);
        }

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
            'icon' => 'icon icon-edit',
            'title' => $this->modx->lexicon('msoptionsedit_item_update'),
            //'multiple' => $this->modx->lexicon('msoptionsedit_items_update'),
            'action' => 'updateItem',
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

        if($array['property'] == 'category') {
            $categories = $this->modx->getCollection('modCategory');

            foreach ($categories as $category) {
                if($category->get('id') == $array['old_value']) {
                    $array['old_value'] = $category->get('category');
                }
                if($category->get('id') == $array['new_value']) {
                    $array['new_value'] = $category->get('category');
                }
            }
        }

        if($array['property'] == 'type') {
            $response = $this->modx->runProcessor(
                'mgr/settings/option/gettypes', [ 'name' => 'textfield' ],
                [ 'processors_path' => MODX_CORE_PATH . 'components/minishop2/processors/' ]
            );
            $types = $response->getResponse();

            foreach ($types as $type) {
                if($type->get('name') == $array['old_value']) {
                    $array['old_value'] = $type->get('caption');
                }
                if($type->get('name') == $array['new_value']) {
                    $array['new_value'] = $type->get('caption');
                }
            }
        }

        return $array;
    }
}

return 'msOptionEditItemPropertyGetListProcessor';