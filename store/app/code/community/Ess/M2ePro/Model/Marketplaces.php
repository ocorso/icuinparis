<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Marketplaces extends Mage_Core_Model_Abstract
{
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;
    
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Marketplaces');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_Marketplaces
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Marketplace does not exist. Probably it was deleted.');
        }

        return $this;
    }
    
    /**
     * @throws LogicException
     * @param  int $listingTemplateId
     * @return Ess_M2ePro_Model_Marketplaces
     */
    public function loadByListingTemplate($listingTemplateId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsTemplates')->load($listingTemplateId);

         if (is_null($tempModel->getId())) {
             throw new Exception('General template does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('marketplace_id'));
    }

    /**
     * @throws LogicException
     * @param  int $ebayListingId
     * @return Ess_M2ePro_Model_Marketplaces
     */
    public function loadByEbayListing($ebayListingId)
    {
         $tempModel = Mage::getModel('M2ePro/EbayListings')->load($ebayListingId);

         if (is_null($tempModel->getId())) {
             throw new Exception('3rd Party Listing does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('marketplace_id'));
    }

    // ########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->getId()) {
            return false;
        }

        return (bool)Mage::getModel('M2ePro/ListingsTemplates')
                                ->getCollection()
                                ->addFieldToFilter('marketplace_id', $this->getId())
                                ->getSize();
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $categoriesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_categories');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($categoriesTable,array('marketplace_id = ?'=>$this->getId()));

        $marketplacesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_marketplaces');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($marketplacesTable,array('marketplace_id = ?'=>$this->getId()));

        $shippingsTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_shippings');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($shippingsTable,array('marketplace_id = ?'=>$this->getId()));

        $shippingsCategoriesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_shippings_categories');
        Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                ->delete($shippingsCategoriesTable,array('marketplace_id = ?'=>$this->getId()));

        $listingsTemplates = $this->getListingsTemplates(true);
        foreach ($listingsTemplates as $listingTemplate) {
            $listingTemplate->deleteInstance();
        }
        
        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListingsTemplates($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsTemplates')->getCollection();
        $tempCollection->addFieldToFilter('marketplace_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/ListingsTemplates')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public function isStatusEnabled()
    {
        return $this->getStatus() == self::STATUS_ENABLE;
    }

    // ########################################

    public function getCode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('code');
    }

    public function getTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('title');
    }

    public function getUrl()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('url');
    }

    public function getStatus()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('status');
    }

    public function getGroupTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('group_title');
    }

    // ########################################

    /**
     * Load instance by marketplace code and return Id
     *
     * @param  string $code marketplace code (US, UK,...)
     * @return int id of marketplace
     */
    public function getIdByCode($code)
    {
        return $this->load($code, "code")->getId();
    }

    // ########################################

    public function getCategoriesVersion()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('categories_version');
    }

    public function getCategory($categoryId)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_categories');

        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from($tableCategories,'*')
                             ->where('`marketplace_id` = ?',(int)$this->getId())
                             ->where('`category_id` = ?',(int)$categoryId);

        $categories = Mage::getModel('Core/Mysql4_Config')
                                ->getReadConnection()
                                ->fetchAll($dbSelect);

        return count($categories) > 0 ? $categories[0] : array();
    }

    public function getChildCategories($parentId)
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tableCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_categories');

        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from($tableCategories,array('category_id','title','is_leaf'))
                             ->where('`marketplace_id` = ?',(int)$this->getId())
                             ->where('`parent_id` = ?',(int)$parentId)
                             ->order(array('title ASC'));

        $categories = Mage::getModel('Core/Mysql4_Config')
                                ->getReadConnection()
                                ->fetchAll($dbSelect);

        return $categories;
    }

    // ########################################
}