<?php

class msOptionActionsGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'msOptionsMergeAction'; // Класс, связанный с таблицей действий
    public $defaultSortField = 'name'; // Сортировка по названию
    public $defaultSortDirection = 'ASC';
    public $objectType = 'msoptionsedit.action'; // Тип объекта (для логов)

    public function initialize()
    {
        // Подключение пакета msOptionsEdit
        $corePath = $this->modx->getOption('msoptionsedit_core_path', $this->modx->getOption('core_path') . 'components/msoptionsedit/');
        $modelPath = $corePath . 'model/';
        $msOptionsEdit = $this->modx->getService('msOptionsEdit', 'msOptionsEdit', $modelPath);

//        $this->modx->addPackage('msoptionsedit', $modelPath);

        if (!$msOptionsEdit) {
            return $this->modx->lexicon('msoptionsedit_service_not_found');
        }

        return parent::initialize();
    }

    /**
     * Подготовка запроса к БД
     *
     * @param xPDOQuery $c
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        return $c;
    }

    /**
     * Подготовка строки результата
     *
     * @param xPDOObject $object
     * @return array
     */
    public function prepareRow(xPDOObject $object)
    {
        return $object->toArray();
    }
}

return 'msOptionActionsGetListProcessor';
