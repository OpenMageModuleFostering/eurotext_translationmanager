<?php

class Eurotext_TranslationManager_Block_Selectlangfiles extends Mage_Adminhtml_Block_Template
{
	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
	}
	
	private function getProjectId()
	{
		return intval($this->getRequest()->getParam("id"));
	}
	
	public function getSelectLangfilesUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectlangfiles/index',array('id' => $this->getProjectId()));
	}
	
	public function getSelectLangfilesSaveUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectlangfiles/save',array('id' => $this->getProjectId()));
	}
	
	public function getProject()
	{
		$id=$this->getProjectId();
		
		$tableName = Mage::getSingleton('core/resource')->getTableName('eurotext_translationmanager/project');
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$projects=$dbr->fetchAll("SELECT * FROM `".$tableName."` WHERE id=".$id);
		
		return $projects[0];
	}
	
	public function getLangfiles()
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$project=$this->getProject();
		$storeview_src=$project['storeview_src'];
		
		$rv=array();		
		
		$projectsController=Mage::helper('eurotext_translationmanager');
		$projectsController->ajaxexportAction_CollectLangfiles($project);
	
		$langfiles=$dbr->fetchAll("SELECT line_hash, filename, translate_flag FROM `".$this->getTableName('eurotext_csv')."` WHERE locale_dst='en_US' AND project_id=".$project['id']." ORDER BY filename ASC");
		foreach($langfiles as $langfile)
		{
			$rvItem=array();
			$rvItem['line_hash']=$langfile['line_hash'];
			$rvItem['filename']=$langfile['filename'];
			$rvItem['checked']=((intval($langfile['translate_flag']))>0);
			
			array_push($rv,$rvItem);
		}
		
		return $rv;
	}
}