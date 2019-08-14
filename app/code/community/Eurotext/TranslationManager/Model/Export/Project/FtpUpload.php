<?php

class Eurotext_TranslationManager_Model_Export_Project_FtpUpload
{
    const FTP_HOST = 'eurotext-services.de';
    const FTP_PORT = 21;

    /**
     * @var Eurotext_TranslationManager_Model_Project
     */
    private $project;

    /**
     * @var Eurotext_TranslationManager_Helper_Data
     */
    private $helper;

    /**
     * @var string
     */
    private $ftpUsername;

    /**
     * @var string
     */
    private $ftpPassword;

    /**
     * @var string
     */
    private $fallbackFilename;

    /**
     * @var resource
     */
    private $ftpConnection;

    public function __construct()
    {
        $this->helper = Mage::helper('eurotext_translationmanager');
        $this->configHelper = Mage::helper('eurotext_translationmanager/config');

        $this->ftpUsername = $this->configHelper->getFtpUsername();
        $this->ftpPassword = $this->configHelper->getFtpPassword();
    }

    public function upload(Eurotext_TranslationManager_Model_Project $project, $zipFile)
    {
        $this->project = $project;
        $this->fallbackFilename = Mage::getBaseDir('export') . DS . $this->project->getData('zip_filename');

        if (!$this->isUploadToEurotextEnabled()) {
            $this->helper->log(
                'File was not transmitted, FTP Upload is disabled in System > Configuration > Developer > Log',
                Zend_Log::ERR
            );
            $this->createFallbackFile($zipFile);
            $this->clearProjectFolder();
            $this->updateProjectStatus();

            return;
        }

        $uploadSuccess = $this->uploadZipFileToEurotext($zipFile);

        if (false === $uploadSuccess || true === $this->configHelper->isDebugMode()) {
            $this->createFallbackFile($zipFile);
        }

        $this->clearProjectFolder();
        $this->updateProjectStatus();
    }

    public function validateFtpConnection()
    {
        if (!$this->isUploadToEurotextEnabled()) {
            return;
        }
        try {
            $this->validateUsername();
            $this->validateFtpConnectExists();
            $this->validateFtpConnectIsEnabled();

            $this->openFtpConnection();

            $this->helper->log('Translation Portal Server successfully connected.', Zend_Log::INFO);

        } catch (Eurotext_TranslationManager_Exception_FtpException $e) {
            if ($this->ftpConnection) {
                ftp_close($this->ftpConnection);
            }

            $this->helper->log('Could not login to Translation Portal Server.', Zend_Log::ERR);

            throw new Eurotext_TranslationManager_Exception_FtpException(
                'Archive could not be transmitted. Please use the debug info in var/log/eurotext_fatal.log'
                . "\n\n" . $e->getMessage()
            );
        }
    }

    private function openFtpConnection()
    {
        $this->ftpConnection = @ftp_connect(self::FTP_HOST, self::FTP_PORT, 30);
        if (!@ftp_login($this->ftpConnection, $this->ftpUsername, $this->ftpPassword)) {
            throw new Eurotext_TranslationManager_Exception_FtpException(
                'Could not login to Translation Portal Server.'
            );
        }
        $this->validateFtpConnectWorks();
    }

    /**
     * @return bool
     */
    private function isUploadToEurotextEnabled()
    {
        return !$this->configHelper->isFtpUploadDisabled();
    }

    private function clearProjectFolder()
    {
        $xmlPath = Mage::helper('eurotext_translationmanager/filesystem')->getExportXMLPath($this->project);
        Mage::helper('eurotext_translationmanager/filesystem')->deleteDirectoryRecursively($xmlPath);
    }

    /**
     * @param string $zipFile
     */
    private function createFallbackFile($zipFile)
    {
        if (!copy($zipFile, $this->fallbackFilename)) {
            $message = sprintf('Could not copy the project data file to %s', $this->fallbackFilename);
            $this->helper->log($message, Zend_Log::ERR);
            throw new Exception($message);
        }
    }

    private function updateProjectStatus()
    {
        $this->project->setProjectStatus(Eurotext_TranslationManager_Model_Project::STATUS_EXPORTED_TO_EUROTEXT);
        $this->project->save();
    }

    /**
     * @param string $functionName
     * @return bool
     */
    private function isFunctionEnabled($functionName)
    {
        $disabled = explode(',', ini_get('disable_functions'));

        return !in_array($functionName, $disabled);
    }

    private function validateUsername()
    {
        if (trim($this->ftpUsername) == '') {
            $this->helper->log('Login data is not set.', Zend_Log::ERR);
            throw new Eurotext_TranslationManager_Exception_FtpException(
                $this->helper->__(
                    'There seems to be a problem with your login data. Please check username and password!'
                )
            );
        }
    }

    private function validateFtpConnectExists()
    {
        if (!function_exists('ftp_connect')) {
            $this->helper->log('There is no FTP Client available: ftp_connect does not exist.', Zend_Log::CRIT);
            throw new Eurotext_TranslationManager_Exception_FtpException(
                $this->helper->__(
                    'There is no FTP Client available: ftp_connect does not exist.'
                )
            );
        }
    }

    private function validateFtpConnectIsEnabled()
    {
        if (!$this->isFunctionEnabled('ftp_connect')) {
            $this->helper->log('There is no FTP Client available: ftp_connect is disabled in PHP.', Zend_Log::CRIT);
            throw new Eurotext_TranslationManager_Exception_FtpException(
                $this->helper->__(
                    'There is no FTP Client available: ftp_connect is disabled in PHP.'
                )
            );
        }
    }

    private function validateFtpConnectWorks()
    {
        if ($this->ftpConnection === false) {
            $this->helper->log('Could not connect to Translation Portal Server.', Zend_Log::ERR);
            throw new Eurotext_TranslationManager_Exception_FtpException(
                $this->helper->__(
                    'Could not connect to server. Could be a temporary error or firewall problem. You could also check for a new module version. It might be a problem with your login data, too. Please check username and password!'
                )
            );
        }
    }

    private function cleanupFtp()
    {
        @ftp_delete($this->ftpConnection, $this->project->getData('zip_filename'));
        @ftp_delete($this->ftpConnection, $this->project->getData('zip_filename') . '.uploading');
    }

    private function setupFtpSettings()
    {
        ftp_pasv($this->ftpConnection, true);
        ftp_chdir($this->ftpConnection, '/');
    }

    /**
     * @param string $zipFile
     */
    private function uploadZipWithUploadingExtension($zipFile)
    {
        if (ftp_put($this->ftpConnection, $this->project->getZipFilename() . '.uploading', $zipFile, FTP_BINARY)) {
            $this->helper->log(
                'File was successfully uploaded to ' . $this->project->getZipFilename() . '.uploading',
                Zend_Log::INFO
            );
        }
    }

    /**
     * @return bool
     */
    private function removeUploadingExtension()
    {
        $filename = $this->project->getZipFilename();
        $uploadSuccess = ftp_rename($this->ftpConnection, $filename . '.uploading', $filename);
        if ($uploadSuccess) {
            $this->helper->log('File was successfully renamed to ' . $filename, Zend_Log::INFO);
        }

        return $uploadSuccess;
    }

    /**
     * @param string $zipFile
     * @return bool
     */
    private function uploadZipFileToEurotext($zipFile)
    {
        $this->validateFtpConnection();

        $this->openFtpConnection();

        $this->setupFtpSettings();
        $this->cleanupFtp();
        $this->uploadZipWithUploadingExtension($zipFile);
        $uploadSuccess = $this->removeUploadingExtension();

        ftp_close($this->ftpConnection);

        return $uploadSuccess;
    }
}
