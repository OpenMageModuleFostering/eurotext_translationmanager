<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_StatusController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function upgradescopeAction()
    {
        $this->updateScopeFor('product');
        $this->updateScopeFor('category');

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('eurotext_translationmanager/status');
    }

    private function updateScopeFor($type)
    {
        /** @var Mage_Catalog_Model_Resource_Product_Attribute_Collection|Mage_Catalog_Model_Resource_Category_Attribute_Collection $productUrlAttributes */
        $productUrlAttributes = Mage::getResourceModel("catalog/{$type}_attribute_collection")
            ->addFieldToFilter('attribute_code', ['in' => ['url_key', 'url_path']]);
        foreach ($productUrlAttributes as $a) {
            $a->setIsGlobal(Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE);
            $a->save();
        }

    }
}
