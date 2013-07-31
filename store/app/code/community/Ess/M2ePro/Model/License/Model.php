<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_License_Model extends Mage_Core_Model_Abstract
{
    const MODE_NONE = 0;
    const MODE_TRIAL = 1;
    const MODE_LIVE = 2;

    const STATUS_NONE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 2;
    const STATUS_CLOSED = 3;

    const LOCK_NO = 0;
    const LOCK_YES = 1;

    const MESSAGE_TYPE_NOTICE = 0;
    const MESSAGE_TYPE_ERROR = 1;
    const MESSAGE_TYPE_WARNING = 2;
    const MESSAGE_TYPE_SUCCESS = 3;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/License_Model');
    }

    // ########################################

    public function getKey()
    {
        $key = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','key');
        return !is_null($key) ? (string)$key : '';
    }

    public function setKey($key)
    {
        $key = strip_tags($key);
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','key',(string)$key);
        return true;
    }

    public function setKeyDefault()
    {
        $key = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','key');
        if (is_null($key)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','key','');
        }
    }

    //--------------------------

    public function getMode()
    {
        $mode = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','mode');

        if (is_null($mode) || $mode === false || $mode == '') {
            return self::MODE_NONE;
        }
        
        if ((int)$mode == self::MODE_NONE) {
            return self::MODE_NONE;
        }
        if ((int)$mode == self::MODE_TRIAL) {
            return self::MODE_TRIAL;
        }
        if ((int)$mode == self::MODE_LIVE) {
            return self::MODE_LIVE;
        }

        return self::MODE_NONE;
    }

    public function isNoneMode()
    {
        return $this->getMode() == self::MODE_NONE;
    }

    public function isTrialMode()
    {
        return $this->getMode() == self::MODE_TRIAL;
    }

    public function isLiveMode()
    {
        return $this->getMode() == self::MODE_LIVE;
    }

    public function setMode($mode)
    {
        $mode = (int)$mode;

        if ($mode != self::MODE_NONE && $mode != self::MODE_TRIAL && $mode != self::MODE_LIVE) {
            return false;
        }

        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','mode',$mode);
        return true;
    }

    public function setModeDefault()
    {
        $mode = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','mode');
        if (is_null($mode)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','mode',self::MODE_NONE);
        }
    }

    //--------------------------

    public function getStatus()
    {
        $status = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','status');

        if (is_null($status) || $status === false || $status == '') {
            return self::STATUS_NONE;
        }

        if ((int)$status == self::STATUS_NONE) {
            return self::STATUS_NONE;
        }
        if ((int)$status == self::STATUS_ACTIVE) {
            return self::STATUS_ACTIVE;
        }
        if ((int)$status == self::STATUS_SUSPENDED) {
            return self::STATUS_SUSPENDED;
        }
        if ((int)$status == self::STATUS_CLOSED) {
            return self::STATUS_CLOSED;
        }

        return self::STATUS_NONE;
    }

    public function isNoneStatus()
    {
        return $this->getStatus() == self::STATUS_NONE;
    }

    public function isActiveStatus()
    {
        return $this->getStatus() == self::STATUS_ACTIVE;
    }

    public function isSuspendedStatus()
    {
        return $this->getStatus() == self::STATUS_SUSPENDED;
    }

    public function isClosedStatus()
    {
        return $this->getStatus() == self::STATUS_CLOSED;
    }

    public function setStatus($status)
    {
        $status = (int)$status;

        if ($status != self::STATUS_NONE && $status != self::STATUS_ACTIVE &&
            $status != self::STATUS_SUSPENDED && $status != self::STATUS_CLOSED) {
            return false;
        }

        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','status',$status);
        return true;
    }

    public function setStatusDefault()
    {
        $status = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','status');
        if (is_null($status)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','status',self::STATUS_NONE);
        }
    }

    //--------------------------

    public function getTimeStampExpiredDate()
    {
        $date = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','expired_date');
        return is_null($date) || $date == '' ? Mage::helper('M2ePro')->getCurrentGmtDate(true)-60*60*24 : (int)strtotime($date);
    }

    public function getTextExpiredDate($withTime = false)
    {
        if ($withTime) {
            return Mage::helper('M2ePro')->gmtDateToTimezone($this->getTimeStampExpiredDate());
        } else {
            return Mage::helper('M2ePro')->gmtDateToTimezone($this->getTimeStampExpiredDate(),false,'Y-m-d');
        }
    }

    public function getIntervalBeforeExpiredDate()
    {
        $timeStampCurrentDate = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $timeStampExpDate = $this->getTimeStampExpiredDate();

        if ($timeStampExpDate <= $timeStampCurrentDate) {
            return 0;
        }

        return $timeStampExpDate - $timeStampCurrentDate;
    }

    public function isExpiredDate()
    {
        return $this->getIntervalBeforeExpiredDate() == 0;
    }

    public function setExpiredDate($date)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','expired_date',(string)$date);
    }

    public function setExpiredDateDefault()
    {
        $date = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','expired_date');
        if (is_null($date)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','expired_date','');
        }
    }

    // ########################################

    public function getComponent()
    {
        $component = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','component');
        return !is_null($component) ? (string)$component : '';
    }

    public function setComponent($component)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','component',(string)$component);
        return true;
    }

    public function setComponentDefault()
    {
        $component = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','component');
        if (is_null($component)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','component','');
        }
    }

    //--------------------------

    public function getDomain()
    {
        $domain = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain');
        return !is_null($domain) ? (string)$domain : '';
    }

    public function setDomain($domain)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain',(string)$domain);
        return true;
    }

    public function setDomainDefault()
    {
        $domain = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain');
        if (is_null($domain)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain','');
        }
    }

    //--------------------------

    public function getIp()
    {
        $ip = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip');
        return !is_null($ip) ? (string)$ip : '';
    }

    public function setIp($ip)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip',(string)$ip);
        return true;
    }

    public function setIpDefault()
    {
        $ip = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip');
        if (is_null($ip)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip','');
        }
    }

    //--------------------------

    public function getDirectory()
    {
        $directory = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory');
        return !is_null($directory) ? (string)$directory : '';
    }

    public function setDirectory($directory)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory',(string)$directory);
        return true;
    }

    public function setDirectoryDefault()
    {
        $directory = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory');
        if (is_null($directory)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory','');
        }
    }

    // ########################################

    public function getLock()
    {
        $lock = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock');

        if (is_null($lock) || $lock === false || $lock == '') {
            return self::LOCK_NO;
        }

        if ((int)$lock == self::LOCK_NO) {
            return self::LOCK_NO;
        }
        if ((int)$lock == self::LOCK_YES) {
            return self::LOCK_YES;
        }

        return self::LOCK_NO;
    }

    public function isLock()
    {
        return $this->getLock() == self::LOCK_YES;
    }

    public function setLock($lock)
    {
        $lock = (int)$lock;

        if ($lock != self::LOCK_NO && $lock != self::LOCK_YES) {
            return false;
        }

        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock',$lock);
        return true;
    }

    public function setLockDefault()
    {
        $lock = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock');
        if (is_null($lock)) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/server/','lock',self::LOCK_NO);
        }
    }

    //--------------------------

    public function getMessages()
    {
        $messages = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages');
        return !is_null($messages) && $messages != '' ? (array)json_decode((string)$messages,true) : array();
    }

    public function setMessages(array $messages)
    {
        Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages',json_encode($messages));
        return true;
    }

    public function setMessagesDefault()
    {
        $messages = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages');
        if (is_null($messages)) {
           Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue('/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages',json_encode(array()));
        }
    }

    // ########################################

    public function setDefaults()
    {
        $this->setKeyDefault();
        $this->setModeDefault();
        $this->setStatusDefault();
        $this->setExpiredDateDefault();

        $this->setDomainDefault();
        $this->setIpDefault();
        $this->setDirectoryDefault();

        $this->setLockDefault();
        $this->setMessagesDefault();
    }

    // ########################################
}