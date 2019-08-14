<?php

class Eurotext_TranslationManager_Helper_Project
{
    /**
     * @param int $id
     * @return Eurotext_TranslationManager_Model_Project
     */
    public function getProject($id)
    {
        /** @var Eurotext_TranslationManager_Model_Project $project */
        $project = Mage::getModel('eurotext_translationmanager/project')->load($id);
        if ($project->isObjectNew()) {
            Mage::throwException(sprintf('Project with ID "%s" not found.', $id));
        }

        $project->setData('storeview_src_locale', Eurotext_TranslationManager_Model_Project::DEFAULT_SRC_LOCALE);
        if ($project->getStoreviewSrc() >= 0) {
            $project->setData(
                'storeview_src_locale',
                Mage::getStoreConfig('general/locale/code', $project->getStoreviewSrc())
            );
        }

        $project->setData('storeview_dst_locale', Eurotext_TranslationManager_Model_Project::DEFAULT_DST_LOCALE);
        if ($project->getStoreviewDst() >= 0) {
            $project->setData(
                'storeview_dst_locale',
                Mage::getStoreConfig('general/locale/code', $project->getStoreviewDst())
            );
        }

        return $project;
    }
}
