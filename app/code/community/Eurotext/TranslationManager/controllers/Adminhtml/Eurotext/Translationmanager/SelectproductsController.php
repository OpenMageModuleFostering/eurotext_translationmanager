<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Translationmanager_SelectproductsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {		
		$this->loadLayout();
        $this->renderLayout();
    }
	
	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
	}

	public function getAllProductIds()
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$eavAttribute = Mage::getModel('eav/entity_attribute');
		$name_id = $eavAttribute->getIdByCode('catalog_product', 'name');

        $open_catids=$this->getOpenCatIds();
        $sql_categoryfilter="";
        if (count($open_catids)>0)
        {
            $selected_catid=$open_catids[count($open_catids)-1];    // Last ID is the selected category
            if ($selected_catid>1)  // root-category has id 1
            {
                $search_catids=Mage::helper('eurotext_translationmanager')->getAllSubCategories($selected_catid); // Get the IDs of all children (direct+indirect)
                array_push($search_catids,$selected_catid); // add selected category to list

                // Filter to products which are assigned to any category in $search_catids:
                $sql_categoryfilter=" AND e.entity_id IN (SELECT cat.product_id FROM ".$this->getTableName('catalog_category_product')." cat WHERE cat.category_id IN (".implode(",",$search_catids)."))";
            }
        }
		
		$sql="SELECT e.entity_id FROM `".$this->getTableName('catalog_product_entity')."` e WHERE (1=1)".$sql_categoryfilter;
		$allProducts=$dbr->fetchAll($sql);
		
		$productIds=array();
		for($i=0; $i<count($allProducts); $i++)
		{		
			array_push($productIds,$allProducts[$i]['entity_id']);
        }
		
		return $productIds;
	}

    public function getOpenCatIds()
    {
        // Parse catids (list of open categories)
        $open_catids_str=$this->getRequest()->getParam("catids");

        $open_catids_list=explode(",",$open_catids_str);
        $open_catids=array();
        foreach($open_catids_list as $opencatid)
        {
            $cat_id=intval($opencatid);	// prevent non-numeric ids
            array_push($open_catids,$cat_id);
        }
        array_push($open_catids,1); // root-category is always open
        $open_catids=array_unique($open_catids);

        return $open_catids;
    }
	
	public function saveAction()
    {

        $this->loadLayout('adminhtml_eurotext_translationmanager_ajax');
        $block = $this->getLayout()->getBlock('et.tm.response.ajax');


        $helper=Mage::helper('eurotext_translationmanager');

		$project_id=intval($this->getRequest()->getParam("project_id"));
		$cnt=intval($this->getRequest()->getParam("cnt"));
		$selectAction=$this->getRequest()->getParam("select");
        $catid=$this->getRequest()->getParam("catid");
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		$dbw=$dbres->getConnection('core_write');
        $time_added=time();
				
		for($i=0; $i<$cnt; $i++)
		{
			$product_id=$this->getRequest()->getParam("product_id_".$i);
			$set=$this->getRequest()->getParam("set_".$i);
			if ($product_id>0)
			{					
				if ($set=="enabled")
				{
					$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_project_products')."` (product_id,project_id) VALUES (?,?);",array($product_id,$project_id));
					
					// Update timestamp:
					$dbw->query("UPDATE `".$this->getTableName('eurotext_project_products')."` SET time_added=? WHERE product_id=? AND project_id=?;",array($time_added,$product_id,$project_id));
				}
				else
				{
                    $dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_products')."` WHERE product_id=? AND project_id=?;",array($product_id,$project_id));
				}
			}
		}

        if ($selectAction=="setcat")
        {
            $touched_product_ids=$helper->getCategoryProducts($dbr,$catid);

            foreach($touched_product_ids as $product_id)
            {
                $dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_project_products')."` (product_id,project_id) VALUES (?,?);",array($product_id,$project_id));
                // Update timestamp:
                $dbw->query("UPDATE `".$this->getTableName('eurotext_project_products')."` SET time_added=? WHERE product_id=? AND project_id=?;",array($time_added,$product_id,$project_id));
            }
        }
        else if ($selectAction=="unsetcat")
        {
            $touched_product_ids=$helper->getCategoryProducts($dbr,$catid);

            foreach($touched_product_ids as $product_id)
            {
                $dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_products')."` WHERE product_id=? AND project_id=?;",array($product_id,$project_id));
            }
        }
		
		if ($selectAction=="all")
		{
			$time_added=time();
			
			$allProductIds=$this->getAllProductIds();
				
			foreach($allProductIds as $product_id)
			{
				$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_project_products')."` (product_id,project_id) VALUES (?,?);",array($product_id,$project_id));
			}
					
			// Update timestamp:
			$dbw->query("UPDATE `".$this->getTableName('eurotext_project_products')."` SET time_added=? WHERE project_id=?;",array($time_added,$project_id));
		}
		else if ($selectAction=="none")
		{
            $allProductIds=$this->getAllProductIds();

            foreach($allProductIds as $product_id)
            {
			    $dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_products')."` WHERE project_id=? AND product_id=?;",array($project_id,$product_id));
            }
		}
		
		// get result list:
		$eavAttribute = Mage::getModel('eav/entity_attribute');
		$name_id = $eavAttribute->getIdByCode('catalog_product', 'name');
		
		$sql="SELECT a.store_id, a.value as article_name, e.sku, e.entity_id FROM `".$this->getTableName('catalog_product_entity_varchar')."` a, `".$this->getTableName('catalog_product_entity')."` e, `".$this->getTableName('eurotext_project_products')."` p WHERE (a.entity_id=e.entity_id) AND (a.attribute_id=?) AND a.store_id=0 AND (p.product_id=e.entity_id) AND (p.project_id=?) ORDER BY time_added DESC, article_name ASC, e.entity_id ASC";
		$products=$dbr->fetchAll($sql,array($name_id,$project_id));

        $product_ids=array();

		$returnBody="";
		
		// Results:
		$returnBody.="<table cellpadding=0 cellspacing=0>";
		$returnBody.="<tr>";
		$returnBody.="  <td class='et_th'>".$this->__("Translate")."</td>";
		$returnBody.="  <td class='et_th'>".$this->__("SKU")."</td>";
		$returnBody.="  <td class='et_th'>".$this->__("Designation")."</td>";
		$returnBody.="  <td class='et_th'>&nbsp;</td>";
		$returnBody.="</tr>";
		
		$alt=0;
				
		foreach($products as $product)
		{
            array_push($product_ids,$product['entity_id']);

			$alt=1-$alt;
			$returnBody.="<tr>";
			$returnBody.="  <td class='et_tc eurotext2_r".$alt."'><input type='checkbox' id='et_selproduct2_".$product['entity_id']."' class='et_selproduct et_selproduct_".$product['entity_id']."' checked='checked' onchange=\"eurotext_selectproduct('2','".$product['entity_id']."')\" /></td>";
			$returnBody.="  <td class='et_tc eurotext2_r".$alt."'>".$product['sku']."</td>"; //  (ID: ".$product['entity_id'].")
			$returnBody.="  <td class='et_tc eurotext2_r".$alt."'>".$product['article_name']."</td>";
			$returnBody.="  <td class='et_tc eurotext2_r".$alt."'>&nbsp;</td>";
			$returnBody.="</tr>";
		}
		
		$returnBody.="</table>";

        // Determine category state: $categories
        $open_catids=$this->getOpenCatIds();

        $rootcat=$helper->getCategoryTree(1,$open_catids);
        $categories=array();
        $this->checkCategoryState($dbr,$rootcat,$project_id,$categories);

        $block->setHtmldata($returnBody);
        $block->setProducts($product_ids);
        $block->setCategories($categories);
        $block->setDebug($open_catids);

        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->renderLayout();

    }

    private function checkCategoryState($dbr,$cat,$project_id, &$categoryDict)
    {
        $categoryState=Mage::helper('eurotext_translationmanager')->getTreeNodeTranslationState($dbr,$cat['id'],$project_id);


        $catitem=array();
        $catitem['id']=$cat['id'];
        $catitem['checked']=($categoryState=="checked");
        $catitem['indeterminate']=($categoryState=="indeterminate");

        array_push($categoryDict,$catitem);

        foreach($cat['childs'] as $child)
        {
            $this->checkCategoryState($dbr,$child,$project_id,$categoryDict);
        }
    }
}
