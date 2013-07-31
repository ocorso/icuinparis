<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Magento extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getName()
    {
        return 'magento';
    }

    public function getVersion($asArray = false)
    {
        $versionString = Mage::getVersion();
        return $asArray ? explode('.',$versionString) : $versionString;
    }

    public function getRevision()
    {
        return 'undefined';
    }

    //----------------------------------------

    public function getLocale()
    {
        $localeComponents = explode('_' , Mage::app()->getLocale()->getLocale());
        return strtolower($localeComponents[0]);
    }

    //----------------------------------------

    public function getEditionName()
    {
        if ($this->isMagentoGoMode()) {
            return 'magento go';
        }
        if ($this->isMagentoProfessionalEdition()) {
            return 'professional';
        }
        if ($this->isMagentoEnterpriseEdition()) {
            return 'enterprise';
        }
        if ($this->isMagentoCommunityEdition()) {
            return 'community';
        }

        return 'undefined';
    }

    //----------------------------------------

    public function isMagentoGoMode()
    {
        return class_exists('Saas_Db',false);
    }

    public function isMagentoProfessionalEdition()
    {
        if ($this->isMagentoGoMode()) {
            return false;
        }

        $modules = $this->getModules();
        if (in_array('Professional_License',$modules)) {
            return true;
        }

        return false;
    }

    public function isMagentoEnterpriseEdition()
    {
        if ($this->isMagentoGoMode()) {
            return false;
        }

        $modules = $this->getModules();
        if (in_array('Enterprise_License',$modules)) {
            return true;
        }

        return false;
    }

    public function isMagentoCommunityEdition()
    {
        if ($this->isMagentoGoMode()) {
            return false;
        }

        if ($this->isMagentoProfessionalEdition()) {
            return false;
        }

        if ($this->isMagentoEnterpriseEdition()) {
            return false;
        }

        return true;
    }

    // ########################################

    public function getMySqlTables()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read')->listTables();
    }

    public function getDatabaseTablesPrefix()
    {
        return (string)Mage::getConfig()->getTablePrefix();
    }

    public function getDatabaseName()
    {
        return (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
    }

    public function getModules()
    {
        return array_keys((array)Mage::getConfig()->getNode('modules')->children());
    }

    public function isTinyMceAvailable()
    {
        return (float)$this->getVersion(false) >= 1.4;
    }

    public function getUrl($route, array $params = array())
    {
        return Mage::registry('M2ePro_base_controller')->getUrl($route,$params);
    }

    public function getBaseCurrency()
	{
		return (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
	}

    //----------------------------------------

    public function isSecretKeyToUrl()
    {
        return (bool)Mage::getStoreConfigFlag('admin/security/use_form_key');
    }

    public function getCurrentSecretKey()
    {
        if (!$this->isSecretKeyToUrl()) {
            return '';
        }
        return Mage::getSingleton('adminhtml/url')->getSecretKey();
    }

    // ########################################
}