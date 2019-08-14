<?php

class Eurotext_TranslationManager_Block_Selectemails extends Mage_Adminhtml_Block_Template
{
	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
	}
	
	private function getProjectId()
	{
		return intval($this->getRequest()->getParam("id"));
	}
	
	public function getSelectEmailsUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectemails/index',array('id' => $this->getProjectId()));
	}
	
	public function getSelectEmailsSaveUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectemails/save',array('id' => $this->getProjectId()));
	}
	
	public function getProject()
	{
		$id=$this->getProjectId();
		
		$tableName = Mage::getSingleton('core/resource')->getTableName('eurotext_translationmanager/project');
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$projects=$dbr->fetchAll("SELECT * FROM `".$tableName."` WHERE id=".$id);
		
		$project=$projects[0];
		
		// storeview_src_locale:
		$project['storeview_src_locale']="en_US";
		if ($project['storeview_src']>=0)
		{
			$project['storeview_src_locale']=Mage::getStoreConfig('general/locale/code', $project['storeview_src']);
		}
		
		// storeview_dst_locale:
		$project['storeview_dst_locale']="en_US";
		if ($project['storeview_dst']>=0)
		{
			$project['storeview_dst_locale']=Mage::getStoreConfig('general/locale/code', $project['storeview_dst']);
		}
		
		return $project;
	}
	
	public function getEMailTemplates()
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$project=$this->getProject();
		$storeview_src=$project['storeview_src'];
		
		$rv=array();		
		
		$helper=Mage::helper('eurotext_translationmanager');
		$helper->ajaxexportAction_CollectEMailTemplates($project);
	
		$langfiles=$dbr->fetchAll("SELECT file_hash, filename, translate_flag FROM `".$this->getTableName('eurotext_emailtemplates')."` WHERE project_id=".$project['id']." AND locale_dst='".$project['storeview_src_locale']."' ORDER BY filename ASC");
		foreach($langfiles as $langfile)
		{
			$rvItem=array();
			$rvItem['file_hash']=$langfile['file_hash'];
			$rvItem['filename']=$langfile['filename'];
			$rvItem['checked']=((intval($langfile['translate_flag']))>0);
			
			array_push($rv,$rvItem);
		}
		
		return $rv;
	}
}