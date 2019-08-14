<?php
/**
 * @method Eurotext_Uploader_Model_Config_Browsebutton setDomNodes(array $domNodesIds)
 *      Array of element browse buttons ids
 * @method Eurotext_Uploader_Model_Config_Browsebutton setIsDirectory(bool $isDirectory)
 *      Pass in true to allow directories to be selected (Google Chrome only)
 * @method Eurotext_Uploader_Model_Config_Browsebutton setSingleFile(bool $isSingleFile)
 *      To prevent multiple file uploads set this to true.
 *      Also look at config parameter singleFile (Eurotext_Uploader_Model_Config_Uploader setSingleFile())
 * @method Eurotext_Uploader_Model_Config_Browsebutton setAttributes(array $attributes)
 *      Pass object of keys and values to set custom attributes on input fields.
 * @see http://www.w3.org/TR/html-markup/input.file.html#input.file-attributes
 */
class Eurotext_Uploader_Model_Config_Browsebutton extends Eurotext_Uploader_Model_Config_Abstract
{
    /**
     * Set params for browse button
     */
    protected function _construct()
    {
        $this->setIsDirectory(false);
    }

    /**
     * Get MIME types from files extensions
     *
     * @param string|array $exts
     * @return string
     */
    public function getMimeTypesByExtensions($exts)
    {
        $mimes = array_unique($this->_getHelper()->getMimeTypeFromExtensionList($exts));

        // Not include general file type
        unset($mimes['application/octet-stream']);

        return implode(',', $mimes);
    }
}
