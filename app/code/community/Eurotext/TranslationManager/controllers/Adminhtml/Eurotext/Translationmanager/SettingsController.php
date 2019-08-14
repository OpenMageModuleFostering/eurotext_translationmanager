<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Translationmanager_SettingsController extends Mage_Adminhtml_Controller_Action
{


    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
                ->isAllowed('eurotext_translationmanager/settings');
    }


    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
	
	public function upgradescopeAction()
	{
		$is_global=0;
		
		$tbl_eav_attribute = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
		$tbl_catalog_eav_attribute = Mage::getSingleton('core/resource')->getTableName('catalog_eav_attribute');
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		$dbw=$dbres->getConnection('core_write');
		
		$result=$dbr->fetchAll("SELECT v.is_global, a.attribute_id, a.attribute_code FROM `".$tbl_eav_attribute."` a, `".$tbl_catalog_eav_attribute."` v WHERE (v.is_global!=".$is_global.") AND (a.attribute_id=v.attribute_id) AND ((a.attribute_code='url_key') OR (a.attribute_code='url_path'))");
		foreach($result as $row)
		{
			$dbw->query("UPDATE `".$tbl_catalog_eav_attribute."` SET is_global=".$is_global." WHERE attribute_id=".$row['attribute_id']);
		}
	
		$url=Mage::helper('adminhtml')->getUrl('*/*/index');
		$this->_redirectUrl($url);
	}
	
	public function saveAction()
	{
		$helper=Mage::helper('eurotext_translationmanager');
		$request=$this->getRequest();
		
		$helper->saveSetting("eurotext_username",$request->getParam("username"));
		$helper->saveSetting("eurotext_password",Mage::helper('core')->encrypt($request->getParam("password")));
		$helper->saveSetting("eurotext_customerid",$request->getParam("customerid"));
		
		$et_products_per_file=intval($request->getParam("et_products_per_file"));
		if ($et_products_per_file<$helper->getExportProductsMinPerFile())
		{
			$et_products_per_file=$helper->getExportProductsMinPerFile();
		}
		
		$et_categories_per_file=intval($request->getParam("et_categories_per_file"));
		if ($et_categories_per_file<$helper->getExportCategoriesMinPerFile())
		{
			$et_categories_per_file=$helper->getExportCategoriesMinPerFile();
		}
		
		$et_cmspages_per_file=intval($request->getParam("et_cmspages_per_file"));
		if ($et_cmspages_per_file<1)
		{
			$et_cmspages_per_file=20;
		}
		
		$helper->saveSetting("et_products_per_file",$et_products_per_file);
		$helper->saveSetting("et_categories_per_file",$et_categories_per_file);
		$helper->saveSetting("et_cmspages_per_file",$et_cmspages_per_file);
	}
}
