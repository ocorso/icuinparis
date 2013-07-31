<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Categories_Tree extends Mage_Adminhtml_Block_Catalog_Category_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(false);
        $this->setTemplate('M2ePro/listings/tree.phtml');
    }

    public function getCategoryCollection()
    {
        $listingData = Mage::registry('M2ePro_data');

        $storeId = (int)$listingData['store_id'];

        $collection = $this->getData('category_collection');

        if (is_null($collection)) {

            $collection = Mage::getModel('catalog/category')->getCollection();

            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount(true)
                ->setStoreId($storeId);

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());

        $ccpTable  = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
        $cpeTable  = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');

        $listingData = Mage::registry('M2ePro_data');

        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from(array('ccp'=>$ccpTable),new Zend_Db_Expr('DISTINCT `ccp`.`product_id`'))
                             ->join(array('cpe'=>$cpeTable),'`cpe`.`entity_id` = `ccp`.`product_id` AND `cpe`.`attribute_set_id` = '.(int)$listingData['attribute_set_id'],array())
                             ->where("`ccp`.`category_id` = ?",(int)$node->getId());
        
        // Hide products others listings
        //----------------------------
        $hideProductsOthersListings = (bool)$listingData['hide_products_others_listings'];
        if ($hideProductsOthersListings) {

            $dbSelect2 = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                 ->select()
                                 ->from(Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable(),new Zend_Db_Expr('DISTINCT `product_id`'));

            $dbSelect->where('`ccp`.`product_id` NOT IN ('.$dbSelect2->__toString().')');
        }
        //----------------------------

        // Add attribute set filter
        //----------------------------
        $sqlQuery = " SELECT count(`rez`.`product_id`) as `count_products`
                      FROM ( ".$dbSelect->__toString()." ) as `rez` ";

        $countProducts = Mage::getModel('Core/Mysql4_Config')
                                    ->getReadConnection()
                                    ->fetchOne($sqlQuery);

        $result .= ' (' . (int)$countProducts . ')';

        return $result;
    }

    public function getTreeJson($parenNodeCategory=null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory, 6));
        $json = Zend_Json::encode(isset($rootArray['children']) ? $rootArray['children'] : array());
        return $json;
    }

    public function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Varien_Data_Tree_Node($node, 'entity_id', new Varien_Data_Tree);
        }

        $item = array();
        $item['text'] = ''.$this->buildNodeName($node). '';

        //$rootForStores = Mage::getModel('core/store')->getCollection()->loadByCategoryIds(array($node->getEntityId()));
        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id']  = $node->getId();
        $item['store_id']  = (int) $this->getStore()->getId();
        // $item['path'] = $node->getData('path');

        // $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = false;
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = $allowMove && (($node->getLevel()==1 && $rootForStores) ? false : true);

        if ((int)$node->getChildrenCount()>0) {
            $item['children'] = array();
        }

        $isParent = false;

        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level+1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }
}