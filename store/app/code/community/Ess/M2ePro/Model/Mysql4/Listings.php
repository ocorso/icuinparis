<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listings extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('M2ePro/Listings', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_null($object->getOrigData())) {
            $object->setData('create_date',Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        $object->setData('update_date',Mage::helper('M2ePro')->getCurrentGmtDate());

        return $this;
    }

    public function updateStatisticColumns()
    {
        $listingsProductsTable = Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable();

        $dbSelect1 = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingsProductsTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`");

        $dbSelect2 = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingsProductsTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`")
                             ->where("`status` = ?",(int)Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED);

        $dbSelect3 = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingsProductsTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`")
                             ->where("`status` != ?",(int)Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED);

        $dbSelect4 = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingsProductsTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`")
                             ->where("`status` = ?",(int)Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD);

        $query = "UPDATE `{$this->getMainTable()}`
                  SET `products_total_count` = (".$dbSelect1->__toString()."),
                      `products_listed_count` = (".$dbSelect2->__toString()."),
                      `products_inactive_count` = (".$dbSelect3->__toString()."),
                      `products_sold_count` =  (".$dbSelect4->__toString().")";

        $this->_getWriteAdapter()->query($query);
    }

    public function getListingsWhereIsProduct($productId)
    {
        $listingsProductsTable = Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable();
        $listingsProductsVariationsTable = Mage::getResourceModel('M2ePro/ListingsProductsVariations')->getMainTable();
        $listingsProductsVariationsOptionsTable = Mage::getResourceModel('M2ePro/ListingsProductsVariationsOptions')->getMainTable();

        $dbSelect = $this->_getWriteAdapter()
                             ->select()
                             ->from(array('l' => $this->getMainTable()),new Zend_Db_Expr('DISTINCT `l`.`id`'))
                             ->join(array('lp' => $listingsProductsTable),'`l`.`id` = `lp`.`listing_id`',array())
                             ->joinLeft(array('lpv' => $listingsProductsVariationsTable),'`lp`.`id` = `lpv`.`listing_product_id`',array())
                             ->joinLeft(array('lpvo' => $listingsProductsVariationsOptionsTable),'`lpv`.`id` = `lpvo`.`listing_product_variation_id`',array())
                             ->where("`lp`.`product_id` = ?",(int)$productId)
                             ->orWhere("`lpvo`.`product_id` IS NOT NULL AND `lpvo`.`product_id` = ?",(int)$productId);

        $result = $this->_getWriteAdapter()->fetchCol($dbSelect);

        return array_unique($result);
    }
}