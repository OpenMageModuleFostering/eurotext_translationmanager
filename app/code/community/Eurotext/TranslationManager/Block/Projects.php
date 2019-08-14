<?php

class Eurotext_TranslationManager_Block_Projects extends Mage_Adminhtml_Block_Template
{
	public function getSelectUrl($selectType, $project_id)
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_'.$selectType.'/index/',array('id' => $project_id));
	}

	public function getNewProjectUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_projects/addproject');
	}

	public function getSaveProjectUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_projects/saveproject');
	}

	public function getProjectUrl($project_id)
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_projects/index/id/'.intval($project_id));
	}

	public function getProjectDeleteUrl($project_id)
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_projects/delete/id/'.$project_id);
	}

	public function getProjectResetUrl($project_id)
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_projects/reset/id/'.$project_id);
	}

	public function getAjaxImportStepUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_projects/importstep');
	}

	public function getUploadUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_projects/upload');
	}

	public function getPostBackUrl2()
	{
		// Postback-URL für Projekt-Einstellungen
		return ""; //return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_export/save');
	}

	public function getAjaxExportUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_projects/ajaxexport');
	}

	public function getProjects()
	{
		$tableName = Mage::getSingleton('core/resource')->getTableName('eurotext_translationmanager/project');

		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');

		$projects=$dbr->fetchAll("SELECT * FROM `".$tableName."` WHERE deleted=false ORDER BY id ASC");

		return $projects;
	}

	public function getSelectedProjectId()
	{
		return intval(Mage::app()->getRequest()->getParam('id',-1));
	}

	public function getSelectedProject()
	{
		$id=$this->getSelectedProjectId();

		$tableName = Mage::getSingleton('core/resource')->getTableName('eurotext_translationmanager/project');

		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');

		$projects=$dbr->fetchAll("SELECT * FROM `".$tableName."` WHERE id=".$id);

		return $projects[0];
	}

	public function GetCheckedStr($val)
	{
		if (($val=="1") || ($val==true))
		{
			return "checked='checked'";
		}

		return "";
	}

	public function getStatusText($status_id)
	{
		if ($status_id==0)
		{
			return $this->__("New");
		}
		else if ($status_id==1)
		{
			return $this->__("In progress");
		}
		else if ($status_id==2)
		{
			return $this->__("In progress");
		}
		else if ($status_id==3)
		{
			return $this->__("Loaded");
		}
	}

	public function getStoreviewTitle($store_id)
	{
		if ($store_id<0)
		{
			return $this->__("Not yet selected");
		}

		try
		{
			$store=Mage::app()->getStore($store_id);
			$locale_code=Mage::getStoreConfig('general/locale/code', $store->getId());
			return $store->getName()." (".$locale_code.")";
		}
		catch(Exception $e)
		{
			return $this->__("A storeview does not exist (anymore)")." (ID: '".$store_id."')";
		}
	}

	public function getSpracheSelect($selectName, $selectedId,$disabledStr)
	{
		$html="<select ".$disabledStr." id='".$selectName."' autocomplete='off'>";
		$html.="<option value='-1'>".$this->__("-- Select storeview --")."</option>";

		$stores=Mage::app()->getStores();
		foreach($stores as $store)
		{
			$locale_code=Mage::getStoreConfig('general/locale/code', $store->getId());

			$selAttr="";
			if ($store->getId()==$selectedId)
			{
				$selAttr="selected='selected'";
			}

			$html.="<option value='".$store->getId()."' ".$selAttr.">".$store->getName()." (".$locale_code.")</option>";
		}
		$html.="</select>";

		return $html;
	}
}