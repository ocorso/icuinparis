<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ProductsChanges extends Mage_Core_Model_Abstract
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    const CREATOR_TYPE_OBSERVER = 1;
    const CREATOR_TYPE_SYNCHRONIZATION = 2;

    //####################################

	public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ProductsChanges');
    }

    //####################################

    public function addCreateAction($productId, $creatorType = self::CREATOR_TYPE_OBSERVER)
    {
         return $this->setOnlyAction($productId, self::ACTION_CREATE, $creatorType);
    }

    public function addDeleteAction($productId, $creatorType = self::CREATOR_TYPE_OBSERVER)
    {
         return $this->setOnlyAction($productId, self::ACTION_DELETE, $creatorType);
    }

    public function updateAttribute($productId , $attribute , $valueOld , $valueNew, $creatorType = self::CREATOR_TYPE_OBSERVER)
    {
         $tempChanges = Mage::getModel('M2ePro/ProductsChanges')
                                ->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->addFieldToFilter('action', self::ACTION_UPDATE)
                                ->addFieldToFilter('attribute', $attribute)
                                ->toArray();

         if ($tempChanges['totalRecords'] > 0) {

             if ($tempChanges['items'][0]['value_old'] == $valueNew) {

                  Mage::getModel('M2ePro/ProductsChanges')
                        ->setId($tempChanges['items'][0]['id'])
                        ->delete();

                  return true;

             } else if ($valueOld != $valueNew) {

                 $dataForUpdate = array( 'value_new' => $valueNew,
                                         'count_changes' => $tempChanges['items'][0]['count_changes']+1,
                                         'creator_type' => $creatorType );

                 Mage::getModel('M2ePro/ProductsChanges')
                         ->load($tempChanges['items'][0]['id'])
                         ->addData($dataForUpdate)
                         ->save()
                         ->getId();

                 return true;
             }

         } else if ($valueOld != $valueNew) {

             $dataForAdd = array( 'product_id' => $productId,
                                  'action' => self::ACTION_UPDATE,
                                  'attribute' => $attribute,
                                  'value_old' => $valueOld,
                                  'value_new' => $valueNew,
                                  'count_changes' => 1,
                                  'creator_type' => $creatorType );

             Mage::getModel('M2ePro/ProductsChanges')
                     ->setData($dataForAdd)
                     ->save()
                     ->getId();

             return true;
         }

         return false;
    }

    //-----------------------------------

    public function clearAll($creatorType = self::CREATOR_TYPE_OBSERVER, $maximumDate = NULL)
    {
        $tempCollection = Mage::getModel('M2ePro/ProductsChanges')->getCollection();
        $tempCollection->getSelect()->where("`creator_type` = ?", $creatorType);

        if (!is_null($maximumDate)) {
            $tempCollection->getSelect()->where("`update_date` <= '{$maximumDate}'");
        }
        
        foreach ($tempCollection->getItems() as $tempItem) {
            $tempItem->delete();
        }
    }

    //####################################

    public function getChangedListingsProductsByAttributes($attributes)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductsChanges')->getMainTable();
        $listingsProductsTable = Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable();

        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->distinct()
                             ->from(array('pc' => $productsChangesTable),array('pc_id'=>'id','pc_attribute'=>'attribute','pc_value_old'=>'value_old','pc_value_new'=>'value_new','pc_count_changes'=>'count_changes'))
                             ->join(array('lp' => $listingsProductsTable),'`pc`.`product_id` = `lp`.`product_id`','*')
                             ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductsChanges::ACTION_UPDATE)
                             ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        return Mage::getModel('Core/Mysql4_Config')
                            ->getReadConnection()
                            ->fetchAll($dbSelect);
    }

    public function getChangedListingsProductsVariationsOptionsByAttributes($attributes)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductsChanges')->getMainTable();
        $listingsProductsVariationsOptionsTable = Mage::getResourceModel('M2ePro/ListingsProductsVariationsOptions')->getMainTable();

        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->distinct()
                             ->from(array('pc' => $productsChangesTable),array('pc_id'=>'id','pc_attribute'=>'attribute','pc_value_old'=>'value_old','pc_value_new'=>'value_new','pc_count_changes'=>'count_changes'))
                             ->join(array('lpvo' => $listingsProductsVariationsOptionsTable),'`pc`.`product_id` = `lpvo`.`product_id`','*')
                             ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductsChanges::ACTION_UPDATE)
                             ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        return Mage::getModel('Core/Mysql4_Config')
                            ->getReadConnection()
                            ->fetchAll($dbSelect);
    }

    //####################################

    private function setOnlyAction($productId , $action, $creatorType = self::CREATOR_TYPE_OBSERVER)
    {
         $tempChanges = Mage::getModel('M2ePro/ProductsChanges')
                                ->getCollection()
                                ->addFieldToFilter('product_id', $productId)
                                ->toArray();

         foreach ($tempChanges['items'] as $item) {

                Mage::getModel('M2ePro/ProductsChanges')
                        ->setId($item['id'])
                        ->delete();
         }

         $dataForAdd = array( 'product_id' => $productId,
                              'action' => $action,
                              'creator_type' => $creatorType );

         Mage::getModel('M2ePro/ProductsChanges')
                     ->setData($dataForAdd)
                     ->save()
                     ->getId();

         return true;
    }

    //####################################
}