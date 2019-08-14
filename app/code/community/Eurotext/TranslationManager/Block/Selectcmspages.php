<?php

class Eurotext_TranslationManager_Block_Selectcmspages extends Mage_Adminhtml_Block_Template
{
	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
	}

	private function getProjectId()
	{
		return intval($this->getRequest()->getParam("id"));
	}

	public function getSelectCmsPagesUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectcmspages/index',array('id' => $this->getProjectId()));
	}

	public function getSelectCmsPagesSaveUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectcmspages/save',array('id' => $this->getProjectId()));
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

	public function getCMSPages()
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');

		$project=$this->getProject();
		$storeview_src=$project['storeview_src'];

		$rv=array();
		$selectedCMSPages=array();

		$selectedPageIds=$dbr->fetchAll("SELECT page_id FROM `".$this->getTableName('eurotext_project_cmspages')."` WHERE project_id=?",array($this->getProjectId()));
		foreach($selectedPageIds as $selectedPageId)
		{
			array_push($selectedCMSPages,$selectedPageId['page_id']);
		}

		// cms-pages:
		$mPages=$dbr->fetchAll("SELECT * FROM `".$this->getTableName('cms_page')."` ORDER BY title ASC");
		foreach($mPages as $mPage)
		{
			// Is this CMS-Page activated for the source storeview?
			$storeCount=intval($dbr->fetchOne("SELECT COUNT(*) FROM `".$this->getTableName('cms_page_store')."` WHERE page_id=".$mPage['page_id']." AND (store_id=0 OR store_id=?)",array($storeview_src)));
			if ($storeCount>0) // Has activated store
			{
				$page=array();
				$page['page_id']=$mPage['page_id'];
				$page['title']=$mPage['title'];
				$page['checked']=in_array($mPage['page_id'],$selectedCMSPages);
				$page['identifier']=$mPage['identifier'];
				$page['type']=$this->__("CMS-Page");

				array_push($rv,$page);
			}
		}

		return $rv;
	}

	public function getCMSBlocks()
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');

		$project=$this->getProject();
		$storeview_src=$project['storeview_src'];

		$rv=array();
		$selectedCMSBlocks=array();

		$selectedBlockIds=$dbr->fetchAll("SELECT block_id FROM `".$this->getTableName('eurotext_project_cmsblocks')."` WHERE project_id=?",array($this->getProjectId()));
		foreach($selectedBlockIds as $selectedBlockId)
		{
			array_push($selectedCMSBlocks,$selectedBlockId['block_id']);
		}

		// static blocks:
		$mPages=$dbr->fetchAll("SELECT * FROM `".$this->getTableName('cms_block')."` ORDER BY title ASC");
		foreach($mPages as $mPage)
		{
			// Is this CMS-Block activated for the source storeview?
			$storeCount=intval($dbr->fetchOne("SELECT COUNT(*) FROM `".$this->getTableName('cms_block_store')."` WHERE block_id=".$mPage['block_id']." AND (store_id=0 OR store_id=?)",array($storeview_src)));
			if ($storeCount>0) // Has activated store
			{
				$page=array();
				$page['block_id']=$mPage['block_id'];
				$page['title']=$mPage['title'];
				$page['checked']=in_array($mPage['block_id'],$selectedCMSBlocks);
				$page['identifier']=$mPage['identifier'];
				$page['type']=$this->__("CMS-Block");

				array_push($rv,$page);
			}
		}

		return $rv;
	}
}