<?php

/**
 * @method int[] getSelectedTransactionEmailDatabase()
 * @method setSelectedTransactionEmailDatabase(int[] $ids)
 * @method setUseAjax(boolean $useAjax)
 */
class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_TransactionEmailsDatabase
    extends Mage_Adminhtml_Block_System_Email_Template_Grid
{
    use Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Grid_OverwriteCheckboxRenderer;

    public function __construct()
    {
        parent::__construct();
        $this->setId('transactionEmailsFromDatabaseGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('transactionEmailsFromDatabase_filter');
    }

    protected function _prepareColumns()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        $this->addColumn(
            'translateFiles',
            [
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'transactionEmailsDatabase',
                'values'           => $this->_getSelectedTransactionEmailsDatabase(),
                'align'            => 'center',
                'index'            => 'template_id',
                'use_index'        => true,
                'filter'           => false,
                'disabled'         => !$project->isEditable() ? 'disabled="disabled"' : '',
            ]
        );

        parent::_prepareColumns();

        $this->removeColumn('action');
        $this->removeColumn('modified_at');
        $this->removeColumn('added_at');

        return $this;
    }

    public function _getSelectedTransactionEmailsDatabase()
    {
        $translateFiles = $this->getSelectedTransactionEmailDatabase();
        if (!is_array($translateFiles)) {
            $translateFiles = $this->getSelectedTransactionEmailsFromDatabase();
        }

        return $translateFiles;
    }

    private function getSelectedTransactionEmailsFromDatabase()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::registry('project');

        return $project->getTransactionEmailDatabase();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/transactionmailsDatabaseGrid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return null;
    }
}
