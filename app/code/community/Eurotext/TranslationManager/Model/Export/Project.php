<?php

class Eurotext_TranslationManager_Model_Export_Project
{
    const STEP_START = 0;
    const STEP_BUILD_LANGXML = 1;
    const STEP_COLLECT_PRODUCTS = 2;
    const STEP_COLLECT_CATEGORIES = 3;
    const STEP_COLLECT_CMSPAGES = 4;
    const STEP_COLLECT_CMSBLOCKS = 5;
    const STEP_EXPORT_ATTRIBUTES = 6;
    const STEP_COLLECT_TEMPLATES_FILES = 7;
    const STEP_COLLECT_TEMPLATES_DATABASE = 8;
    const STEP_GENERATE_CONTROL_FILE = 9;
    const STEP_COMPRESS_FILES = 10;
    const STEP_TRANSMIT_ARCHIVE = 11;
    const STEP_DONE = 12;
    /**
     * @var Eurotext_TranslationManager_Helper_Data
     */
    private $helper;

    /**
     * @var Eurotext_TranslationManager_Helper_Config
     */
    private $configHelper;

    public function __construct()
    {
        $this->helper = Mage::helper('eurotext_translationmanager');
        $this->configHelper = Mage::helper('eurotext_translationmanager/config');
    }


    /**
     * @param int                                             $step
     * @param Eurotext_TranslationManager_Block_Response_Ajax $block
     * @param Eurotext_TranslationManager_Model_Project       $project
     * @param int                                             $offset
     */
    public function export(
        $step,
        Eurotext_TranslationManager_Block_Response_Ajax $block,
        Eurotext_TranslationManager_Model_Project $project,
        $offset
    ) {
        // steps:
        // -------------------------------------------------------------------------------------
        // 0: Jump to step 4, if language files is not selected for export
        // 1: Find language *.csv files and write found filenames to eurotext_csv
        // 2: For each offset import one *.csv file to eurotext_csv_data
        // 3: For each offset find missing translations for one *.csv and generate xml files
        // -------------------------------------------------------------------------------------
        // 4: Jump to step 5, if product files were selected manually
        //    For each offset: Find missing translations for one product
        // 5: Jump to step 6, if category files where selected manually
        //    For each offset: Find missing translations for one category

        if ($step == self::STEP_START) {
            $this->cleanupProjectExportDirectory($project);

            Mage::getModel('eurotext_translationmanager/export_project_ftpUpload')
                ->validateFtpConnection();
            $this->setDataOnStatusBlock(
                $block,
                ['offset' => 0, 'step' => self::STEP_BUILD_LANGXML, 'status_msg' => '']
            );
        } elseif ($step == self::STEP_BUILD_LANGXML) {
            $rvSub = $this->ajaxexportAction_BuildLangXML($project, $offset);
            $this->setDataOnStatusBlock($block, $rvSub);
        } elseif ($step == self::STEP_COLLECT_PRODUCTS) {
            $rvSub = $this->ajaxexportAction_CollectProducts($project, $offset);
            $this->setDataOnStatusBlock($block, $rvSub);
        } elseif ($step == self::STEP_COLLECT_CATEGORIES) {
            $rvSub = $this->ajaxexportAction_CollectCategories($project, $offset);
            $this->setDataOnStatusBlock($block, $rvSub);
        } elseif ($step == self::STEP_COLLECT_CMSPAGES) {
            $rvSub = $this->ajaxexportAction_CollectCMSPages($project, $offset);
            $this->setDataOnStatusBlock($block, $rvSub);
        } elseif ($step == self::STEP_COLLECT_CMSBLOCKS) {
            $rvSub = $this->ajaxexportAction_CollectCMSBlocks($project, $offset);
            $this->setDataOnStatusBlock($block, $rvSub);
        } elseif ($step == self::STEP_COLLECT_TEMPLATES_FILES) {
            $rvSub = $this->ajaxexportAction_CollectTemplatesFromFiles($project);
            $this->setDataOnStatusBlock($block, $rvSub);
        } elseif ($step == self::STEP_COLLECT_TEMPLATES_DATABASE) {
            $rvSub = $this->ajaxexportAction_CollectTemplatesFromDatabase($project, $offset);
            $this->setDataOnStatusBlock($block, $rvSub);
        } elseif ($step == self::STEP_EXPORT_ATTRIBUTES) {
            $rvSub = $this->ajaxexportAction_ExportAttributes($project);
            $this->setDataOnStatusBlock($block, $rvSub);
        } elseif ($step == self::STEP_GENERATE_CONTROL_FILE) {
            $this->ajaxexportAction_GenerateControlFile($project);
            $block->setStep(self::STEP_COMPRESS_FILES);
            $block->setStatusMsg($this->helper->__('Generating ZIP archive'));
        } elseif ($step == self::STEP_COMPRESS_FILES) {
            $this->ajaxexportAction_CompressFiles($project);
            $block->setStep(self::STEP_TRANSMIT_ARCHIVE);
            $block->setStatusMsg($this->helper->__('Sending data'));
        } elseif ($step == self::STEP_TRANSMIT_ARCHIVE) {
            $this->ajaxexportAction_TransmitArchive($project);
            $block->setStep(self::STEP_DONE);
            $block->setStatusMsg($this->helper->__('Data sent.'));
        } else {
            $block->setStep(self::STEP_DONE);
            $block->setStatusMsg($this->helper->__('Export done.'));
            $block->setStatusCode('success');
            $block->setFinished(true);
        }
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return mixed[]
     */
    private function ajaxexportAction_BuildLangXML(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_localeCsvFiles')
            ->process($project, $offset);
    }

    /**
     * @return Eurotext_TranslationManager_Helper_Eurotext
     */
    private function getEurotextHelper()
    {
        return Mage::helper('eurotext_translationmanager/eurotext');
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return mixed[]
     */
    private function ajaxexportAction_CollectProducts(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_product')->process($project, $offset);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return mixed[]
     */
    private function ajaxexportAction_CollectCategories(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_category')->process($project, $offset);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return mixed[]
     */
    private function ajaxexportAction_CollectCMSPages(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_cmsPage')->process($project, $offset);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return mixed[]
     */
    private function ajaxexportAction_CollectCMSBlocks(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_cmsBlock')->process($project, $offset);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return mixed[]
     */
    private function ajaxexportAction_CollectTemplatesFromFiles(Eurotext_TranslationManager_Model_Project $project)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_emailFileTemplates')
            ->process($project);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return mixed[]
     */
    private function ajaxexportAction_CollectTemplatesFromDatabase(
        Eurotext_TranslationManager_Model_Project $project,
        $offset
    ) {
        return Mage::getModel('eurotext_translationmanager/export_project_emailDatabaseTemplates')
            ->process($project, $offset);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return mixed[]
     */
    private function ajaxexportAction_ExportAttributes(Eurotext_TranslationManager_Model_Project $project)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_attribute')->process($project);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return mixed[]
     */
    private function ajaxexportAction_GenerateControlFile(Eurotext_TranslationManager_Model_Project $project)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_createControlFile')->create($project);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function ajaxexportAction_CompressFiles(Eurotext_TranslationManager_Model_Project $project)
    {
        $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')->getExportXMLPath($project);
        $this->getEurotextHelper()
            ->zipFolder($xmlDir, $this->getProjectZipFilename($project), 'Created by Eurotext Magento Module');
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return string
     */
    private function getProjectZipFilename(Eurotext_TranslationManager_Model_Project $project)
    {
        $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')->getExportXMLPath($project);

        if ($project->getZipFilename() != '') {
            return $xmlDir . DS . $project->getZipFilename();
        }

        $filename = $this->generateZipFilename($project);

        return "$xmlDir/$filename";
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function ajaxexportAction_TransmitArchive(Eurotext_TranslationManager_Model_Project $project)
    {
        return Mage::getModel('eurotext_translationmanager/export_project_ftpUpload')
            ->upload($project, $this->getProjectZipFilename($project));
    }

    /**
     * @param Eurotext_TranslationManager_Block_Response_Ajax $block
     * @param mixed []                                        $rvSub
     */
    private function setDataOnStatusBlock(Eurotext_TranslationManager_Block_Response_Ajax $block, $rvSub)
    {
        $block->setStep($rvSub['step']);
        $block->setOffset($rvSub['offset']);
        $block->setStatusMsg($rvSub['status_msg']);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function resetProjectZipFilename(Eurotext_TranslationManager_Model_Project $project)
    {
        $project->setZipFilename('');
        $project->save();
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return string
     */
    private function generateZipFilename(Eurotext_TranslationManager_Model_Project $project)
    {
        $filename = sprintf(
            'ET-%s-%s-%s.zip',
            Mage::helper('eurotext_translationmanager/filesystem')
                ->getFilenameSafeString($this->configHelper->getShopname()),
            Mage::helper('eurotext_translationmanager/filesystem')
                ->getFilenameSafeString($this->configHelper->getCustomerId()),
            date('Y-m-d_H-i-s_T')
        );

        $project->setZipFilename($filename);
        $project->save();

        return $filename;
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     */
    private function cleanupProjectExportDirectory(Eurotext_TranslationManager_Model_Project $project)
    {
        $filesystemHelper = Mage::helper('eurotext_translationmanager/filesystem');
        $xmlPath = $filesystemHelper->getExportXMLPath($project);
        $filesystemHelper->deleteDirectoryRecursively($xmlPath);
        $this->resetProjectZipFilename($project);
    }
}
