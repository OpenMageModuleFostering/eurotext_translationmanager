<?php

class Eurotext_TranslationManager_Model_EurotextLanguages
{
    /**
     * @var string[]
     */
    private $supportedLanguages = [
        'af_ZA' => 'afr',
        'sq_AL' => 'alb',
        'ar_DZ' => 'ar-dz',
        'ar_EG' => 'ar-eg',
        'ar_KW' => 'ar-kw',
        'ar_MA' => 'ar-ma',
        'ar_SA' => 'ar-sa',
        'az_AZ' => 'aze',
        'be_BY' => 'bel',
        'bg_BG' => 'bg',
        'bs_BA' => 'bos',
        'ca_ES' => 'cat',
        'cs_CZ' => 'cz-cz',
        'da_DK' => 'da',
        'de_AT' => 'de-at',
        'de_CH' => 'de-ch',
        'de_DE' => 'de-de',
        'el_GR' => 'el',
        'en_AU' => 'en-au',
        'en_CA' => 'en-ca',
        'en_GB' => 'en-gb',
        'en_IE' => 'en-ie',
        'en_NZ' => 'en-nz',
        'en_US' => 'en-us',
        'es_AR' => 'es-ar',
        'es_CL' => 'es-cl',
        'es_CO' => 'es-co',
        'es_CR' => 'es-cr',
        'es_ES' => 'es-es',
        'es_MX' => 'es-mx',
        'es_PA' => 'es-pa',
        'es_PE' => 'es-pe',
        'es_VE' => 'es-ve',
        'et_EE' => 'et',
        'fi_FI' => 'fi-fi',
        'fr_CA' => 'fr-ca',
        'fr_FR' => 'fr-fr',
        'gl_ES' => 'glg',
        'gu_IN' => 'guj',
        'he_IL' => 'he',
        'hi_IN' => 'hin',
        'hr_HR' => 'hr',
        'hu_HU' => 'hu',
        'is_IS' => 'ice',
        'id_ID' => 'ind',
        'it_CH' => 'it-ch',
        'it_IT' => 'it-it',
        'ja_JP' => 'ja',
        'ko_KR' => 'ko-kr',
        'lt_LT' => 'lt-lt',
        'lv_LV' => 'lv',
        'mk_MK' => 'mk',
        'ms_MY' => 'msa',
        'nl_NL' => 'nl-nl',
        'nb_NO' => 'no-nb',
        'nn_NO' => 'no-nn',
        'pl_PL' => 'pl',
        'pt_BR' => 'pt-br',
        'pt_PT' => 'pt-pt',
        'ro_RO' => 'ro-ro',
        'ru_RU' => 'ru-ru',
        'sk_SK' => 'sk',
        'sl_SI' => 'sl',
        'sr_RS' => 'sr',
        'sv_SE' => 'sv-se',
        'th_TH' => 'th',
        'tr_TR' => 'tr',
        'uk_UA' => 'uk',
        'vi_VN' => 'vn',
        'cy_GB' => 'wel',
        'zh_CN' => 'zh-cn',
        'zh_HK' => 'zh-hk',
        'zh_TW' => 'zh-tw',
    ];

    private $fromMagentoLocale;
    private $fromEurotext;

    private function generateSupportedLanguages()
    {
        if ($this->fromMagentoLocale !== null) {
            return;
        }

        $languages = Mage::getModel('adminhtml/system_config_source_locale')->toOptionArray();
        $this->fromMagentoLocale = [];
        $this->fromEurotext = [];

        foreach ($languages as $lang) {
            if (isset($this->supportedLanguages[$lang['value']])) {
                $this->fromMagentoLocale[$lang['value']] = [
                    'locale'          => $lang['value'],
                    'locale_eurotext' => $this->supportedLanguages[$lang['value']],
                    'lang_name'       => $lang['label'],
                    'supported'       => true,
                ];
                $this->fromEurotext[$this->supportedLanguages[$lang['value']]] = [
                    'locale'          => $lang['value'],
                    'locale_eurotext' => $this->supportedLanguages[$lang['value']],
                    'lang_name'       => $lang['label'],
                    'supported'       => true,
                ];
            }
        }
    }

    public function getInfoByMageLocale($locale)
    {
        $this->generateSupportedLanguages();

        if (isset($this->fromMagentoLocale[$locale])) {
            return $this->fromMagentoLocale[$locale];
        }

        return [
            'locale'          => $locale,
            'locale_eurotext' => '-',
            'lang_name'       => Mage::helper('eurotext_translationmanager')->__('Unsupported language'),
            'supported'       => false,
        ];
    }

    public function getInfoByEurotext($locale)
    {
        $this->generateSupportedLanguages();

        if (isset($this->fromEurotext[$locale])) {
            return $this->fromEurotext[$locale];
        }

        return [
            'locale'          => $locale,
            'locale_eurotext' => '-',
            'lang_name'       => Mage::helper('eurotext_translationmanager')->__('Unsupported language'),
            'supported'       => false,
        ];
    }
}
