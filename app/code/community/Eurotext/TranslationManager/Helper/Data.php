<?php
/**
 */
class Eurotext_TranslationManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_EUROTEXT_DEBUGMODE = 'dev/log/ettm_debug';
	private $help_url="http://www.eurotext.de";

	private $live_ftp_host="eurotext-services.de";
	private $live_ftp_port=21;
	private $live_registration_email="magento@eurotext.de";
	private $live_registration_email_name="Eurotext Magento (Live)";
	
	private $exportProducts_minPerFile=6;
	private $exportCategories_minPerFile=6;

	private $debug_registration_email="debug@eurotext.de";
	private $debug_registration_email_name="Eurotext Magento (Debug)";

	public function getExportProductsMinPerFile()
	{
		return $this->exportProducts_minPerFile;
	}
	
	public function getExportCategoriesMinPerFile()
	{
		return $this->exportCategories_minPerFile;
	}
	
	public function getDebugMode()
	{
        return Mage::getStoreConfigFlag(self::XML_PATH_EUROTEXT_DEBUGMODE);
    }

    public function getCustomProductAttributesForExport(){
		// @TODO Modul ist noch nicht Scope-Fähig, wir lassen das erstmal so und stellen das später auf Mage::getStoreConfig um.
        $custom_attributes = Mage::getConfig()->getNode('default/eurotext/translation_manager/custom_product_attributes');
        return $custom_attributes ? $custom_attributes->asArray() : false;
    }

    public function getCustomCategoryAttributesForExport(){
		// @TODO Modul ist noch nicht Scope-Fähig, wir lassen das erstmal so und stellen das später auf Mage::getStoreConfig um.
        $custom_attributes = Mage::getConfig()->getNode('default/eurotext/translation_manager/custom_category_attributes');
         return $custom_attributes ? $custom_attributes->asArray() : false;
     }

	// Prüft, ob der URL-Key von Produkt/Kategorie auf "global" statt "storeview" steht
	// Liefert true, zurück wenn mindestens ein Scope NICHT(!) auf "storeview" steht
	public function urlKeyScopeIsGlobal()
	{
		$tbl_eav_attribute = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
		$tbl_catalog_eav_attribute = Mage::getSingleton('core/resource')->getTableName('catalog_eav_attribute');
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr=$dbres->getConnection('core_read');
		
		$result=$dbr->fetchAll("SELECT v.is_global, a.attribute_id, a.attribute_code FROM `".$tbl_eav_attribute."` a, `".$tbl_catalog_eav_attribute."` v WHERE (a.attribute_id=v.attribute_id) AND ((a.attribute_code='url_key') OR (a.attribute_code='url_path'))");
		foreach($result as $row)
		{
			if ($row['is_global']>0)
			{
				return true;
			}
		}
		
		return false;
	}

    /**
     * Checks for a incremental url
     * if increment value is detected it'll +1 the value
     * else will create an increment
     *
     * @param string $url
     */
    public function getUniqueUrl($url)
    {
        if (preg_match('/^(.*\-)(\d+)$/i', $url, $matches) == 1) {
            return $matches[1] . ++$matches[2];
        } else {
            return $url . '-1';
        }
    }

	public function getLocaleInfoByMagentoLocale($locale)
	{
		$backendLocale = Mage::app()->getLocale()->getLocaleCode();
		
		$rv=array();
		$rv['locale']=$locale;
		$rv['locale_eurotext']="-";
		$rv['lang_name']=$this->__("Unsupported language");
		$rv['supported']=false;
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr= $dbres->getConnection('core_read');
		
		$tbl_eurotext_languages = Mage::getSingleton('core/resource')->getTableName('eurotext_languages');
		
		// Try current backend language first:
		$localeInfos=$dbr->fetchAll("SELECT * FROM `".$tbl_eurotext_languages."` WHERE UPPER(locale_magento)=?",array(strtoupper($locale)));
		if (count($localeInfos)>0)
		{
			$localeInfo=$localeInfos[0];
			$rv['lang_name']=$localeInfo['lang_name'];
			$rv['locale_eurotext']=$localeInfo['locale_eurotext'];
			$rv['supported']=true;
			
			return $rv;
		}
		
		return $rv;
	}
	
	public function getLocaleInfoByEurotextLocale($locale)
	{
		$backendLocale = Mage::app()->getLocale()->getLocaleCode();
		
		$rv=array();
		$rv['locale']=$locale;
		$rv['locale_eurotext']=$locale;
		$rv['lang_name']=$this->__("Unsupported language");
		$rv['supported']=false;
		
		$dbres = Mage::getSingleton('core/resource');
		$dbr= $dbres->getConnection('core_read');
		
		$tbl_eurotext_languages = Mage::getSingleton('core/resource')->getTableName('eurotext_languages');
		
		// Try current backend language first:
		$localeInfos=$dbr->fetchAll("SELECT * FROM `".$tbl_eurotext_languages."` WHERE UPPER(locale_eurotext)=?",array(strtoupper($locale)));
		if (count($localeInfos)>0)
		{
			$localeInfo=$localeInfos[0];
			$rv['locale']=$localeInfo['locale_magento'];
			$rv['lang_name']=$localeInfo['lang_name'];
			$rv['locale_eurotext']=$localeInfo['locale_eurotext'];
			$rv['supported']=true;
			
			return $rv;
		}
		
		return $rv;
	}
	
	
	public function getRegistrationRecipient()
	{
		$rv=array();
		
		if ($this->getDebugMode())
		{
			$rv["email"]=$this->debug_registration_email;
			$rv["name"]=$this->debug_registration_email_name;
		}
		else
		{
			$rv["email"]=$this->live_registration_email;
			$rv["name"]=$this->live_registration_email_name;
		}
		
		return $rv;
	}
	
	public function getHelpUrl()
	{
		return $this->help_url;
	}

	public function saveSetting($key, $val)
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbw= $dbres->getConnection('core_write');
		
		$tbl_eurotext_config = Mage::getSingleton('core/resource')->getTableName('eurotext_config');

        $val = $this->sanitize($val);

		$dbw->query("INSERT IGNORE INTO `".$tbl_eurotext_config."` (config_key, config_value) VALUES (?,?);",array($key,$val));
		$dbw->query("UPDATE `".$tbl_eurotext_config."` SET config_value=? WHERE config_key=?;",array($val,$key));
	}

    public function log($message, $level = Zend_Log::DEBUG){
            if($this->getDebugMode()) {
                Mage::log($message, $level, 'eurotext.log', true);
            } elseif ($level < Zend_Log::ERR) {
                Mage::log($message, $level, 'eurotext_fatal.log', true);
            }
    }

	public function openFtpConnection()
	{
        if (!function_exists("ftp_connect")) {
            $this->log('There is no FTP Client available: ftp_connect does not exist.', Zend_Log::CRIT);

            return false;
        }
		

		$ftp_host=$this->live_ftp_host;
		$ftp_port=$this->live_ftp_port;

			
		return ftp_connect($ftp_host,$ftp_port,30);

	}
	
	public function testFtpConnection()
	{
		$helper=Mage::helper('eurotext_translationmanager');
		$et_username=$helper->getSetting("eurotext_username","");
		$et_password=Mage::helper('core')->decrypt($helper->getSetting("eurotext_password",""));

		$ftp_username=$et_username;
		$ftp_password=$et_password;

	
		$rv=array();
		$rv['ok']=false;
		$rv['statusmessage']="Unknown error";
	
		if (trim($ftp_username)=="")
		{
            $this->log('Login data is not set.', Zend_Log::ERR);
            $rv['statusmessage']="<span class='et_error'>".$this->__("There seems to be a problem with your login data. Please check username and password!")."</span>";
		}
		else
		{
            $ftpConn=$this->openFtpConnection();
            if ($ftpConn===false)
            {
                // Could not connect to host
                $this->log('Could not connect to Translation Portal Server.', Zend_Log::ERR);
                $rv['statusmessage']="<span class='et_error'>".$this->__("Could not connect to server. Could be a temporary error or firewall problem. You could also check for a new module version.")."</span>";
            }
            else
            {
                // Login:
                if (@ftp_login($ftpConn,$ftp_username,$ftp_password))
                {
                    $this->log('Translation Portal Server successfully connected.', Zend_Log::INFO);
                    $rv['statusmessage']="<span class='et_ok'>".$this->__("Translation portal successfully connected!")."</span>";
                    $rv['ok']=true;
                }
                else
                {
                    $this->log('Could not login to Translation Portal Server.', Zend_Log::ERR);
                    $rv['statusmessage']="<span class='et_error'>".$this->__("There seems to be a problem with your login data. Please check username and password!")."</span>";
                }

                ftp_close($ftpConn);
            }
		}
		
		return $rv;
	}		
	
	public function getSetting($key, $defaultValue="")
	{		
		$dbres = Mage::getSingleton('core/resource');
		$dbr= $dbres->getConnection('core_read');
		
		$tbl_eurotext_config = Mage::getSingleton('core/resource')->getTableName('eurotext_config');
		
		$result=$dbr->fetchOne("SELECT config_value FROM `".$tbl_eurotext_config."` WHERE config_key=?;",array($key));
		if ($result!==false)
		{
			return $result;
		}
		else
		{		
			return $defaultValue;
		}		
	}
	
	public function ajaxexportAction_CollectLangfiles($project)
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');

        $project_id = $this->sanitize($project['id']);

		$dbw->query("UPDATE `".$this->getTableName("eurotext_csv")."` SET deleteflag=1 WHERE project_id=".$project_id);
	
		$base_dir=Mage::getBaseDir('app');
		$this->ajaxexportAction_CollectLangfiles2($dbw,$project,$base_dir);
		
		$dbw->query("DELETE FROM `".$this->getTableName("eurotext_csv")."` WHERE deleteflag=1 AND project_id=".$project_id);
	}
	
	public function ajaxexportAction_CollectLangfilesLocaleCSV($dbw,$project,$locale,$localeFolder)
	{

        $project_id = intval($this->sanitize($project['id']));

		$pathNames=scandir($localeFolder);
		foreach($pathNames as $path)
		{
			$full_path=$localeFolder.DS.$path;
			if ((is_file($full_path)) && (stripos($path,".csv")!==false))
			{
				$base_dir=Mage::getBaseDir('app');
				$filename=substr($full_path,strlen($base_dir));
				
				$line_hash=sha1($project_id."_".$filename);
				$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_csv')."` (line_hash,project_id,filename,locale_dst) VALUES (?,?,?,?);",array($line_hash,$project_id,$filename,$locale));
				$dbw->query("UPDATE `".$this->getTableName('eurotext_csv')."` SET deleteflag=0 WHERE line_hash=?;",array($line_hash));
			}
		}
	}
	
	public function ajaxexportAction_CollectLangfilesLocale($dbw,$project,$localeFolder)
	{		
		$pathNames=scandir($localeFolder);
		foreach($pathNames as $path)
		{
			$full_path=$localeFolder.DS.$path;
			if (($path==".") || ($path==".."))
			{
				// Ignore
			}
			elseif (is_dir($full_path))
			{				
				$this->ajaxexportAction_CollectLangfilesLocaleCSV($dbw,$project,$path,$full_path);
			}
		}
	}
	
	public function ajaxexportAction_CollectLangfiles2($dbw,$project,$curdir)
	{
		$pathNames=scandir($curdir);
		foreach($pathNames as $path)
		{
			$full_path=$curdir.DS.$path;
			if (($path==".") || ($path==".."))
			{
				// Ignore
			}
			elseif (is_dir($full_path))
			{				
				if ($path=="locale")
				{
					$this->ajaxexportAction_CollectLangfilesLocale($dbw,$project,$full_path);
				}
				else
				{
					$this->ajaxexportAction_CollectLangfiles2($dbw,$project,$full_path);
				}					
			}
		}
	}
	
	public function ajaxexportAction_CollectEMailTemplates2($helper, $dbw, $project, $locale, $localeFolder)
	{
        $project_id = intval($this->sanitize($project['id']));

		$templates=$helper->getDirectoryContent($localeFolder,true,true,false);
		foreach($templates as $template)
		{
			$filename=$template['full_path'];
			if ($helper->endsWith(strtolower($filename),".html"))
			{
				$short_filename=substr($filename,strlen($localeFolder));
				$short_filename=substr($filename,strlen($localeFolder));

				$file_hash=sha1($project_id."_".$locale."_".$short_filename);

				$dbw->query("INSERT IGNORE INTO `".$this->getTableName('eurotext_emailtemplates')."` (file_hash,project_id,filename,locale_dst) VALUES (?,?,?,?);",array($file_hash,$project_id,$short_filename,$locale));
				$dbw->query("UPDATE `".$this->getTableName('eurotext_emailtemplates')."` SET deleteflag=0 WHERE file_hash=?;",array($file_hash));
			}
		}
	}
	
	public function ajaxexportAction_CollectEMailTemplates($project)
	{
		$dbres = Mage::getSingleton('core/resource');
		$dbw=$dbres->getConnection('core_write');

        $project_id = intval($this->sanitize($project['id']));

		$dbw->query("UPDATE `".$this->getTableName("eurotext_emailtemplates")."` SET deleteflag=1 WHERE project_id=".$project_id);
	
		$baseLocaleFolder=Mage::getBaseDir('locale');
		
		$helper_et=Mage::helper('eurotext_translationmanager/eurotext');
		$localeFolders=$helper_et->getDirectoryContent($baseLocaleFolder,false,false,true);
		foreach($localeFolders as $localeFolder)
		{
			$templateFolder=$localeFolder['full_path'].DS."template";
			if (is_dir($templateFolder))
			{
				$this->ajaxexportAction_CollectEMailTemplates2($helper_et, $dbw,$project, $localeFolder['name'], $templateFolder);
			}
		}
		
		$dbw->query("DELETE FROM `".$this->getTableName("eurotext_emailtemplates")."` WHERE deleteflag=1 AND project_id=".$project_id);
	}
	
	private function getTableName($tblName)
	{
		return Mage::getSingleton('core/resource')->getTableName($tblName);
	}

    // Gets an array with the ids of all children of $cat_id
    public function getAllSubCategories($cat_id, $recurse=true)
    {
        $rv=array();

        $_cat=Mage::getModel("catalog/category")->load($cat_id);

        $subcat_ids_str=$_cat->getChildren();

        // Free memory:
        unset($_cat);
        $_cat=null;

        $subcat_ids=array();
        if (strlen($subcat_ids_str)>0)
        {
            $subcat_ids=explode(",",$subcat_ids_str);

            $rv=array_merge($rv,$subcat_ids);

            if ($recurse)
            {
                foreach($subcat_ids as $subcat_id)
                {
                    $rv=array_merge($rv,Mage::helper('eurotext_translationmanager')->getAllSubCategories($subcat_id,true));
                }
            }
        }

        return $rv;
    }

    // Generates a tree (recurses to all cats which ids are in open_catids
    public function getCategoryTree($cat_id, $open_catids)
    {
        $node=array();

        $_cat=Mage::getModel("catalog/category")->load($cat_id);

        $node['childs']=array();
        $node['hasChildren']=false;

        if ($cat_id==1) // Pseudo-Category
        {
            $node['id']=1;
            $node['name']=$this->__("(All products)"); // ." (".$cat_id.")";
        }
        else
        {
            $node['id']=$cat_id;
            $node['name']=$_cat->getName(); // ." (".$cat_id.")";
        }

        // get children ids (comma-seperated list as string)
        $subcat_ids_str=$_cat->getChildren();

        // Free memory:
        unset($_cat);
        $_cat=null;

        $subcat_ids=array();
        if (strlen($subcat_ids_str)>0)
        {
            $subcat_ids=explode(",",$subcat_ids_str);
        }

        if (count($subcat_ids)>0)
        {
            $node['hasChildren']=true;

            if (in_array($cat_id, $open_catids))
            {
                // Load sub categories:
                foreach($subcat_ids as $subcat_id)
                {
                    $childItem=$this->getCategoryTree($subcat_id,$open_catids);
                    array_push($node['childs'],$childItem);
                }
            }
        }

        return $node;
    }

    public  static function getCategoryProducts($dbr,$cat_id)
    {
        $sql_categoryfilter="";

        $search_catids=Mage::helper('eurotext_translationmanager')->getAllSubCategories($cat_id); // Get the IDs of all children (direct+indirect)
        array_push($search_catids,$cat_id); // add selected category to list

        // Filter to products which are assigned to any category in $search_catids:
        $sql_categoryfilter=" AND e.entity_id IN (SELECT cat.product_id FROM catalog_category_product cat WHERE cat.category_id IN (".implode(",",$search_catids)."))";

        $sql1="SELECT e.entity_id FROM `".Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')."` e WHERE (1=1)".$sql_categoryfilter;
        $res1=$dbr->fetchAll($sql1);

        $rv=array();
        for($i=0; $i<count($res1); $i++)
        {
            array_push($rv,$res1[$i]['entity_id']);
        }

        return $rv;
    }

    // Returns 'checked', 'indeterminate' or 'unchecked' for a given category id
    // checked: all products of the category are selected for translation
    // indeterminate: some products of the category are selected for translation
    // unchecked: no products are selected for translation
    public function getTreeNodeTranslationState($dbr,$cat_id, $project_id)
    {
        $sql_categoryfilter="";

        $search_catids=Mage::helper('eurotext_translationmanager')->getAllSubCategories($cat_id); // Get the IDs of all children (direct+indirect)
        array_push($search_catids,$cat_id); // add selected category to list

        // Filter to products which are assigned to any category in $search_catids:
        if ($cat_id>1)
        {
            $sql_categoryfilter=" AND e.entity_id IN (SELECT cat.product_id FROM catalog_category_product cat WHERE cat.category_id IN (".implode(",",$search_catids)."))";
        }

        $sql1="SELECT COUNT(e.product_id) cnt FROM eurotext_project_products e WHERE e.project_id=".intval($project_id)." AND e.product_id IN (SELECT e.entity_id FROM `".Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')."` e WHERE (1=1)".$sql_categoryfilter.")";
        //echo $sql1;
        //die($sql1);
        $res1=$dbr->fetchAll($sql1);
        $translatedProducts=$res1[0]['cnt'];

        $sql2="SELECT COUNT(e.entity_id) cnt FROM `".Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')."` e WHERE (1=1)".$sql_categoryfilter;
        //echo $sql2;
        $res2=$dbr->fetchAll($sql2);
        $allProducts=$res2[0]['cnt'];

        if ($allProducts==0)    // empty category
        {
            return "unchecked";
        }
        else if ($translatedProducts==$allProducts)
        {
            return "checked";
        }
        else if ($translatedProducts==0)
        {
            return "unchecked";
        }
        else if ($translatedProducts<$allProducts)
        {
            return "indeterminate";
        }
        else
        {
            return "unchecked";
        }
    }

    public function sanitize($value){
        $value = strip_tags($value);
        $value = trim($value);
        $value = htmlspecialchars($value);
        return $value;
    }
}
