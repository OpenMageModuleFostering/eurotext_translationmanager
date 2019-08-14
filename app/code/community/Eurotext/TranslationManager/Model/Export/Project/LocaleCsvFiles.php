<?php

use Eurotext_TranslationManager_Model_Export_Project as ExportProject;

class Eurotext_TranslationManager_Model_Export_Project_LocaleCsvFiles
{
    /**
     * @var Varien_File_Csv
     */
    private $csvReader;

    /**
     * @var DOMDocument
     */
    private $doc;

    public function __construct()
    {
        $this->csvReader = new Varien_File_Csv();
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $offset
     * @return array
     */
    public function process(Eurotext_TranslationManager_Model_Project $project, $offset)
    {
        /** @var Mage_Core_Model_Abstract $csv */
        $csvCollection = Mage::getResourceModel('eurotext_translationmanager/csv_collection')
            ->filterByProject($project);

        $csv = $csvCollection->getItemById($offset);
        $count = $csvCollection->count();
        if (!$csv) {
            return [
                'status_msg' => $this->getHelper()->__('Exported language files.'),
                'step'       => ExportProject::STEP_COLLECT_PRODUCTS,
                'offset'     => 1,
            ];
        }

        $xmlDir = $this->createSubdirectory($project);

        $filenameSrc = $csv->getData('filename');
        $filenameEn = preg_replace('#/[a-z]{2}_[A-Z]{2}/#', '/en_US/', $filenameSrc);
        $filenameDst = preg_replace('#/[a-z]{2}_[A-Z]{2}/#', "/{$project->getStoreviewDstLocale()}/", $filenameSrc);

        $statusMessage = $this->getHelper()->__('Batch %s / %s CSV File: %s', $offset + 1, $count, $filenameSrc);

        $srcCsvFilename = basename($filenameEn);
        $xmlFilename = $this->createFilenameFromCsvFilename($srcCsvFilename, $xmlDir);

        $xmlFile = $this->createXml($this->removeBasePathFrom($filenameSrc), $this->removeBasePathFrom($filenameDst));

        $itemCount = 0;
        $csvDataSrc = $this->readCsvIntoArray($filenameSrc);
        $csvDataDst = $this->readCsvIntoArray($filenameDst);
        $csvDataEn = $this->readCsvIntoArray($filenameEn);

        foreach ($csvDataSrc as $txtSrc) {
            $itemCount++;

            $txtEn = isset($csvDataEn[$txtSrc]) ? $csvDataEn[$txtSrc] : '';
            $txtDst = isset($csvDataDst[$txtSrc]) ? $csvDataDst[$txtSrc] : '';

            $this->createLine($project, $itemCount, $xmlFile, $txtEn, $txtDst, $txtSrc);
        }

        if ($itemCount) {
            $this->doc->save($xmlFilename);
        }

        return [
            'step'       => ExportProject::STEP_BUILD_LANGXML,
            'offset'     => $offset + 1,
            'status_msg' => $statusMessage,
        ];
    }

    /**
     * @return Eurotext_TranslationManager_Helper_Data
     */
    private function getHelper()
    {
        return Mage::helper('eurotext_translationmanager');
    }

    /**
     * @param string $srcCsvFilename
     * @param string $xmlDir
     * @return string
     */
    private function createFilenameFromCsvFilename($srcCsvFilename, $xmlDir)
    {

        $xmlFilename = Mage::helper('eurotext_translationmanager/filesystem')
            ->getFilenameSafeString($srcCsvFilename);
        $xmlFilename = pathinfo($xmlFilename, PATHINFO_FILENAME);

        return "$xmlDir/$xmlFilename.xml";
    }

    /**
     * @param string $filenameSrc
     * @param string $filenameDst
     * @return DOMNode
     */
    private function createXml($filenameSrc, $filenameDst)
    {
        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;
        $translation = $this->doc->createElement('translation');

        $this->writeAttribute($translation, 'src_filename', $filenameSrc);
        $this->writeAttribute($translation, 'dst_filename', $filenameDst);

        $this->doc->appendChild($translation);

        return $translation;
    }

    /**
     * @param int     $itemCount
     * @param string  $txtEn
     * @param string  $locale
     * @param DOMNOde $lineExport
     */
    private function createLineContextNode($itemCount, $txtEn, $locale, DOMNode $lineExport)
    {
        $lineContext = $this->doc->createElement('line-context');
        $this->writeAttribute($lineContext, 'num', $itemCount);
        $this->writeAttribute($lineContext, 'context', 'yes');
        $this->writeAttribute($lineContext, 'locale', $locale);

        Mage::helper('eurotext_translationmanager/xml')->appendTextChild($this->doc, $lineContext, $txtEn);
        $lineExport->appendChild($lineContext);
    }

    /**
     * @param DOMNode $node
     * @param string  $name
     * @param string  $value
     */
    private function writeAttribute(DOMNode $node, $name, $value)
    {
        $lineIndex = $this->doc->createAttribute($name);
        $lineIndex->value = $value;
        $node->appendChild($lineIndex);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $itemCount
     * @param string                                    $txtSrc
     * @param DOMNode                                   $lineExport
     */
    private function createLineNode(
        Eurotext_TranslationManager_Model_Project $project,
        $itemCount,
        $txtSrc,
        $lineExport
    ) {
        $lineContext = $this->doc->createElement('line');
        $this->writeAttribute($lineContext, 'num', $itemCount);
        $this->writeAttribute($lineContext, 'locale-src', $project->getStoreviewSrcLocale());
        $this->writeAttribute($lineContext, 'locale-dst', $project->getStoreviewDstLocale());
        Mage::helper('eurotext_translationmanager/xml')->appendTextChild($this->doc, $lineContext, $txtSrc);
        $lineExport->appendChild($lineContext);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @param int                                       $itemCount
     * @param DOMNode                                   $translation
     * @param string                                    $txtEn
     * @param string                                    $txtDst
     * @param string                                    $txtSrc
     */
    private function createLine(
        Eurotext_TranslationManager_Model_Project $project,
        $itemCount,
        $translation,
        $txtEn,
        $txtDst,
        $txtSrc
    ) {
        $comment = $this->doc->createComment('Line ' . $itemCount);
        $translation->appendChild($comment);

        $lineExport = $this->doc->createElement('line' . $itemCount);
        $translation->appendChild($lineExport);

        $this->createLineContextNode($itemCount, $txtEn, 'en_US', $lineExport);
        $this->createLineContextNode($itemCount, $txtDst, $project->getStoreviewDstLocale(), $lineExport);
        $this->createLineNode($project, $itemCount, $txtSrc, $lineExport);
    }

    /**
     * @param string $filenameSrc
     * @return array
     */
    private function readCsvIntoArray($filenameSrc)
    {
        try {
            return $this->csvReader->getDataPairs($filenameSrc);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function removeBasePathFrom($path)
    {
        return str_replace(Mage::getBaseDir('app'), '', $path);
    }

    /**
     * @param Eurotext_TranslationManager_Model_Project $project
     * @return string
     */
    private function createSubdirectory(Eurotext_TranslationManager_Model_Project $project)
    {
        $subdir = 'framework';
        $xmlDir = Mage::helper('eurotext_translationmanager/filesystem')
            ->getXmlSubdirectoryAndMakeSureItExists($project, $subdir);

        return $xmlDir;
    }
}
