<?php

class Eurotext_TranslationManager_Model_Resource_Project extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * @var mixed[]
     */
    private $relations = [
        'products'                   => [
            'relation'    => 'products',
            'table'       => 'eurotext_translationmanager/project_products',
            'valueColumn' => 'product_id',
        ],
        'categories'                 => [
            'relation'    => 'categories',
            'table'       => 'eurotext_translationmanager/project_categories',
            'valueColumn' => 'category_id',
        ],
        'blocks'                     => [
            'relation'    => 'blocks',
            'table'       => 'eurotext_translationmanager/project_cmsblocks',
            'valueColumn' => 'block_id',
        ],
        'pages'                      => [
            'relation'    => 'pages',
            'table'       => 'eurotext_translationmanager/project_cmspages',
            'valueColumn' => 'page_id',
        ],
        'translation_files'          => [
            'relation'    => 'translation_files',
            'table'       => 'eurotext_translationmanager/project_csv',
            'valueColumn' => 'filename',
        ],
        'transaction_email_files'    => [
            'relation'    => 'transaction_email_files',
            'table'       => 'eurotext_translationmanager/project_emailtemplate_files',
            'valueColumn' => 'filename',
        ],
        'transaction_email_database' => [
            'relation'    => 'transaction_email_database',
            'table'       => 'eurotext_translationmanager/project_emailtemplate_database',
            'valueColumn' => 'emailtemplate_id',
        ],
    ];

    protected function _construct()
    {
        $this->_init('eurotext_translationmanager/project', 'id');
    }

    public function addAllRelationalData(Eurotext_TranslationManager_Model_Project $project)
    {
        if ($project->isObjectNew()) {
            $this->setRelationsWithEmptyArray($project);

            return;
        }

        $selects = [];
        foreach ($this->relations as $relation) {
            $selects[] = $this->buildQueryForRelation($project, $relation);
        }

        $queryToIncreaseGroupConcat = 'SET SESSION group_concat_max_len = 100000;';
        // TODO query einzeln machen, prüfen ob funktioniert, wenn nicht => Error in critical log und Info an Kunden
        $mainSelect = $this->getReadConnection()->select();
        $mainSelect->union($selects);
        $mainSelect = $queryToIncreaseGroupConcat . (string)$mainSelect;

        /** @var $results Varien_Db_Statement_Pdo_Mysql[] */
        $results = $this->_getWriteAdapter()->multiQuery($mainSelect);

        $this->addRelationDataToProject($project, $results[1]->fetchAll());
    }

    protected function _afterSave(Mage_Core_Model_Abstract $project)
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        foreach ($this->relations as $name => $info) {
            $this->saveDifferenceFor($name, $project);
        }

        return parent::_afterSave($project);
    }

    private function saveDifferenceFor($relation, Eurotext_TranslationManager_Model_Project $project)
    {
        $origData = $project->getOrigData($relation) ?: [];
        $newData = $project->getData($relation);

        if (!is_array($origData) || !is_array($newData)) {
            return;
        }

        $relationInfo = $this->relations[$relation];

        $toDelete = array_unique(array_diff($origData, $newData));
        if ($toDelete) {
            $this->deleteRelations($toDelete, $project, $relationInfo);
        }

        $toAdd = array_unique(array_diff($newData, $origData));
        if ($toAdd) {
            $this->addRelations($toAdd, $project, $relationInfo);
        }
    }

    /**
     * @param mixed[]                                   $toDelete
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param string[]                                  $tableData
     */
    private function deleteRelations($toDelete, Eurotext_TranslationManager_Model_Project $project, array $tableData)
    {
        $adapter = $this->_getWriteAdapter();
        $where = $adapter->quoteInto($tableData['valueColumn'] . ' IN (?) ', $toDelete);
        $where .= $adapter->quoteInto('AND project_id = ?', $project->getId());
        $adapter->delete($this->getTable($tableData['table']), $where);
    }

    /**
     * @param mixed[]                                   $toAdd
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param string[]                                  $tableData
     */
    private function addRelations($toAdd, Eurotext_TranslationManager_Model_Project $project, array $tableData)
    {
        $columnToFill = $tableData['valueColumn'];

        $valuesToSave = array_map(
            function ($value) use ($project, $columnToFill) {
                return [
                    'project_id'  => $project->getId(),
                    $columnToFill => $value,
                ];
            },
            $toAdd
        );
        $this->_getWriteAdapter()->insertMultiple($this->getTable($tableData['table']), $valuesToSave);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param string                                    $relation
     * @return Zend_Db_Select
     */
    private function buildQueryForRelation(Eurotext_TranslationManager_Model_Project $project, $relation)
    {
        $select = $this->getReadConnection()->select();
        $select->from(
            $this->getTable($relation['table']),
            new Zend_Db_Expr(
                "'{$relation['relation']}' AS 'relation', GROUP_CONCAT(`{$relation['valueColumn']}`) AS 'ids'"
            )
        );
        $select->where('project_id = ?', $project->getId());

        return $select;
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param mixed[]                                   $result
     */
    private function addRelationDataToProject(Eurotext_TranslationManager_Model_Project $project, $result)
    {
        foreach ($result as $row) {
            $project->setData($row['relation'], []);
            $project->setOrigData($row['relation'], []);
            if ($row['ids']) {
                $project->setData($row['relation'], explode(',', $row['ids']));
                $project->setOrigData($row['relation'], explode(',', $row['ids']));
            }
        }
        $project->setDataChanges(false);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function setRelationsWithEmptyArray(Eurotext_TranslationManager_Model_Project $project)
    {
        foreach ($this->relations as $relation) {
            $project->setData($relation['relation'], []);
        }
    }
}
