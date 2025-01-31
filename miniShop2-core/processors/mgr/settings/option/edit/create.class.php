<?php

class msOptionEditCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'msOptionEdit';
    public $classKey = 'msOptionEdit';
    public $languageTopics = ['msoptionsedit'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $option_key = trim($this->getProperty('option_key'));
        if (empty($option_key)) {
            $this->modx->error->addField('option_key', $this->modx->lexicon('msoptionsedit_item_err_option_key'));
        }

        return parent::beforeSet();
    }

}

return 'msOptionEditCreateProcessor';