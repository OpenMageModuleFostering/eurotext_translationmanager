<?php

class Eurotext_TranslationManager_Block_Settings extends Mage_Adminhtml_Block_Template
{
	public function getSaveUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_settings/save');
	}
	
	public function getUpgradeScopeUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_settings/upgradescope');
	}
	
	public function getStoreviews()
	{
		$array_stores=array();
		
		$stores=Mage::app()->getStores();
		foreach($stores as $store)
		{		
			$array_store=array();
			$array_store['store_id']=$store->getId();
			$array_store['name']=$store->getName();
			$array_store['code']=$store->getCode();
			$array_store['locale']=Mage::getStoreConfig('general/locale/code', $store->getId());
			
			array_push($array_stores,$array_store);
		}
		
		return $array_stores;
	}
	
}