<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_EbayItems extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_product_to_project';
    const TABLE_NAME_NEW = 'm2epro_ebay_items';

    protected $tableNameOldListings = '';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_EbayItems');
        
        $this->tableNameOldListings  = Mage::getSingleton('core/resource')->getTableName('m2e_listings');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection
                                ->select()
                                ->from(array('lp'=>$this->tableNameOld),array('id','item_id','product_id'))
                                ->joinLeft(array('l'=>$this->tableNameOldListings),'`lp`.`project_id` = `l`.`id`',array('store_id'=>'store'))
                                ->where('`lp`.`item_id` IS NOT NULL')
                                ->where('`lp`.`item_id` > 0')
                                ->where('`lp`.`item_is_relisted` = 0 OR TO_DAYS(NOW()) - TO_DAYS(`lp`.`action_time`) <= 90');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldEbayItem = $pdoStmt->fetch()) {

            $newEbayItem = array(
                'item_id' => (double)$oldEbayItem['item_id'],
                'product_id' => (int)$oldEbayItem['product_id'],
                'store_id' => is_null($oldEbayItem['store_id']) ? 0 : (int)$oldEbayItem['store_id']
            );

            $existEbayItem = $this->getLikeExistItem($newEbayItem,false);
            if (!is_null($existEbayItem)) {
                $this->tempDbTable->addValue('ebay_items.id',(int)$oldEbayItem['id'],(int)$existEbayItem['id']);
            } else {
                $newEbayItem['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $newEbayItem['update_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $this->mySqlWriteConnection->insert($this->tableNameNew,$newEbayItem);
                $newEbayItemId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');
                $this->tempDbTable->addValue('ebay_items.id',(int)$oldEbayItem['id'],(int)$newEbayItemId);
            }
        }
    }

    // ########################################
}