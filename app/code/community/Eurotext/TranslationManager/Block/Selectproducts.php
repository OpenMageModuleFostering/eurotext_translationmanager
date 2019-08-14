<?php

class Eurotext_TranslationManager_Block_Selectproducts extends Mage_Adminhtml_Block_Template
{
	public function updateFilterData()
	{
		if ($this->getRequest()->getParam("status")!="")
		{
			$filter_status=intval($this->getRequest()->getParam("status"));
			$filter_stock=intval($this->getRequest()->getParam("stock"));
			$filter_product_type=$this->getRequest()->getParam("producttype");
			$project_id=$this->getProjectId();

			$dbres = Mage::getSingleton('core/resource');
			$dbw=$dbres->getConnection('core_write');
			$dbw->query("UPDATE `".$this->getTableName('eurotext_project')."` SET filter_status=?, filter_stock=?, filter_product_type=? WHERE id=?;",array($filter_status,$filter_stock,$filter_product_type,$project_id));
		}
	}

	private function getProjectId()
	{
		return intval($this->getRequest()->getParam("id"));
	}

	public function getSelectProductsUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectproducts/index',array('id' => $this->getProjectId()));
	}

	public function getSelectProductsSaveUrl()
	{
		return Mage::helper('adminhtml')->getUrl('*/eurotext_translationmanager_selectproducts/save'); //,array('id' => $this->getProjectId()));
	}

	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
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

	public function getCategoryTreeHTML()
	{
		$pagesize=20;

        $dbres = Mage::getSingleton('core/resource');
        $dbr=$dbres->getConnection('core_read');

		$rv=array();
		$rv['find']=trim($this->getRequest()->getParam("find"));
		$page_current=intval($this->getRequest()->getParam("page"));

		$pagesize=intval($this->getRequest()->getParam("pagesize"));
		if ($pagesize<20)
		{
			$pagesize=20;
		}

        $helper=Mage::helper('eurotext_translationmanager');

        $open_catids=$this->getOpenCatIds();
		$root=$helper->getCategoryTree(1,$open_catids);
        $baselink=$this->getSelectProductsUrl()."find/".urlencode($rv['find'])."/pagesize/".$pagesize;

        $current_path=array();
        $project_id=$this->getProjectId();

		return $this->getCategoryTreeHTMLInternal($dbr,$project_id,$root,-1,$open_catids,$current_path,$baselink);
	}

    private function getCategoryTreeHTMLInternal($dbr, $project_id, $treeNode, $lvl, $open_catids, $current_path, $baselink)
	{

        $rv = '';
        $_lvl=max(0,$lvl);
		$marginLeft=($_lvl)*22;

        $current_path_new=array_merge(array(),$current_path);
        array_push($current_path_new,$treeNode['id']);
        $current_path_new=array_unique($current_path_new);

        $showChildren=false;
        $isSelected=in_array($treeNode['id'],$open_catids);

        $cssClass="eurotext_category_none";
        if ($treeNode['hasChildren'])
        {
            if ($isSelected)
            {
                $showChildren=true;
                $cssClass="eurotext_category_minus";
            }
            else
            {
                $cssClass="eurotext_category_plus id_".$treeNode['id']." path_".implode("_",$open_catids);
            }
        }
        if ($treeNode['id']==1)
        {
            $cssClass="eurotext_category_none"; // no 'open'-icon on root-category
        }

        $linkStyle="color:black;text-decoration:none;padding:2px;";
        if ($isSelected)
        {
            if ( ($treeNode['id']==1) && (count($open_catids)>1))
            {
                // Ignore selection on root category
            }
            else
            {
                $linkStyle="color:black;text-decoration:none;background-color: rgb(245, 214, 199);padding:2px;";
            }
        }

        // Determine category state:
        $categoryState=Mage::helper('eurotext_translationmanager')->getTreeNodeTranslationState($dbr,$treeNode['id'],$project_id);

        $checkedStr="";
        if ($categoryState=="checked")
        {
            $checkedStr="checked='checked'";
        }


        $rv .= "<table style='margin-left:".$marginLeft."px;'> \r\n";
        $rv .= " <tr> \r\n";
        $rv .= "  <td><div class='".$cssClass."'></div></td> \r\n";   // Icon
        $rv .= "  <td><input type='checkbox' autocomplete='off' x-state='".$categoryState."' x-catid='".$treeNode['id']."' class='eurotext_catsel' id='eurotext_catsel_".$treeNode['id']."' ".$checkedStr." /> </td> \r\n";
        $rv .= "  <td><a style='".$linkStyle."' href='".$baselink."/catids/".implode(",",$current_path_new)."'>".htmlentities($treeNode['name'])."</a></td> \r\n";
        $rv .= " </tr> \r\n";
        $rv .= "</table> \r\n";

        if ($categoryState=="indeterminate")
        {
            $rv .= "<script>document.getElementById('eurotext_catsel_".$treeNode['id']."').indeterminate = true;</script> \r\n";
        }

        if ($showChildren)
        {
            foreach($treeNode['childs'] as $child)
            {
                $rv .= $this->getCategoryTreeHTMLInternal($dbr,$project_id,$child,($lvl+1),$open_catids,$current_path_new,$baselink);
            }
        }

        return $rv;

    }

	private $filterDataRead=false;
	private $filter_status=1;
	private $filter_stock=1;
	private $filter_product_type="";

	private function readFilterData()
	{
		if ($this->filterDataRead)
		{
			return;
		}

		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');

		$project_id=$this->getProjectId();

		$allProducts=$dbr->fetchAll("SELECT filter_status, filter_stock, filter_product_type FROM `".$this->getTableName('eurotext_project')."` WHERE id=?",array($project_id));
		foreach($allProducts as $row)
		{
			$this->filter_status=$row['filter_status'];
			$this->filter_stock=$row['filter_stock'];
			$this->filter_product_type=$row['filter_product_type'];
		}

		$this->filterDataRead=true;
	}

	public function getFilterStatus()
	{
		$this->readFilterData();

		return $this->filter_status;
	}

	public function getFilterStock()
	{
		$this->readFilterData();

		return $this->filter_stock;
	}

	public function getFilterProductType()
	{
		$this->readFilterData();

		$tmp=$this->filter_product_type;

		$validProductTypes=array("simple","grouped","configurable","virtual","bundle","downloadable");
		if (in_array($tmp,$validProductTypes))
		{
			return $tmp;
		}
		else
		{
			return "";
		}
	}

	public function getSearchResult()
	{
		$pagesize=20;

		$rv=array();
		$rv['find']=trim($this->getRequest()->getParam("find"));
		$page_current=intval($this->getRequest()->getParam("page"));

		$pagesize=intval($this->getRequest()->getParam("pagesize"));
		if ($pagesize<20)
		{
			$pagesize=20;
		}

		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');

		$eavAttribute = Mage::getModel('eav/entity_attribute');
		$name_id = $eavAttribute->getIdByCode('catalog_product', 'name');

		$attr_status_id=$eavAttribute->getIdByCode('catalog_product', 'status');

		$findme="%".$rv['find']."%";

        $sql_categoryfilter="";
        $open_catids=$this->getOpenCatIds();
        if (count($open_catids)>0)
        {
            $selected_catid=$open_catids[count($open_catids)-1];    // Last ID is the selected category
            if ($selected_catid>1)  // root-category has id 1
            {
                $search_catids=Mage::helper('eurotext_translationmanager')->getAllSubCategories($selected_catid); // Get the IDs of all children (direct+indirect)
                array_push($search_catids,$selected_catid); // add selected category to list

                // Filter to products which are assigned to any category in $search_catids:
                $sql_categoryfilter=" AND e.entity_id IN (SELECT cat.product_id FROM catalog_category_product cat WHERE cat.category_id IN (".implode(",",$search_catids)."))";
            }
        }

		$sql_stockfilter=" AND e.entity_id IN (SELECT stock.product_id FROM cataloginventory_stock_item stock WHERE stock.is_in_stock=".$this->getFilterStock().")";
		$sql_statusfilter=" AND e.entity_id IN (SELECT pstatus.entity_id FROM catalog_product_entity_int pstatus WHERE pstatus.attribute_id=".$attr_status_id." AND pstatus.value=".$this->getFilterStatus().")";

		if ($this->getFilterProductType()!=="")
		{
			$sql_statusfilter.=" AND e.type_id='".$this->getFilterProductType()."'";
		}

		$sql="SELECT a.store_id, a.value as article_name, e.sku, e.entity_id FROM `".$this->getTableName('catalog_product_entity_varchar')."` a, `".$this->getTableName('catalog_product_entity')."` e WHERE (a.entity_id=e.entity_id) AND a.store_id=0 AND (a.attribute_id=?) AND ((UPPER(e.sku) LIKE ?) OR (UPPER(a.value) LIKE ?)) $sql_categoryfilter $sql_stockfilter $sql_statusfilter ORDER BY article_name ASC, e.entity_id ASC";

		//echo $sql;
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
			$sql="SELECT product_id FROM `".$this->getTableName('eurotext_project_products')."` p WHERE (p.project_id=?) AND (p.product_id=?)";
			$selProducts=$dbr->fetchAll($sql,array($this->getProjectId(),$prod['entity_id']));
			$prod['checked']=(count($selProducts)>0);

			array_push($resultProducts,$prod);
		}

		$rv['page_current']=$page_current;
		$rv['page_last']=$pageCount;
		$rv['page_size']=$pagesize;
		$rv['result_count']=count($allProducts);
		$rv['products']=$resultProducts;

		return $rv;
	}
}