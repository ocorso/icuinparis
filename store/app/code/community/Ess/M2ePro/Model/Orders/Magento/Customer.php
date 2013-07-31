<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_Customer
{
    protected $_account = NULL;

    // ########################################

    public function setAccount(Ess_M2ePro_Model_Accounts $account)
    {
        $this->_account = $account;

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Accounts
     */
    public function getAccount()
    {
        if (is_null($this->_account) || !$this->_account->getId()) {
            throw new Exception('Account was not set.');
        }

        return $this->_account;
    }

    // ########################################

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function createCustomer(Mage_Core_Model_Store $store, array $addressInfo = array())
    {
        if (!count($addressInfo)) {
            throw new Exception('Address info was not set.');
        }

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->setId(null);

        $customer->setData('firstname', $addressInfo['firstname']);
        $customer->setData('lastname', $addressInfo['lastname']);
        $customer->setData('email', $addressInfo['email']);
        $customer->setData('password', $addressInfo['customer_password']);
        $customer->setData('confirmation', $addressInfo['confirm_password']);

        $account = $this->getAccount();

        if ($websiteId = $account->getData('orders_customer_new_website')) {
            $customer->setWebsiteId($websiteId);
        } else {
            if (!$store->getId()) {
                $customer->setWebsiteId(Mage::helper('M2ePro/Sales')->getDefaultWebsiteId());
            } else {
                $customer->setStore($store);
            }
        }

        if ($groupId = $account->getData('orders_customer_new_group')) {
            $customer->setGroupId($groupId);
        }

        if ($account->isCustomerNewAccountNotificationEnabled()) {
            $customer->sendNewAccountEmail('registered', '', $store->getId());
        }

        if ($account->isCustomerSubscribeToNewsletterEnabled()) {
            $customer->setIsSubscribed(1);
        }

        // Remove confirmation if required
        if ($customer->getConfirmation() && $customer->isConfirmationRequired()) {
            $customer->setConfirmation(null);
        }

        $customer->save();

        // Add customer address
        // ---------------------------
        $customerAddress = Mage::getModel('customer/address')->setData($addressInfo)
                                                             ->setCustomerId($customer->getId())
                                                             ->setIsDefaultBilling('1')
                                                             ->setIsDefaultShipping('1');

        $customerAddress->save();
        // --------------------------

        return $customer;
    }

    // ########################################
}