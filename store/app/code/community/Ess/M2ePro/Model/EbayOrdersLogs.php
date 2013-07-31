<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_EbayOrdersLogs extends Mage_Core_Model_Abstract
{
    const MESSAGE_CODE_NOTICE = 0;
    const MESSAGE_CODE_WARNING = 1;
    const MESSAGE_CODE_ERROR = 2;

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/EbayOrdersLogs');
    }

    /**
     * Adding log message to specific order
     * 
     * @return void
     */
    public function addLogMessage($orderId, $message, $messageTrace = null,  $code = self::MESSAGE_CODE_NOTICE)
    {
        $logData = array(
            'order_id' => $orderId,
            'message' => $message,
            'message_trace' => $messageTrace,
            'code' => $code
        );
        
        $this->setData($logData)->setId(null)->save();

        return $this->getId();
    }

    public function getLogDateHtml()
    {
        if (!$this->getId()) {
            return false;
        }

        //        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(
        //            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
        //        );

        return Mage::helper('M2ePro')->gmtDateToTimezone($this->getCreateDate());
    }

    public function deleteLogsForOrder($orderIdForRemove)
    {
        $this->getResource()
                ->getReadConnection()
                ->delete($this->getResource()->getMainTable(),
                         array('order_id = ?' => $orderIdForRemove));
    }    
}