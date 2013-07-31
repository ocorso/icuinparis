<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'm2epropayment';

    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;

    protected $_infoBlockType = 'M2ePro/adminhtml_PaymentInfo';

    protected $_customTitle = null;

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Ess_M2ePro_Model_PaymentMethod
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $details = array(
            'ebay_payment_method' => $data->getEbayPaymentMethod(),
            'ebay_order_id' => $data->getEbayOrderId(),
            'external_transactions' => $data->getExternalTransactions()
        );

        $this->getInfoInstance()->setAdditionalData(serialize($details));

        return $this;
    }

    public function canUseForCountry($country)
    {
        $ebayPaymentData = Mage::registry('ebayPaymentData');

        if (is_null($ebayPaymentData)) {
            return false;
        }

        return parent::canUseForCountry($country);
    }
}