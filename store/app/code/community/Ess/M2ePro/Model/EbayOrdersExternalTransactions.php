<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_EbayOrdersExternalTransactions extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/EbayOrdersExternalTransactions');
    }

    /**
     * Save external transactions for eBay orders.
     * On update eBay Order - update
     *
     * @param  $eventId
     * @param  $extTransactionsList
     * @param bool $isNewEvent
     * @return bool update success or not
     */
    public function saveItems($eventId, $extTransactionsList, $isNewEvent = false)
    {
        if ($extTransactionsList == array() || $extTransactionsList == null) {
            return false;
        }

        foreach ($extTransactionsList as $singleExtTransaction) {

            $singleExtTransaction['order_id'] = $eventId;
            $extTransactionId = null;
            
            if (!$isNewEvent) {
                $exitingExtTransactionInfo = $this->getCollection()
                        ->addFieldToFilter('order_id', $eventId)
                        ->addFieldToFilter('ebay_id', $singleExtTransaction['ebay_id'])
                        ->getData();

                if (count($exitingExtTransactionInfo) > 0) {
                    $extTransactionId = $exitingExtTransactionInfo[0]['id'];
                }
            }

            $this->setData($singleExtTransaction)->setId($extTransactionId)->save();
        }

        return true;
    }

    public function deleteTransactionForOrder($orderIdForRemove)
    {
        $this->getResource()
                ->getReadConnection()
                ->delete($this->getResource()->getMainTable(),
                         array('order_id = ?' => $orderIdForRemove));
    }
}