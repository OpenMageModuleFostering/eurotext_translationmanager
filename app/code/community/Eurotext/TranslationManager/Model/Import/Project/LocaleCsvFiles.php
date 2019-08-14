<?php

class Eurotext_TranslationManager_Model_Import_Project_LocaleCsvFiles
{
    /**
     * @var Varien_File_Csv
     */
    private $csvHelper;

    public function __construct()
    {
        $this->csvHelper = new Varien_File_Csv();
    }

    public function import($filename)
    {
        $doc = $this->createDomDocument($filename);
        $destinationFile = $this->getDestinationFile($doc);

        $translations = $this->readCsvIntoArray($destinationFile);
        $translations = $this->readNewTranslationsFromXml($doc, $filename, $translations);

        $this->createCsvDirectory($destinationFile);
        $this->writeTranslationToCsv($translations, $destinationFile);
    }

    /**
     * @param $path
     */
    private function createCsvDirectory($path)
    {
        $csvDirectory = dirname($path);
        if (!is_dir($csvDirectory)) {
            mkdir($csvDirectory, 0777, true);
        }
    }

    /**
     * @param string $csv
     * @return string[]
     */
    private function readCsvIntoArray($csv)
    {
        if (!is_file($csv)) {
            return [];
        }

        return $this->csvHelper->getDataPairs($csv);
    }

    /**
     * @param DOMDocument $doc
     * @param string      $path
     * @param string[]    $translations
     * @return string[]
     */
    private function readNewTranslationsFromXml($doc, $path, $translations)
    {
        /** @var $newTranslations DOMNode */
        $newTranslations = $doc->getElementsByTagName("translation")[0];

        foreach ($newTranslations->childNodes as $line) {
            $originalString = null;
            $translatedString = null;
            if (!($line instanceof DOMElement)) {
                continue;
            }
            foreach ($line->childNodes as $content) {
                if (!($line instanceof DOMElement)) {
                    continue;
                }
                if ($this->nodeContainsOrigString($content)) {
                    $originalString = (string)$content->textContent;
                }
                if ($this->nodeContainsTranslation($content)) {
                    $translatedString = (string)$content->textContent;
                }
            }
            $this->validateTranslation($path, $originalString, $translatedString);
            $translations[$originalString] = $translatedString;
        }

        return $translations;
    }

    /**
     *
     * @param string[] $translations
     * @param string   $csvFile
     */
    private function writeTranslationToCsv($translations, $csvFile)
    {
        $translations = $this->fixCsvDataFormat($translations);
        $this->csvHelper->saveData($csvFile, $translations);
    }

    /**
     * @param DOMElement $content
     * @return bool
     */
    private function nodeContainsOrigString($content)
    {
        return $content->nodeName == 'line-context' && $content->getAttribute('locale') == 'en_US';
    }

    /**
     * @param DOMNode $content
     * @return bool
     */
    private function nodeContainsTranslation($content)
    {
        return $content->nodeName == 'line';
    }

    /**
     * @param string $path
     * @param string $originalString
     * @param string $translatedString
     */
    private function validateTranslation($path, $originalString, $translatedString)
    {
        if ($originalString === null || $translatedString === null) {
            throw new Exception(
                sprintf('Something went wrong while reading CSV translation data from "%s"', $path)
            );
        }
    }

    /**
     * @param DOMDocument $doc
     * @return string
     */
    private function getDestinationFile($doc)
    {
        /** @var DOMElement $node */
        $node = $doc->getElementsByTagName('translation')[0];
        $relativeDestinationFile = $node->getAttribute('dst_filename');
        $destinationFile = Mage::getBaseDir('app') . $relativeDestinationFile;

        return $destinationFile;
    }

    /**
     * @param string $filename
     * @return DOMDocument
     */
    private function createDomDocument($filename)
    {
        $doc = new DOMDocument();
        $doc->load($filename);

        return $doc;
    }

    /**
     * @param string[] $translations
     * @return string[]
     */
    private function fixCsvDataFormat($translations)
    {
        $translations = array_map(
            function ($orig, $translation) {
                return [$orig, $translation];
            },
            array_keys($translations),
            $translations
        );

        return $translations;
    }
}
