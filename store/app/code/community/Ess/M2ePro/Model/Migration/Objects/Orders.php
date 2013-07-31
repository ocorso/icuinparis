<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_Orders extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_transactions_events';
    const TABLE_NAME_NEW = 'm2epro_ebay_orders';

    protected $_tableNameOldOrderItems;
    protected $_tableNameNewOrderItems;
    protected $_tableNameNewAccounts;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_Orders');

        $this->_tableNameOldOrderItems = Mage::getSingleton('core/resource')->getTableName('m2e_transactions_in_order');
        $this->_tableNameNewOrderItems = Mage::getResourceModel('M2ePro/Orders_OrderItem')->getMainTable();

        $this->_tableNameNewAccounts = Mage::getResourceModel('M2ePro/Accounts')->getMainTable();
    }

    // ########################################

    public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldOrderRow = $pdoStmt->fetch()) {

            $dbSelect = $this->mySqlReadConnection->select()
                                                  ->from($this->_tableNameOldOrderItems,'*')
                                                  ->where('`event_id` = ?',(int)$oldOrderRow['id']);

            $oldOrderItemsContent = $this->mySqlReadConnection->fetchAll($dbSelect);

            $accountId = $this->tempDbTable->getNewValue('accounts.id', (int)$oldOrderRow['account_id']);
            if ($accountId === false) {
                continue;
            }

            $dbSelect = $this->mySqlReadConnection->select()
                                                  ->from($this->_tableNameNewAccounts,'mode')
                                                  ->where('`id` = ?',(int)$accountId);

            $accountMode = $this->mySqlReadConnection->fetchOne($dbSelect);
            if ($accountMode === false) {
                continue;
            }

            if ($oldOrderRow['ebay_order_id'] === '') {
                $oldOrderRow['ebay_order_id'] = NULL;
            }

            $newOrderRow = array(
                'account_id' => (int)$accountId,
                'account_mode' => (int)$accountMode,
                'marketplace_id' => null,
                'magento_order_id' => (int)$oldOrderRow['order_id'],
                'amount_saved' => 0,
                'buyer_name' => (string)$oldOrderRow['customer_name'],
                'buyer_email' => (string)$oldOrderRow['buyer_email'],
                'buyer_userid' => (string)$oldOrderRow['buyer_username'],
                'shipping_type' => 'NotSpecified',
                'get_it_fast' => 0,
                'shipping_address' => (string)$oldOrderRow['shipping_address'],
                'created_date' => (string)$oldOrderRow['update_time'],
                'is_part_of_order' => (!is_null($oldOrderRow['ebay_order_id']) && count(explode('-', $oldOrderRow['ebay_order_id'])) <= 1) ? 0 : 1,
                'ebay_order_id' => $oldOrderRow['ebay_order_id'],
                'checkout_status' => ($oldOrderRow['ebay_order_status'] == 'Completed' || $oldOrderRow['checkout_status'] == 1) ? 1 : 0,
                'update_time' => $oldOrderRow['update_time'],
                'payment_time' => null,
                'payment_used' => $oldOrderRow['payment_used'],
                'payment_status_m2e_code' => (int)$oldOrderRow['payment_status'],

                'shipping_status' => $oldOrderRow['shipping_status'],
                'shipping_selected_service' => $oldOrderRow['shipping_title'],
                'shipping_selected_cost' => $oldOrderRow['shipping_cost'],
                'price' => 0,
                'currency' => $oldOrderRow['currency_id'],
                'sales_tax_amount' => 0, // this calculated from order items
            );

            $orderItems = array();
            $isSetTax = false;
            $orderItemTotalCost = 0;
            foreach ($oldOrderItemsContent as $oldItemRow) {
                $orderItemTotalCost += $oldItemRow['price'] * $oldItemRow['qty_sold'];
                $orderItem = array(
                    'ebay_order_id' => 0, // correct FK to created line set after insert main table
                    'item_id' => $oldItemRow['ebay_item_id'],
                    'transaction_id' => $oldItemRow['transaction_id'],
                    'product_id' => $oldItemRow['product_id'],
                    'store_id' => $oldOrderRow['store_id'],
                    'listing_type' => 'FixedPriceItem',
                    'buy_it_now_price' => 0,
                    'auto_pay' => 0,
                    'currency' => $oldOrderRow['currency_id'],
                    'item_sku' => null,
                    'item_title' => $oldItemRow['ebay_item_title'],
                    'item_condition_display_name' => null,
                    'qty_purchased' => $oldItemRow['qty_sold'],
                    'price' => $oldItemRow['price'],
                    'variations' => $oldItemRow['variations'],
                );
                if (!$isSetTax) {
                    $newOrderRow['sales_tax_percent'] = (float)$oldItemRow['tax_percent'];
                    $newOrderRow['sales_tax_state'] = (string)$oldItemRow['tax_state'];
                    $newOrderRow['sales_tax_shipping_included'] = (int)$oldItemRow['tax_shipping_include'];
                    $isSetTax = true;
                }
                $newOrderRow['sales_tax_amount'] += $oldItemRow['tax_ammount'];
                $orderItems[] = $orderItem;
                if (count($oldOrderItemsContent) == 1 && is_null($newOrderRow['ebay_order_id'])) {
                    $newOrderRow['ebay_order_id'] = $oldItemRow['ebay_item_id'] . '-' . $oldItemRow['transaction_id'];
                }
            }
            
            $newOrderRow['amount_paid'] = $orderItemTotalCost + $newOrderRow['shipping_selected_cost'];

            if (is_null($newOrderRow['ebay_order_id'])) {
                $newOrderRow['ebay_order_id'] = 0;
            }

            $this->mySqlWriteConnection->insert($this->tableNameNew, $newOrderRow);
            $newOrderId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew, 'id');
            foreach ($orderItems as &$newItemRow) {
                $newItemRow['ebay_order_id'] = $newOrderId;
                // save
                $this->mySqlWriteConnection->insert($this->_tableNameNewOrderItems, $newItemRow);
            }
        }
    }

    // ########################################
}