<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_LogsCleaning extends Mage_Core_Model_Abstract
{
    const LOG_LISTINGS = 'listings';
    const LOG_EBAY_LISTINGS = 'ebay_listings';
    const LOG_SYNCHRONIZATIONS = 'synchronizations';
        
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/LogsCleaning');
    }

    // ########################################

    public function cron()
    {
        $this->clearOldRecords(self::LOG_LISTINGS);
        $this->clearOldRecords(self::LOG_EBAY_LISTINGS);
        $this->clearOldRecords(self::LOG_SYNCHRONIZATIONS);
    }

    // ########################################

    public function saveSettings($log, $mode, $days)
    {
        $log = (string)$log;
        $mode = (int)$mode;
        $days = (int)$days;

        if ($log != self::LOG_LISTINGS &&
            $log != self::LOG_EBAY_LISTINGS &&
            $log != self::LOG_SYNCHRONIZATIONS) {
            return false;
        }

        if ($mode < 0 || $mode > 1) {
           $mode = 0;
        }

        if ($days <= 0) {
           $days = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.$log.'/','default');
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/'.$log.'/','mode', $mode);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/logs/cleaning/'.$log.'/','days', $days);

        return true;
    }

    // ########################################

    public function clearOldRecords($log)
    {
        $log = (string)$log;

        if ($log != self::LOG_LISTINGS &&
            $log != self::LOG_EBAY_LISTINGS &&
            $log != self::LOG_SYNCHRONIZATIONS) {
            return false;
        }

        $mode = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.$log.'/','mode');
        $days = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/logs/cleaning/'.$log.'/','days');

        $mode = (int)$mode;
        $days = (int)$days;

        if ($mode != 1) {
            return false;
        }

        $minTime = $this->getMinTime($days);
        $this->clearLogByMinTime($log,$minTime);

        return true;
    }

    public function clearAllLog($log)
    {
        $log = (string)$log;

        if ($log != self::LOG_LISTINGS &&
            $log != self::LOG_EBAY_LISTINGS &&
            $log != self::LOG_SYNCHRONIZATIONS) {
            return false;
        }

        $timestamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $minTime = Mage::helper('M2ePro')->getDate($timestamp+60*60*24*365*10);
        $this->clearLogByMinTime($log,$minTime);

        return true;
    }

    // ########################################

    private function clearLogByMinTime($log ,$minTime)
    {
        $table  = '';

        switch($log) {
            case self::LOG_LISTINGS:
                $table  = Mage::getResourceModel('M2ePro/ListingsLogs')->getMainTable();
                break;
            case self::LOG_EBAY_LISTINGS:
                $table  = Mage::getResourceModel('M2ePro/EbayListingsLogs')->getMainTable();
                break;
            case self::LOG_SYNCHRONIZATIONS:
                $table  = Mage::getResourceModel('M2ePro/Synchronization_Logs')->getMainTable();
                break;
        }

        $where = array(' `create_date` < ? OR `create_date` IS NULL ' => (string)$minTime);

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getModel('Core/Mysql4_Config')->getReadConnection();
        $connRead->delete($table, $where);


        $connRead->delete($table,$where);

        return true;
    }

    private function getMinTime($days)
    {
        $dateTimeArray = getdate(Mage::helper('M2ePro')->getCurrentGmtDate(true));

        $hours = $dateTimeArray['hours'];
        $minutes = $dateTimeArray['minutes'];
        $seconds = $dateTimeArray['seconds'];
        $month = $dateTimeArray['mon'];
        $day = $dateTimeArray['mday'];
        $year = $dateTimeArray['year'];

        $timeStamp = mktime($hours,$minutes,$seconds,$month,$day - $days, $year);

        return Mage::helper('M2ePro')->getDate($timeStamp);
    }

    // ########################################
}