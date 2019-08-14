<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Translationmanager_SelectcategoriesController extends Mage_Adminhtml_Controller_Action
{
	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
	}
	
    public function indexAction()
    {		
		$this->loadLayout();
        $this->renderLayout();
    }
	
	private function getAllChildCategoryIds($cat_id)
	{
		$rv=array();
		
		array_push($rv,$cat_id);
		
		$category=Mage::getModel('catalog/category')->load($cat_id);		
		$childs=$category->getChildrenCategories();
		if ($childs!=null)
		{
			foreach($childs as $child)
			{
				$rvSub=$this->getAllChildCategoryIds($child->getId());
				
				$rv=array_merge($rv,$rvSub);
			}
		}
		
		$rv=array_unique($rv);
		
		return $rv;
	}
	
	public function saveAction()
    {

        $this->loadLayout('adminhtml_eurotext_translationmanager_text');
        $block = $this->getLayout()->getBlock('et.tm.response.text');

		$project_id=intval($this->getRequest()->getParam("project_id"));
		$cnt=intval($this->getRequest()->getParam("cnt"));
		$selectAction=$this->getRequest()->getParam("select");
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		$dbw=$dbres->getConnection('core_write');
		
		for($i=0; $i<$cnt; $i++)
		{
			$category_id=$this->getRequest()->getParam("category_id_".$i);
			$set=$this->getRequest()->getParam("set_".$i);			
		
			if ($category_id>0)
			{		
				$cat_ids=$this->getAllChildCategoryIds($category_id);
				
				foreach($cat_ids as $cat_id)
				{			
					if ($set=="enabled")
					{
						$time_added=time();
					
						$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_project_categories')."` (category_id,project_id) VALUES (?,?);",array($cat_id,$project_id));
						
						// Update timestamp:
						$dbw->query("UPDATE `".$this->getTableName('eurotext_project_categories')."` SET time_added=? WHERE category_id=? AND project_id=?;",array($time_added,$cat_id,$project_id));
					}
					else
					{
						$dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_categories')."` WHERE category_id=? AND project_id=?;",array($cat_id,$project_id));
					}
				}
			}
		}
		
		if ($selectAction=="all")
		{
			$time_added=time();
			
			$allCategoryIds=$this->getAllCategoryIds();
				
			foreach($allCategoryIds as $category_id)
			{
				$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_project_categories')."` (category_id,project_id) VALUES (?,?);",array($category_id,$project_id));
			}
					
			// Update timestamp:
			$dbw->query("UPDATE `".$this->getTableName('eurotext_project_categories')."` SET time_added=? WHERE project_id=?;",array($time_added,$project_id));
		}
		else if ($selectAction=="none")
		{
			$dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_categories')."` WHERE project_id=?;",array($project_id));
		}
		
		// get result list:
		$eavAttribute = Mage::getModel('eav/entity_attribute');
		$name_id = $eavAttribute->getIdByCode('catalog_category', 'name');
		
		$sql="SELECT e.entity_id FROM `".$this->getTableName('catalog_category_entity_varchar')."` a, `".$this->getTableName('catalog_category_entity')."` e, `".$this->getTableName('eurotext_project_categories')."` p WHERE (a.store_id=0) AND (a.entity_id=e.entity_id) AND (a.attribute_id=?) AND (p.category_id=e.entity_id) AND (p.project_id=?) ORDER BY time_added DESC, a.value ASC, e.entity_id ASC";
		$categories=$dbr->fetchAll($sql,array($name_id,$project_id));
        
        $rv = '';
        
		// Results:
		$rv .=  "<table cellpadding=0 cellspacing=0>";
		$rv .=  "<tr>";
		$rv .=  "  <td class='et_th'>".$this->__("Translate")."</td>";
		$rv .=  "  <td class='et_th'>".$this->__("Designation")."</td>";
		$rv .=  "  <td class='et_th'>&nbsp;</td>";
		$rv .=  "</tr>";
				
		foreach($categories as $category_id)
		{
			$category=Mage::getModel("catalog/category")->load($category_id);
			
			$rv .=  "<tr>";
			$rv .=  "  <td class='et_tc'><input type='checkbox' id='et_selcategory2_".$category->getId()."' class='et_selcategory et_selcategory_".$category->getId()."' checked='checked' onchange=\"eurotext_selectcategory('2','".$category->getId()."')\" /></td>";
			$rv .=  "  <td class='et_tc'>".$category->getName()."</td>";
			$rv .=  "  <td class='et_tc'>&nbsp;</td>";
			$rv .=  "</tr>";
		}
		
		$rv .=  "</table>";


        $block->setText($rv);
        $this->renderLayout();
    }
}
