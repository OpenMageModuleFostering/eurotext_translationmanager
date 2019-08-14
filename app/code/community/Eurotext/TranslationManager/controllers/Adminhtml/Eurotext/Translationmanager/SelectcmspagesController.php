<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Translationmanager_SelectcmspagesController extends Mage_Adminhtml_Controller_Action
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

	public function saveAction()
    {
                $this->loadLayout('adminhtml_eurotext_translationmanager_text');
        $block = $this->getLayout()->getBlock('et.tm.response.text');


        $rv = '';
        
		$project_id=intval($this->getRequest()->getParam("project_id"));
		$cnt_pages=intval($this->getRequest()->getParam("cnt_pages"));
		$cnt_blocks=intval($this->getRequest()->getParam("cnt_blocks"));
		$selectAction=$this->getRequest()->getParam("select");

		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		$dbw=$dbres->getConnection('core_write');

		for($i=0; $i<$cnt_pages; $i++)
		{
			$page_id=intval($this->getRequest()->getParam("page_id_".$i));
			$set=$this->getRequest()->getParam("setpage_".$i);

			if ($page_id>0)
			{
				if ($set=="enabled")
				{
					$time_added=time();

					$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_project_cmspages')."` (page_id,project_id) VALUES (?,?);",array($page_id,$project_id));

					// Update timestamp:
					$dbw->query("UPDATE `".$this->getTableName('eurotext_project_cmspages')."` SET time_added=? WHERE page_id=? AND project_id=?;",array($time_added,$page_id,$project_id));
				}
				else
				{
					$dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_cmspages')."` WHERE page_id=? AND project_id=?;",array($page_id,$project_id));
				}
			}
		}

		for($i=0; $i<$cnt_blocks; $i++)
		{
			$block_id=intval($this->getRequest()->getParam("block_id_".$i));
			$set=$this->getRequest()->getParam("setblock_".$i);

			if ($block_id>0)
			{
				if ($set=="enabled")
				{
					$time_added=time();

					$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_project_cmsblocks')."` (block_id,project_id) VALUES (?,?);",array($block_id,$project_id));

					// Update timestamp:
					$dbw->query("UPDATE `".$this->getTableName('eurotext_project_cmsblocks')."` SET time_added=? WHERE block_id=? AND project_id=?;",array($time_added,$block_id,$project_id));
				}
				else
				{
					$dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_cmsblocks')."` WHERE block_id=? AND project_id=?;",array($block_id,$project_id));
				}
			}
		}

		if ($selectAction=="none")
		{
			$dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_cmspages')."` WHERE project_id=?;",array($project_id));
			$dbw->query("DELETE FROM `".$this->getTableName('eurotext_project_cmsblocks')."` WHERE project_id=?;",array($project_id));
		}

		// Results:
		$rv .=  "<table cellpadding=0 cellspacing=0>";
		$rv .=  "<tr>";
		$rv .=  "  <td class='et_th'>".$this->__("Translate")."</td>";
		$rv .=  "  <td class='et_th'>".$this->__("Designation")."</td>";
		$rv .=  "  <td class='et_th'>&nbsp;</td>";
		$rv .=  "</tr>";

		// get result list:
		$sql="SELECT c.* FROM `".$this->getTableName('cms_page')."` c, `".$this->getTableName('eurotext_project_cmspages')."` p WHERE (c.page_id=p.page_id) AND (p.project_id=?) ORDER BY time_added DESC, c.title ASC, c.page_id ASC";
		$cms_pages=$dbr->fetchAll($sql,array($project_id));
		foreach($cms_pages as $cms_page)
		{
			$rv .=  "<tr>";
			$rv .=  "  <td class='et_tc'><input type='checkbox' id='et_selpage2_".$cms_page['page_id']."' class='et_selcmspage et_selcmspage_".$cms_page['page_id']."' checked='checked' onchange=\"eurotext_selectcmspage('2','".$cms_page['page_id']."')\" /></td>";
			$rv .=  "  <td class='et_tc'>".$cms_page['title']."</td>";
			$rv .=  "  <td class='et_tc'>&nbsp;</td>";
			$rv .=  "</tr>";
		}

		// get result list:
		$sql="SELECT c.* FROM `".$this->getTableName('cms_block')."` c, `".$this->getTableName('eurotext_project_cmsblocks')."` p WHERE (c.block_id=p.block_id) AND (p.project_id=?) ORDER BY time_added DESC, c.title ASC, c.block_id ASC";
		$cms_blocks=$dbr->fetchAll($sql,array($project_id));
		foreach($cms_blocks as $cms_block)
		{
			$rv .=  "<tr>";
			$rv .=  "  <td class='et_tc'><input type='checkbox' id='et_selblock2_".$cms_block['block_id']."' class='et_selcmsblock et_selcmsblock_".$cms_block['block_id']."' checked='checked' onchange=\"eurotext_selectcmsblock('2','".$cms_block['block_id']."')\" /></td>";
			$rv .=  "  <td class='et_tc'>".$cms_block['title']."</td>";
			$rv .=  "  <td class='et_tc'>&nbsp;</td>";
			$rv .=  "</tr>";
		}

		$rv .=  "</table>";

        $block->setText($rv);
        $this->renderLayout();

    }
}
