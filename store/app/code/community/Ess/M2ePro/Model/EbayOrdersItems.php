<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_EbayOrdersItems extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/EbayOrdersItems');
    }

    /**
     * Save items for eBay orders.
     * On update transaction - update items information
     *
     * @param  $eventId
     * @param  $itemsInfoList
     * @param bool $isNewEvent
     * @return bool update success or not
     */
    public function saveItems($eventId, $itemsInfoList, $isNewEvent = false)
    {
        foreach ($itemsInfoList as $singleItem) {

            if (isset($singleItem['variations']) && !is_string($singleItem['variations'])) {
                $singleItem['variations'] = serialize($singleItem['variations']);
            }

            $singleItem['ebay_order_id'] = $eventId;
            $transactionId = null;

            if (!$isNewEvent) {
                $exitingTransactionInfo = $this->getCollection()
                        ->addFieldToFilter('ebay_order_id', $eventId)
                        ->addFieldToFilter('transaction_id', $singleItem['transaction_id'])
                        ->addFieldToFilter('item_id', $singleItem['item_id'])
                        ->getData();

                if (count($exitingTransactionInfo) > 0) {
                    $transactionId = $exitingTransactionInfo[0]['id'];
                }
            }

            $this->setData($singleItem)->setId($transactionId)->save();
        }

        return true;
    }

    /**
     * Try to find transaction
     * @param  $transactionId
     * @param  $itemId
     * @return int|boolean false when not found, id of order when found
     */
    public function findExistingTransaction($transactionId, $itemId)
    {
        $exitingTransactionInfo = $this->getCollection()
                ->addFieldToFilter('transaction_id', $transactionId)
                ->addFieldToFilter('item_id', $itemId)
                ->getData();

        $ebayOrderId = false;
        if (count($exitingTransactionInfo) > 0) {
            $ebayOrderId = $exitingTransactionInfo[0]['ebay_order_id'];
        }

        return $ebayOrderId;
    }

    public function getVariations()
    {
        $variationInfo = $this->getData('variations');
        
        if (is_null($variationInfo) || $variationInfo == '') {
            return false;
        } else if (is_string($variationInfo)) {
            return unserialize($variationInfo);
        }

        return $variationInfo;
    }

    public function deleteItemsForOrder($orderIdForRemove)
    {
        $this->getResource()
                ->getReadConnection()
                ->delete($this->getResource()->getMainTable(),
                         array('ebay_order_id = ?' => $orderIdForRemove));
    }
}