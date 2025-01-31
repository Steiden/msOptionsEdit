<?php

class msOptionEditRemoveProcessor extends modObjectProcessor
{
    public $objectType = 'msOptionEdit';
    public $classKey = 'msOptionEdit';
    public $languageTopics = ['msoptionsedit'];
    //public $permission = 'remove';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('msoptionsedit_item_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var msOptionEdit $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('msoptionsedit_item_err_nf'));
            }

            $object->remove();
        }

        return $this->success();
    }

}

return 'msOptionEditRemoveProcessor';