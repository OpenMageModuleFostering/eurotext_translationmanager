<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Translationmanager_ProjectsController extends Mage_Adminhtml_Controller_Action
{
    /** @var Eurotext_TranslationManager_Helper_Data */
    protected $helper = false;
    /** @var Eurotext_TranslationManager_Helper_Eurotext */
    protected $eurotextHelper = false;

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
                ->isAllowed('eurotext_translationmanager/export');
    }

    protected function _construct(){
        $this->helper = Mage::helper('eurotext_translationmanager');
        $this->eurotextHelper = Mage::helper('eurotext_translationmanager/eurotext');
    }

    protected function getHelper(){
        return $this->helper;
    }
    
    protected function getEurotextHelper(){
        return $this->eurotextHelper;
    }

	public function getModuleVersion()
	{
		return (string) Mage::getConfig()->getNode('modules/Eurotext_TranslationManager/version');
	}

	protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('eurotext_translationmanager_projects/export')
            ->_title('Eurotext')->_title($this->__('Export'))
            ->_addBreadcrumb('Eurotext', $this->__('Export'));
         
        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
	
	public function addprojectAction()
    {   
        // Create new project:
		$tableName = Mage::getSingleton('core/resource')->getTableName('eurotext_translationmanager/project');
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		
		$create_id=rand();
		
		$dbw->query("INSERT INTO `".$tableName."` (create_id,project_name,last_update) VALUES (?,?,NOW());",array($create_id,$this->__("New Project")));
		$project_id=$dbw->fetchOne("SELECT id FROM `".$tableName."` WHERE create_id=".$create_id);
		
		$url=Mage::helper('adminhtml')->getUrl('*/*/index', array('id' => $project_id));
		$this->_redirectUrl($url);
    }
	
	public function deleteAction()
	{
		$project_id=intval(Mage::app()->getRequest()->getParam('id',-1));
				
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
				
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_project')."` WHERE id=?;",array($project_id));
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_csv')."` WHERE project_id=?;",array($project_id));
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_csv_data')."` WHERE project_id=?;",array($project_id));
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_emailtemplates')."` WHERE project_id=?;",array($project_id));
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_import')."` WHERE project_id=?;",array($project_id));
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_project_categories')."` WHERE project_id=?;",array($project_id));
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_project_cmspages')."` WHERE project_id=?;",array($project_id));
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_project_cmsblocks')."` WHERE project_id=?;",array($project_id));
		$dbw->query("DELETE FROM `".Mage::getSingleton('core/resource')->getTableName('eurotext_project_products')."` WHERE project_id=?;",array($project_id));
		
		$url=Mage::helper('adminhtml')->getUrl('*/*/index', array('id' => '-1'));
		$this->_redirectUrl($url);
	}
	
	public function resetAction()
	{
		$project_id=intval(Mage::app()->getRequest()->getParam('id',-1));
				
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
				
		$dbw->query("UPDATE `".Mage::getSingleton('core/resource')->getTableName('eurotext_project')."` SET project_status=(project_status-1) WHERE project_status>0 AND id=?;",array($project_id));		
		
		$url=Mage::helper('adminhtml')->getUrl('*/*/index', array('id' => $project_id));
		$this->_redirectUrl($url);
	}
		
	public function saveprojectAction()
    {
        $this->loadLayout('adminhtml_eurotext_translationmanager_ajax');
        $block = $this->getLayout()->getBlock('et.tm.response.ajax');

        $block->setStatusCode('ok');
        $block->setStatusMsg($this->__("Saved Project!"));

		// Update project:
		try
		{	
			$project_id=intval(Mage::app()->getRequest()->getParam('id',-1));	
			$project_name=$this->getHelper()->sanitize(Mage::app()->getRequest()->getParam('project_name'));
			$storeview_src=intval(Mage::app()->getRequest()->getParam('storeview_src',-1));	
			$storeview_dst=intval(Mage::app()->getRequest()->getParam('storeview_dst',-1));	
			$export_seo=intval(Mage::app()->getRequest()->getParam('export_seo',1));
			$export_attributes=intval(Mage::app()->getRequest()->getParam('export_attributes',1));
			$export_urlkeys=intval(Mage::app()->getRequest()->getParam('export_urlkeys',0));
			$productmode=intval(Mage::app()->getRequest()->getParam('productmode',0));
			$categorymode=intval(Mage::app()->getRequest()->getParam('categorymode',0));
			$cmsmode=intval(Mage::app()->getRequest()->getParam('cmsmode',0));
			$langfilesmode=intval(Mage::app()->getRequest()->getParam('langfilesmode',0));
			$templatemode=intval(Mage::app()->getRequest()->getParam('templatemode',0));
			
			$tableName = Mage::getSingleton('core/resource')->getTableName('eurotext_translationmanager/project');
			
			$dbres = Mage::getSingleton('core/resource');
			$dbw=$dbres->getConnection('core_write');
					
			if (strlen($project_name)==0)
			{
                $block->setStatusCode('error');
			    $block->setStatusMsg($this->__("Please enter project name!"));
			}
			
			if ($storeview_src<0)
			{
                $block->setStatusCode('error');
				$block->setStatusMsg($this->__("Please select Source Storeview!"));
			}
			elseif ($storeview_dst<0)
			{
                $block->setStatusCode('error');
				$block->setStatusMsg($this->__("Please select Destination Storeview!"));
			}
			elseif ($storeview_dst==$storeview_src)
			{
                $block->setStatusCode('error');
				$block->setStatusMsg($this->__("Please select different Storeviews for Source and Destination!"));
			}

            if($block->getStatusCode() == 'ok'){
			    $dbw->query("UPDATE `".$tableName."` SET create_id=-1, last_update=NOW(), project_name=?, storeview_src=?, storeview_dst=?, export_seo=?, export_attributes=?, productmode=?, categorymode=?, cmsmode=?, langfilesmode=?, templatemode=?, export_urlkeys=? WHERE id=".$project_id, array($project_name,$storeview_src,$storeview_dst,$export_seo,$export_attributes,$productmode,$categorymode,$cmsmode,$langfilesmode,$templatemode,$export_urlkeys));
			}
		}
		catch(Exception $e)
		{
            $block->setStatusCode('error');
			$block->setStatusMsg($e->getMessage());
		}

        $this->renderLayout();

    }
	
	private function getProject($project_id)
	{
		$tableName = Mage::getSingleton('core/resource')->getTableName('eurotext_translationmanager/project');
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$projects=$dbr->fetchAll("SELECT * FROM `".$tableName."` WHERE id=".$project_id);
		
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
		
	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
	}
	
	public function ajaxexportAction_CollectLangfiles($project)
	{
		$helper=$this->getHelper();
		$helper->ajaxexportAction_CollectLangfiles($project);
	}
	
	public function ajaxexportAction_ImportLangfiles($project,$offset)
	{

		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection('core_read');
		
		$rv=array();
		$rv['offset']=-1;
		$rv['status_msg']=$this->__("Finished loading language files.");
		
		$addWhere="";
		if ($project['langfilesmode']==0)
		{
			$addWhere=" AND translate_flag=1";
		}
		
		if ($offset==0)
		{
			$dbw->query("DELETE FROM `".$this->getTableName('eurotext_csv_data')."` WHERE project_id=?;",array($project['id']));
		}
		
		$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('eurotext_csv')."` WHERE project_id=".$project['id'].$addWhere));
		
		$rows=$dbr->fetchAll("SELECT * FROM `".$this->getTableName('eurotext_csv')."` WHERE project_id=".$project['id'].$addWhere." ORDER BY filename ASC, line_hash ASC LIMIT $offset,1");
		if (count($rows) > 0)
		{
			$row=$rows[0];
			
			$filename=$row['filename'];
			$locale=$row['locale_dst'];
			$rv['status_msg']=$this->__("Analysing")." ".($offset+1)."/".$cnt.": '".$filename."'";
			
			$full_path=Mage::getBaseDir('app').$filename;
			
			// Read CSV:
			$fp=fopen($full_path,"r");
			$lineIndex=1;
			do
			{
				$fields=fgetcsv($fp,0,",","\"");	
				if ($fields!==false)
				{				
					if (count($fields)==2)
					{
						$line_hash=sha1($project['id']."_".$filename."_".$fields[0]);
						$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_csv_data')."` (line_hash,project_id,filename,locale_dst,text_src,text_src_hash,text_dst,csvline) VALUES (?,?,?,?,?,?,?,?);",array($line_hash,$project['id'],$filename,$locale,$fields[0],sha1($fields[0]),$fields[1],$lineIndex));
					}
				}
				
				$lineIndex++;
			}
			while($fields!==false);
			fclose($fp);
			
			$rv['offset']=$offset+1;
		}			
		
		return $rv;
	}
	
	public function getImportXMLPath($project)
	{
		return $this->getProjectXMLPath($project,"import");
	}
	
	public function getExportXMLPath($project)
	{
		return $this->getProjectXMLPath($project,"export");
	}	
	
	private function getProjectXMLPath($project,$folder)
	{
		$dir=Mage::getBaseDir('var');
		$dir.=DS."eurotext";
		if (!is_dir($dir))
		{
			if(!mkdir($dir)){
                $this->getHelper()->log('Working directory could not be created in '.Mage::getBaseDir('var'), Zend_Log::CRIT);
                throw new Magento_Exception('Eurotext working directory could not be created.');
            }
		}

		$htaccessFilename=$dir.DS.".htaccess";
		if (!is_file($htaccessFilename))
		{
			file_put_contents($htaccessFilename,"# Eurotext Export folder\r\nOrder Deny,Allow\r\nDeny From All");
		}
		
		$dir.=DS."projects";
		if (!is_dir($dir))
		{
			mkdir($dir);
		}

		$dir.=DS.$project['id'];
		if (!is_dir($dir))
		{
			mkdir($dir);
		}		
		
		$dir.=DS.$folder;
		if (!is_dir($dir))
		{
			mkdir($dir);
		}		
		
		return $dir;
	}
	
	private function getTempDir()
	{
		$dir=Mage::getBaseDir('tmp');
		$dir.=DS."eurotext";
		if (!is_dir($dir))
		{
			if(!mkdir($dir)){
                $this->getHelper()->log('Temporary directory could not be created in '.Mage::getBaseDir('var'), Zend_Log::CRIT);
                throw new Magento_Exception('Eurotext temporary directory could not be created.');
            }
		}
		$htaccessFilename=$dir.DS.".htaccess";
		if (!is_file($htaccessFilename))
		{
			file_put_contents($htaccessFilename,"# Eurotext Temp folder\r\nOrder Deny,Allow\r\nDeny From All");
		}

		return $dir;
	}

	public function ajaxexportAction_BuildLangXML($project, $offset)
	{
		$rv=array();
		$rv["status_msg"]=$this->__("Generating language files...");
		$rv["offset"]=$offset;
		$rv["step"]=$this->STEP_BUILD_LANGXML;
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection('core_read');
		
		$xmlDir=$this->getExportXMLPath($project);
		$xmlDir.=DS."framework";
		if (!is_dir($xmlDir))
		{
			mkdir($xmlDir);
		}
				
		$cnt=count($dbr->fetchAll("SELECT filename FROM `".$this->getTableName('eurotext_csv')."` WHERE project_id=".$project['id']." GROUP BY filename"));
		
		$rows=$dbr->fetchAll("SELECT filename FROM `".$this->getTableName('eurotext_csv')."` WHERE project_id=".$project['id']." GROUP BY filename ORDER BY filename ASC LIMIT ".$offset.",1");
		if (count($rows)>0)
		{
			$row=$rows[0];
			
			$en_filename=$row['filename'];
			$dst_filename=str_replace("en_US",$project['storeview_dst_locale'],$en_filename);
			$src_filename=str_replace("en_US",$project['storeview_src_locale'],$en_filename);
			
			$rv["status_msg"]=$this->__("Analyzing")." ".($offset+1)."/".$cnt." '".$en_filename."'";
			
			$short_filename=$this->getEurotextHelper()->GetFilenameFromPath($en_filename);
			$short_filename_safe=$this->getEurotextHelper()->GetFilenameSafeString($short_filename);
			$short_filename_safe=str_replace(".csv","",$short_filename_safe);
			
			$xml_filename=$xmlDir.DS.$short_filename_safe."_".sha1($en_filename).".xml";
			
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
			$translation = $doc->createElement( "translation" );
			
			$translationAttr1=$doc->createAttribute("src_filename");
			$translationAttr1->value=$src_filename;
			$translation->appendChild($translationAttr1);
			
			$translationAttr2=$doc->createAttribute("dst_filename");
			$translationAttr2->value=$dst_filename;
			$translation->appendChild($translationAttr2);

			$doc->appendChild($translation);
			
			$itemCount=0;	
			
			$tbl=$this->getTableName('eurotext_csv_data');
			
			$sql="SELECT text_src FROM `".$tbl."` WHERE project_id=? AND (filename=? OR filename=? OR filename=?) GROUP BY text_src";
			$srcLines=$dbr->fetchAll($sql,array($project['id'],$src_filename,$dst_filename,$en_filename));
			foreach($srcLines as $srcLine)
			{
				$txt_en=$srcLine['text_src'];
				$txt_src="";
				$txt_dst="";
				
				// Src-Sprache lesen, falls vorhanden:
				if ($project['storeview_src_locale']=="en_US")
				{
					$txt_src=$txt_en;
				}
				else
				{
					$tmpData=$dbr->fetchAll("SELECT text_dst, csvline FROM `".$tbl."` WHERE project_id=? AND text_src=? AND filename=? AND locale_dst=?",array($project['id'],$txt_en,$src_filename,$project['storeview_src_locale']));
					foreach($tmpData as $tmpLine)
					{
						$txt_src=$tmpLine['text_dst'];
					}
				}				
				
				// Dst-Sprache lesen, falls vorhanden:
				$tmpData=$dbr->fetchAll("SELECT text_dst, csvline FROM `".$tbl."` WHERE project_id=? AND text_src=? AND filename=? AND locale_dst=?",array($project['id'],$txt_en,$dst_filename,$project['storeview_dst_locale']));
				foreach($tmpData as $tmpLine)
				{
					$txt_dst=$tmpLine['text_dst'];
				}				
				
				if (true)
				{				
					// Needs to be translated:
					$itemCount++;		
					
					// Add comment as separator:
					$comment=$doc->createComment("Line ".$itemCount);
					$translation->appendChild($comment);
					
					{
						$lineExport=$doc->createElement("line".$itemCount);
						$translation->appendChild($lineExport);
															
						// context
						{
							$line=$doc->createElement("line-context");
							{
								$lineIndex=$doc->createAttribute("num");
								$lineIndex->value=$itemCount;
								$line->appendChild($lineIndex);
							}
							{
								$srchashAttribute=$doc->createAttribute("srchash");
								$srchashAttribute->value=sha1($txt_en);
								$line->appendChild($srchashAttribute);
							}
							{
								$contextAttribute=$doc->createAttribute("context");
								$contextAttribute->value="yes";
								$line->appendChild($contextAttribute);
							}
							
							{
								$contextAttribute=$doc->createAttribute("locale");
								$contextAttribute->value="en_US";
								$line->appendChild($contextAttribute);
							}
							
							$this->appendTextChild($line,$txt_en);
							$lineExport->appendChild($line);
						}
					
						// context
						{
							$line=$doc->createElement("line-context");
							{
								$lineIndex=$doc->createAttribute("num");
								$lineIndex->value=$itemCount;
								$line->appendChild($lineIndex);
							}
							{
								$srchashAttribute=$doc->createAttribute("srchash");
								$srchashAttribute->value=sha1($txt_en);
								$line->appendChild($srchashAttribute);
							}
							{
								$contextAttribute=$doc->createAttribute("context");
								$contextAttribute->value="yes";
								$line->appendChild($contextAttribute);
							}
							{
								$contextAttribute=$doc->createAttribute("locale");
								$contextAttribute->value=$project['storeview_dst_locale'];
								$line->appendChild($contextAttribute);
							}
							$this->appendTextChild($line,$txt_dst);
							$lineExport->appendChild($line);
						}
				
						// content
						{			
							
							$line=$doc->createElement("line");
							{
								$lineIndex=$doc->createAttribute("num");
								$lineIndex->value=$itemCount;
								$line->appendChild($lineIndex);
							}
							{
								$srchashAttribute=$doc->createAttribute("srchash");
								$srchashAttribute->value=sha1($txt_en);
								$line->appendChild($srchashAttribute);
							}
							{
								$contextAttribute=$doc->createAttribute("locale-src");
								$contextAttribute->value=$project['storeview_src_locale'];
								$line->appendChild($contextAttribute);
							}
							{
								$contextAttribute=$doc->createAttribute("locale-dst");
								$contextAttribute->value=$project['storeview_dst_locale'];
								$line->appendChild($contextAttribute);
							}
							$this->appendTextChild($line,$txt_src);
							$lineExport->appendChild($line);
						}
					}
				}
			}
			
			if ($itemCount>0)
			{
				$doc->save($xml_filename);
			}
			
			$rv['offset']=($offset+1);
		}
		else
		{
			$rv['step']=$this->STEP_COLLECT_PRODUCTS;
			$rv['offset']=0;
		}
		
		return $rv;
	}
	
	private function appendTextChild($xmlNode, $textContent)
	{
		$doc=$xmlNode->ownerDocument;
		
		$needsCDATA = true; preg_match('/[<>&]/', $textContent);
		
		if ($needsCDATA)
		{
			$xmlNode->appendChild($doc->createCDATASection($textContent));
		}
		else
		{
			$xmlNode->appendChild($doc->createTextNode($textContent));
		}
	}
	
	public function ajaxexportAction_CollectProducts($project, $offset)
	{
		$rv=array();
		$rv["status_msg"]=$this->__("Checking Product")."...";
		$rv["offset"]=$offset;
		$rv["step"]=$this->STEP_COLLECT_PRODUCTS;	
		
		$helper=$this->getHelper();
		$maxItems=intval($helper->getSetting("et_products_per_file",20));
		if ($maxItems<1) { $maxItems=20; }
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection('core_read');
				
		$product_id=-1;
		
		$manualSelected=false;
		
		if ($project['productmode']==1)
		{			
			$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('catalog_product_entity')."`"));
			
			$rows=$dbr->fetchAll("SELECT entity_id, sku FROM `".$this->getTableName('catalog_product_entity')."` ORDER BY sku ASC, entity_id ASC LIMIT $offset,$maxItems");
		}
		else
		{
			$manualSelected=true;
			
			$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('eurotext_project_products')."` WHERE project_id=?",array($project['id'])));
			
			$rows=$dbr->fetchAll("SELECT e.entity_id, e.sku FROM `".$this->getTableName('catalog_product_entity')."` e, `".$this->getTableName('eurotext_project_products')."` p WHERE (e.entity_id=p.product_id) AND project_id=".$project['id']." ORDER BY e.sku ASC, e.entity_id ASC LIMIT $offset,$maxItems");
		}
		
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;
			
		$nodeArticles=$doc->createElement("articles");
		$doc->appendChild($nodeArticles);
		
		$itemsFound=false;
		foreach($rows as $row)
		{			
			$itemsFound=true;
							
			$product_id=intval($row['entity_id']);
			$sku=$row['sku'];
				
			$rv["status_msg"]="(".($offset+1)." / ".$cnt.") ".$this->__("Checking Product")." '".$sku."'...";			
			
			// Compare languages:
			$productSrc = Mage::getModel('catalog/product')->setStoreId($project['storeview_src'])->load($product_id);
			$productDst = Mage::getModel('catalog/product')->setStoreId($project['storeview_dst'])->load($product_id);
			
			$hasChangedProperty=false;		
			
			$nodeArticle = $doc->createElement("article");
			
			{
				$nodeArticleId = $doc->createElement("Id");
				$nodeArticleId->appendChild($doc->createTextNode($product_id));
				$nodeArticle->appendChild($nodeArticleId);
			}
			
			{
				$nodeArticleUrl = $doc->createElement("Url");
				$this->appendTextChild($nodeArticleUrl,$productSrc->getProductUrl());
				$nodeArticle->appendChild($nodeArticleUrl);
			}

			// Name:
			if ($productSrc->getName()!="")
			{
				if ( ($productSrc->getName()==$productDst->getName()) || ($productDst->getName()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Title");
					$this->appendTextChild($item,$productSrc->getName());
					$nodeArticle->appendChild($item);
				}
			}
			
			// Description:
			if ($productSrc->getDescription()!="")
			{
				if ( ($productSrc->getDescription()==$productDst->getDescription()) || ($productDst->getDescription()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Longdesc");
					$this->appendTextChild($item,$productSrc->getDescription());
					$nodeArticle->appendChild($item);
				}
			}
			
			// Short-Description:
			if ($productSrc->getShortDescription()!="")
			{
				if ( ($productSrc->getShortDescription()==$productDst->getShortDescription()) || ($productDst->getShortDescription()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Shortdesc");
					$this->appendTextChild($item,$productSrc->getShortDescription());
					$nodeArticle->appendChild($item);
				}
			}

            // Custom Attributes through config xml
            if($custom_attributes = Mage::helper('eurotext_translationmanager')->getCustomProductAttributesForExport()){
                $hasChangedProperty = true;
                $nodeCustomAttributes = $doc->createElement('custom_attributes');
                // Custom Attribute Mapping is not yet used nor implemented
                foreach($custom_attributes as $custom_attribute => $custom_attribute_mapping){
                    $item = $doc->createElement((string) $custom_attribute);
                    $this->appendTextChild($item, (string) $productSrc->getData($custom_attribute));
                    $nodeCustomAttributes->appendChild($item);
                }
                $nodeArticle->appendChild($nodeCustomAttributes);
            }
			
			// Images:
			{
				$images_orig=array();
				$images_orig_url=array();
				$images_dst_disabled=array();
				$images_dst_position=array();

				$images_dstLabel=array();
				
				$rows=$dbr->fetchAll("SELECT g.value_id, g.value, v.store_id, v.label, v.disabled, position FROM `".$this->getTableName('catalog_product_entity_media_gallery')."` g, `".$this->getTableName('catalog_product_entity_media_gallery_value')."` v WHERE (v.value_id=g.value_id) AND g.entity_id=".$product_id." ORDER BY v.store_id ASC");
				foreach($rows as $row)
				{
					$img_store_id=$row['store_id'];
					$img_value_id=$row['value_id'];
					$img_value=$row['value'];
					$img_label=$row['label'];
					$img_disabled=$row['disabled'];
					$img_position=$row['position'];

                    $images_dst_disabled[$img_value_id]=$img_disabled;
                    $images_dst_position[$img_value_id]=$img_position;

					if (($img_store_id==0) || ($img_store_id==$project['storeview_src']))
					{
						$images_orig[$img_value_id]=$img_label;
						$images_orig_url[$img_value_id]=$img_value;
					}
					
					if ($img_store_id==$project['storeview_dst'])
					{
						$images_dstLabel[$img_value_id]=$img_label;
					}
				}
				
				$hasImages=false;
				$imagesNode=$doc->createElement("Images");
				
				foreach($images_orig as $img_value_id => $img_label)
				{
					if(!array_key_exists($img_value_id,$images_dstLabel))
					{				
						$images_dstLabel[$img_value_id]="";
					}

					$needsUpdate=$manualSelected;					
					if (strlen(trim($images_dstLabel[$img_value_id]))==0)
					{
						$needsUpdate=true;
					}
					
					if ($needsUpdate)
					{
						if (strlen(trim($img_label))>0)
						{
							$hasImages=true;
							$hasChangedProperty=true;
							
							$imageNode=$doc->createElement("Image");
							$imagesNode->appendChild($imageNode);
							
							$imageNodeId=$doc->createAttribute("value_id");
							$imageNodeId->value=$img_value_id;
							$imageNode->appendChild($imageNodeId);
							
							$imageNodePosition=$doc->createAttribute("position");
							$imageNodePosition->value=$images_dst_position[$img_value_id];
							$imageNode->appendChild($imageNodePosition);
							
							$imageNodeDisabled=$doc->createAttribute("disabled");
							$imageNodeDisabled->value=$images_dst_disabled[$img_value_id];
							$imageNode->appendChild($imageNodeDisabled);
							
							// URL:
							$img_url=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."catalog/product".$images_orig_url[$img_value_id];
							
							$imageNodeUrl=$doc->createElement("Url");
							$this->appendTextChild($imageNodeUrl,$img_url);
							$imageNode->appendChild($imageNodeUrl);
							
							// Label:
							$labelNode=$doc->createElement("Label");
							$this->appendTextChild($labelNode,$img_label);
							$imageNode->appendChild($labelNode);
						}
					}
				}
				
				if ($hasImages)
				{
					$nodeArticle->appendChild($imagesNode);
				}
			}
			
			// Options:
			{
				$optionsNode=$doc->createElement("Options");
				$hasOptions=false;
				
				$_options=$productSrc->getOptions();
				if ($_options!==false)
				{
					foreach($_options as $_option)
                    /** @var Mage_Catalog_Model_Product_Option $_option */
					{
						$optionNode=$doc->createElement("Option");
						
						$optionNodeIdAttribute=$doc->createAttribute("Id");
						$optionNodeIdAttribute->value=$_option->getId();
						$optionNode->appendChild($optionNodeIdAttribute);
						
						$_optionTitle=$_option->getTitle();
						if ($_optionTitle!==null)
						{
							$optionsNode->appendChild($optionNode);
							$hasOptions=true;
						
							$optionsNodeTitle=$doc->createElement("Title");
							$this->appendTextChild($optionsNodeTitle,$_optionTitle);
							$optionNode->appendChild($optionsNodeTitle);
							
							// Values:
							$hasValues=false;
							$_values=$_option->getValues();
							if ($_values!==null)
							{
								$valuesNode=$doc->createElement("Values");
								
								foreach($_values as $_value)
								{
									$_valueTitle=$_value->getTitle();
									if ($_valueTitle!==null)
									{
										$_valueId=$_value->getId();
										
										$valueNode=$doc->createElement("Value");
										
										$valueNodeIdAttribute=$doc->createAttribute("Id");
										$valueNodeIdAttribute->value=$_valueId;
										$valueNode->appendChild($valueNodeIdAttribute);
																			
										$valueNodeTitle=$doc->createElement("Title");
										$this->appendTextChild($valueNodeTitle,$_valueTitle);
										$valueNode->appendChild($valueNodeTitle);
										
										$valuesNode->appendChild($valueNode);
									
										$hasValues=true;
									}
								}
							}
							if ($hasValues)
							{
								$optionNode->appendChild($valuesNode);
							}			
						}
					}
				}
				
				if ($hasOptions)
				{
					$nodeArticle->appendChild($optionsNode);
				}
			}
			
			if ($project['export_urlkeys']=="1")
			{
				// URL-Key:
				if ($productSrc->getUrlKey()!="")
				{
					if ( ($productSrc->getUrlKey()==$productDst->getUrlKey()) || ($productDst->getUrlKey()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("UrlKey");
						$this->appendTextChild($item,$productSrc->getUrlKey());
						$nodeArticle->appendChild($item);
					}
				}
			}
			
			if ($project['export_seo']=="1")
			{
				// Meta-Title:
				if ($productSrc->getMetaTitle()!="")
				{
					if ( ($productSrc->getMetaTitle()==$productDst->getMetaTitle()) || ($productDst->getMetaTitle()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoTitle");
						$this->appendTextChild($item,$productSrc->getMetaTitle());
						$nodeArticle->appendChild($item);
					}
				}
				
				// Meta-Description:
				if ($productSrc->getMetaDescription()!="")
				{
					if ( ($productSrc->getMetaDescription()==$productDst->getMetaDescription()) || ($productDst->getMetaDescription()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoDescription");
						$this->appendTextChild($item,$productSrc->getMetaDescription());
						$nodeArticle->appendChild($item);
					}
				}
				
				// Meta-Keywords:
				if ($productSrc->getMetaKeyword()!="")
				{
					if ( ($productSrc->getMetaKeyword()==$productDst->getMetaKeyword()) || ($productDst->getMetaKeyword()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoKeywords");
						$this->appendTextChild($item,$productSrc->getMetaKeyword());
						$nodeArticle->appendChild($item);
					}
				}
			}
			
			if ($hasChangedProperty)
			{
				$nodeArticles->appendChild($nodeArticle);
			}
			
			$rv["offset"]=($offset+$maxItems);
		}
		
		if ($itemsFound)
		{
			$xmlDir=$this->getExportXMLPath($project);
			$xmlDir.=DS."articles";
			if (!is_dir($xmlDir))
			{
				mkdir($xmlDir);
			}
			
			$fileNum=($offset / $maxItems)+1;
			$fileNum=intval($fileNum);
			
			$xmlFilename=$xmlDir.DS."a".$fileNum.".xml";
			
			$doc->save($xmlFilename);
		}
		else
		{
			// no further product found, go to next step:
			$rv['step']=$this->STEP_COLLECT_CATEGORIES;
			$rv["offset"]=0;
		}
		
		return $rv;		
	}
	
	public function ajaxexportAction_CollectCMSPages($project, $offset)
	{
		$rv=array();
		$rv["status_msg"]=$this->__("Checking CMS-Pages")."...";
		$rv["offset"]=$offset;
		$rv["step"]=$this->STEP_COLLECT_CMSPAGES;
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection('core_read');
				
		$page_id=-1;
		
		$manualSelected=false;
		
		if ($project['cmsmode']==1)
		{		
			$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('cms_page')."` p, `".$this->getTableName('cms_page_store')."` s WHERE (s.page_id=p.page_id) AND ((s.store_id=0) OR (s.store_id=".$project['storeview_src']."))"));
			
			// Pages:
			$rows=$dbr->fetchAll("SELECT p.page_id, p.title FROM `".$this->getTableName('cms_page')."` p, `".$this->getTableName('cms_page_store')."` s WHERE (s.page_id=p.page_id) AND ((s.store_id=0) OR (s.store_id=".$project['storeview_src'].")) ORDER BY page_id ASC LIMIT $offset,1");
			if (count($rows)>0)
			{
				$page_id=intval($rows[0]['page_id']);
				$title=$rows[0]['title'];
				
				$rv["status_msg"]="(".($offset+1)." / ".$cnt.") ".$this->__("Checking CMS-Page")." '".$title."'...";
			}
		}
		else
		{
			$manualSelected=true;
			
			$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('eurotext_project_cmspages')."` WHERE project_id=".$project['id']));
			
			$rows=$dbr->fetchAll("SELECT p.page_id, p.title FROM `".$this->getTableName('cms_page')."` p, `".$this->getTableName('eurotext_project_cmspages')."` e WHERE (e.page_id=p.page_id) AND project_id=".$project['id']." ORDER BY p.page_id ASC LIMIT $offset,1");
			if (count($rows)>0)
			{
				$page_id=intval($rows[0]['page_id']);
				$title=$rows[0]['title'];
				
				$rv["status_msg"]="(".($offset+1)." / ".$cnt.") ".$this->__("CMS-Page")." '".$title."'...";
			}
		}
		
		if ($page_id>0)
		{
			// Compare languages:
			$pageSrc = Mage::getModel('cms/page')->load($page_id);
			$pageDst = Mage::getModel('cms/page')->load($page_id);
			
			$page_id_dst=-1;
			
			$identifier=$pageSrc->getIdentifier();
			// Find matching page:
			$matchingPages=$dbr->fetchAll("SELECT p.page_id FROM `".$this->getTableName('cms_page')."` p, `".$this->getTableName('cms_page_store')."` s WHERE (p.page_id=s.page_id) AND UPPER(p.identifier)=? AND s.store_id>0 AND s.store_id=? ORDER BY p.page_id ASC",array($identifier,$project['storeview_dst']));
			if (count($matchingPages)>0)
			{
				$matchingPage=$matchingPages[0];
				$page_id_dst=$matchingPage['page_id'];
				
				$pageDst = Mage::getModel('cms/page')->load($page_id_dst);
			}
			
			$hasChangedProperty=false;
			
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
			$cmsSites=$doc->createElement("cms-sites");
			$doc->appendChild($cmsSites);
			$cmsSite = $doc->createElement("cms-site");
			$cmsSites->appendChild($cmsSite);
			
			// ID:
			{
				$item=$doc->createElement("Id");
				$item->appendChild($doc->createTextNode($page_id));
				$cmsSite->appendChild($item);
			}
			
			// Storeview_src:
			{
				$item=$doc->createElement("StoreviewSrc");
				$item->appendChild($doc->createTextNode($project['storeview_src']));
				$cmsSite->appendChild($item);
			}
			
			// Storeview_dst:
			{
				$item=$doc->createElement("StoreviewDst");
				$item->appendChild($doc->createTextNode($project['storeview_dst']));
				$cmsSite->appendChild($item);
			}
			
			// page_id_dst:
			{
				$item=$doc->createElement("PageIdDst");
				$item->appendChild($doc->createTextNode($page_id_dst));
				$cmsSite->appendChild($item);
			}
			
			// Title:
			if ($pageSrc->getTitle()!="")
			{
				if ( ($pageSrc->getTitle()==$pageDst->getTitle()) || ($pageDst->getTitle()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Title");
					$this->appendTextChild($item,$pageSrc->getTitle());
					$cmsSite->appendChild($item);
				}
			}
			
			// Content:
			if ($pageSrc->getContent()!="")
			{
				if ( ($pageSrc->getContent()==$pageDst->getContent()) || ($pageDst->getContent()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Content");
					$this->appendTextChild($item,$pageSrc->getContent());
					$cmsSite->appendChild($item);
				}
			}
			
			// Content-Heading:
			if ($pageSrc->getContentHeading()!="")
			{
				if ( ($pageSrc->getContentHeading()==$pageDst->getContentHeading()) || ($pageDst->getContentHeading()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("ContentHeading");
					$this->appendTextChild($item,$pageSrc->getContentHeading());
					$cmsSite->appendChild($item);
				}
			}
			
			if ($project['export_seo']=="1")
			{
				// Meta-Keywords
				if ($pageSrc->getMetaKeywords()!="")
				{
					if ( ($pageSrc->getMetaKeywords()==$pageDst->getMetaKeywords()) || ($pageDst->getMetaKeywords()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoKeywords");
						$this->appendTextChild($item,$pageSrc->getMetaKeywords());
						$cmsSite->appendChild($item);
					}
				}
				
				// Meta-Description:
				if ($pageSrc->getMetaDescription()!="")
				{
					if ( ($pageSrc->getMetaDescription()==$pageDst->getMetaDescription()) || ($pageDst->getMetaDescription()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoDescription");
						$this->appendTextChild($item,$pageSrc->getMetaDescription());
						$cmsSite->appendChild($item);
					}
				}
			}
			
			if ($hasChangedProperty)
			{
				$xmlDir=$this->getExportXMLPath($project);
				$xmlDir.=DS."cms-sites";
				if (!is_dir($xmlDir))
				{
					mkdir($xmlDir);
				}
				
				$xmlFilename=$xmlDir.DS."cms-".$this->getEurotextHelper()->GetFilenameSafeString($identifier)."-".$page_id.".xml";
				
				$doc->save($xmlFilename);
			}
			
			$rv["offset"]=($offset+1);
		}
		else
		{
			// no further cms-page found, go to next step:
			$rv['step']=$this->STEP_COLLECT_CMSBLOCKS;
			$rv["offset"]=0;
		}
		
		return $rv;		
	}
	
	public function ajaxexportAction_CollectCMSBlocks($project, $offset)
	{
		$rv=array();
		$rv["status_msg"]=$this->__("Checking CMS-Blocks")."...";
		$rv["offset"]=$offset;
		$rv["step"]=$this->STEP_COLLECT_CMSBLOCKS;
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection('core_read');
				
		$block_id=-1;
		
		$manualSelected=false;
		
		if ($project['cmsmode']==1)
		{		
			$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('cms_block')."` p, `".$this->getTableName('cms_block_store')."` s WHERE (s.block_id=p.block_id) AND ((s.store_id=0) OR (s.store_id=".$project['storeview_src']."))"));
			
			// Blocks:
			$rows=$dbr->fetchAll("SELECT p.block_id, p.title FROM `".$this->getTableName('cms_block')."` p, `".$this->getTableName('cms_block_store')."` s WHERE (s.block_id=p.block_id) AND ((s.store_id=0) OR (s.store_id=".$project['storeview_src'].")) ORDER BY block_id ASC LIMIT $offset,1");
			if (count($rows)>0)
			{
				$block_id=intval($rows[0]['block_id']);
				$title=$rows[0]['title'];
				
				$rv["status_msg"]="(".($offset+1)." / ".$cnt.") ".$this->__("Checking CMS-Block")." '".$title."'...";
			}
		}
		else
		{
			$manualSelected=true;
			
			$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('eurotext_project_cmsblocks')."` WHERE project_id=".$project['id']));
			
			$rows=$dbr->fetchAll("SELECT p.block_id, p.title FROM `".$this->getTableName('cms_block')."` p, `".$this->getTableName('eurotext_project_cmsblocks')."` e WHERE (e.block_id=p.block_id) AND project_id=".$project['id']." ORDER BY p.block_id ASC LIMIT $offset,1");
			if (count($rows)>0)
			{
				$block_id=intval($rows[0]['block_id']);
				$title=$rows[0]['title'];
				
				$rv["status_msg"]="(".($offset+1)." / ".$cnt.") ".$this->__("Checking CMS-Block")." '".$title."'...";
			}
		}
		
		if ($block_id>0)
		{
			// Compare languages:
			$blockSrc = Mage::getModel('cms/block')->load($block_id);
			$blockDst = Mage::getModel('cms/block')->load($block_id);
			
			$block_id_dst=-1;
			
			$identifier=$blockSrc->getIdentifier();
			// Find matching block:
			$matchingPages=$dbr->fetchAll("SELECT p.block_id FROM `".$this->getTableName('cms_block')."` p, `".$this->getTableName('cms_block_store')."` s WHERE (p.block_id=s.block_id) AND UPPER(p.identifier)=? AND s.store_id>0 AND s.store_id=? ORDER BY p.block_id ASC",array($identifier,$project['storeview_dst']));
			if (count($matchingPages)>0)
			{
				$matchingPage=$matchingPages[0];
				$block_id_dst=$matchingPage['block_id'];
				
				$blockDst = Mage::getModel('cms/block')->load($block_id_dst);
			}
			
			$hasChangedProperty=false;
			
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
			$cmsSites=$doc->createElement("cms-sites");
			$doc->appendChild($cmsSites);
			$cmsSite = $doc->createElement("cms-site");
			$cmsSites->appendChild($cmsSite);
			
			// ID:
			{
				$item=$doc->createElement("Id");
				$item->appendChild($doc->createTextNode($block_id));
				$cmsSite->appendChild($item);
			}
			
			// Storeview_src:
			{
				$item=$doc->createElement("StoreviewSrc");
				$item->appendChild($doc->createTextNode($project['storeview_src']));
				$cmsSite->appendChild($item);
			}
			
			// Storeview_dst:
			{
				$item=$doc->createElement("StoreviewDst");
				$item->appendChild($doc->createTextNode($project['storeview_dst']));
				$cmsSite->appendChild($item);
			}
			
			// block_id_dst:
			{
				$item=$doc->createElement("PageIdDst");
				$item->appendChild($doc->createTextNode($block_id_dst));
				$cmsSite->appendChild($item);
			}
			
			// Title:
			if ($blockSrc->getTitle()!="")
			{
				if ( ($blockSrc->getTitle()==$blockDst->getTitle()) || ($blockDst->getTitle()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Title");
					$this->appendTextChild($item,$blockSrc->getTitle());
					$cmsSite->appendChild($item);
				}
			}
			
			// Content:
			if ($blockSrc->getContent()!="")
			{
				if ( ($blockSrc->getContent()==$blockDst->getContent()) || ($blockDst->getContent()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Content");
					$this->appendTextChild($item,$blockSrc->getContent());
					$cmsSite->appendChild($item);
				}
			}
			
			// Content-Heading:
			if ($blockSrc->getContentHeading()!="")
			{
				if ( ($blockSrc->getContentHeading()==$blockDst->getContentHeading()) || ($blockDst->getContentHeading()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("ContentHeading");
					$this->appendTextChild($item,$blockSrc->getContentHeading());
					$cmsSite->appendChild($item);
				}
			}
			
			if ($project['export_seo']=="1")
			{
				// Meta-Keywords
				if ($blockSrc->getMetaKeywords()!="")
				{
					if ( ($blockSrc->getMetaKeywords()==$blockDst->getMetaKeywords()) || ($blockDst->getMetaKeywords()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoKeywords");
						$this->appendTextChild($item,$blockSrc->getMetaKeywords());
						$cmsSite->appendChild($item);
					}
				}
				
				// Meta-Description:
				if ($blockSrc->getMetaDescription()!="")
				{
					if ( ($blockSrc->getMetaDescription()==$blockDst->getMetaDescription()) || ($blockDst->getMetaDescription()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoDescription");
						$this->appendTextChild($item,$blockSrc->getMetaDescription());
						$cmsSite->appendChild($item);
					}
				}
			}
			
			if ($hasChangedProperty)
			{
				$xmlDir=$this->getExportXMLPath($project);
				$xmlDir.=DS."cms-sites";
				if (!is_dir($xmlDir))
				{
					mkdir($xmlDir);
				}
				
				$xmlFilename=$xmlDir.DS."cmsblock-".$this->getEurotextHelper()->GetFilenameSafeString($identifier)."-".$block_id.".xml";
				
				$doc->save($xmlFilename);
			}
			
			$rv["offset"]=($offset+1);
		}
		else
		{
			// no further cms-block found, go to next step:
			$rv['step']=$this->STEP_COLLECT_TEMPLATES;
			$rv["offset"]=0;
		}
		
		return $rv;		
	}
	
	public function ajaxexportAction_CollectTemplates($project, $offset)
	{
		$rv=array();
		$rv["status_msg"]=$this->__("Checking eMail-Templates")."...";
		$rv["offset"]=$offset;
		$rv["step"]=$this->STEP_COLLECT_TEMPLATES;
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection('core_read');
						
		$manualSelected=false;
		
		if ($offset==0)
		{
			$helper=$this->getHelper();
			$helper->ajaxexportAction_CollectEMailTemplates($project);
		}
		
		$sql_cnt="SELECT COUNT(*) cnt FROM `".$this->getTableName('eurotext_emailtemplates')."` WHERE deleteflag=0 AND project_id=".$project['id']." AND locale_dst='".$project['storeview_src_locale']."'";
		$sql_fetch="SELECT * FROM `".$this->getTableName('eurotext_emailtemplates')."` WHERE deleteflag=0 AND project_id=".$project['id']." AND locale_dst='".$project['storeview_src_locale']."'";
		
		if ($project['templatemode']==0)
		{		
			$manualSelected=true;
			
			$sql_cnt.=" AND translate_flag=1";
			$sql_fetch.=" AND translate_flag=1";
		}
		
		$sql_fetch.=" ORDER BY filename ASC, file_hash ASC LIMIT $offset,1";
		
		$cnt=intval($dbr->fetchOne($sql_cnt));
			
		$rows=$dbr->fetchAll($sql_fetch);
		if (count($rows)>0)
		{
			$filename=$rows[0]['filename'];
			$locale_dst=$rows[0]['locale_dst'];
			
			$rv["status_msg"]="(".($offset+1)." / ".$cnt.") ".$this->__("Checking eMail-Template")." '".$filename."'...";
			
			$xmlDir=$this->getExportXMLPath($project);
			$xmlDir.=DS."emailtemplates";
			if (!is_dir($xmlDir))
			{
				mkdir($xmlDir);
			}
			
			$baseLocaleFolder=Mage::getBaseDir('locale');

			$src_filename=$baseLocaleFolder.DS.$locale_dst.DS."template".$filename;
			$dst_filename=$baseLocaleFolder.DS.$project['storeview_dst_locale'].DS."template".$filename;
			
			if ((!$manualSelected) && (file_exists($dst_filename)))
			{
				// "Datei schon übersetzt!"
			}
			else
			{
				$copyto_filename=$xmlDir.$filename;
				$copyto_path=$this->getEurotextHelper()->GetDirectoryFromPath($copyto_filename);
				if (!is_dir($copyto_path))
				{
					mkdir($copyto_path,0777,true);
				}
				
				copy($src_filename,$copyto_filename);
			}	
						
			$rv["offset"]=($offset+1);
		}
		else
		{		
			// no further cms-page found, go to next step:
			$rv['step']=$this->STEP_EXPORT_ATTRIBUTES;
			$rv["offset"]=0;
		}
		
		return $rv;		
	}
	
	public function getProjectZipFilename($project)
	{
        $xmlDir=$this->getExportXMLPath($project);

		if ($project['zip_filename']!="")
		{
			return $xmlDir.DS.$project['zip_filename'];
		}		
		
		$helper=$this->getHelper();
		
		$shopName=$this->getEurotextHelper()->GetFilenameSafeString($helper->getSetting("register_shopname","shop"));
		$customer_id = $this->getEurotextHelper()->GetFilenameSafeString($helper->getSetting('eurotext_customerid','no-customerid'));
		$filename="ET-".$customer_id.'-'.$shopName.'-'.date("Y-m-d_h-i-s").'_'.date_default_timezone_get().".zip";
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbw->query("UPDATE `".$this->getTableName('eurotext_project')."` SET zip_filename=? WHERE id=?",array($filename,$project['id']));
		
		$project['zip_filename']=$filename;
		
		return $xmlDir.DIRECTORY_SEPARATOR."".$filename;
	}
	
	public function ajaxexportAction_GenerateControlFile($project)
	{
		$xmlDir=$this->getExportXMLPath($project);
		
		$helper=$this->getHelper();
		$register_fname=$helper->getSetting("register_fname","");
		$register_lname=$helper->getSetting("register_lname","");
		$register_company=$helper->getSetting("register_company","");
		$register_email=$helper->getSetting("register_email","");
		$eurotext_customerid=$helper->getSetting("eurotext_customerid","");
		
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;

        // utf8-detection comment:
        $defComment=$doc->createComment("allow utf8-detection: öäü€");
		$doc->appendChild($defComment);

		$nodeRequest = $doc->createElement("Request");
		$doc->appendChild($nodeRequest);
		{
			$nodeGeneral = $doc->createElement("General");
			$nodeRequest->appendChild($nodeGeneral);
			{
				$nodeCustomerContact= $doc->createElement("CustomerContact");
				$nodeCustomerContact->appendChild($doc->createTextNode($register_fname." ".$register_lname));
				$nodeGeneral->appendChild($nodeCustomerContact);
				
				$nodeCustomerMail= $doc->createElement("CustomerMail");
				$nodeCustomerMail->appendChild($doc->createTextNode($register_email));
				$nodeGeneral->appendChild($nodeCustomerMail);
				
				$nodeCustomerID_of_Supplier= $doc->createElement("CustomerID_of_Supplier");
				$nodeCustomerID_of_Supplier->appendChild($doc->createTextNode($eurotext_customerid));
				$nodeGeneral->appendChild($nodeCustomerID_of_Supplier);
				
				$nodeProjectName= $doc->createElement("ProjectName");
				$nodeProjectName->appendChild($doc->createTextNode($project['project_name']));
				$nodeGeneral->appendChild($nodeProjectName);
				
				$moduleVersion=$this->getModuleVersion();
				
				$src_name=Mage::getModel('core/store')->load($project['storeview_src'])->getName();
				$dst_name=Mage::getModel('core/store')->load($project['storeview_dst'])->getName();
				
				$commentText="Magento-Project-Id: ".$project['id'].", Module-Version: $moduleVersion\n\n";
				$commentText.="Src-Storeview: '".$src_name."' (".$project['storeview_src_locale'].")\n";
				$commentText.="Dst-Storeview: '".$dst_name."' (".$project['storeview_dst_locale'].")\n";
				$commentText.="Export SEO content: ".($project['export_seo'] ? "Yes" : "No")."\n";
				$commentText.="Export attributes and attribute options? ".($project['export_attributes'] ? "Yes" : "No")."\n";
				$commentText.="Export URL keys? ".($project['export_urlkeys'] ? "Yes" : "No");
				
				$nodeDescription= $doc->createElement("Description");
				$nodeDescription->appendChild($doc->createTextNode($commentText));
				$nodeGeneral->appendChild($nodeDescription);
				
				$nodeDeadline= $doc->createElement("Deadline");
				$nodeGeneral->appendChild($nodeDeadline);
				
				$nodeTargetProject= $doc->createElement("TargetProject");
				$nodeTargetProject->appendChild($doc->createTextNode("Quote"));
				$nodeGeneral->appendChild($nodeTargetProject);			
			}
			
			$nodeLanguageCombinations= $doc->createElement("LanguageCombinations");
			$nodeRequest->appendChild($nodeLanguageCombinations);
			
			$locale_dst=$project['storeview_dst_locale'];
			$locale_src=$project['storeview_src_locale'];
			
			// Shop-Translations:			
			$allFiles=$this->getEurotextHelper()->getDirectoryContent($xmlDir,true,true,false);
			$frameworkFiles=array();
			$articlesFiles=array();
			$attributesFiles=array();
			$categoriesFiles=array();
			$cmsFiles=array();
			$emailtemplatesFiles=array();
									
			foreach($allFiles as $file)
			{
				if (stripos($file['full_path'],DS."framework".DS)!==false)
				{
					array_push($frameworkFiles,$file);
				}
				elseif (stripos($file['full_path'],DS."articles".DS)!==false)
				{
					array_push($articlesFiles,$file);
				}
				elseif (stripos($file['full_path'],DS."attributes".DS)!==false)
				{
					array_push($attributesFiles,$file);
				}
				elseif (stripos($file['full_path'],DS."categories".DS)!==false)
				{
					array_push($categoriesFiles,$file);
				}
				elseif (stripos($file['full_path'],DS."cms-sites".DS)!==false)
				{
					array_push($cmsFiles,$file);
				}
				elseif (stripos($file['full_path'],DS."emailtemplates".DS)!==false)
				{
					array_push($emailtemplatesFiles,$file);
				}
				else
				{
                    $rv=array();
                    $rv['status_code']="error";
		            $rv['status_msg']="Unknown file: '".$file['full_path']."'";

                    Mage::app()->getResponse()->clearBody();
                    Mage::app()->getResponse()->setBody(json_encode($rv));
                    Mage::app()->getResponse()->sendResponse();

                    // @todo Hässlicher Magento Hack, da man die Ausgabe anders nicht abbrechen kann
                    exit();

				}
			}
			
			$fileGroups=array();
			array_push($fileGroups,$frameworkFiles);
			array_push($fileGroups,$articlesFiles);
			array_push($fileGroups,$attributesFiles);
			array_push($fileGroups,$categoriesFiles);
			array_push($fileGroups,$cmsFiles);
			array_push($fileGroups,$emailtemplatesFiles);
			
			foreach($fileGroups as $fileGroup)
			{
				if (count($fileGroup)>0)
				{
					$nodeLanguageCombination=$doc->createElement("LanguageCombination");
					{
						$source=$doc->createAttribute("source");
						
						$eurotext_locale_src=$helper->getLocaleInfoByMagentoLocale($locale_src);
						if ($eurotext_locale_src['supported']==false)
						{
							die("Sprache wird durch Eurotext nicht unterstützt: '".$locale_src."'"); 
						}
						
						$source->value=$eurotext_locale_src['locale_eurotext'];
						$nodeLanguageCombination->appendChild($source);
					}
					{
						$eurotext_locale_dst=$helper->getLocaleInfoByMagentoLocale($locale_dst);
						if ($eurotext_locale_dst['supported']==false)
						{
							die("Sprache wird durch Eurotext nicht unterstützt: '".$locale_dst."'"); 
						}
					
						$target=$doc->createAttribute("target");
						$target->value=$eurotext_locale_dst['locale_eurotext'];
						$nodeLanguageCombination->appendChild($target);
					}
					$nodeLanguageCombinations->appendChild($nodeLanguageCombination);
					
					foreach($fileGroup as $otherFile)
					{				
						$relative_filename=substr($otherFile['full_path'],strlen($xmlDir)+1);
						$fsize=filesize($otherFile['full_path']);
						
						$nodeUploadedFile=$doc->createElement("uploadedFile");
						$nodeLanguageCombination->appendChild($nodeUploadedFile);
						{
							$attrfileName=$doc->createAttribute("fileName");
							$attrfileName->value=$relative_filename;
							$nodeUploadedFile->appendChild($attrfileName);
						}
						{
							$attrsize=$doc->createAttribute("size");
							$attrsize->value=$fsize;
							$nodeUploadedFile->appendChild($attrsize);
						}
					}
				}
			}
		}
		
		$xmlFilename=$xmlDir.DS."control.xml";
		$doc->save($xmlFilename);
	}	
	
	public function ajaxexportAction_CompressFiles($project)
	{
		$xmlDir=$this->getExportXMLPath($project);
		$genZip=$this->getEurotextHelper()->zipFolder($xmlDir,$this->getProjectZipFilename($project),"Created by Eurotext Magento Module");
	}	
	
	public function ajaxexportAction_TransmitArchive($project)
	{
		$zipFile=$this->getProjectZipFilename($project);
		$helper=$this->getHelper();
        $fallback_filename=Mage::getBaseDir('export').DS.$project['zip_filename'];
        $ftp_upload_successful = false;

		$ftp_username=$helper->getSetting("eurotext_username","");
		$ftp_password=Mage::helper('core')->decrypt($helper->getSetting("eurotext_password",""));

        $testResult=$helper->testFtpConnection();

        if ($testResult['ok']!=true)
        {
            die("Archive could not be transmitted. Please use the debug info in var/log/eurotext_fatal.log");
        }
        else {
            if($ftpConn = $helper->openFtpConnection()) {
                if (@ftp_login($ftpConn, $ftp_username, $ftp_password)) {
                    // Switch to Passive-Mode:
                    ftp_pasv($ftpConn, true);

                    ftp_chdir($ftpConn, "/");

                    // Delete old files with same name
                    @ftp_delete($ftpConn, $project['zip_filename']);
                    @ftp_delete($ftpConn, $project['zip_filename'] . ".uploading");

                    // Upload with .uploading extension
                    if(ftp_put($ftpConn, $project['zip_filename'] . ".uploading", $zipFile, FTP_BINARY)) {
                        $helper->log('File was successfully uploaded to ' . $project['zip_filename'].'.uploading', Zend_Log::INFO);
                    }

                    // Remove .uploading extension
                    if (ftp_rename($ftpConn, $project['zip_filename'] . ".uploading", $project['zip_filename'])) {
                        $ftp_upload_successful = true;
                        $helper->log('File was successfully renamed to ' . $project['zip_filename'], Zend_Log::INFO);
                    }
                } else {
                    $helper->log('Could not login to Translation Portal Server.', Zend_Log::ERR);
                }

                ftp_close($ftpConn);
            }
        }

        if(false === $ftp_upload_successful || true === $helper->getDebugMode())
        {
                if(false === copy($zipFile,$fallback_filename)) {
                    $helper->log('Could not copy the project data file to '.$fallback_filename, Zend_Log::ERR);
                }
        }

        // Clear project folder:
        $xmlPath=$this->getExportXMLPath($project);
        $this->deleteDirectoryRecursive($xmlPath);
		
		$this->updateProjectState($project['id'],1);	// 1 = Processing
	}	
	
	public function ajaxexportAction_CollectCategories($project, $offset)
	{
		$rv=array();
		$rv["status_msg"]=$this->__("Checking category")."...";
		$rv["offset"]=$offset;
		$rv["step"]=$this->STEP_COLLECT_CATEGORIES;
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection('core_read');
				
		$cat_id=-1;
		
		$helper=$this->getHelper();
		$maxItems=intval($helper->getSetting("et_categories_per_file",20));
		if ($maxItems<1) { $maxItems=20; }
		
		$manualSelected=false;
		
		if ($project['categorymode']==1)
		{
			if ($offset==0)
			{
				// Reset table:
				//$dbw->query("DELETE FROM eurotext_project_products WHERE project_id=?;",array($project['id']));
			}				
			
			$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('catalog_category_entity')."`"));
			
			$rows=$dbr->fetchAll("SELECT entity_id FROM `".$this->getTableName('catalog_category_entity')."` ORDER BY entity_id ASC LIMIT $offset,$maxItems");
		}
		else
		{
			$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('catalog_category_entity')."` e, `".$this->getTableName('eurotext_project_categories')."` p WHERE (e.entity_id=p.category_id) AND project_id=".$project['id']));			
			
			$manualSelected=true;
			$rows=$dbr->fetchAll("SELECT e.entity_id FROM `".$this->getTableName('catalog_category_entity')."` e, `".$this->getTableName('eurotext_project_categories')."` p WHERE (e.entity_id=p.category_id) AND project_id=".$project['id']." ORDER BY e.entity_id ASC LIMIT $offset,$maxItems");
		}
		
		$itemsFound=false;
		
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;
		
		$cats = $doc->createElement("categories");
		$doc->appendChild($cats);
		
		foreach($rows as $row)
		{			
			$itemsFound=true;
			
			$cat_id=intval($row['entity_id']);				
			$rv["status_msg"]="(".($offset+1)." / ".$cnt.") ".$this->__("Checking category")." ID=".$cat_id;
		
			// Compare languages:
			$catSrc = Mage::getModel('catalog/category')->setStoreId($project['storeview_src'])->load($cat_id);
			$catDst = Mage::getModel('catalog/category')->setStoreId($project['storeview_dst'])->load($cat_id);
			
			$hasChangedProperty=false;	
			
			$cat = $doc->createElement("category");			
			
			$nodeId = $doc->createElement("Id");
			$nodeId->appendChild($doc->createTextNode($cat_id));
			$cat->appendChild($nodeId);
			
			$nodeUrl = $doc->createElement("Url");
			$this->appendTextChild($nodeUrl,$catSrc->getUrl());
			$cat->appendChild($nodeUrl);

			// Name:
			if ($catSrc->getName()!="")
			{
				if ( ($catSrc->getName()==$catDst->getName()) || ($catDst->getName()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Title");
					$this->appendTextChild($item,$catSrc->getName());
					$cat->appendChild($item);
				}
			}
			
			// Description:
			if ($catSrc->getDescription()!="")
			{
				if ( ($catSrc->getDescription()==$catDst->getDescription()) || ($catDst->getDescription()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Longdesc");
					$this->appendTextChild($item,$catSrc->getDescription());
					$cat->appendChild($item);
				}
			}
			
			// Short-Description:
			if ($catSrc->getShortDescription()!="")
			{
				if ( ($catSrc->getShortDescription()==$catDst->getShortDescription()) || ($catDst->getShortDescription()=="") || ($manualSelected))
				{
					$hasChangedProperty=true;
					
					$item=$doc->createElement("Shortdesc");
					$this->appendTextChild($item,$catSrc->getShortDescription());
					$cat->appendChild($item);
				}
			}
			
            // Custom Attributes through config xml
            if($custom_attributes = Mage::helper('eurotext_translationmanager')->getCustomCategoryAttributesForExport()){
                $hasChangedProperty = true;
                $nodeCustomAttributes = $doc->createElement('custom_attributes');
                // Custom Attribute Mapping is not yet used nor implemented
                foreach($custom_attributes as $custom_attribute => $custom_attribute_mapping){
                    $item = $doc->createElement((string) $custom_attribute);
                    $this->appendTextChild($item, (string) $catSrc->getData($custom_attribute));
                    $nodeCustomAttributes->appendChild($item);
                }
                $cat->appendChild($nodeCustomAttributes);
            }

			if ($project['export_urlkeys']=="1")
			{
				// URL-Key:
				if ($catSrc->getUrlKey()!="")
				{
					if ( ($catSrc->getUrlKey()==$catDst->getUrlKey()) || ($catDst->getUrlKey()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("UrlKey");
						$this->appendTextChild($item,$catSrc->getUrlKey());
						$cat->appendChild($item);
					}
				}
			}
			
			if ($project['export_seo']=="1")
			{
				// Meta-Title:
				if ($catSrc->getMetaTitle()!="")
				{
					if ( ($catSrc->getMetaTitle()==$catDst->getMetaTitle()) || ($catDst->getMetaTitle()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoTitle");
						$this->appendTextChild($item,$catSrc->getMetaTitle());
						$cat->appendChild($item);
					}
				}
				
				// Meta-Description:
				if ($catSrc->getMetaDescription()!="")
				{
					if ( ($catSrc->getMetaDescription()==$catDst->getMetaDescription()) || ($catDst->getMetaDescription()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoDescription");
						$this->appendTextChild($item,$catSrc->getMetaDescription());
						$cat->appendChild($item);
					}
				}
				
				// Meta-Keywords:
				if ($catSrc->getMetaKeywords()!="")
				{
					if ( ($catSrc->getMetaKeywords()==$catDst->getMetaKeywords()) || ($catDst->getMetaKeywords()=="") || ($manualSelected))
					{
						$hasChangedProperty=true;
						
						$item=$doc->createElement("SeoKeywords");
						$this->appendTextChild($item,$catSrc->getMetaKeywords());
						$cat->appendChild($item);
					}
				}
			}
			
			if ($hasChangedProperty)
			{
				$cats->appendChild($cat);				
			}
			
			$rv["offset"]=($offset+$maxItems);
		}
		
		if ($itemsFound)
		{
			$xmlDir=$this->getExportXMLPath($project);
			$xmlDir.=DS."categories";
			if (!is_dir($xmlDir))
			{
				mkdir($xmlDir);
			}
			
			$fileNum=($offset/$maxItems)+1;
			$fileNum=intval($fileNum);
			
			$xmlFilename=$xmlDir.DS."cat".$fileNum.".xml";
			
			$doc->save($xmlFilename);
		}
		else
		{
			// no further cat found, go to next step:
			$rv['step']=$this->STEP_COLLECT_CMSPAGES;
			$rv["offset"]=0;
		}
		
		return $rv;		
	}
	
	public function deleteDirectoryRecursive($dir)
	{
		foreach(glob($dir . DS.'*') as $file)
		{
			if(is_dir($file))
			{
				$this->deleteDirectoryRecursive($file);
			}
			else
			{
				unlink($file);
			}
		}
		rmdir($dir);
	}
	
	public function ajaxexportAction_ExportAttributes($project, $offset)
	{
		$rv=array();
		$rv["status_msg"]=$this->__("Checking attributes")."...";
		$rv["offset"]=$offset;
		$rv["step"]=$this->STEP_EXPORT_ATTRIBUTES;
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		if ($project['export_attributes']==1)
		{		
			$foundItems=0;
			
			$doc = new DOMDocument('1.0', 'UTF-8');
			$doc->formatOutput = true;
			
			$defComment=$doc->createComment("allow utf8-detection: öäü€");
			$doc->appendChild($defComment);
			
			$nodeAttributes = $doc->createElement("attributes");
			$doc->appendChild($nodeAttributes);			
			
			// eav_attribute_label (min 1.4)
		
			$attributesResult=$dbr->fetchAll("SELECT a.attribute_id, a.frontend_label, attribute_code, entity_type_id FROM `".$this->getTableName('eav_attribute')."` a WHERE is_user_defined=1");
			foreach($attributesResult as $attributeResult)
			{
				$foundItems++;
				
				$src_val=$attributeResult['frontend_label'];
				$attribute_id=$attributeResult['attribute_id'];
				
				$attribute_comment="(has no label)";
				
				// Default Text available?
				$attributeDefaultTextResults=$dbr->fetchAll("SELECT `value` FROM `".$this->getTableName('eav_attribute_label')."` WHERE attribute_id=".$attribute_id." AND store_id=0");
				foreach($attributeDefaultTextResults as $attributeDefaultTextResult)
				{
					$src_val=$attributeDefaultTextResult['value'];
					$attribute_comment="(default label)";
				}		

				// Source Storeview-Text available?
				$attributeDefaultTextResults=$dbr->fetchAll("SELECT `value` FROM `".$this->getTableName('eav_attribute_label')."` WHERE attribute_id=".$attribute_id." AND store_id=".intval($project['storeview_src']));
				foreach($attributeDefaultTextResults as $attributeDefaultTextResult)
				{
					$src_val=$attributeDefaultTextResult['value'];
					$attribute_comment="(src-storeview label)";
				}							
				
				$nodeAttribute = $doc->createElement("attribute");
				$nodeAttributes->appendChild($nodeAttribute);	

				$attrId=$doc->createAttribute("id");
				$attrId->value=$attribute_id;
				$nodeAttribute->appendChild($attrId);
				
				$attribute_comment.=" attribute_code='".$attributeResult['attribute_code']."', entity_type_id: ".$attributeResult['entity_type_id'];
				$nodeAttributeComment=$doc->createComment($attribute_comment);
				$nodeAttribute->appendChild($nodeAttributeComment);
				
				$nodeName = $doc->createElement("AttributeName");
				$this->appendTextChild($nodeName,$src_val);
				
				$attrTranslate=$doc->createAttribute("translate");
				$attrTranslate->value="1";
				$nodeName->appendChild($attrTranslate);
				
				$nodeAttribute->appendChild($nodeName);
				
				// Options:
				$attributeOptionsResult=$dbr->fetchAll("SELECT option_id FROM ".$this->getTableName('eav_attribute_option')." WHERE attribute_id=".$attribute_id." ORDER BY sort_order ASC, option_id ASC");
				if (count($attributeOptionsResult)>0)
				{
					$nodeOptions = $doc->createElement("options");
					$nodeAttribute->appendChild($nodeOptions);
				
					foreach($attributeOptionsResult as $attributeOptionResult)
					{
						$nodeOption = $doc->createElement("option");
						$nodeOptions->appendChild($nodeOption);	

						$attrId=$doc->createAttribute("id");
						$attrId->value=$attributeOptionResult['option_id'];
						$nodeOption->appendChild($attrId);
						
						$option_comment="(has no label)";
						
						// Default Text available?
						$attributeOptionDefaultTextResults=$dbr->fetchAll("SELECT `value` FROM `".$this->getTableName('eav_attribute_option_value')."` WHERE option_id=".$attributeOptionResult['option_id']." AND store_id=0");
						foreach($attributeOptionDefaultTextResults as $attributeOptionDefaultTextResult)
						{
							$src_val=$attributeOptionDefaultTextResult['value'];
							$option_comment="(has default label)";
						}		

						// Source Storeview-Text available?
						$attributeOptionDefaultTextResults=$dbr->fetchAll("SELECT `value` FROM `".$this->getTableName('eav_attribute_option_value')."` WHERE option_id=".$attributeOptionResult['option_id']." AND store_id=".intval($project['storeview_src']));
						foreach($attributeOptionDefaultTextResults as $attributeOptionDefaultTextResult)
						{
							$src_val=$attributeOptionDefaultTextResult['value'];
							$option_comment="(has src-storeview label)";
						}
						
						$nodeAttributeOptionComment=$doc->createComment($option_comment);
						$nodeOption->appendChild($nodeAttributeOptionComment);
						
						$nodeName = $doc->createElement("OptionName");
						$this->appendTextChild($nodeName,$src_val);
						
						$attrTranslate=$doc->createAttribute("translate");
						$attrTranslate->value="1";
						$nodeName->appendChild($attrTranslate);
						
						$nodeOption->appendChild($nodeName);
					}
				}
			}
			
			if ($foundItems>0)
			{
				$xmlDir=$this->getExportXMLPath($project);
				$xmlDir.=DS."attributes";
				if (!is_dir($xmlDir))
				{
					mkdir($xmlDir);
				}
				
				$xml_filename=$xmlDir.DS."attributes.xml";
				
				$doc->save($xml_filename);
			}
		}
		
		$rv["step"]=$this->STEP_GENERATE_CONTROL_FILE;
		$rv["offset"]=0;

		return $rv;		
	}
	
	private $STEP_START=0;
	private $STEP_COLLECT_LANGFILES=1;
	private $STEP_IMPORT_LANGFILES=2;
	private $STEP_BUILD_LANGXML=3;
	private $STEP_COLLECT_PRODUCTS=4;
	private $STEP_COLLECT_CATEGORIES=5;
	private $STEP_COLLECT_CMSPAGES=6;
	private $STEP_COLLECT_CMSBLOCKS=7;
	private $STEP_EXPORT_ATTRIBUTES=8;
	private $STEP_GENERATE_CONTROL_FILE=9;
	private $STEP_COMPRESS_FILES=10;
	private $STEP_TRANSMIT_ARCHIVE=11;
	private $STEP_DONE=12;
	private $STEP_COLLECT_TEMPLATES=13;
	
	public function ajaxexportAction()
	{	
        $this->loadLayout('adminhtml_eurotext_translationmanager_ajax');
        $block = $this->getLayout()->getBlock('et.tm.response.ajax');

		$request=$this->getRequest();
		$step=intval($request->getParam("step"));
		$offset=intval($request->getParam("offset"));
		$project_id=intval($request->getParam("project_id"));
		
		$project=$this->getProject($project_id);
		
		// Defaults:
		$block->setStatus_code("ok");
		$block->setStatusMsg($this->__("Please wait")."...");
		$block->setOffset($offset);
		$block->setStep($step);
		$block->setFinished(0);
		
		// steps:
		// -------------------------------------------------------------------------------------
		// 0: Jump to step 4, if language files is not selected for export
		// 1: Find language *.csv files and write found filenames to eurotext_csv
		// 2: For each offset import one *.csv file to eurotext_csv_data
		// 3: For each offset find missing translations for one *.csv and generate xml files
		// -------------------------------------------------------------------------------------
		// 4: Jump to step 5, if product files were selected manually
		//    For each offset: Find missing translations for one product
		// 5: Jump to step 6, if category files where selected manually
		//    For each offset: Find missing translations for one category
		
		if ($step==$this->STEP_START)
		{
			// Clear project folder:
			$xmlPath=$this->getExportXMLPath($project);
			$this->deleteDirectoryRecursive($xmlPath);
			
			$helper=$this->getHelper();
			$testResult=$helper->testFtpConnection();
		
			if ($testResult['ok']!=true)
			{
				$block->setStatus_code("error");
				$block->setStatusMsg($testResult['statusmessage']);
			}
			else
			{		
				if ($project['langfilesmode']==1)
				{				
					// Search for missing translations:
					$block->setStep($this->STEP_COLLECT_LANGFILES);
				}
				else
				{
					$dbres = Mage::getSingleton('core/resource');
					$dbr=$dbres->getConnection('core_read');
			
					$cnt=intval($dbr->fetchOne("SELECT COUNT(*) cnt FROM `".$this->getTableName('eurotext_csv')."` WHERE project_id=".$project['id']." AND translate_flag=1 AND locale_dst='en_US'"));
					if ($cnt>0)
					{
						// Customer has selected translation files:
						$block->setStep($this->STEP_COLLECT_LANGFILES);
					}
					else
					{
						$block->setStep($this->STEP_COLLECT_PRODUCTS);
					}
				}
			}
		}
		elseif ($step==$this->STEP_COLLECT_LANGFILES)
		{
			$this->ajaxexportAction_CollectLangfiles($project);
			$block->setStep($this->STEP_IMPORT_LANGFILES);
			$block->setOffset(0);
		}
		elseif ($step==$this->STEP_IMPORT_LANGFILES)
		{			
			$rvSub=$this->ajaxexportAction_ImportLangfiles($project,$offset);
			if ($rvSub['offset']>=0)
			{
				$block->setStep($this->STEP_IMPORT_LANGFILES);
				$block->setStatusMsg($rvSub['status_msg']);
				$block->setOffset($rvSub['offset']);
			}
			else
			{
				$block->setStep($this->STEP_BUILD_LANGXML);
				$block->setOffset(0);
				$block->setStatusMsg($this->__("Generating Language files")."...");
			}
		}
		elseif ($step==$this->STEP_BUILD_LANGXML)
		{
			$rvSub=$this->ajaxexportAction_BuildLangXML($project,$offset);
			$block->setStep($rvSub['step']);
			$block->setOffset($rvSub['offset']);
			$block->setStatusMsg($rvSub['status_msg']);
		}
		elseif ($step==$this->STEP_COLLECT_PRODUCTS)
		{
			$rvSub=$this->ajaxexportAction_CollectProducts($project,$offset);
			$block->setStep($rvSub['step']);
			$block->setOffset($rvSub['offset']);
			$block->setStatusMsg($rvSub['status_msg']);
		}
		elseif ($step==$this->STEP_COLLECT_CATEGORIES)
		{
			$rvSub=$this->ajaxexportAction_CollectCategories($project,$offset);
			$block->setStep($rvSub['step']);
			$block->setOffset($rvSub['offset']);
			$block->setStatusMsg($rvSub['status_msg']);
		}
		elseif ($step==$this->STEP_COLLECT_CMSPAGES)
		{
			$rvSub=$this->ajaxexportAction_CollectCMSPages($project,$offset);
			$block->setStep($rvSub['step']);
			$block->setOffset($rvSub['offset']);
			$block->setStatusMsg($rvSub['status_msg']);
		}
		elseif ($step==$this->STEP_COLLECT_CMSBLOCKS)
		{
			$rvSub=$this->ajaxexportAction_CollectCMSBlocks($project,$offset);
			$block->setStep($rvSub['step']);
			$block->setOffset($rvSub['offset']);
			$block->setStatusMsg($rvSub['status_msg']);
		}
		elseif ($step==$this->STEP_COLLECT_TEMPLATES)
		{
			$rvSub=$this->ajaxexportAction_CollectTemplates($project,$offset);
			$block->setStep($rvSub['step']);
			$block->setOffset($rvSub['offset']);
			$block->setStatusMsg($rvSub['status_msg']);
		}
		elseif ($step==$this->STEP_EXPORT_ATTRIBUTES)
		{
			$rvSub=$this->ajaxexportAction_ExportAttributes($project,$offset);
			$block->setStep($rvSub['step']);
			$block->setOffset($rvSub['offset']);
			$block->setStatusMsg($rvSub['status_msg']);			
		}
		elseif ($step==$this->STEP_GENERATE_CONTROL_FILE)
		{
			$this->ajaxexportAction_GenerateControlFile($project);
			$block->setStep($this->STEP_COMPRESS_FILES);
			$block->setStatusMsg($this->__("Generating ZIP-Archive"));
		}
		elseif ($step==$this->STEP_COMPRESS_FILES)
		{
			$this->ajaxexportAction_CompressFiles($project);

			$block->setStep($this->STEP_TRANSMIT_ARCHIVE);
			$block->setStatusMsg($this->__("Sending data"));
		}
		elseif ($step==$this->STEP_TRANSMIT_ARCHIVE)
		{
			$this->ajaxexportAction_TransmitArchive($project);
			$block->setStep($this->STEP_DONE);
			$block->setStatusMsg($this->__("Done"));
		}
		else
		{
			$block->setStep($this->STEP_DONE);
			$block->setStatusMsg($this->__("Done"));
			$block->setFinished("1");
		}
		
        $this->renderLayout();
	}
	
	public function importextractAction()
	{
        $zipFound = false;
		$tmpdir=$this->getTempDir();
		$files=$this->getEurotextHelper()->getDirectoryContent($tmpdir, false, true, false, false);
		foreach($files as $file)
		{
			$filename=$file['full_path'];
			if ($this->getEurotextHelper()->endsWith(strtoupper($filename),".ZIP"))
			{
				// Extract file:
				if(!$this->getEurotextHelper()->extractZip($filename,$tmpdir)){
                    $this->getHelper()->log('ZIP File could not be extracted', Zend_Log::CRIT);
                }
                $zipFound = true;
			}
		}

        $this->getHelper()->log('ZIP File could '.($zipFound ? '' : 'not').' be found.');
		$import_url=Mage::helper('adminhtml')->getUrl('*/*/importparse');
		$this->_redirectUrl($import_url);
	}
	
	public function importparseAction()
	{
		$tmpdir=$this->getTempDir();
		$controlFile=$tmpdir.DIRECTORY_SEPARATOR."control.xml";
		
		if (!file_exists($controlFile))
		{
            $this->getHelper()->log('ZIP did not countain a control.xml file');
			Mage::throwException($this->__("Fehler: Die ZIP-Datei enthaelt keine control.xml"));
		}
		
		$project_id=-1;
	
		$doc = new DOMDocument();
		$doc->load($controlFile);
		$nodes=$doc->getElementsByTagName("Description");
		foreach($nodes as $node)
		{
			$description=$node->textContent;
			$pos1=stripos($description,"Magento-Project-Id: ");
			if ($pos1>=0)
			{
				$pos1+=strlen("Magento-Project-Id: ");
			
				$pos2=stripos($description,",",$pos1);
				if ($pos2>$pos1)
				{
					$project_id_str=trim(substr($description,$pos1,($pos2-$pos1)));
					$project_id=intval($project_id_str);
				}
			}
		}

		if ($project_id<=0)
		{
			Mage::throwException($this->__("Could not read project-id from Description-Field in control.xml"));
		}

        $this->getHelper()->log('ZIP contains Data for Project ID '.$project_id);

		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		
		// Remove old entries:
		$dbw->query("DELETE FROM `".$this->getTableName('eurotext_import')."` WHERE project_id=".$project_id);
		
		$project=$this->getProject($project_id);
		$storeview_dst=$project['storeview_dst'];
		
		$filenames=array();
		
		$uploadedFiles=$doc->getElementsByTagName("uploadedFile");
		foreach($uploadedFiles as $uploadedFile)
		{
			$filename="";
			foreach ($uploadedFile->attributes as $attrName => $attrNode) 
			{				
				if ($attrName=="fileName")
				{
					$filename=$attrNode->textContent;
				}
			}
			if ($filename!="")
			{
				$filenames[]=$filename;
			}			
		}
		
		sort($filenames);
		
		$num=0;
		foreach($filenames as $filename)
		{
			$num++;
			$dbw->query("INSERT INTO `".$this->getTableName('eurotext_import')."` (project_id,filename,storeview_dst,is_imported,num) VALUES (?,?,?,?,?);",array($project_id,$filename,$storeview_dst,'0',$num));
		}
		
		$this->updateProjectState($project_id,2);	// 2 = Processing (2)
		
		$import_url=Mage::helper('adminhtml')->getUrl('*/*/index',array('id'=>$project_id));
		$this->_redirectUrl($import_url);
	}
	
	public function importstepAction()
	{
        $this->loadLayout('adminhtml_eurotext_translationmanager_ajax');
        $block = $this->getLayout()->getBlock('et.tm.response.ajax');

		$request=$this->getRequest();
		$offset=intval($request->getParam("offset"));
		$project_id=intval($request->getParam("project_id"));
		$project=$this->getProject($project_id);
			
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$finished=0;
		$status_msg="";
		
		$tmpdir=$this->getTempDir();
		
		$itemCount=intval($dbr->fetchOne("SELECT COUNT(*) FROM `".$this->getTableName('eurotext_import')."` WHERE project_id=?",array($project_id)));
		
		$nextItems=$dbr->fetchAll("SELECT * FROM `".$this->getTableName('eurotext_import')."` WHERE project_id=? ORDER BY num ASC, filename ASC LIMIT $offset,1",array($project_id));
		if (count($nextItems)>0)
		{
			$nextItem=$nextItems[0];
			$filename=$nextItem['filename'];
			
			$full_filename=$tmpdir.DIRECTORY_SEPARATOR.$filename;
			
			if (stripos($filename,"cms-sites".DS."cms-")===0)
			{
				$this->importstepAction_ImportCMS($nextItem,$full_filename,$project,$dbr);
			}
			else if (stripos($filename,"cms-sites".DS."cmsblock-")===0)
			{
				$this->importstepAction_ImportBlocks($nextItem,$full_filename,$project,$dbr);
			}
			elseif (stripos($filename,"articles".DS)===0)
			{
				$this->importstepAction_ImportArticle($nextItem,$full_filename,$project);
			}
			elseif (stripos($filename,"categories".DS)===0)
			{
				$this->importstepAction_ImportCategory($nextItem,$full_filename,$project);
			}
			elseif(stripos($filename,"framework".DS)===0)
			{
				$this->importstepAction_ImportTranslation($nextItem,$full_filename,$project,$dbr);
			}
			elseif(stripos($filename,"attributes".DS)===0)
			{
				$this->importstepAction_ImportAttributes($nextItem,$full_filename,$project,$dbr);
			}
			elseif(stripos($filename,"emailtemplates".DS)===0)
			{
				$this->importstepAction_ImportTemplates($nextItem,$full_filename,$project,$dbr);
			}
			
			$status_msg="[".($offset+1)." / ".$itemCount."] ".$this->__("Processing File")." '".$filename."'";
		}
		else
		{
			$this->updateProjectState($project_id,3);	// 3 = Imported			
		
			$finished=1;
			$status_msg=$this->__("Done");
		}
		
		$block->setStatusMsg($status_msg);
		$block->setStatus_code("ok");
		$block->setFinished($finished);
		$block->setOffset(($offset+1));
		
        $this->renderLayout();

	}
	
	private function updateProjectState($project_id, $newStatusId)
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
			
		// Update state:
		$dbw->query("UPDATE `".$this->getTableName('eurotext_project')."` SET project_status=? WHERE id=?",array($newStatusId,$project_id));
	}
	
	private function getXMLChildNode($element,$childnodeName)
	{
		$childNodes=$element->childNodes;
		foreach($childNodes as $childNode)
		{
			if ($childNode->nodeType==XML_ELEMENT_NODE)
			{
				if ($childNode->tagName==$childnodeName)
				{
					return $childNode;
				}
			}
		}
		
		return null;
	}
	
	private function getXMLChildNodeText($element,$childnodeName,$defaultValue="")
	{
		$childNode=$this->getXMLChildNode($element,$childnodeName);
		if ($childNode==null)
		{
			return $defaultValue;
		}
		
		return $childNode->textContent;	
	}
	
	public function importstepAction_ImportArticle($item,$full_filename,$project)
	{
        $this->getHelper()->log('=== Importing Products ===');

		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection("core_read");
		
		$eavAttribute = Mage::getModel('eav/entity_attribute');
		$urlkey_attribute_id = $eavAttribute->getIdByCode('catalog_product', 'url_key');
		
		$dst_store_id=$project['storeview_dst'];
								
		$doc = new DOMDocument();
		$doc->load($full_filename);
		$articles=$doc->getElementsByTagName("article");
		foreach($articles as $article)
		{
			$fieldNodes=$article->childNodes;
			
			$Id=0;
			$fields=array();
			
			foreach($fieldNodes as $fieldNode)
			{
				$nodeName=trim($fieldNode->nodeName);
				$nodeContent=trim($fieldNode->textContent);
				
				if ($nodeName!="")
				{				
					if ($nodeName=="Id")
					{
						$Id=intval($nodeContent);
					}
                    else if('custom_attributes' == $nodeName){
                        $fields[$nodeName]=$fieldNode;
                    }
					else
					{
						$fields[$nodeName]=$nodeContent;
					}
				}
			}
			
			if ($Id>0)
			{
                /** @var Mage_Core_Model_Product $productDst */
				$productDst = Mage::getModel('catalog/product')->load($Id)->setStoreId($project['storeview_dst']);
                $this->getHelper()->log('Saving Product (ID '.$Id.') for StoreID: '.$project['storeview_dst']);

				// Ignore fields:
				$ignoreFields=array("#text","Url");
				
				$hasUrlKey=false;
				
				foreach ($fields as $key => $value) 
				{
					if ($key=="Title")
					{
						$productDst->setName($value);
					}
					else if ($key=="Longdesc")
					{
						$productDst->setDescription($value);
					}
                    else if ($key == "UrlKey") {
                        $urlProductCheck=Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('url_key', array('eq', $value));
                        if ($urlProductCheck->count() > 0) {
                            Mage::log($this->getHelper()->__('Changed duplicate URL Key'), Zend_Log::INFO, 'eurotext_urlkey_changes.log', true);
                            Mage::log($this->getHelper()->__('   from "%s"', $value), Zend_Log::INFO, 'eurotext_urlkey_changes.log', true);

                            $value = $this->getHelper()->getUniqueUrl($value);

                            Mage::log($this->getHelper()->__('   to "%s"', $value), Zend_Log::INFO, 'eurotext_urlkey_changes.log', true);

                        }
                        $productDst->setUrlKey($value);
                        $hasUrlKey = true;
                    }
					else if ($key=="Shortdesc")
					{
						$productDst->setShortDescription($value);
					}
					else if ($key=="SeoTitle")
					{
						$productDst->setMetaTitle($value);
					}
					else if ($key=="SeoDescription")
					{
						$productDst->setMetaDescription($value);
					}
					else if ($key=="SeoKeywords")
					{
						$productDst->setMetaKeyword($value);
					}
					else if ($key=="Images")
					{
						$imageNodes=$article->getElementsByTagName("Image");
						foreach($imageNodes as $imageNode)
						{
							$img_value_id=intval($imageNode->getAttributeNode("value_id")->value);
							$img_position=intval($imageNode->getAttributeNode("position")->value);
							$img_disabled=intval($imageNode->getAttributeNode("disabled")->value);
							
							$labelNodes=$imageNode->getElementsByTagName("Label");
							foreach($labelNodes as $labelNode)
							{
								$img_label=trim($labelNode->textContent);
											
								try
								{
									$dbw->query("INSERT IGNORE INTO `".$this->getTableName('catalog_product_entity_media_gallery_value')."` (value_id,store_id,label,position,disabled) VALUES (?,?,?,?,?);",array($img_value_id,$dst_store_id,$img_label,$img_position,$img_disabled));
									$dbw->query("UPDATE `".$this->getTableName('catalog_product_entity_media_gallery_value')."` SET label=? WHERE value_id=? AND store_id=?;",array($img_label,$img_value_id,$dst_store_id));
								}
								catch(Exception $e)
								{
									// Exception might occur, if the image was deleted between export and import
									// due to foreign-key checks
								}
							}
						}
					}
					else if ($key=="Options")
					{
						$optionNodes=$article->getElementsByTagName("Option");
						foreach($optionNodes as $optionNode)
						{
							// Id:
							$option_id=intval($optionNode->getAttributeNode("Id")->value);
							
							// Title:
							$option_title=trim($this->getXMLChildNodeText($optionNode,"Title",""));
							if (strlen($option_title)>0)
							{
								// Update Title:
								$dbw->query("INSERT IGNORE INTO `".$this->getTableName('catalog_product_option_title')."` (option_id,store_id,title) VALUES (?,?,?);",array($option_id,$dst_store_id,$option_title));
								$dbw->query("UPDATE `".$this->getTableName('catalog_product_option_title')."` SET title=? WHERE option_id=? AND store_id=?;",array($option_title,$option_id,$dst_store_id));
							}
							
							$OptionValueNodes=$article->getElementsByTagName("Value");
							foreach($OptionValueNodes as $OptionValueNode)
							{
								$option_value_id=intval($OptionValueNode->getAttributeNode("Id")->value);								
								$option_value_title=trim($this->getXMLChildNodeText($OptionValueNode,"Title",""));
								
								if (strlen($option_value_title)>0)
								{
									// Update Title:
									$dbw->query("INSERT IGNORE INTO `".$this->getTableName('catalog_product_option_type_title')."` (option_type_id,store_id,title) VALUES (?,?,?);",array($option_value_id,$dst_store_id,$option_value_title));
									$dbw->query("UPDATE `".$this->getTableName('catalog_product_option_type_title')."` SET title=? WHERE option_type_id=? AND store_id=?;",array($option_value_title,$option_value_id,$dst_store_id));
								}
							}
						}
						
						//echo "Options translated";
						//die();
					}
					else
					{
                        if('custom_attributes' == $key){
                            $custom_attributes = Mage::helper('eurotext_translationmanager')->getCustomProductAttributesForExport();

                            // value doesn't contain a text value as usual
                            // only for custom_attributes key it's the node object
                            $customAttributesNodes=$value->childNodes;

                            foreach ($customAttributesNodes as $customAttributesNode) {
                                $custom_attribute_key   = trim($customAttributesNode->nodeName);
                                $custom_attribute_value = trim($customAttributesNode->textContent);
                                if (array_key_exists($custom_attribute_key, $custom_attributes)) {
                                    $productDst->setDataUsingMethod($custom_attribute_key, $custom_attribute_value);
                                }
                            }
                        }
						else if (!in_array($key,$ignoreFields))
						{
                            $this->getHelper()->log('Unknown Field: '.$key, Zend_Log::EMERG);
							throw new Mage_Exception('Unknown Field: '.$key);
						}
					}
				}
				
				if (!$hasUrlKey)
                {
                    $productDst->setUrlKey(false);
				}
                $productDst->save();
                $this->getHelper()->log('== Product has been saved ==');
            }
			else
			{
                $this->getHelper()->log('Wrong Product (ID: '.$Id.')');
				Mage::throwException($this->__("Wrong Product ID '%s'", $Id));
			}
		}
	}
	
	public function importstepAction_ImportCategory($item,$full_filename,$project)
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection("core_read");
		
		$eavAttribute = Mage::getModel('eav/entity_attribute');
		$urlkey_attribute_id = $eavAttribute->getIdByCode('catalog_category', 'url_key');		
	
		$doc = new DOMDocument();
		$doc->load($full_filename);
		$categories=$doc->getElementsByTagName("category");
		foreach($categories as $category)
		{
			$fieldNodes=$category->childNodes;
			
			$Id=0;
			$fields=array();
			
			foreach($fieldNodes as $fieldNode)
			{
				$nodeName=trim($fieldNode->nodeName);
				$nodeContent=trim($fieldNode->textContent);
				
				if ($nodeName!="")
				{				
					if ($nodeName=="Id")
					{
						$Id=intval($nodeContent);
					}
					else
					{
						$fields[$nodeName]=$nodeContent;
					}
				}
			}
			
			if ($Id>0)
			{
				$catDst = Mage::getModel('catalog/category')->load($Id)->setStoreId($project['storeview_dst']);
				
				$hasUrlKey=false;

				foreach ($fields as $key => $value) 
				{
					if ($key=="Title")
					{
						$catDst->setName($value);
					}
					else if ($key=="Longdesc")
					{
						$catDst->setDescription($value);
					}
					else if ($key=="Shortdesc")
					{
						$catDst->setShortDescription($value);
					}
					else if ($key=="SeoTitle")
					{
						$catDst->setMetaTitle($value);
					}
					else if ($key=="SeoDescription")
					{
						$catDst->setMetaDescription($value);
					}
					else if ($key=="SeoKeywords")
					{
						$catDst->setMetaKeywords($value);
					}
					else if ($key=="UrlKey")
					{
						$catDst->setUrlKey($value);
						$hasUrlKey=true;
					}
					if('custom_attributes' == $key){
						$custom_attributes = Mage::helper('eurotext_translationmanager')->getCustomCategoryAttributesForExport();

						// value doesn't contain a text value as usual
						// only for custom_attributes key it's the node object
						$customAttributesNodes=$value->childNodes;

						foreach ($customAttributesNodes as $customAttributesNode) {
							$custom_attribute_key   = trim($customAttributesNode->nodeName);
							$custom_attribute_value = trim($customAttributesNode->textContent);
							if (array_key_exists($custom_attribute_key, $custom_attributes)) {
								$catDst->setDataUsingMethod($custom_attribute_key, $custom_attribute_value);
							}
						}
					}
				}
				
				if (!$hasUrlKey)
				{
					// Check if urlkey is already set:
					$res=$dbr->fetchAll("SELECT value FROM ".$this->getTableName('catalog_category_entity_varchar')." WHERE entity_id=".$Id." AND store_id=".$project['storeview_dst']." AND attribute_id=".$urlkey_attribute_id);
					if (count($res)==0)	// Currently using default storeview value
					{
						// setting null will force magento to generate the urlkey using the product-name
						$catDst->setUrlKey(null);
					}
				}
				
				$catDst->save();
			}
			else
			{
				Mage::throwException($this->__("Wrong Category ID '%s'", $Id));
			}
		}
	}
	
	public function importstepAction_ImportCMS($item,$full_filename,$project,$dbr)
	{
		$doc = new DOMDocument();
		$doc->load($full_filename);
		$cmsSites=$doc->getElementsByTagName("cms-site");
		foreach($cmsSites as $cmsSite)
		{
			$fieldNodes=$cmsSite->childNodes;
			
			$Id=0;
			$fields=array();
			$StoreviewSrc=-1;
			$StoreviewDst=-1;
			
			foreach($fieldNodes as $fieldNode)
			{
				$nodeName=trim($fieldNode->nodeName);
				$nodeContent=trim($fieldNode->textContent);
				
				if ($nodeName!="")
				{				
					if ($nodeName=="Id")
					{
						$Id=intval($nodeContent);
					}
					elseif ($nodeName=="StoreviewSrc")
					{
						$StoreviewSrc=intval($nodeContent);
					}
					elseif ($nodeName=="StoreviewDst")
					{
						$StoreviewDst=intval($nodeContent);
					}
					else
					{
						$fields[$nodeName]=$nodeContent;
					}
				}
			}
			
			if ($Id>0)
			{
				$pageDst = null;				
				$pageSrc=Mage::getModel('cms/page')->load($Id);
												
				// Remove dst-storeview from source cms-page:
				$srcStoreviewIds=$pageSrc->getStoreId();
				if (in_array(0,$srcStoreviewIds))
				{
					// Resolve store_id=0 ("all storeviews"):
					$srcStore=Mage::getModel("core/store")->load($StoreviewSrc);
					$srcWebsite=$srcStore->getWebsite();
					$srcWebsiteStoreIds=$srcWebsite->getStoreIds();
					foreach($srcWebsiteStoreIds as $srcWebsiteStoreId)
					{
						$srcStoreviewIds[]=$srcWebsiteStoreId;
					}
				}
				$srcStoreviewIds=array_unique($srcStoreviewIds);
				// Remove destination storeview:				
				$srcStoreviewIds=array_diff($srcStoreviewIds,array(0,$StoreviewDst));
				
				$pageSrc->setStoreId($srcStoreviewIds);
				$pageSrc->save();	

				$identifier=$pageSrc->getIdentifier();
				// Find matching page:
				$matchingPages=$dbr->fetchAll("SELECT p.page_id FROM `".$this->getTableName('cms_page')."` p, `".$this->getTableName('cms_page_store')."` s WHERE (p.page_id=s.page_id) AND UPPER(p.identifier)=? AND s.store_id>0 AND s.store_id=? ORDER BY p.page_id ASC",array($identifier,$project['storeview_dst']));
				if (count($matchingPages)>0)
				{
					$matchingPage=$matchingPages[0];
					$page_id_dst=$matchingPage['page_id'];
					
					$pageDst = Mage::getModel('cms/page')->load($page_id_dst);
				}
				else
				{
					// Destination CMS-Page does not exist yet, and needs to be created (clone source cms page)
					$pageData = array(
						'title' => $pageSrc->getTitle(),
						'root_template' => $pageSrc->getRootTemplate(),
						'meta_keywords' => $pageSrc->getMetaKeywords(),
						'meta_description' => $pageSrc->getMetaDescription(),
						'identifier' => $pageSrc->getIdentifier(),
						'content_heading' => $pageSrc->getContentHeading(),
						'stores' => array($StoreviewDst),
						'content' => $pageSrc->getContent()
						);
					$pageDst=Mage::getModel('cms/page')->setData($pageData)->save();				
				}	
				
				$pageDst->setStoreId(array($StoreviewDst));
								
				foreach ($fields as $key => $value) 
				{
					if ($key=="Title")
					{
						$pageDst->setTitle($value);
					}
					elseif($key=="Content")
					{
						$pageDst->setContent($value);
					}
					elseif($key=="ContentHeading")
					{
						$pageDst->setContentHeading($value);
					}
					elseif($key=="SeoKeywords")
					{
						$pageDst->setMetaKeywords($value);
					}
					elseif($key=="SeoDescription")
					{
						$pageDst->setMetaDescription($value);
					}
				}
				
				$pageDst->save();
			}
		}
	}
	
	public function importstepAction_ImportBlocks($item,$full_filename,$project,$dbr)
	{
		$doc = new DOMDocument();
		$doc->load($full_filename);
		$cmsSites=$doc->getElementsByTagName("cms-site");
		foreach($cmsSites as $cmsSite)
		{
			$fieldNodes=$cmsSite->childNodes;
			
			$Id=0;
			$fields=array();
			$StoreviewSrc=-1;
			$StoreviewDst=-1;
			
			foreach($fieldNodes as $fieldNode)
			{
				$nodeName=trim($fieldNode->nodeName);
				$nodeContent=trim($fieldNode->textContent);
				
				if ($nodeName!="")
				{				
					if ($nodeName=="Id")
					{
						$Id=intval($nodeContent);
					}
					elseif ($nodeName=="StoreviewSrc")
					{
						$StoreviewSrc=intval($nodeContent);
					}
					elseif ($nodeName=="StoreviewDst")
					{
						$StoreviewDst=intval($nodeContent);
					}
					else
					{
						$fields[$nodeName]=$nodeContent;
					}
				}
			}
			
			if ($Id>0)
			{
				$blockDst = null;				
				$blockSrc=Mage::getModel('cms/block')->load($Id);
												
				// Remove dst-storeview from source cms-block:
				$srcStoreviewIds=$blockSrc->getStoreId();
				if (in_array(0,$srcStoreviewIds))
				{
					// Resolve store_id=0 ("all storeviews"):
					$srcStore=Mage::getModel("core/store")->load($StoreviewSrc);
					$srcWebsite=$srcStore->getWebsite();
					$srcWebsiteStoreIds=$srcWebsite->getStoreIds();
					foreach($srcWebsiteStoreIds as $srcWebsiteStoreId)
					{
						$srcStoreviewIds[]=$srcWebsiteStoreId;
					}
				}
				$srcStoreviewIds=array_unique($srcStoreviewIds);
				// Remove destination storeview:				
				$srcStoreviewIds=array_diff($srcStoreviewIds,array(0,$StoreviewDst));
				
				$blockSrc->setStoreId($srcStoreviewIds);
				$blockSrc->save();	

				$identifier=$blockSrc->getIdentifier();
				// Find matching block:
				$matchingPages=$dbr->fetchAll("SELECT p.block_id FROM `".$this->getTableName('cms_block')."` p, `".$this->getTableName('cms_block_store')."` s WHERE (p.block_id=s.block_id) AND UPPER(p.identifier)=? AND s.store_id>0 AND s.store_id=? ORDER BY p.block_id ASC",array($identifier,$project['storeview_dst']));
				if (count($matchingPages)>0)
				{
					$matchingPage=$matchingPages[0];
					$block_id_dst=$matchingPage['block_id'];
					
					$blockDst = Mage::getModel('cms/block')->load($block_id_dst);
				}
				else
				{
					// Destination CMS-Page does not exist yet, and needs to be created (clone source cms block)
					$blockData = array(
						'title' => $blockSrc->getTitle(),
						'root_template' => $blockSrc->getRootTemplate(),
						'meta_keywords' => $blockSrc->getMetaKeywords(),
						'meta_description' => $blockSrc->getMetaDescription(),
						'identifier' => $blockSrc->getIdentifier(),
						'content_heading' => $blockSrc->getContentHeading(),
						'stores' => array($StoreviewDst),
						'content' => $blockSrc->getContent()
						);
					$blockDst=Mage::getModel('cms/block')->setData($blockData)->save();				
				}	
				
				$blockDst->setStoreId(array($StoreviewDst));
								
				foreach ($fields as $key => $value) 
				{
					if ($key=="Title")
					{
						$blockDst->setTitle($value);
					}
					elseif($key=="Content")
					{
						$blockDst->setContent($value);
					}
					elseif($key=="ContentHeading")
					{
						$blockDst->setContentHeading($value);
					}
					elseif($key=="SeoKeywords")
					{
						$blockDst->setMetaKeywords($value);
					}
					elseif($key=="SeoDescription")
					{
						$blockDst->setMetaDescription($value);
					}
				}
				
				$blockDst->save();
			}
		}
	}
	
	public function importstepAction_ImportAttributes($item,$full_filename,$project,$dbr)
	{
		$doc = new DOMDocument();
		$doc->load($full_filename);
		$attributes=$doc->getElementsByTagName("attribute");
		
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');
		$dbr=$dbres->getConnection('core_read');
		
		foreach($attributes as $attribute)
		{
			$attribute_id=intval($attribute->getAttribute("id"));
			$attributeName="";

			$attributeNameNodes=$attribute->getElementsByTagName("AttributeName");
			foreach($attributeNameNodes as $attributeNameNode)
			{
				$attributeName=$attributeNameNode->textContent;
			}
						
			// Update attribute label:
			$qResult=$dbr->fetchAll("SELECT attribute_label_id FROM `".$this->getTableName('eav_attribute_label')."` WHERE attribute_id=".$attribute_id." AND store_id=".$project['storeview_dst']);
			if (count($qResult)>0)
			{
				// Update:
				$dbw->query("UPDATE `".$this->getTableName('eav_attribute_label')."` SET `value`=? WHERE attribute_id=".$attribute_id." AND store_id=".$project['storeview_dst'],array($attributeName));
			}
			else
			{
				// Insert:
				$dbw->query("INSERT INTO `".$this->getTableName('eav_attribute_label')."` (attribute_id,store_id,`value`) VALUES (?,?,?)",array($attribute_id,$project['storeview_dst'],$attributeName));
			}
			
			$options=$attribute->getElementsByTagName("option");
			foreach($options as $option)
			{
				$option_id=intval($option->getAttribute("id"));
				$optionName="";		

				$optionNameNodes=$option->getElementsByTagName("OptionName");
				foreach($optionNameNodes as $optionNameNode)
				{
					$optionName=$optionNameNode->textContent;
				}
				
				// Update attribute label:
				$qResult=$dbr->fetchAll("SELECT value_id FROM `".$this->getTableName('eav_attribute_option_value')."` WHERE option_id=".$option_id." AND store_id=".$project['storeview_dst']);
				if (count($qResult)>0)
				{
					// Update:
					$dbw->query("UPDATE `".$this->getTableName('eav_attribute_option_value')."` SET `value`=? WHERE option_id=".$option_id." AND store_id=".$project['storeview_dst'],array($optionName));
				}
				else
				{
					// Insert:
					$dbw->query("INSERT INTO `".$this->getTableName('eav_attribute_option_value')."` (option_id,store_id,`value`) VALUES (?,?,?)",array($option_id,$project['storeview_dst'],$optionName));
				}
			}
		}		
	}
	
	public function importstepAction_ImportTemplates($item,$full_filename,$project,$dbr)
	{
		$abc=array();
		$abc['item']=$item;
		$abc['full_filename']=$full_filename;
		
		$filename=$item['filename'];
		$filename=substr($filename,strlen("emailtemplates"));
		
		$baseLocaleFolder=Mage::getBaseDir('locale');

		$dst_filename=$baseLocaleFolder.DS.$project['storeview_dst_locale'].DS."template".$filename;
		$dst_directory=$this->getEurotextHelper()->GetDirectoryFromPath($dst_filename);
		if (!is_dir($dst_directory))
		{
			mkdir($dst_directory,0777,true);
		}
		
		copy($full_filename,$dst_filename);
	}
	
	public function importstepAction_ImportTranslation($item,$full_filename,$project,$dbr)
	{
		$doc = new DOMDocument();
		$doc->load($full_filename);
		$translations=$doc->getElementsByTagName("translation");
		foreach($translations as $translation)
		{
			$src_filename=$translation->getAttribute("src_filename");
			$dst_filename=$translation->getAttribute("dst_filename");
			
			$translationDict=array();
			
			$idx=0;
			$foundRow=true;
			while($foundRow)
			{
				$idx++;
				$lineName="line".$idx;
				
				$foundRow=false;
				$sublines=$translation->getElementsByTagName($lineName);
				foreach($sublines as $subline)
				{
					$foundRow=true;
			
					$lines=$subline->getElementsByTagName("line");
					foreach($lines as $line)
					{
						$srchash=$line->getAttribute("srchash");
						$text_translated=$line->textContent;
						
						// Get original line:
						$origLines=$dbr->fetchAll("SELECT text_src FROM `".$this->getTableName('eurotext_csv_data')."` WHERE text_src_hash=?",array($srchash));
						if (count($origLines)>0)
						{
							$origLine=$origLines[0];
							$text_original=$origLine['text_src'];
							
							$translationDict[$text_original]=$text_translated;
						}
					}
				}
			}
			
			$full_path=Mage::getBaseDir('app').$dst_filename;
			
			// Create Backup:
			if (is_file($full_path))
			{
				$backup_path=$this->getProjectXMLPath($project,"backup").$dst_filename;
				$backup_dir=$this->getEurotextHelper()->GetDirectoryFromPath($backup_path);
				if (!is_dir($backup_dir))
				{
					mkdir($backup_dir,0777,true);
				}
				if (!is_file($backup_path))
				{
					copy($full_path,$backup_path);
				}
			}
						
			// Read destination csv:						
			if (is_file($full_path))
			{
				$fp=fopen($full_path,"r");
				$lineIndex=0;
				do
				{
					$fields=fgetcsv($fp,0,",","\"");	
					if ($fields!==false)
					{				
						if (count($fields)==2)
						{
							if (array_key_exists($fields[0],$translationDict))
							{
								// Skip
							}
							else
							{
								// Add item:
								$translationDict[$fields[0]]=$fields[1];
							}
						}
					}
					
					$lineIndex++;
				}
				while($fields!==false);
				fclose($fp);
			}
			
			// Create folder, if not exists:
			$full_path_dir=$this->getEurotextHelper()->GetDirectoryFromPath($full_path);
			if (!is_dir($full_path_dir))
			{
				mkdir($full_path_dir,0777,true);
			}
			
			// Write new file:
			{
				$wp=fopen($full_path,"w");
				{
					foreach($translationDict as $key => $val)
					{
						$fields=array($key,$val);
						fputcsv($wp,$fields,",","\"");	
					}
				}			
				fclose($wp);
			}			
		}
	}
	
	public function uploadAction()
	{

		// Clear tmp folder:
		$tmpdir=$this->getTempDir();
		$this->deleteDirectoryRecursive($tmpdir);
		$tmpdir=$this->getTempDir();
	
		if(isset($_FILES['zipfile']['name']) && $_FILES['zipfile']['name'] != '')
		{
			try
			{         
				$fname = $_FILES['zipfile']['name']; //file name                        
				$uploader = new Varien_File_Uploader('zipfile'); //load class
				$uploader->setAllowedExtensions(array('zip'));
				$uploader->setAllowCreateFolders(true); //for creating the directory if not exists
				$uploader->setAllowRenameFiles(false); //if true, uploaded file's name will be changed, if file with the same name already exists directory.
				$uploader->setFilesDispersion(false);
				$uploader->save($tmpdir,$fname); //save the file on the specified path		

				$import_url=Mage::helper('adminhtml')->getUrl('*/*/importextract');
				$this->_redirectUrl($import_url);
			}
			catch (Exception $e)
			{
                $this->loadLayout('adminhtml_eurotext_translationmanager_text');
                $block = $this->getLayout()->getBlock('et.tm.response.text');

                $block->setText($this->__("Error Message").': '.$e->getMessage());
                $this->renderLayout();
			}
		}
		else
		{
                $this->loadLayout('adminhtml_eurotext_translationmanager_text');
                $block = $this->getLayout()->getBlock('et.tm.response.text');

                $block->setText($this->__("No file given."));
                $this->renderLayout();
		}
	}
}
