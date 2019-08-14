<?php

/**
 * @method Eurotext_Uploader_Model_Config_Uploader setTarget(string $url)
 *      The target URL for the multipart POST request.
 * @method Eurotext_Uploader_Model_Config_Uploader setSingleFile(bool $isSingleFile)
 *      Enable single file upload.
 *      Once one file is uploaded, second file will overtake existing one, first one will be canceled.
 * @method Eurotext_Uploader_Model_Config_Uploader setChunkSize(int $chunkSize) The size in bytes of each uploaded chunk of data.
 * @method Eurotext_Uploader_Model_Config_Uploader setForceChunkSize(bool $forceChunkSize)
 *      Force all chunks to be less or equal than chunkSize.
 * @method Eurotext_Uploader_Model_Config_Uploader setSimultaneousUploads(int $amountOfSimultaneousUploads)
 * @method Eurotext_Uploader_Model_Config_Uploader setFileParameterName(string $fileUploadParam)
 * @method Eurotext_Uploader_Model_Config_Uploader setQuery(array $additionalQuery)
 * @method Eurotext_Uploader_Model_Config_Uploader setHeaders(array $headers)
 *      Extra headers to include in the multipart POST with data.
 * @method Eurotext_Uploader_Model_Config_Uploader setWithCredentials(bool $isCORS)
 *      Standard CORS requests do not send or set any cookies by default.
 *      In order to include cookies as part of the request, you need to set the withCredentials property to true.
 * @method Eurotext_Uploader_Model_Config_Uploader setMethod(string $sendMethod)
 *       Method to use when POSTing chunks to the server. Defaults to "multipart"
 * @method Eurotext_Uploader_Model_Config_Uploader setTestMethod(string $testMethod) Defaults to "GET"
 * @method Eurotext_Uploader_Model_Config_Uploader setUploadMethod(string $uploadMethod) Defaults to "POST"
 * @method Eurotext_Uploader_Model_Config_Uploader setAllowDuplicateUploads(bool $allowDuplicateUploads)
 *      Once a file is uploaded, allow reupload of the same file. By default, if a file is already uploaded,
 *      it will be skipped unless the file is removed from the existing Flow object.
 * @method Eurotext_Uploader_Model_Config_Uploader setPrioritizeFirstAndLastChunk(bool $prioritizeFirstAndLastChunk)
 *      This can be handy if you can determine if a file is valid for your service from only the first or last chunk.
 * @method Eurotext_Uploader_Model_Config_Uploader setTestChunks(bool $prioritizeFirstAndLastChunk)
 *      Make a GET request to the server for each chunks to see if it already exists.
 * @method Eurotext_Uploader_Model_Config_Uploader setPreprocess(bool $prioritizeFirstAndLastChunk)
 *      Optional function to process each chunk before testing & sending.
 * @method Eurotext_Uploader_Model_Config_Uploader setInitFileFn(string $function)
 *      Optional function to initialize the fileObject (js).
 * @method Eurotext_Uploader_Model_Config_Uploader setReadFileFn(string $function)
 *      Optional function wrapping reading operation from the original file.
 * @method Eurotext_Uploader_Model_Config_Uploader setGenerateUniqueIdentifier(string $function)
 *      Override the function that generates unique identifiers for each file. Defaults to "null"
 * @method Eurotext_Uploader_Model_Config_Uploader setMaxChunkRetries(int $maxChunkRetries) Defaults to 0
 * @method Eurotext_Uploader_Model_Config_Uploader setChunkRetryInterval(int $chunkRetryInterval) Defaults to "undefined"
 * @method Eurotext_Uploader_Model_Config_Uploader setProgressCallbacksInterval(int $progressCallbacksInterval)
 * @method Eurotext_Uploader_Model_Config_Uploader setSpeedSmoothingFactor(int $speedSmoothingFactor)
 *      Used for calculating average upload speed. Number from 1 to 0.
 *      Set to 1 and average upload speed wil be equal to current upload speed.
 *      For longer file uploads it is better set this number to 0.02,
 *      because time remaining estimation will be more accurate.
 * @method Eurotext_Uploader_Model_Config_Uploader setSuccessStatuses(array $successStatuses)
 *      Response is success if response status is in this list
 * @method Eurotext_Uploader_Model_Config_Uploader setPermanentErrors(array $permanentErrors)
 *      Response fails if response status is in this list
  */
class Eurotext_Uploader_Model_Config_Uploader extends Eurotext_Uploader_Model_Config_Abstract
{
    /**
     * Type of upload
     */
    const UPLOAD_TYPE = 'multipart';

    /**
     * Test chunks on resumable uploads
     */
    const TEST_CHUNKS = false;

    /**
     * Used for calculating average upload speed.
     */
    const SMOOTH_UPLOAD_FACTOR = 0.02;

    /**
     * Progress check interval
     */
    const PROGRESS_CALLBACK_INTERVAL = 0;

    /**
     * Set default values for uploader
     */
    protected function _construct()
    {
        $this
            ->setChunkSize($this->_getHelper()->getDataMaxSizeInBytes())
            ->setWithCredentials(false)
            ->setForceChunkSize(false)
            ->setQuery(
                [
                    'form_key' => Mage::getSingleton('core/session')->getFormKey()
                ]
            )
            ->setMethod(self::UPLOAD_TYPE)
            ->setAllowDuplicateUploads(true)
            ->setPrioritizeFirstAndLastChunk(false)
            ->setTestChunks(self::TEST_CHUNKS)
            ->setSpeedSmoothingFactor(self::SMOOTH_UPLOAD_FACTOR)
            ->setProgressCallbacksInterval(self::PROGRESS_CALLBACK_INTERVAL)
            ->setSuccessStatuses([200, 201, 202])
            ->setPermanentErrors([404, 415, 500, 501]);
    }
}
