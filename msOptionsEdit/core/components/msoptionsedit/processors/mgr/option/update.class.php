<?php

class msOptionUpdateProcessor extends modObjectUpdateProcessor
{
    /** @var msOption $object */
    public $object;
    public $classKey = 'msOption';
    public $objectType = 'ms2_option';
    public $languageTopics = ['minishop2:default'];
    protected $oldKey = null;
    public $permission = 'mssetting_save';

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }

        // Сохраняем оригинальные значения
        $this->originalValues = $this->object->toArray();
        return parent::initialize();

        return parent::initialize();
    }

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $key = $this->getProperty('key');
        if (empty($key)) {
            $this->addFieldError('key', $this->modx->lexicon($this->objectType . '_err_name_ns'));
        }
        $key = str_replace('.', '_', $key);

        $oldKey = $this->object->get('key');
        if (($oldKey != $key)) {
            if ($this->doesAlreadyExist(['key' => $key])) {
                $this->addFieldError('key', $this->modx->lexicon($this->objectType . '_err_ae', ['key' => $key]));
            }

            $this->oldKey = $oldKey;
        }
        $this->setProperty('key', $key);

        return parent::beforeSet();
    }

    /**
     * @param array $categories
     */
    public function removeNotAssignedCategories($categories)
    {
        $q = $this->modx->newQuery('msCategoryOption');
        $q->command('DELETE');
        $q->where(['option_id' => $this->object->get('id')]);
        $q->where(['category_id:IN' => $categories]);
        $q->prepare();
        $q->stmt->execute();
    }

    /**
     *
     */
    public function updateOldKeys()
    {
        if ($this->oldKey) {
            $q = $this->modx->newQuery('msProductOption');
            $q->command('UPDATE');
            $q->where(['key' => $this->oldKey]);
            $q->set(['key' => $this->object->get('key')]);
            $q->prepare();
            $q->stmt->execute();
        }
    }

    /**
     *
     */
    public function updateAssignedCategory()
    {
        $categoryId = $this->getProperty('category_id');
        if ($categoryId) {
            /** @var msCategoryOption $ftCat */
            $ftCat = $this->modx->getObject('msCategoryOption', [
                'option_id' => $this->object->get('id'),
                'category_id' => $categoryId,
                'active' => true,
            ]);

            if ($ftCat) {
                $ftCat->fromArray($this->getProperties());
                $ftCat->save();
            }
        }
    }

    /**
     * @return bool
     */
    public function afterSave()
    {
        if ($categories = json_decode($this->getProperty('categories', false), true)) {
            $enabled = $disabled = [];
            foreach ($categories as $id => $checked) {
                if ($checked) {
                    $enabled[] = $id;
                } else {
                    $disabled[] = $id;
                }
            }
            if ($enabled) {
                $this->object->setCategories($enabled);
            }
            if ($disabled) {
                $this->removeNotAssignedCategories($disabled);
            }
            $this->object->set('categories', $categories);
        }
        $this->updateAssignedCategory();
        $this->updateOldKeys();

        // Добавляем запись изменений
        $this->logOptionChanges();

        return parent::afterSave();;
    }

    protected function logOptionChanges()
    {
        $userId = $this->modx->user->id;
        $newValues = $this->object->toArray();

        // Создаём запись в msOptionsEditItem
        /** @var msOptionsEditItem $optionEdit */
        $optionEdit = $this->modx->newObject('msOptionsEditItem');
        $optionEdit->fromArray([
            'option_key' => $this->object->get('key'),
            'createdon' => date('Y-m-d H:i:s'),
            'createdby' => $userId,
            'editedon' => date('Y-m-d H:i:s'),
            'editedby' => $userId,
        ]);
        $optionEdit->save();

        // Создаём записи для каждого изменённого свойства
        foreach ($this->originalValues as $key => $oldValue) {
            if (array_key_exists($key, $newValues) && $oldValue !== $newValues[$key]) {
                /** @var msOptionEditItemProperty $optionProperty */
                $optionProperty = $this->modx->newObject('msOptionEditItemProperty');
                $optionProperty->fromArray([
                    'option_edit_id' => $optionEdit->get('id'),
                    'property' => $key,
                    'old_value' => $oldValue,
                    'new_value' => $newValues[$key],
                    'createdon' => date('Y-m-d H:i:s'),
                    'createdby' => $userId,
                    'editedon' => date('Y-m-d H:i:s'),
                    'editedby' => $userId,
                ]);
                $optionProperty->save();
            }
        }
    }
}

return 'msOptionUpdateProcessor';
