<?php

class msOptionsMergeActionItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'msOptionsMergeAction';
    public $classKey = 'msOptionsMergeAction';
    public $languageTopics = ['msoptionsmergeaction'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $key = trim($this->getProperty('key'));
        if (empty($key)) {
            $this->modx->error->addField('key', $this->modx->lexicon('msoptionsmergeaction_item_err_key'));
        }

        return parent::beforeSet();
    }

}

return 'msOptionsMergeActionItemCreateProcessor';