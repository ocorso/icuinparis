<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_License_Server extends Mage_Core_Model_Abstract
{
    const INTERVAL_UPDATE_STATUS = 3600;
    const INTERVAL_UPDATE_LOCK = 3600;
    const INTERVAL_UPDATE_MESSAGES = 3600;
    
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/License_Server');
    }

    // ########################################

    public function updateStatus($forceUpdate = false)
    {
        $forceUpdate = (bool)$forceUpdate;
        $cacheTimeKey = Mage::helper('M2ePro/Module')->getName().'_LICENSE_UPDATE_STATUS';

        $timeNextCheck = $this->getTimeNextCheck($cacheTimeKey);
        if (!$forceUpdate && Mage::helper('M2ePro')->getCurrentGmtDate(true) < $timeNextCheck) {
            return;
        }

        Mage::getModel('M2ePro/License_Model')->setDefaults();

        $data = Mage::getModel('M2ePro/Connectors_Api_Dispatcher')
                        ->processVirtual('license','get','status');

        Mage::getModel('M2ePro/License_Model')->setMode($data['mode']);

        if (isset($data['status'])) {
            Mage::getModel('M2ePro/License_Model')->setStatus($data['status']);
        } else {
            Mage::getModel('M2ePro/License_Model')->setStatus(Ess_M2ePro_Model_License_Model::STATUS_NONE);
        }
        
        if (isset($data['expired_date'])) {
            Mage::getModel('M2ePro/License_Model')->setExpiredDate($data['expired_date']);
        } else {
            Mage::getModel('M2ePro/License_Model')->setExpiredDate('');
        }
        
        if (isset($data['component'])) {
            Mage::getModel('M2ePro/License_Model')->setComponent($data['component']);
        } else {
            Mage::getModel('M2ePro/License_Model')->setComponent('');
        }

        if (isset($data['domain'])) {
            Mage::getModel('M2ePro/License_Model')->setDomain($data['domain']);
        } else {
            Mage::getModel('M2ePro/License_Model')->setDomain('');
        }

        if (isset($data['ip'])) {
            Mage::getModel('M2ePro/License_Model')->setIp($data['ip']);
        } else {
            Mage::getModel('M2ePro/License_Model')->setIp('');
        }

        if (isset($data['directory'])) {
            Mage::getModel('M2ePro/License_Model')->setDirectory($data['directory']);
        } else {
            Mage::getModel('M2ePro/License_Model')->setDirectory('');
        }

        $timeNextCheck = Mage::helper('M2ePro')->getCurrentGmtDate(true) + self::INTERVAL_UPDATE_STATUS;
        $this->setTimeNextCheck($cacheTimeKey, $timeNextCheck);
    }

    public function updateLock($forceUpdate = false)
    {
        $forceUpdate = (bool)$forceUpdate;
        $cacheTimeKey = Mage::helper('M2ePro/Module')->getName().'_LICENSE_UPDATE_LOCK';

        $timeNextCheck = $this->getTimeNextCheck($cacheTimeKey);
        if (!$forceUpdate && Mage::helper('M2ePro')->getCurrentGmtDate(true) < $timeNextCheck) {
            return;
        }

        Mage::getModel('M2ePro/License_Model')->setDefaults();

        $lock = Mage::getModel('M2ePro/Connectors_Api_Dispatcher')
                        ->processVirtual('domain','get','lock',array(),'lock');
        is_null($lock) && $lock = 0;

        Mage::getModel('M2ePro/License_Model')->setLock($lock);
       
        $timeNextCheck = Mage::helper('M2ePro')->getCurrentGmtDate(true) + self::INTERVAL_UPDATE_LOCK;
        $this->setTimeNextCheck($cacheTimeKey, $timeNextCheck);
    }

    public function updateMessages($forceUpdate = false)
    {
        $forceUpdate = (bool)$forceUpdate;
        $cacheTimeKey = Mage::helper('M2ePro/Module')->getName().'_LICENSE_UPDATE_MESSAGES';

        $timeNextCheck = $this->getTimeNextCheck($cacheTimeKey);
        if (!$forceUpdate && Mage::helper('M2ePro')->getCurrentGmtDate(true) < $timeNextCheck) {
            return;
        }

        Mage::getModel('M2ePro/License_Model')->setDefaults();

        $messages = Mage::getModel('M2ePro/Connectors_Api_Dispatcher')
                        ->processVirtual('messages','get','items',array(),'messages');
        is_null($messages) && $messages = array();

        Mage::getModel('M2ePro/License_Model')->setMessages($messages);

        $timeNextCheck = Mage::helper('M2ePro')->getCurrentGmtDate(true) + self::INTERVAL_UPDATE_MESSAGES;
        $this->setTimeNextCheck($cacheTimeKey, $timeNextCheck);
    }

    // ########################################

    private function getTimeNextCheck($cacheTimeKey)
    {
        $time = Mage::app()->getCache()->load($cacheTimeKey);

        if ($time !== false) {
            return (int)unserialize($time);
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) - 1;
    }

    private function setTimeNextCheck($cacheTimeKey, $time)
    {
        Mage::app()->getCache()->save(serialize($time), $cacheTimeKey, array(), 60*60*24*365);
    }

    // ########################################
}