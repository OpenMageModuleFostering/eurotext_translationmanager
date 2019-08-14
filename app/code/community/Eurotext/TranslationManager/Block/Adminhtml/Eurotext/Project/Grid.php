<?php

class Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('projectGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('project_filter');
        $this->setPagerVisibility(false);
        $this->setDefaultLimit(1000);
    }

    protected function _prepareCollection()
    {
        /** @var Eurotext_TranslationManager_Model_Resource_Project_Collection $collection */
        $collection = Mage::getResourceModel('eurotext_translationmanager/project_collection');
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('updated_at', [
            'header' => Mage::helper('eurotext_translationmanager')->__('updated_at'),
            'width'  => '50px',
            'type'   => 'date',
            'index'  => 'updated_at',
        ]);

        $this->addColumn('id', [
            'header' => Mage::helper('eurotext_translationmanager')->__('ID'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'id',
        ]);

        $this->addColumn('project_name', [
            'header' => Mage::helper('eurotext_translationmanager')->__('Name'),
            'index'  => 'project_name',
        ]);

        $this->addColumn('storeview_src', [
            'header'   => Mage::helper('eurotext_translationmanager')->__('Source StoreView'),
            'index'    => 'storeview_src',
            'renderer' => 'eurotext_translationmanager/adminhtml_eurotext_grid_renderer_storeviewWithLocale',
            'filter'   => false,
            'sortable' => false,
        ]);

        $this->addColumn('storeview_dst', [
            'header'   => Mage::helper('eurotext_translationmanager')->__('Target StoreView'),
            'index'    => 'storeview_dst',
            'renderer' => 'eurotext_translationmanager/adminhtml_eurotext_grid_renderer_storeviewWithLocale',
            'filter'   => false,
            'sortable' => false,
        ]);

        $this->addColumn('project_status', [
            'type'    => 'options',
            'header'  => Mage::helper('eurotext_translationmanager')->__('Status'),
            'index'   => 'project_status',
            'options' => Mage::getModel('eurotext_translationmanager/project_source_status')->toArray(),
        ]);

        $this->addColumn('updated_at', [
            'header' => Mage::helper('eurotext_translationmanager')->__('updated_at'),
            'width'  => '50px',
            'type'   => 'date',
            'index'  => 'updated_at',
        ]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', [
                'store'      => $this->getRequest()->getParam('store'),
                'project_id' => $row->getId()
            ]
        );
    }
}
