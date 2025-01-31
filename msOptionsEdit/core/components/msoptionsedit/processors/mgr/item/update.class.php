<?php

class msOptionsEditItemUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'msOptionsEditItem';
    public $classKey = 'msOptionsEditItem';
    public $languageTopics = ['msoptionsedit'];
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
            return $this->modx->lexicon('msoptionsedit_item_err_ns');
        }

        $option_id = trim($this->getProperty('option_id'));
        if (empty($option_id)) {
            $this->modx->error->addField('name', $this->modx->lexicon('msoptionsedit_item_err_option_id'));
        }

        return parent::beforeSet();
    }
}

return 'msOptionsEditItemUpdateProcessor';
