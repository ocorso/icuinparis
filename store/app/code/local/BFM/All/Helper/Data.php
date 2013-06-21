<?php
class BFM_All_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getStoresAsArray()
    {
        $options = false;
        $stores = Mage::app()->getStores();

        if (count($stores) > 1) {
            foreach ($stores as $store)
            {
                $options [$store->getId()] = $store->getName();
            }
        }

        return $options;
    }

}
