<?php

/** @var Mage_Core_Model_Resource_Setup $installer */

$installer = $this;
$installer->startSetup();

try {
    $sql = "";
    $sql .= "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_config") . "` (";
    $sql .= "  `config_key` varchar(255) NOT NULL,";
    $sql .= "  `config_value` text NOT NULL,";
    $sql .= "  PRIMARY KEY (`config_key`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_csv") . "` (";
    $sql .= "  `line_hash` varchar(60) NOT NULL,";
    $sql .= "  `project_id` bigint(20) NOT NULL,";
    $sql .= "  `filename` varchar(255) NOT NULL,";
    $sql .= "  `locale_dst` varchar(50) NOT NULL,";
    $sql .= "  `translate_flag` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  `time_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,";
    $sql .= "  `deleteflag` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  PRIMARY KEY (`line_hash`),";
    $sql .= "  KEY `idx_filename` (`filename`),";
    $sql .= "  KEY `idx_project_id` (`project_id`),";
    $sql .= "  KEY `idx_locale_dst` (`locale_dst`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_csv_data") . "` (";
    $sql .= "  `line_hash` varchar(60) NOT NULL,";
    $sql .= "  `project_id` bigint(20) NOT NULL,";
    $sql .= "  `filename` varchar(255) NOT NULL,";
    $sql .= "  `csvline` int(11) NOT NULL DEFAULT '-1',";
    $sql .= "  `locale_dst` varchar(50) NOT NULL,";
    $sql .= "  `text_src` varchar(5000) NOT NULL,";
    $sql .= "  `text_src_hash` varchar(100) NOT NULL DEFAULT '',";
    $sql .= "  `text_dst` varchar(5000) NOT NULL,";
    $sql .= "  PRIMARY KEY (`line_hash`),";
    $sql .= "  KEY `project_id` (`project_id`),";
    $sql .= "  KEY `filename` (`filename`),";
    $sql .= "  KEY `text_src_hash` (`text_src_hash`),";
    $sql .= "  KEY `locale_dst` (`locale_dst`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch
(Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_emailtemplates") . "` (";
    $sql .= "  `file_hash` varchar(150) NOT NULL,";
    $sql .= "  `filename` varchar(1500) NOT NULL,";
    $sql .= "  `translate_flag` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  `project_id` bigint(20) NOT NULL DEFAULT '-1',";
    $sql .= "  `deleteflag` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  `locale_dst` varchar(50) NOT NULL,";
    $sql .= "  `time_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,";
    $sql .= "  PRIMARY KEY (`file_hash`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_import") . "` (";
    $sql .= "  `project_id` bigint(20) NOT NULL,";
    $sql .= "  `filename` varchar(255) NOT NULL,";
    $sql .= "  `storeview_dst` int(11) NOT NULL,";
    $sql .= "  `num` int(11) NOT NULL DEFAULT '-1',";
    $sql .= "  `is_imported` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  UNIQUE KEY `pk` (`project_id`,`filename`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_languages") . "` (";
    $sql .= "  `locale_magento` varchar(20) CHARACTER SET utf8 NOT NULL,";
    $sql .= "  `locale_eurotext` varchar(100) CHARACTER SET utf8 NOT NULL,";
    $sql .= "  `lang_name` varchar(200) CHARACTER SET utf8 NOT NULL,";
    $sql .= "  PRIMARY KEY (`locale_eurotext`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "INSERT IGNORE INTO `" . $installer->getTable("eurotext_languages") . "` (`locale_magento`, `locale_eurotext`, `lang_name`) VALUES";
    $sql .= "  ('af_ZA', 'afr', 'Afrikaans'),";
    $sql .= "  ('sq_AL', 'alb', 'Albanian'),";
    $sql .= "  ('ar_DZ', 'ar-dz', 'Arabic (Algeria)'),";
    $sql .= "  ('ar_EG', 'ar-eg', 'Arabic (Egypt)'),";
    $sql .= "  ('ar_KW', 'ar-kw', 'Arabic (Kuwait)'),";
    $sql .= "  ('ar_MA', 'ar-ma', 'Arabic (Morocco)'),";
    $sql .= "  ('ar_SA', 'ar-sa', 'Arabic (Saudi Arabia)'),";
    $sql .= "  ('az_AZ', 'aze', 'Azerbaijani (Latin)'),";
    $sql .= "  ('be_BY', 'bel', 'Byelorussian'),";
    $sql .= "  ('bg_BG', 'bg', 'Bulgarian'),";
    $sql .= "  ('bs_BA', 'bos', 'Bosnian'),";
    $sql .= "  ('ca_ES', 'cat', 'Catalan'),";
    $sql .= "  ('cs_CZ', 'cz-cz', 'Czech'),";
    $sql .= "  ('da_DK', 'da', 'Danish'),";
    $sql .= "  ('de_AT', 'de-at', 'German (AT)'),";
    $sql .= "  ('de_CH', 'de-ch', 'German (CH)'),";
    $sql .= "  ('de_DE', 'de-de', 'German (DE)'),";
    $sql .= "  ('el_GR', 'el', 'Greek'),";
    $sql .= "  ('en_AU', 'en-au', 'English (AU)'),";
    $sql .= "  ('en_CA', 'en-ca', 'English (CA)'),";
    $sql .= "  ('en_GB', 'en-gb', 'English (GB)'),";
    $sql .= "  ('en_IE', 'en-ie', 'English (IE)'),";
    $sql .= "  ('en_NZ', 'en-nz', 'English (NZ)'),";
    $sql .= "  ('en_US', 'en-us', 'English (US)'),";
    $sql .= "  ('es_AR', 'es-ar', 'Spanish (Argentina)'),";
    $sql .= "  ('es_CL', 'es-cl', 'Spanish (Chile)'),";
    $sql .= "  ('es_CO', 'es-co', 'Spanish (Colombia)'),";
    $sql .= "  ('es_CR', 'es-cr', 'Spanish (Costa Rica)'),";
    $sql .= "  ('es_ES', 'es-es', 'Spanish (ES)'),";
    $sql .= "  ('es_MX', 'es-mx', 'Spanish (Mexico)'),";
    $sql .= "  ('es_PA', 'es-pa', 'Spanish (Panama)'),";
    $sql .= "  ('es_PE', 'es-pe', 'Spanish (Peru)'),";
    $sql .= "  ('es_VE', 'es-ve', 'Spanish (Venezuela)'),";
    $sql .= "  ('et_EE', 'et', 'Estonian'),";
    $sql .= "  ('fi_FI', 'fi-fi', 'Finnish'),";
    $sql .= "  ('fr_CA', 'fr-ca', 'French (CA)'),";
    $sql .= "    ('fr_FR', 'fr-fr', 'French (FR)'),";
    $sql .= "    ('gl_ES', 'glg', 'Galician'),";
    $sql .= "    ('gu_IN', 'guj', 'Gujarati'),";
    $sql .= "    ('he_IL', 'he', 'Hebrew'),";
    $sql .= "    ('hi_IN', 'hin', 'Hindi'),";
    $sql .= "    ('hr_HR', 'hr', 'Croatian'),";
    $sql .= "    ('hu_HU', 'hu', 'Hungarian'),";
    $sql .= "    ('is_IS', 'ice', 'Icelandic'),";
    $sql .= "    ('id_ID', 'ind', 'Indonesian'),";
    $sql .= "    ('it_CH', 'it-ch', 'Italy (CH)'),";
    $sql .= "    ('it_IT', 'it-it', 'Italian (IT)'),";
    $sql .= "    ('ja_JP', 'ja', 'Japanese'),";
    $sql .= "    ('ko_KR', 'ko-kr', 'Korean'),";
    $sql .= "    ('lt_LT', 'lt-lt', 'Lithuanian'),";
    $sql .= "    ('lv_LV', 'lv', 'Latvian'),";
    $sql .= "    ('mk_MK', 'mk', 'Mecedonian'),";
    $sql .= "    ('ms_MY', 'msa', 'Malay'),";
    $sql .= "    ('nl_NL', 'nl-nl', 'Dutch (NL)'),";
    $sql .= "    ('nb_NO', 'no-nb', 'Norwegian (Bokmål)'),";
    $sql .= "    ('nn_NO', 'no-nn', 'Norwegian (Nynorsk)'),";
    $sql .= "    ('pl_PL', 'pl', 'Polish'),";
    $sql .= "    ('pt_BR', 'pt-br', 'Portuguese (BR)'),";
    $sql .= "    ('pt_PT', 'pt-pt', 'Portuguese (PT)'),";
    $sql .= "    ('ro_RO', 'ro-ro', 'Romanian'),";
    $sql .= "    ('ru_RU', 'ru-ru', 'Russian'),";
    $sql .= "    ('sk_SK', 'sk', 'Slovak'),";
    $sql .= "    ('sl_SI', 'sl', 'Slovenian'),";
    $sql .= "    ('sr_RS', 'sr', 'Serbian (Latin)'),";
    $sql .= "    ('sv_SE', 'sv-se', 'Swedish'),";
    $sql .= "    ('th_TH', 'th', 'Thai'),";
    $sql .= "    ('tr_TR', 'tr', 'Turkish'),";
    $sql .= "    ('uk_UA', 'uk', 'Ukrainian'),";
    $sql .= "    ('vi_VN', 'vn', 'Vietnamese'),";
    $sql .= "    ('cy_GB', 'wel', 'Welsh'),";
    $sql .= "    ('zh_CN', 'zh-cn', 'Chinese (PRC)'),";
    $sql .= "    ('zh_HK', 'zh-hk', 'Chinese (Hong Kong S.A.R.)'),";
    $sql .= "    ('zh_TW', 'zh-tw', 'Chinese (Taiwan)');";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_project") . "` (";
    $sql .= "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',";
    $sql .= "  `deleted` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  `create_id` varchar(100) DEFAULT NULL,";
    $sql .= "  `project_name` text NOT NULL COMMENT 'Project Name',";
    $sql .= "  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last Update',";
    $sql .= "  `storeview_src` int(11) NOT NULL DEFAULT '-1' COMMENT 'Source Storeview',";
    $sql .= "  `storeview_dst` int(11) NOT NULL DEFAULT '-1' COMMENT 'Destination Storeview',";
    $sql .= "  `project_status` int(11) NOT NULL DEFAULT '0' COMMENT 'Status',";
    $sql .= "  `langfilesmode` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  `export_seo` smallint(6) NOT NULL DEFAULT '1',";
    $sql .= "  `productmode` smallint(6) NOT NULL DEFAULT '0' COMMENT '0=Selected Products, 1=Products without translation',";
    $sql .= "  `categorymode` smallint(6) NOT NULL DEFAULT '0' COMMENT '0=Selected Categories, 1=Categories without translation',";
    $sql .= "  `cmsmode` smallint(6) NOT NULL DEFAULT '0' COMMENT '0=Selected CMS-Pages, 1 = Missing CMS-Pages',";
    $sql .= "  `zip_filename` varchar(255) NOT NULL DEFAULT '',";
    $sql .= "  `export_attributes` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  `templatemode` smallint(6) NOT NULL COMMENT '0',";
    $sql .= "  `export_urlkeys` smallint(6) NOT NULL DEFAULT '0',";
    $sql .= "  PRIMARY KEY (`id`)";
    $sql .= ") ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='eurotext_project';";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_project_categories") . "` (";
    $sql .= "  `category_id` bigint(20) NOT NULL,";
    $sql .= "  `project_id` bigint(20) NOT NULL,";
    $sql .= "  `time_added` bigint(20) NOT NULL DEFAULT '0',";
    $sql .= "  PRIMARY KEY (`category_id`,`project_id`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_project_cmspages") . "` (";
    $sql .= "  `page_id` bigint(20) NOT NULL,";
    $sql .= "  `project_id` bigint(20) NOT NULL,";
    $sql .= "  `time_added` bigint(20) NOT NULL DEFAULT '0',";
    $sql .= "  PRIMARY KEY (`page_id`,`project_id`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `" . $installer->getTable("eurotext_project_products") . "` (";
    $sql .= "  `product_id` bigint(20) NOT NULL,";
    $sql .= "  `project_id` bigint(20) NOT NULL,";
    $sql .= "  `time_added` bigint(20) NOT NULL DEFAULT '0',";
    $sql .= "  PRIMARY KEY (`product_id`,`project_id`)";
    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
    $installer->run($sql);
} catch (Exception $e) {
//void if table exists 
}

$installer->endSetup();

