<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Project_ExportController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        try {
            $project = $this->getProject($this->getRequest()->getParam('project_id'));
            if (!$project->isExportable()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('Project was already exported! Can\'t export it again.')
                );
                $this->_redirect('*/eurotext_project/edit', ['project_id' => $project->getId()]);

                return;
            }
            Mage::register('project', $project);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/eurotext_project/index');

            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function exportAction()
    {
        $this->loadLayout('adminhtml_eurotext_translationmanager_ajax');

        $response = $this->getResponse();
        $response->setHeader('Content-type', 'application/json', true);

        /** @var $block Eurotext_TranslationManager_Block_Response_Ajax */
        $block = $this->getLayout()->getBlock('et.tm.response.ajax');

        $request = $this->getRequest();
        $step = (int)$request->getParam('step');
        $offset = (int)$request->getParam('offset');
        $project_id = (int)$request->getParam('project_id');

        $project = $this->getProject($project_id);

        $block->setStatusMsg($this->__('Please wait …'));
        $block->setStatusCode('ok');
        $block->setStep($step);
        $block->setFinished(false);
        try {
            Mage::getModel('eurotext_translationmanager/export_project')->export($step, $block, $project, $offset);
        } catch (Exception $e) {
            $block->setStatusCode('error');
            $block->setStatusMsg($e->getMessage());
        }
        $response->setBody($block->toJson());
    }

    /**
     * @param int $projectId
     * @return Eurotext_TranslationManager_Model_Project
     */
    private function getProject($projectId)
    {
        return Mage::helper('eurotext_translationmanager/project')->getProject($projectId);
    }
}
