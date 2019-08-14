<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Project_ImportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Eurotext_TranslationManager_Helper_Data
     */
    private $helper;

    /**
     * @var Eurotext_TranslationManager_Helper_Eurotext
     */
    private $eurotextHelper;

    protected function _construct()
    {
        parent::_construct();
        $this->helper         = Mage::helper('eurotext_translationmanager');
        $this->eurotextHelper = Mage::helper('eurotext_translationmanager/eurotext');
    }

    public function importAction()
    {
        try {
            $tmpDir = $this->getTempDirectory();
            $this->cleanUp($tmpDir);

            $filename = $this->checkAndMoveZipFileInto($tmpDir);
            $this->saveFilenameToSession($filename);
            $this->saveProjectIdToSession();
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(
                    array_merge(
                        $_REQUEST,
                        ['importExtractUrl' => $this->getUrl('*/eurotext_project_import/importExtract')]
                    )
                )
            );

            return;
        } catch (Exception $e) {
            $message = $this->getHelper()->__('Error Message: %s', $e->getMessage());
            $this->setWarningAndRedirect($message);

            return;
        }
    }

    public function importExtractAction()
    {
        $tmpdir = $this->getTempDirectory();
        try {
            $zipFilename = $this->getFilenameFromSession();
            if (!$zipFilename) {
                $this->setWarningAndRedirect(
                    $this->getHelper()->__('ZIP File not found for project %s', $this->getProjectIdFromSession())
                );

                return;
            }
            Mage::getModel('eurotext_translationmanager/extractor')->extract($zipFilename, $tmpdir);
            $project = Mage::getModel('eurotext_translationmanager/project');
            $project->load($this->getProjectIdFromSession());
            $project->setProjectStatus(
                Eurotext_TranslationManager_Model_Project::STATUS_IMPORT_TRANSLATIONS_INTO_QUEUE
            );
            $project->save();
            $this->_redirect('*/*/importParse');

            return;
        } catch (Exception $e) {
            $this->setWarningAndRedirect(
                $this->getHelper()->__(
                    'ZIP File "%s" could not be extracted for project %s',
                    $e->getMessage(),
                    $this->getProjectIdFromSession()
                )
            );

            return;
        }
    }

    public function importParseAction()
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::getModel('eurotext_translationmanager/project')->load($this->getProjectIdFromSession());
        Mage::register('project', $project);

        if (!$project->isImportFileLoaded()) {
            $session = Mage::getSingleton('adminhtml/session');
            if ($project->isDone()) {
                $session->addError($this->getHelper()->__('Project already imported. Can\'t import again.'));
            } elseif ($project->isExportable()) {
                $session->addError($this->getHelper()->__('Project was not exported yet, please export first!'));
            }
            $this->_redirect('*/eurotext_project/edit', ['project_id' => $project->getId()]);

            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function addFilesToImportQueueAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);

        $controlFile = $this->getTempDirectory() . DIRECTORY_SEPARATOR . 'control.xml';
        $projectId   = $this->getProjectIdFromSession();
        try {
            Mage::getModel('eurotext_translationmanager/import_validator')->validate($projectId, $controlFile);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $this->setAjaxError($message);

            return;
        }

        $project = $this->getProject($projectId);

        /** @var $queue Eurotext_TranslationManager_Model_Import_Queue */
        $queue = Mage::getModel('eurotext_translationmanager/import_queue');

        $queue->cleanUpQueueFor($project);
        $this->resetProcessedFiles();
        $queue->addControlXmlToQueue($project, $controlFile);

        $filesAddedToTheQueue = $queue->getFilesAddedToTheQueue();

        $this->setFilesAddedToQueueOnSession($filesAddedToTheQueue);
        $message = $this->getHelper()->__('Added %s files to the import queue.', $filesAddedToTheQueue);

        $this->addNoteToResponse($message);
    }

    public function processFilesFromQueueAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        try {
            $project  = $this->getProject($this->getProjectIdFromSession());
            $importer = Mage::getModel('eurotext_translationmanager/import_project');
            $importer->import($project);
            $skipped = $importer->getSkippedEntities();

            $this->logSkippedEntities($skipped);
            $filesProcessed               = $this->getAndIncreaseFilesProcessed();
            $filesAddedToQueueFromSession = $this->getFilesAddedToQueueFromSession();

            $message = $this->getHelper()->__(
                'File %s processed of %s.',
                $filesProcessed,
                $filesAddedToQueueFromSession
            );

            if (count(array_filter($skipped))) {
                $message = $this->getHelper()->__(
                    'File %s processed of %s. Skipped %s: IDs %s (because they were deleted since export)',
                    $filesProcessed,
                    $filesAddedToQueueFromSession,
                    $this->getHelper()->__(key($skipped)),
                    current($skipped) ? implode(', ', current($skipped)) : $this->getHelper()->__('none')
                );
            }

            $this->addNoteToResponse($message);

            if ($project->isDone()) {
                $this->getResponse()->setBody(
                    json_encode(
                        [
                            'class'          => 'success', 'message' => 'Finished loading language files.',
                            'continueImport' => false,
                        ]
                    )
                );
            }
        } catch (Exception $e) {
            $this->setAjaxError($e->getMessage());
        }
    }

    /**
     * @param string $directory
     */
    public function cleanUp($directory)
    {
        Mage::helper('eurotext_translationmanager/filesystem')->deleteDirectoryRecursively($directory);
        $this->createDirectory($directory);
        $this->createHtaccessFile($directory);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('eurotext_translationmanager/import');
    }

    /**
     * @return Eurotext_TranslationManager_Helper_Eurotext
     */
    protected function getEurotextHelper()
    {
        return $this->eurotextHelper;
    }

    /**
     * @return string
     */
    private function getTempDirectory()
    {
        return Mage::getBaseDir('tmp') . '/eurotext';
    }

    /**
     * @param string $dir
     */
    private function createDirectory($dir)
    {
        if (is_dir($dir)) {
            return;
        }

        if (!@mkdir($dir) && !is_dir($dir)) {
            $baseDir = Mage::getBaseDir('var');
            $this->getHelper()->log('Temporary directory could not be created in ' . $baseDir, Zend_Log::CRIT);
            throw new Magento_Exception('Eurotext temporary directory could not be created.');
        }
    }

    /**
     * @return Eurotext_TranslationManager_Helper_Data
     */
    private function getHelper()
    {
        return $this->helper;
    }

    /**
     * @param string $dir
     */
    private function createHtaccessFile($dir)
    {
        $htaccessFilename = $dir . DS . '.htaccess';
        if (!is_file($htaccessFilename)) {
            file_put_contents($htaccessFilename, "# Eurotext Temp folder\r\nOrder Deny,Allow\r\nDeny From All");
        }
    }

    /**
     * @param string $tmpDir
     *
     * @return string
     */
    private function checkAndMoveZipFileInto($tmpDir)
    {
        $uploader = new Varien_File_Uploader('translation_file');
        $uploader->setAllowedExtensions(['zip']);
        $uploader->setAllowCreateFolders(true);
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $uploader->save($tmpDir, $uploader->getUploadedFileName()); //save the file on the specified path

        return $tmpDir . DS . $uploader->getUploadedFileName();
    }

    /**
     * @param string $filename
     */
    private function saveFilenameToSession($filename)
    {
        Mage::getSingleton('adminhtml/session')->setData('import_filename', $filename);
    }

    /**
     * @param string $message
     */
    private function setWarningAndRedirect($message)
    {
        Mage::getSingleton('adminhtml/session')->addError($message);
        $this->_redirect('*/eurotext_project/index');
    }

    /**
     * @return string
     */
    private function getFilenameFromSession()
    {
        return Mage::getSingleton('adminhtml/session')->getData('import_filename');
    }

    private function saveProjectIdToSession()
    {
        Mage::getSingleton('adminhtml/session')->setData('project_id', $this->getRequest()->getParam('project_id'));
    }

    /**
     * @return int
     */
    private function getProjectIdFromSession()
    {
        return Mage::getSingleton('adminhtml/session')->getData('project_id');
    }

    /**
     * @param $message
     */
    private function setAjaxError($message)
    {
        $this->getResponse()->setHttpResponseCode(400);
        $this->getResponse()->setBody(
            json_encode(['class' => 'error', 'message' => $this->getHelper()->__($message), 'continueImport' => false])
        );
    }

    /**
     * @param int $projectId
     *
     * @return Eurotext_TranslationManager_Model_Project
     */
    private function getProject($projectId)
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::getModel('eurotext_translationmanager/project')->load($projectId);

        return $project;
    }

    /**
     * @param $message
     */
    private function addNoteToResponse($message)
    {
        $this->getResponse()->setBody(
            json_encode(['class' => 'ok', 'message' => $message, 'continueImport' => true])
        );
    }

    /**
     * @param int $filesAddedToTheQueue
     */
    private function setFilesAddedToQueueOnSession($filesAddedToTheQueue)
    {
        Mage::getSingleton('adminhtml/session')->setData('import_files', $filesAddedToTheQueue);
    }

    /**
     * @return int
     */
    private function getFilesAddedToQueueFromSession()
    {
        return Mage::getSingleton('adminhtml/session')->getData('import_files');
    }

    /**
     * @return int
     */
    private function getAndIncreaseFilesProcessed()
    {
        $session = Mage::getSingleton('adminhtml/session');
        /** @var $processed int */
        $processed = $session->getData('processed_files');
        $session->setData('processed_files', ++$processed);

        return $processed;
    }

    private function resetProcessedFiles()
    {
        $session = Mage::getSingleton('adminhtml/session');
        $session->setData('processed_files', 0);
    }

    /**
     * @param mixed[] $skipped
     */
    private function logSkippedEntities($skipped)
    {
        foreach ($skipped as $entity => $ids) {
            if (!$ids) {
                continue;
            }
            Mage::log(
                sprintf('Entity %s could not be updated: %s', $entity, implode(', ', $ids)),
                Zend_Log::WARN,
                'eurotext_previously_deleted.log',
                true
            );
        }
    }
}
