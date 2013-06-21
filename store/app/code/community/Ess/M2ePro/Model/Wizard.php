<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Wizard extends Mage_Core_Model_Abstract
{
    const STATUS_NONE = 0;

    const STATUS_AUTO_SETTINGS = 1;
    const STATUS_LICENSE = 2;
    const STATUS_MARKETPLACES = 3;
    const STATUS_MIGRATION = 4;
    const STATUS_ACCOUNTS = 5;
    const STATUS_SYNCHRONIZATION = 6;

    const STATUS_SKIP = 99;
    const STATUS_COMPLETE = 100;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Wizard');
    }

    //####################################

    public function getStatus()
    {
        $status = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/wizard/', 'status');
        if (is_null($status)) {
            $this->setStatus(Ess_M2ePro_Model_Wizard::STATUS_NONE);
            $status = Ess_M2ePro_Model_Wizard::STATUS_NONE;
        }
        return (int)$status;
    }

    public function setStatus($status = self::STATUS_NONE)
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/wizard/', 'status', (int)$status);
    }

    //-----------------------

    public function isWelcome()
    {
        return in_array($this->getStatus(),array(self::STATUS_NONE));
    }

    public function isActive()
    {
        return !$this->isFinished() && !$this->isWelcome();
    }

    public function isFinished()
    {
        return in_array($this->getStatus(),array(self::STATUS_COMPLETE,self::STATUS_SKIP));
    }

    //####################################

    public function clearMenuCache()
    {
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                                       array(Mage_Adminhtml_Block_Page_Menu::CACHE_TAGS));
    }

    //####################################
}