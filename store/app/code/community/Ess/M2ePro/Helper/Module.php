<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module extends Mage_Core_Helper_Abstract
{
    // ########################################
    
    /**
     * @return Ess_M2ePro_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/Config');
    }

    // ########################################

    public function getName()
    {
        return 'm2epro';
    }

    public function getVersion()
    {
        $version = (string)Mage::getConfig()->getNode('modules/Ess_M2ePro/version');
        $version = strtolower($version);

        $cacheKey = $this->getName().'_VERSION_UPDATER';
        if (Mage::app()->getCache()->load($cacheKey) === false) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/modules/',$this->getName(),$version.'.r'.$this->getRevision());
            Mage::app()->getCache()->save(serialize(array()), $cacheKey, array(), 60*60*24);
        }

        return $version;
    }

    public function getRevision()
    {
        $revision = '500';
        
        if ($revision == str_replace('|','#','|REVISION_VERSION|')) {
            $revision = '';
            $svnEntireFile = Mage::getBaseDir().'/.svn/entries';
            if (file_exists($svnEntireFile)) {
                $svnEntireContent = file($svnEntireFile);
                $revision .= trim($svnEntireContent[3]);
            }
            $revision .= '-dev';
        }

        return strtolower($revision);
    }

    public function getVersionWithRevision()
    {
        return $this->getVersion().'r'.$this->getRevision();
    }

    // ########################################

    public function isDeveloper()
    {
        $domain = Mage::helper('M2ePro/Server')->getDomain();
        $ip = Mage::helper('M2ePro/Server')->getIp();
        return strpos($domain,'localhost') !== false || $ip == '127.0.0.1';
    }

    public function isInstalledM2eLastVersion()
    {
        return (string)Mage::getConfig()->getNode('modules/Ess_M2e/version') === '2.0.15';
    }

    // ########################################
    
    public function getServerDirectory()
    {
        return Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.$this->getName().'/server/','directory');
    }

    public function getServerScriptsPath()
    {
        $path = Mage::helper('M2ePro/Ess')->getServerBaseUrl().$this->getServerDirectory();
        $path = str_replace('//', '/', $path);
        return str_replace(':/', '://', $path);
    }

    public function getMySqlTables()
    {
        return array(
            'ess_config',
            'm2epro_accounts',
            'm2epro_accounts_store_categories',
            'm2epro_config',
            'm2epro_descriptions_templates',
            'm2epro_dictionary_categories',
            'm2epro_dictionary_marketplaces',
            'm2epro_dictionary_shippings',
            'm2epro_dictionary_shippings_categories',
            'm2epro_ebay_items',
            'm2epro_ebay_listings',
            'm2epro_ebay_listings_logs',
            'm2epro_ebay_orders',
            'm2epro_ebay_orders_external_transactions',
            'm2epro_ebay_orders_items',
            'm2epro_ebay_orders_logs',
            'm2epro_feedbacks',
            'm2epro_feedbacks_templates',
            'm2epro_listings',
            'm2epro_listings_categories',
            'm2epro_listings_logs',
            'm2epro_listings_products',
            'm2epro_listings_products_variations',
            'm2epro_listings_products_variations_options',
            'm2epro_listings_templates',
            'm2epro_listings_templates_calculated_shipping',
            'm2epro_listings_templates_payments',
            'm2epro_listings_templates_shippings',
            'm2epro_listings_templates_specifics',
            'm2epro_lock_items',
            'm2epro_marketplaces',
            'm2epro_messages',
            'm2epro_migration_temp',
            'm2epro_products_changes',
            'm2epro_selling_formats_templates',
            'm2epro_synchronizations_logs',
            'm2epro_synchronizations_runs',
            'm2epro_synchronizations_templates',
            'm2epro_templates_attribute_sets'
        );
    }

    public function getApplicationKey()
    {
        return (string)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.$this->getName().'/','application_key');
    }

    // ########################################

    public function getSummaryTextInfo()
    {
        $systemInfo = array();
        $systemInfo['name'] = Mage::helper('M2ePro/Server')->getSystem();

        $locationInfo = array();
        $locationInfo['domain'] = Mage::helper('M2ePro/Server')->getDomain();
        $locationInfo['ip'] = Mage::helper('M2ePro/Server')->getIp();
        $locationInfo['directory'] = Mage::helper('M2ePro/Server')->getBaseDirectory();

        $platformInfo = array();
        $platformInfo['name'] = Mage::helper('M2ePro/Magento')->getName();
        $platformInfo['edition'] = Mage::helper('M2ePro/Magento')->getEditionName();
        $platformInfo['version'] = Mage::helper('M2ePro/Magento')->getVersion();
        $platformInfo['revision'] = Mage::helper('M2ePro/Magento')->getRevision();

        $moduleInfo = array();
        $moduleInfo['name'] = Mage::helper('M2ePro/Module')->getName();
        $moduleInfo['version'] = Mage::helper('M2ePro/Module')->getVersion();
        $moduleInfo['revision'] = Mage::helper('M2ePro/Module')->getRevision();

        $phpInfo = Mage::helper('M2ePro/Server')->getPhpSettings();
        $phpInfo['api'] = Mage::helper('M2ePro/Server')->getPhpApiName();
        $phpInfo['version'] = Mage::helper('M2ePro/Server')->getPhpVersion();

        $mysqlInfo = Mage::Helper('M2ePro/Server')->getMysqlSettings();
        $mysqlInfo['api'] = Mage::helper('M2ePro/Server')->getMysqlApiName();
        $prefix = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        $mysqlInfo['prefix'] = $prefix != '' ? $prefix : 'Disabled';
        $mysqlInfo['version'] = Mage::helper('M2ePro/Server')->getMysqlVersion();

        $info = <<<DATA
-------------------------------- PLATFORM INFO -----------------------------------
Name: {$platformInfo['name']}
Edition: {$platformInfo['edition']}
Version: {$platformInfo['version']}
Revision: {$platformInfo['revision']}

-------------------------------- MODULE INFO -------------------------------------
Name: {$moduleInfo['name']}
Version: {$moduleInfo['version']}
Revision: {$moduleInfo['revision']}

-------------------------------- LOCATION INFO -----------------------------------
Domain: {$locationInfo['domain']}
Ip: {$locationInfo['ip']}
Directory: {$locationInfo['directory']}

-------------------------------- SYSTEM INFO -------------------------------------
Name: {$systemInfo['name']}

-------------------------------- PHP INFO ----------------------------------------
Version: {$phpInfo['version']}
Api: {$phpInfo['api']}
Memory Limit: {$phpInfo['memory_limit']}
Max Execution Time: {$phpInfo['max_execution_time']}

-------------------------------- MYSQL INFO --------------------------------------
Version: {$mysqlInfo['version']}
Api: {$mysqlInfo['api']}
Tables Prefix: {$mysqlInfo['prefix']}
Connection Timeout: {$mysqlInfo['connect_timeout']}
Wait Timeout: {$mysqlInfo['wait_timeout']}
DATA;

        return $info;
    }

    // ########################################
}