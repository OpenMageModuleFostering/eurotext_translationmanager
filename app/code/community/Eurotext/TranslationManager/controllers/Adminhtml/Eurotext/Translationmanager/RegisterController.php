<?php

class Eurotext_TranslationManager_Adminhtml_Eurotext_Translationmanager_RegisterController extends Mage_Adminhtml_Controller_Action
{

    protected $helper = false;

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
                ->isAllowed('eurotext_translationmanager/register');
    }


    protected function _construct(){
        $this->helper = Mage::helper('eurotext_translationmanager');
    }

    protected function getHelper(){
        return $this->helper;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
	
	public function addLine(&$str, $line)
	{
		$str=$str."".$line."\r\n";
		return $str;
	}
	
	public function addLineHtml(&$str, $line)
	{
		$str2="<div>";
		$str2.=$line;		
		$str2.="</div>";

		return $this->addLine($str,$str2);
	}
	
	public function htmlencode($str)
	{
		return htmlentities($str,ENT_QUOTES,'UTF-8');
	}
	
	public function AnredeAufDeutsch($anrede)
	{
		if ($anrede=="MR") { return "Herr"; }
		if ($anrede=="MRS") { return "Frau"; }
		
		return $anrede;
	}
	
	public function saveAction()
	{
        /** @var Eurotext_TranslationManager_Helper_Data $helper */
        $helper=$this->getHelper();
		$request=$this->getRequest();
		
		$prev_register_shopname = $helper->getSetting("register_shopname","");
		$prev_register_url = $helper->getSetting("register_url","");
		$prev_register_email = $helper->getSetting("register_email","");
		$prev_register_sal = $helper->getSetting("register_sal","");
		$prev_register_fname = $helper->getSetting("register_fname","");
		$prev_register_lname = $helper->getSetting("register_lname","");
		$prev_register_company = $helper->getSetting("register_company","");
		$prev_register_street = $helper->getSetting("register_street","");
		$prev_register_hnumber = $helper->getSetting("register_hnumber","");
		$prev_register_zip = $helper->getSetting("register_zip","");
		$prev_register_city = $helper->getSetting("register_city","");
		$prev_register_country = $helper->getSetting("register_country","");
		$prev_register_telefon = $helper->getSetting("register_telefon","");
		
		$new_register_shopname = $request->getParam("register_shopname");
		$new_register_url = $request->getParam("register_url");
		$new_register_email = $request->getParam("register_email");
		$new_register_sal = $request->getParam("register_sal");
		$new_register_fname = $request->getParam("register_fname");
		$new_register_lname = $request->getParam("register_lname");
		$new_register_company = $request->getParam("register_company");
		$new_register_street = $request->getParam("register_street");
		$new_register_hnumber = $request->getParam("register_hnumber");
		$new_register_zip = $request->getParam("register_zip");
		$new_register_city = $request->getParam("register_city");
		$new_register_country = $request->getParam("register_country");
		$new_register_telefon = $request->getParam("register_telefon");
		
		// Save settings:
		$helper->saveSetting("register_shopname", $request->getParam("register_shopname"));
		$helper->saveSetting("register_url", $request->getParam("register_url"));
		$helper->saveSetting("register_email", $request->getParam("register_email"));
		$helper->saveSetting("register_sal", $request->getParam("register_sal"));
		$helper->saveSetting("register_fname", $request->getParam("register_fname"));
		$helper->saveSetting("register_lname", $request->getParam("register_lname"));
		$helper->saveSetting("register_shopname", $request->getParam("register_shopname"));
		$helper->saveSetting("register_company", $request->getParam("register_company"));
		$helper->saveSetting("register_street", $request->getParam("register_street"));
		$helper->saveSetting("register_hnumber", $request->getParam("register_hnumber"));
		$helper->saveSetting("register_zip", $request->getParam("register_zip"));
		$helper->saveSetting("register_city", $request->getParam("register_city"));
		$helper->saveSetting("register_country", $request->getParam("register_country"));
		$helper->saveSetting("register_telefon", $request->getParam("register_telefon"));

		$register_mailsent=$helper->getSetting("register_mailsent", "0");

		$email_body="";
		$this->addLine($email_body,"<div>Hallo,</div>");
		if ($register_mailsent=="1")
		{
			$this->addLine($email_body,"<div>".$this->htmlencode("Ein Kunde hat im Magento-Modul seine Kontaktdaten geändert:")."</div>");
		}
		else
		{
			$this->addLine($email_body,"<div>".$this->htmlencode("Ein Kunde hat sich via Magento-Modul neu registriert")."</div>");
		}
		
		$this->addLine($email_body,"<div>Kundennummer: ".$this->htmlencode($helper->getSetting("eurotext_customerid",""))."</div>");
		$this->addLine($email_body,"<div>Benutzername: ".$this->htmlencode($helper->getSetting("eurotext_username",""))."</div>");
		
		$fieldItems=array();
		$fieldItems[0]['title']='Shopname';
		$fieldItems[0]['new']=$new_register_shopname;
		$fieldItems[0]['prev']=$prev_register_shopname;
		
		$fieldItems[1]['title']='Shop URL';
		$fieldItems[1]['new']=$new_register_url;
		$fieldItems[1]['prev']=$prev_register_url;
		
		$fieldItems[2]['title']='eMail-Adresse';
		$fieldItems[2]['new']=$new_register_email;
		$fieldItems[2]['prev']=$prev_register_email;
		
		$fieldItems[3]['title']='Anrede';
		$fieldItems[3]['new']=$this->AnredeAufDeutsch($new_register_sal);
		$fieldItems[3]['prev']=$this->AnredeAufDeutsch($prev_register_sal);
		
		$fieldItems[4]['title']='Vorname';
		$fieldItems[4]['new']=$new_register_fname;
		$fieldItems[4]['prev']=$prev_register_fname;
		
		$fieldItems[5]['title']='Nachname';
		$fieldItems[5]['new']=$new_register_lname;
		$fieldItems[5]['prev']=$prev_register_lname;
		
		$fieldItems[6]['title']='Firma';
		$fieldItems[6]['new']=$new_register_company;
		$fieldItems[6]['prev']=$prev_register_company;
		
		$fieldItems[7]['title']='Strasse';
		$fieldItems[7]['new']=$new_register_street;
		$fieldItems[7]['prev']=$prev_register_street;
		
		$fieldItems[8]['title']='Hausnr';
		$fieldItems[8]['new']=$new_register_hnumber;
		$fieldItems[8]['prev']=$prev_register_hnumber;
		
		$fieldItems[9]['title']='PLZ';
		$fieldItems[9]['new']=$new_register_zip;
		$fieldItems[9]['prev']=$prev_register_zip;
		
		$fieldItems[10]['title']='Stadt';
		$fieldItems[10]['new']=$new_register_city;
		$fieldItems[10]['prev']=$prev_register_city;
		
		$fieldItems[11]['title']='Land';
		$fieldItems[11]['new']=$new_register_country;
		$fieldItems[11]['prev']=$prev_register_country;
		
		$fieldItems[12]['title']='Tel-Nr';
		$fieldItems[12]['new']=$new_register_telefon;
		$fieldItems[12]['prev']=$prev_register_telefon;		
		
		$this->addLine($email_body,"<hr size=1 color=black>");
		
		for($i=0; $i<count($fieldItems); $i++)
		{
			$item=$fieldItems[$i];
			
			$hasChanged=($item['new']!=$item['prev']);
			
			$line=$this->htmlencode($item['title']);
			$line.=": ";
			$line.=$this->htmlencode($item['new']);
			$line.=" (";
			if ($hasChanged)
			{
				$line.="<span style='color:red;font-weight:bold;'>";
			}
			$line.=$this->htmlencode($item['prev']);
			if ($hasChanged)
			{
				$line.="</span>";
			}
			$line.=")";
			
			$this->addLineHtml($email_body,$line);			
		}
		
		// Send email:
		$helper=$this->getHelper();
		$sender_email=Mage::getStoreConfig('trans_email/ident_general/email');
		$sender_name=Mage::getStoreConfig('trans_email/ident_general/name');
		$recipient=$helper->getRegistrationRecipient();
		
		$mail = new Zend_Mail('UTF-8');
		$mail->setBodyHtml($email_body);
		$mail->setFrom($sender_email,$sender_name);
		$mail->addTo($recipient['email'],$recipient['name']);		
		
		if ($register_mailsent=="1")
		{		
			$mail->setSubject("Update - Ein Magento translationMANAGER-Kunde hat seine Registrierungsdaten geändert");
		}
		else
		{
			$mail->setSubject("Magento translationMANAGER: Registrierung");
		}
		$mail->send();
		
		// Registration done:		
		$helper->saveSetting("register_mailsent_date", date("d.m.Y H:i:s")." (".date_default_timezone_get().")");
		$helper->saveSetting("register_mailsent", "1");
		
		$url=Mage::helper('adminhtml')->getUrl('*/*/index',array('saved' => true));
		$this->_redirectUrl($url);
	}
}