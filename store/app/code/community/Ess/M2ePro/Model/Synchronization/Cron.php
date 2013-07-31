<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Cron extends Mage_Core_Model_Abstract
{
    const INTERVAL = 300;
    
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Synchronization_Cron');
    }

    //####################################

    public function everyFiveMinutes()
    {
        if (!Mage::getModel('M2ePro/Wizard')->isFinished()) {
            return;
        }

        Mage::helper('M2ePro/Exception')->setFatalErrorHandler();
        
        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process( array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Tasks::ORDERS,
            Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS,
            Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES,
            Ess_M2ePro_Model_Synchronization_Tasks::EBAY_LISTINGS
        ),Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_CRON, array());

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'last_access', Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    //####################################

    public function isHaveUserAlert()
    {
        if (!(bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/notification/', 'mode')) {
            return false;
        }

        $cronLastAccessTime = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'last_access');

        if (is_null($cronLastAccessTime)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'last_access', Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $allowedInactiveHours = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/notification/', 'inactive_hours');

        if (Mage::helper('M2ePro')->getCurrentGmtDate(true) > (strtotime($cronLastAccessTime) + $allowedInactiveHours * 60*60)) {
            return true;
        }

        return false;
    }

    //####################################
}