<?php

class Eurotext_TranslationManager_Block_Selectcategories extends Mage_Adminhtml_Block_Template
{
	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
	}
	
	private function getProjectId()
	{
		return intval($this->getRequest()->getParam("id"));
	}
	
	public function getSelectCategoriesUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectcategories/index',array('id' => $this->getProjectId()));
	}
	
	public function getSelectCategoriesSaveUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectcategories/save',array('id' => $this->getProjectId()));
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
	
	public function getRootCategory()
	{
		$project=$this->getProject();
		$store=Mage::app()->getStore($project['storeview_src']);
		$rootCategoryId=$store->getRootCategoryId();
		
		$rootCategory=Mage::getModel("catalog/category")->load($rootCategoryId);
		return $rootCategory;
	}
	
	public function getOpenedPathIds()
	{
		return array();
	}
	
	public function getSelectedCategoryIds()
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$rv=array();
		
		$selectedCategories=$dbr->fetchAll("SELECT category_id FROM `".$this->getTableName('eurotext_project_categories')."` WHERE project_id=?",array($this->getProjectId()));
		foreach($selectedCategories as $selectedCategory)
		{
			array_push($rv,$selectedCategory['category_id']);
		}
		
		return $rv;
	}
	
	public function getSearchResult()
	{
		$pagesize=20;
	
		$rv=array();
		$rv['find']=trim($this->getRequest()->getParam("find"));
		$page_current=intval($this->getRequest()->getParam("page"));
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$eavAttribute = Mage::getModel('eav/entity_attribute');
		$name_id = $eavAttribute->getIdByCode('catalog_category', 'name');
		
		$findme="%".$rv['find']."%";
		
		$sql="SELECT a.store_id, a.value as article_name, e.sku, e.entity_id FROM `".$this->getTableName('catalog_product_entity_varchar')."` a, `".$this->getTableName('catalog_product_entity')."` e WHERE (a.entity_id=e.entity_id) AND a.store_id=0 AND (a.attribute_id=?) AND ((UPPER(e.sku) LIKE ?) OR (UPPER(a.value) LIKE ?)) ORDER BY article_name ASC, e.entity_id ASC";
		$allProducts=$dbr->fetchAll($sql,array($name_id,$findme,$findme));
		
		$pageCount=intval(count($allProducts)/$pagesize);
		if (($pageCount*$pagesize)<count($allProducts))
		{
			$pageCount++;
		}
		$rv['page_last']=$pageCount;
		
		if ($page_current>$pageCount)
		{
			$page_current=$pageCount;
		}
		if ($page_current<1)
		{
			$page_current=1;
		}
		
		if ($pageCount<1)
		{
			$pageCount=1;
		}
		
		// Result-Array:
		$resultProducts=array();
		$ofs_start=($page_current-1)*$pageCount;
		$ofs_end=$ofs_start+$pagesize;
		if ($ofs_end>=count($allProducts))
		{
			$ofs_end=count($allProducts)-1;
		}
		
		for ($i=$ofs_start; $i<=$ofs_end; $i++)
		{
			$prod=$allProducts[$i];
			$prod['checked']=false;
			
			// Already selected?
			$sql="SELECT product_id FROM `".$this->getTableName('eurotext_project_products')."` p WHERE  (p.project_id=?) AND (p.product_id=?)";
			$selProducts=$dbr->fetchAll($sql,array($this->getProjectId(),$prod['entity_id']));
			$prod['checked']=(count($selProducts)>0);
			
			array_push($resultProducts,$prod);
		}
		
		$rv['page_current']=$page_current;
		$rv['page_last']=$pageCount;
		$rv['result_count']=count($allProducts);
		$rv['products']=$resultProducts;
		
		return $rv;
	}
}