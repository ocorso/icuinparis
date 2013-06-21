<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Dispatcher extends Mage_Core_Model_Abstract
{
    const DEVELOPMENT_MODE = false;

    const ALREADY_WORKED_NO = 0;
    const ALREADY_WORKED_YES = 1;
    
    const CUSTOM_USER_INTERFACE_NO = 0;
    const CUSTOM_USER_INTERFACE_YES = 1;

    /**
     * @var Ess_M2ePro_Model_Migration_TempDbTable
     */
    private $tempDbTable = NULL;

    private $timestampStart = 0;
    private $microtimeStart = 0;
    private $microtimeStep = 0;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Dispatcher');
        $this->tempDbTable = Mage::getModel('M2ePro/Migration_TempDbTable');
    }

    // ########################################

	public function process()
    {
        if (!self::DEVELOPMENT_MODE) {
            if (!$this->isMigrateAvailable() || $this->isAlreadyWorked()) {
                return false;
            }
        }
        
        //-------------
        if (self::DEVELOPMENT_MODE) {
            $this->developmentPrepare();
        }
        //-------------

        try {
            // Start Migration
            $this->addStartProfilerData();

            // Settings migrated just after accounts. After settings is
            // migrated it disable synchronization for old m2e
            Mage::getModel('M2ePro/Migration_Objects_Settings')->process();
            $this->addStepProfilerData('Settings');

            //~~~~~~~~~~~~~~
            Mage::getModel('M2ePro/Migration_Objects_Accounts')->process();
            $this->addStepProfilerData('Accounts');

            Mage::getModel('M2ePro/Migration_Objects_Marketplaces')->process();
            $this->addStepProfilerData('Marketplaces');

            Mage::getModel('M2ePro/Migration_Objects_SynchronizationTemplates')->process();
            $this->addStepProfilerData('SynchronizationTemplates');

            Mage::getModel('M2ePro/Migration_Objects_SellingFormatTemplates')->process();
            $this->addStepProfilerData('SellingFormatTemplates');

            Mage::getModel('M2ePro/Migration_Objects_DescriptionTemplates')->process();
            $this->addStepProfilerData('DescriptionTemplates');

            Mage::getModel('M2ePro/Migration_Objects_ListingTemplates')->process();
            $this->addStepProfilerData('ListingTemplates');

            Mage::getModel('M2ePro/Migration_Objects_ListingTemplatesCalculatedShipping')->process();
            $this->addStepProfilerData('ListingTemplatesCalculatedShipping');

            Mage::getModel('M2ePro/Migration_Objects_ListingTemplatesPayments')->process();
            $this->addStepProfilerData('ListingTemplatesPayments');

            Mage::getModel('M2ePro/Migration_Objects_ListingTemplatesShippings')->process();
            $this->addStepProfilerData('ListingTemplatesShippings');

            Mage::getModel('M2ePro/Migration_Objects_ListingTemplatesSpecifics')->process();
            $this->addStepProfilerData('ListingTemplatesSpecifics');

            Mage::getModel('M2ePro/Migration_Objects_EbayItems')->process();
            $this->addStepProfilerData('EbayItems');

            Mage::getModel('M2ePro/Migration_Objects_Listings')->process();
            $this->addStepProfilerData('Listings');

            Mage::getModel('M2ePro/Migration_Objects_ListingsProducts')->process();
            $this->addStepProfilerData('ListingsProducts');

            Mage::getModel('M2ePro/Migration_Objects_Orders')->process();
            $this->addStepProfilerData('Orders');
            //~~~~~~~~~~~~~~
            
            // End Migration
            $this->addEndProfilerData();

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            if (!self::DEVELOPMENT_MODE) {
                $this->tempDbTable->clear();
            } else {
                $this->developmentComplete();
            }

            return false;
        }

        //-------------

        // End Temp DB Table
        if (!self::DEVELOPMENT_MODE) {
            $this->tempDbTable->clear();
            $this->setAlreadyWorked();
        } else {
            $this->developmentComplete();
        }

        return true;
    }

    // ########################################

    public function isMigrateAvailable()
    {
        return Mage::helper('M2ePro/Module')->isInstalledM2eLastVersion();
    }

    // ########################################

    public function isAlreadyWorked()
    {
        $status = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/migrate/', 'already_worked');
        if (is_null($status)) {
            $status = self::ALREADY_WORKED_NO;
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/migrate/', 'already_worked', $status);
        }
        return (bool)$status;
    }

    public function setAlreadyWorked()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/migrate/', 'already_worked', self::ALREADY_WORKED_YES);
    }

    // ########################################

    public function isUserInterfaceActiveNow()
    {
        return $this->isWizardUserInterfaceActiveNow() || $this->isCustomUserInterfaceActiveNow();
    }

    //-----------------------
    
    public function isWizardUserInterfaceActiveNow()
    {
        return Mage::getModel('M2ePro/Wizard')->isActive() &&
               Mage::getModel('M2ePro/Wizard')->getStatus() == Ess_M2ePro_Model_Wizard::STATUS_MIGRATION;
    }

    public function isCustomUserInterfaceActiveNow()
    {
        $status = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/migrate/', 'custom_user_interface');
        if (is_null($status)) {
            $status = self::CUSTOM_USER_INTERFACE_NO;
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/migrate/', 'custom_user_interface', $status);
        }
        return (bool)$status;
    }

    //-----------------------

    public function startCustomUserInterface()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/migrate/', 'custom_user_interface', self::CUSTOM_USER_INTERFACE_YES);
    }

    public function endCustomUserInterface()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/migrate/', 'custom_user_interface', self::CUSTOM_USER_INTERFACE_NO);
    }

    // ########################################

    private function addStartProfilerData()
    {
        $this->timestampStart = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $this->microtimeStart = microtime(true);
        $this->microtimeStep = microtime(true);
        
        $this->addProfilerData('Start Migration');
        $this->addProfilerData('----------------------------');
    }

    private function addStepProfilerData($stepTitle)
    {
        $this->addProfilerData($stepTitle.': '.round(microtime(true) - $this->microtimeStep,4));
        $this->microtimeStep = microtime(true);
    }

    private function addEndProfilerData()
    {
        $this->addProfilerData('----------------------------');
        $this->addProfilerData('Total time: '.round(microtime(true) - $this->microtimeStart,4));

        $this->timestampStart = 0;
        $this->microtimeStart = 0;
        $this->microtimeStep = 0;
    }

    //----------------------------------------

    private function addProfilerData($string)
    {
        $fileName = str_replace(array('-',' ',':'),'_',Mage::helper('M2ePro')->getDate($this->timestampStart));
        $logFileModel = Mage::getModel('M2ePro/LogFile',array('nameFile'=>$fileName,'folder'=>'migrate'));

        if (!$logFileModel->isExist()) {
            $logFileModel->create();
        }

        $logFileModel->addLine($string);
    }

    // ########################################

    private function developmentPrepare()
    {
        $this->tempDbTable->clear();

        $tables = Mage::helper('M2ePro/Module')->getMySqlTables();
        $mySqlWriteConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($tables as $table) {
            
            if ($table == 'ess_config' ||
                $table == 'm2epro_accounts' ||
                $table == 'm2epro_accounts_store_categories' ||
                $table == 'm2epro_config' ||
                $table == 'm2epro_dictionary_categories' ||
                $table == 'm2epro_dictionary_marketplaces' ||
                $table == 'm2epro_dictionary_shippings' ||
                $table == 'm2epro_dictionary_shippings_categories' ||
                $table == 'm2epro_marketplaces' ||
                $table == 'm2epro_migration_temp') {
                continue;
            }

            $tempTable = Mage::getSingleton('core/resource')->getTableName($table);
            $mySqlWriteConnection->delete($tempTable);
        }

        $this->tempDbTable->addValue('accounts.id',1,1);
        $this->tempDbTable->addValue('accounts.id',2,2);
    }

    private function developmentComplete()
    {
        $mySqlWriteConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_templates_attribute_sets');
        $mySqlWriteConnection->update($tempTable,array('attribute_set_id'=>9));
    }

    // ########################################
}