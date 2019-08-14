<?php

abstract class Eurotext_Uploader_Model_Config_Abstract extends Varien_Object
{
    /**
     * Get file helper
     *
     * @return Eurotext_Uploader_Helper_File
     */
    protected function _getHelper()
    {
        return Mage::helper('eurotext_uploader/file');
    }

    /**
     * Set/Get attribute wrapper
     * Also set data in cameCase for config values
     *
     * @param string $method
     * @param array  $args
     * @return bool|mixed|Varien_Object
     * @throws Varien_Exception
     */
    public function __call($method, $args)
    {
        $key = lcfirst($this->_camelize(substr($method, 3)));
        switch (substr($method, 0, 3)) {
            case 'get' :
                return $this->getData($key, isset($args[0]) ? $args[0] : null);

            case 'set' :
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);

                return $result;

            case 'uns' :
                $result = $this->unsetData($key);

                return $result;

            case 'has' :
                return isset($this->_data[$key]);
        }
        throw new Varien_Exception(
            'Invalid method ' . get_class($this) . '::' . $method . '(' . print_r($args, 1) . ')'
        );
    }
}
