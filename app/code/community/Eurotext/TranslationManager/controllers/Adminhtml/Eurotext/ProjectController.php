<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_ProjectController extends Mage_Adminhtml_Controller_Action
{
    public function categoriesGridAction()
    {
        $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $categories = $this->getRequest()->getPost('categories', null);
        $this->renderCategoryGrid($categories);
    }

    public function categoriesJsonAction()
    {
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($categoryId = (int)$this->getRequest()->getPost('id')) {

            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category->getId()) {
                Mage::register('category', $category);
                Mage::register('current_category', $category);
            }
            $this->getResponse()->setBody(
                $this->_getCategoryTreeBlock()->getTreeJson($category)
            );
        }
    }

    public function categoriesTabAction()
    {
        $project = $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $this->renderCategoryGrid($project->getCategories());
    }

    public function cmsBlocksGridAction()
    {
        $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $cmsBlocks = $this->getRequest()->getPost('cmsBlocks', null);
        $this->renderCmsBlockGrid($cmsBlocks);
    }

    public function cmsBlocksTabAction()
    {
        $project = $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $this->renderCmsBlockGrid($project->getBlocks());
    }

    public function cmsPagesGridAction()
    {
        $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $cmsPages = $this->getRequest()->getPost('cmsPages', null);
        $this->renderCmsPageGrid($cmsPages);
    }

    public function cmsPagesTabAction()
    {
        $project = $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $this->renderCmsPageGrid($project->getPages());
    }

    public function deleteAction()
    {

        /** @noinspection PhpAssignmentInConditionInspection */
        if ($id = $this->getRequest()->getParam('project_id')) {
            /** @var $id int */
            try {

                $model = Mage::getModel('eurotext_translationmanager/project');
                $model->load($id);
                if (!$model->getId()) {
                    Mage::throwException($this->getHelper()->__('Unable to find a project with id "%s" to delete.', $id));
                }
                $model->delete();

                $this->_getSession()->addSuccess(
                    $this->getHelper()->__('The project with id "%s" has been deleted.', $id)
                );

                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->getHelper()->__(
                        'An error occurred while deleting project data with id "%s". Please review log and try again.',
                        $id
                    )
                );
                Mage::logException($e);
            }
            // redirect to edit form
            $this->_redirect('*/*/', ['project_id' => $id]);

            return;
        }
        // display error message
        $this->_getSession()->addError(
            $this->getHelper()->__('Unable to find a project with id "%s" to delete.', $id)
        );
        // go to grid
        $this->_redirect('*/*/');
    }

    public function editAction()
    {
        /** @var $project Eurotext_TranslationManager_Model_Project */
        $project = $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));
        if (!$project->isEditable()) {
            $this->getSession()->addNotice($this->getHelper()->__('Project "%s" was exported. You can\'t edit it anymore.', $project->getProjectName()));
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function productsGridAction()
    {
        $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $products = $this->getRequest()->getPost('products', []);
        $categories = $this->getRequest()->getPost('categories', []);
        $this->renderProductGridAndCategoryTree($products, $categories);
    }

    public function productsTabAction()
    {
        $project = $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $this->renderProductGridAndCategoryTree($project->getProducts(), []);
    }

    public function resetAction()
    {
        $project = $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));
        $project->reset();
        $project->save();
        $this->_redirect('*/*/edit', ['project_id' => $project->getId()]);
    }

    public function saveAction()
    {
        $project = $this->loadAndRegisterProject($this->getRequest()->getParam('id'));
        if ($project->isEditable()) {
            $this->saveProject($project);
        }
    }

    public function transactionEmailFilesGridAction()
    {
        $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $this->loadLayout();

        $transactionEmailFiles = $this->getRequest()->getPost('transactionEmailFiles', null);
        $this->renderTransactionEmailFilesGrid($transactionEmailFiles);

        $this->renderLayout();
    }

    public function transactionEmailFilesTabAction()
    {
        $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $this->loadLayout();

        $transactionEmailDatabase = $this->getRequest()->getPost('transactionEmailsDatabase', null);
        $this->renderTransactionEmailDatabaseGrid($transactionEmailDatabase);

        $transactionEmailFiles = $this->getRequest()->getPost('transactionEmailFiles', null);
        $this->renderTransactionEmailFilesGrid($transactionEmailFiles);

        $this->renderLayout();
    }

    public function transactionmailsDatabaseGridAction()
    {
        $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $this->loadLayout();

        $transactionEmailFiles = $this->getRequest()->getPost('transactionEmailsDatabase', null);
        $this->renderTransactionEmailDatabaseGrid($transactionEmailFiles);
        $this->renderLayout();
    }

    public function translateFilesGridAction()
    {
        $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $translateFiles = $this->getRequest()->getPost('translateFiles', null);
        $this->renderTranslateFileGrid($translateFiles);
    }

    public function translateFilesTabAction()
    {
        $project = $this->loadAndRegisterProject($this->getRequest()->getParam('project_id'));

        $this->renderTranslateFileGrid($project->getTranslationFiles());
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('eurotext_translationmanager/export');
    }

    /**
     * @return Mage_Adminhtml_Block_Catalog_Category_Widget_Chooser
     */
    private function _getCategoryTreeBlock()
    {
        return $this->getLayout()->createBlock(
            'eurotext_translationmanager/adminhtml_eurotext_project_edit_tab_products_categoryTreeAjax',
            '',
            [
                'id'             => $this->getRequest()->getParam('uniq_id'),
                'use_massaction' => $this->getRequest()->getParam('use_massaction', false)
            ]
        );
    }

    /**
     * @param int[] $categories
     */
    private function renderCategoryGrid($categories)
    {
        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Categories $categoryGrid */
        $categoryGrid = $this->loadLayout()->getLayout()->getBlock('project_edit.project.tab.categories');
        $categoryGrid->setSelectedCategories($categories);

        $this->renderLayout();
    }

    /**
     * @param int[] $cmsBlocks
     */
    private function renderCmsBlockGrid($cmsBlocks)
    {
        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_CmsBlock $cmsBlockGrid */
        $cmsBlockGrid = $this->loadLayout()->getLayout()->getBlock('project_edit.project.tab.cmsblocks');
        $cmsBlockGrid->setSelectedCmsBlocks($cmsBlocks);

        $this->renderLayout();
    }

    /**
     * @param int[] $products
     */
    private function renderProductGridAndCategoryTree($products, $categories)
    {
        $this->loadLayout();

        $products = array_unique(
            array_merge(
                Mage::helper('eurotext_translationmanager/category')->getProductIdsByCategoryIds($categories),
                is_array($products) ? $products : []
            )
        );

        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products_Script $scriptBlock */
        $scriptBlock = $this->getLayout()->getBlock('project.edit.project.tab.products.script');
        if ($scriptBlock) {
            $scriptBlock->setSelectedProducts(array_combine($products, $products));
        }

        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products $productGrid */
        $productGrid = $this->getLayout()->getBlock('project_edit.project.tab.products');
        $productGrid->setSelected($products);
        $productGrid->setSelectedProducts($products);

        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products_CategoryTree|Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_Products_CategoryTreeAjax $categoryTree */
        $categoryTree = $this->getLayout()->getBlock('project.edit.project.tab.products.categry_tree');
        if ($categoryTree) {
            $categoryTree->setCategoryIds([]);
        }

        $this->renderLayout();
    }

    /**
     * @param int[] $cmsPages
     */
    private function renderCmsPageGrid($cmsPages)
    {
        $this->loadLayout();
        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_CmsPage $block */
        $block = $this->loadLayout()->getLayout()->getBlock('project_edit.project.tab.cmspages');
        $block->setSelectedCmsPages($cmsPages);

        $this->renderLayout();
    }

    /**
     * @param string[] $transactionEmailFiles
     */
    private function renderTransactionEmailFilesGrid($transactionEmailFiles)
    {
        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_TransactionEmailFiles $block */
        $block = $this->getLayout()->getBlock('project_edit.project.tab.transactionemailfiles');
        $block->setSelectedTransactionEmailFiles($transactionEmailFiles);
    }

    /**
     * @param int[] $transactionEmailsDatabase
     */
    private function renderTransactionEmailDatabaseGrid($transactionEmailsDatabase)
    {
        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_TransactionEmailsDatabase $block */
        $block = $this->getLayout()->getBlock('project_edit.project.tab.transactionemailsdatabase');
        $block->setSelectedTransactionEmailDatabase($transactionEmailsDatabase);
    }

    /**
     * @param string[] $translateFiles
     */
    private function renderTranslateFileGrid($translateFiles)
    {
        $this->loadLayout();
        /** @var Eurotext_TranslationManager_Block_Adminhtml_Eurotext_Project_Edit_Tab_TranslateFiles $block */
        $block = $this->getLayout()->getBlock('project_edit.project.tab.translatefiles');
        $block->setSelectedTranslateFiles($translateFiles);

        $this->renderLayout();
    }

    /**
     * @param int $projectId
     * @return Eurotext_TranslationManager_Model_Project
     */
    private function loadAndRegisterProject($projectId)
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::getModel('eurotext_translationmanager/project')->load($projectId);
        if ($projectId !== null) {
            $project->addAllRelationalData();
        }

        Mage::register('project', $project);

        return $project;
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    private function getSession()
    {
        return Mage::getModel('adminhtml/session');
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function saveProject(Eurotext_TranslationManager_Model_Project $project)
    {
        $project->addData($this->getRequest()->getParams());
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($productIds = $this->getRequest()->getParam('product_ids', null)) {
            $productIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($productIds);
            $project->setProducts($productIds);
        }
        $this->addProductsBySku($project, $productIds);
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($categoryIds = $this->getRequest()->getParam('category_ids', null)) {
            $categoryIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($categoryIds);
            $project->setCategories($categoryIds);
        }
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($cmsBlockIds = $this->getRequest()->getParam('cmsBlock_ids', null)) {
            $cmsBlockIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($cmsBlockIds);
            $project->setBlocks($cmsBlockIds);
        }
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($cmsPageIds = $this->getRequest()->getParam('cmsPage_ids', null)) {
            $cmsPageIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($cmsPageIds);
            $project->setPages($cmsPageIds);
        }
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($transactionEmailFilesFilenames = $this->getRequest()->getParam('transactionEmailFile_ids', null)) {
            $transactionEmailFilesFilenames = array_filter(explode('&', $transactionEmailFilesFilenames));
            $project->setTransactionEmailFiles($transactionEmailFilesFilenames);
        }
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($mailDatabaseIds = $this->getRequest()->getParam('transactionEmailDatabase_ids', null)) {
            $mailDatabaseIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput($mailDatabaseIds);
            $project->setTransactionEmailDatabase($mailDatabaseIds);
        }
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($translationFileFilenames = $this->getRequest()->getParam('translateFiles_ids', null)) {
            $translationFileFilenames = array_filter(explode('&', $translationFileFilenames));
            $project->setTranslationFiles($translationFileFilenames);
        }

        $project->save();
        $this->getSession()->addSuccess(
            $this->getHelper()->__('Project "%s" saved with ID "%s".', $project->getProjectName(), $project->getId())
        );
        $this->_redirect('*/*/edit', ['project_id' => $project->getId()]);
    }

    /**
     * @return Eurotext_TranslationManager_Helper_Data
     */
    private function getHelper()
    {
        return Mage::helper('eurotext_translationmanager');
    }

    /**
     * @param string $skus
     * @return string[]
     */
    private function getBulkProducts($skus)
    {
        $output = preg_split("#(\n|,|;)#", $skus);

        return array_filter(array_map('trim', $output));
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int[]                                     $productIds
     */
    private function addProductsBySku(Eurotext_TranslationManager_Model_Project $project, $productIds)
    {
        $skus = $this->getBulkProducts($this->getRequest()->getParam('bulk_sku'));
        if ($skus) {
            $productIds = $project->getProducts() ?: [];
            $project->setProducts(
                array_filter(
                    array_merge(
                        $productIds,
                        Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToFilter('sku', ['in', $skus])
                            ->getAllIds()
                    )
                )
            );
        }
    }
}
