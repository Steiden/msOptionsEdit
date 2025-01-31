<?php

class msOptionsMergeActionUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'msOptionsMergeAction';
    public $classKey = 'msOptionsMergeAction';
    public $languageTopics = ['msoptionsmergeaction'];
    //public $permission = 'save';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return bool|string
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if (empty($id)) {
            return $this->modx->lexicon('msoptionsmergeaction_item_err_ns');
        }

        return parent::beforeSet();
    }
}

return 'msOptionsMergeActionUpdateProcessor';
