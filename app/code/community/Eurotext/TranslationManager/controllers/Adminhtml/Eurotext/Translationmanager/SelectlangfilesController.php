<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Translationmanager_SelectlangfilesController extends Mage_Adminhtml_Controller_Action
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


		$project_id=intval($this->getRequest()->getParam("project_id"));
		$cnt=intval($this->getRequest()->getParam("cnt"));
		$selectAction=$this->getRequest()->getParam("select");
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		$dbw=$dbres->getConnection('core_write');
		
		for($i=0; $i<$cnt; $i++)
		{
			$line_hash=$this->getRequest()->getParam("langfile_linehash_".$i);
			$set=$this->getRequest()->getParam("set_".$i);
			
			if ($line_hash!="")
			{				
				$translate_flag=0;
				
				if ($set=="enabled")
				{
					$translate_flag=1;
				}
				
				$time_added=time();
								
				// Update:
				$dbw->query("UPDATE `".$this->getTableName('eurotext_csv')."` SET translate_flag=?, time_added=? WHERE line_hash=? AND project_id=?;",array($translate_flag,$time_added,$line_hash,$project_id));
			}
		}
		
		if ($selectAction=="all")
		{							
			$dbw->query("UPDATE `".$this->getTableName('eurotext_csv')."` SET translate_flag=1 WHERE project_id=?;",array($project_id));			
		}
		else if ($selectAction=="none")
		{
			$dbw->query("UPDATE `".$this->getTableName('eurotext_csv')."` SET translate_flag=0 WHERE project_id=?;",array($project_id));
		}
		
		// get result list:		
		$sql="SELECT * FROM `".$this->getTableName('eurotext_csv')."` WHERE project_id=? AND translate_flag=1 ORDER BY time_added DESC, filename ASC";
		$langfiles=$dbr->fetchAll($sql,array($project_id));
		
        $rv = '';
        
		// Results:
		$rv .=  "<table cellpadding=0 cellspacing=0>";
		$rv .=  "<tr>";
		$rv .=  "  <td class='et_th'>".$this->__("Translate")."</td>";
		$rv .=  "  <td class='et_th'>".$this->__("Filename")."</td>";
		$rv .=  "  <td class='et_th'>&nbsp;</td>";
		$rv .=  "</tr>";
				
		foreach($langfiles as $langfile)
		{			
			$rv .=  "<tr>";
			$rv .=  "  <td class='et_tc'><input type='checkbox' id='et_sellangfile2_".$langfile['line_hash']."' class='et_sellangfile et_sellangfile_".$langfile['line_hash']."' checked='checked' onchange=\"eurotext_selectlangfile('2','".$langfile['line_hash']."')\" /></td>";			
			$rv .=  "  <td class='et_tc'>".$langfile['filename']."</td>";
			$rv .=  "  <td class='et_tc'>&nbsp;</td>";
			$rv .=  "</tr>";
		}
		
		$rv .=  "</table>";

        $block->setText($rv);
        $this->renderLayout();
    }
}
