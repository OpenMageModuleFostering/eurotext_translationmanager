<?php

class Eurotext_TranslationManager_Model_Extractor
{
    /**
     * @var ZipArchive
     */
    private $zip;

    /**
     * @param string $file
     * @param string $intoDirectory
     */
    public function extract($file, $intoDirectory)
    {
        $this->zip = new ZipArchive();
        if ($errorCode = $this->zip->open($file) !== true) {
            throw new Exception($this->handleErrorCode($errorCode));
        }

        if (!$this->zip->extractTo($intoDirectory)) {
            throw new Exception(sprintf('Zipfile %s can\'t be extracted.', $file));
        }
    }

    /**
     * @param int $errorCode
     * @return string
     */
    private function handleErrorCode($errorCode)
    {
        switch ($errorCode) {
            case ZipArchive::ER_EXISTS:
                return 'File already exists.';
            case   ZipArchive::ER_INCONS:
                return 'Zip archive inconsistent.';
            case  ZipArchive::ER_INVAL:
                return 'Invalid argument.';
            case  ZipArchive::ER_MEMORY:
                return 'Malloc failure.';
            case  ZipArchive::ER_NOENT:
                return 'No such file.';
            case  ZipArchive::ER_NOZIP:
                return 'Not a zip archive.';
            case  ZipArchive::ER_OPEN  :
                return 'Can\'t open file . ';
            case  ZipArchive::ER_READ   :
                return 'Read error . ';
            case  ZipArchive::ER_SEEK    :
                return 'Seek error . ';
        }

        return 'Unknown error.';
    }

    public function __destruct()
    {
        $this->zip->close();
    }
}
