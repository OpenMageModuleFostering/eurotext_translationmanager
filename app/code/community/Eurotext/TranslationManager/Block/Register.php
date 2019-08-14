<?php

class Eurotext_TranslationManager_Block_Register extends Mage_Adminhtml_Block_Template
{
	public function getModuleVersion()
	{
		return (string) Mage::getConfig()->getNode()->modules->Eurotext_TranslationManager->version;
	}
	
	public function getPostBackUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_register/save');
	}
	
	// returns "saved", if the email was sent
	public function getMessage()
	{		
		if ($this->isSaved())
		{
			return $this->__("Last save").": ".$this->getSetting("register_mailsent_date","?");
		}
		else
		{
			return $this->__("Not yet saved");
		}
	}
	
	// returns true, if the registration data was sent previously
	public function isSaved()
	{
		return ($this->getSetting("register_mailsent","0")=="1");
	}
	
	public function getSetting($key)
	{
		$helper=Mage::helper('eurotext_translationmanager');
		return $helper->getSetting($key,"");
	}
}