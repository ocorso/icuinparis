<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_OrderLog extends Mage_Core_Model_Abstract
{
    const MESSAGE_TYPE_SUCCESS = 0;
    const MESSAGE_TYPE_NOTICE  = 1;
    const MESSAGE_TYPE_ERROR   = 2;
    const MESSAGE_TYPE_WARNING = 3;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Orders_OrderLog');
    }

    // ########################################

    public function addSuccessMessage($orderId, $message, $exceptionTrace = NULL)
    {
        $this->addLogMessage($orderId, $message, self::MESSAGE_TYPE_SUCCESS, $exceptionTrace);
    }

    public function addNoticeMessage($orderId, $message, $exceptionTrace = NULL)
    {
        $this->addLogMessage($orderId, $message, self::MESSAGE_TYPE_NOTICE, $exceptionTrace);
    }

    public function addWarningMessage($orderId, $message, $exceptionTrace = NULL)
    {
        $this->addLogMessage($orderId, $message, self::MESSAGE_TYPE_WARNING, $exceptionTrace);
    }

    public function addErrorMessage($orderId, $message, $exceptionTrace = NULL)
    {
        $this->addLogMessage($orderId, $message, self::MESSAGE_TYPE_ERROR, $exceptionTrace);
    }

    // ########################################

    protected function addLogMessage($orderId, $message, $type, $exceptionTrace)
    {
        $logMessage = array(
            'order_id'      => (int)$orderId,
            'message'       => $message,
            'code'          => (int)$type,
            'message_trace' => $exceptionTrace
        );

        $this->setId(null)
             ->setData($logMessage)
             ->save();
    }

    // ########################################
}