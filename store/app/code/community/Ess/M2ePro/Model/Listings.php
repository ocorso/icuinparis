<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listings extends Mage_Core_Model_Abstract
{
    const SYNCHRONIZATION_START_TYPE_NEVER       = 0;
    const SYNCHRONIZATION_START_TYPE_IMMEDIATELY = 1;
    const SYNCHRONIZATION_START_TYPE_THROUGH     = 2;
    const SYNCHRONIZATION_START_TYPE_DATE        = 3;

    const SYNCHRONIZATION_START_THROUGH_METRIC_NONE    = 0;
    const SYNCHRONIZATION_START_THROUGH_METRIC_MINUTES = 1;
    const SYNCHRONIZATION_START_THROUGH_METRIC_HOURS   = 2;
    const SYNCHRONIZATION_START_THROUGH_METRIC_DAYS    = 3;

    const SYNCHRONIZATION_STOP_TYPE_NEVER   = 0;
    const SYNCHRONIZATION_STOP_TYPE_THROUGH = 1;
    const SYNCHRONIZATION_STOP_TYPE_DATE    = 2;

    const SYNCHRONIZATION_STOP_THROUGH_METRIC_NONE    = 0;
    const SYNCHRONIZATION_STOP_THROUGH_METRIC_MINUTES = 1;
    const SYNCHRONIZATION_STOP_THROUGH_METRIC_HOURS   = 2;
    const SYNCHRONIZATION_STOP_THROUGH_METRIC_DAYS    = 3;

    const SYNCHRONIZATION_STATUS_INACTIVE = 0;
    const SYNCHRONIZATION_STATUS_ACTIVE   = 1;

    const SYNCHRONIZATION_ALREADY_START_NO  = 0;
    const SYNCHRONIZATION_ALREADY_START_YES = 1;

    const SYNCHRONIZATION_ALREADY_STOP_NO  = 0;
    const SYNCHRONIZATION_ALREADY_STOP_YES = 1;

    const SOURCE_PRODUCTS_CUSTOM     = 1;
    const SOURCE_PRODUCTS_CATEGORIES = 2;

    const CATEGORIES_ADD_ACTION_NONE     = 0;
    const CATEGORIES_ADD_ACTION_ADD      = 1;
    const CATEGORIES_ADD_ACTION_ADD_LIST = 2;

    const CATEGORIES_DELETE_ACTION_NONE        = 0;
    const CATEGORIES_DELETE_ACTION_STOP        = 1;
    const CATEGORIES_DELETE_ACTION_STOP_REMOVE = 2;

    const HIDE_PRODUCTS_OTHERS_LISTINGS_NO  = 0;
    const HIDE_PRODUCTS_OTHERS_LISTINGS_YES = 1;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_SellingFormatTemplates
     */
    private $_sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_ListingsTemplates
     */
    private $_listingTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_DescriptionsTemplates
     */
    private $_descriptionTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_SynchronizationsTemplates
     */
    private $_synchronizationTemplateModel = NULL;

    // ########################################
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listings');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_Listings
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingProductId
     * @return Ess_M2ePro_Model_Listings
     */
    public function loadByListingProduct($listingProductId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsProducts')->load($listingProductId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Listing product does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('listing_id'));
    }

    /**
     * @throws LogicException
     * @param  int $listingProductId
     * @return Ess_M2ePro_Model_Listings
     */
    public function loadByListingCategory($listingCategoryId)
    {
         $tempModel = Mage::getModel('M2ePro/ListingsCategories')->load($listingCategoryId);

         if (is_null($tempModel->getId())) {
             throw new Exception('Listing category does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('listing_id'));
    }

    /**
     * @throws LogicException
     * @param  int $sellingFormatTemplateId
     * @return Ess_M2ePro_Model_Listings
     */
    public function loadBySellingFormatTemplate($sellingFormatTemplateId)
    {
        $this->load($sellingFormatTemplateId,'selling_format_template_id');

        if (is_null($this->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
        }

        return $this;
    }
    
    /**
     * @throws LogicException
     * @param  int $listingTemplateId
     * @return Ess_M2ePro_Model_Listings
     */
    public function loadByListingTemplate($listingTemplateId)
    {
        $this->load($listingTemplateId,'listing_template_id');

        if (is_null($this->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $descriptionTemplateId
     * @return Ess_M2ePro_Model_Listings
     */
    public function loadByDescriptionTemplate($descriptionTemplateId)
    {
        $this->load($descriptionTemplateId,'description_template_id');

        if (is_null($this->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
        }

        return $this;
    }
    
    /**
     * @throws LogicException
     * @param  int $synchronizationTemplateId
     * @return Ess_M2ePro_Model_Listings
     */
    public function loadBySynchronizationTemplate($synchronizationTemplateId)
    {
        $this->load($synchronizationTemplateId,'synchronization_template_id');

        if (is_null($this->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
        }

        return $this;
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

        return (bool)Mage::getModel('M2ePro/ListingsProducts')
                            ->getCollection()
                            ->addFieldToFilter('listing_id', $this->getId())
                            ->addFieldToFilter('status', Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED)
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

        $listingsProducts = $this->getListingsProducts(true);
        foreach ($listingsProducts as $listingProduct) {
            $listingProduct->deleteInstance();
        }

        $listingsCategories = $this->getListingsCategories(true);
        foreach ($listingsCategories as $listingCategory) {
            $listingCategory->deleteInstance();
        }

        Mage::getModel('M2ePro/ListingsLogs')
                ->addListingMessage( $this->getId(),
                                     Ess_M2ePro_Model_LogsBase::INITIATOR_UNKNOWN,
                                     NULL,
                                     Ess_M2ePro_Model_ListingsLogs::ACTION_DELETE_LISTING,
                                     // Parser hack -> Mage::helper('M2ePro')->__('Listing was successfully deleted');
                                     'Listing was successfully deleted',
                                     Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                     Ess_M2ePro_Model_ListingsLogs::PRIORITY_HIGH );

        $this->_sellingFormatTemplateModel = NULL;
        $this->_listingTemplateModel = NULL;
        $this->_descriptionTemplateModel = NULL;
        $this->_synchronizationTemplateModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_SellingFormatTemplates
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->_sellingFormatTemplateModel)) {
            $this->_sellingFormatTemplateModel = Mage::getModel('M2ePro/SellingFormatTemplates')
                 ->loadInstance($this->getData('selling_format_template_id'));
        }

        return $this->_sellingFormatTemplateModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_SellingFormatTemplates $instance
     * @return void
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_SellingFormatTemplates $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->_sellingFormatTemplateModel = $instance;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_ListingsTemplates
     */
    public function getListingTemplate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->_listingTemplateModel)) {
            $this->_listingTemplateModel = Mage::getModel('M2ePro/ListingsTemplates')
                                            ->loadInstance($this->getData('listing_template_id'));
        }

        return $this->_listingTemplateModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_ListingsTemplates $instance
     * @return void
     */
    public function setListingTemplate(Ess_M2ePro_Model_ListingsTemplates $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->_listingTemplateModel = $instance;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_DescriptionsTemplates
     */
    public function getDescriptionTemplate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->_descriptionTemplateModel)) {
            $this->_descriptionTemplateModel = Mage::getModel('M2ePro/DescriptionsTemplates')
                 ->loadInstance($this->getData('description_template_id'));
        }

        return $this->_descriptionTemplateModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_DescriptionsTemplates $instance
     * @return void
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_DescriptionsTemplates $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->_descriptionTemplateModel = $instance;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_SynchronizationsTemplates
     */
    public function getSynchronizationTemplate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->_synchronizationTemplateModel)) {
            $this->_synchronizationTemplateModel = Mage::getModel('M2ePro/SynchronizationsTemplates')
                                                    ->loadInstance($this->getData('synchronization_template_id'));
        }

        return $this->_synchronizationTemplateModel;
    }

    /**
     * @throws LogicException
     * @param Ess_M2ePro_Model_SynchronizationsTemplates $instance
     * @return void
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_SynchronizationsTemplates $instance)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->_synchronizationTemplateModel = $instance;
    }

    // ########################################

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListingsProducts($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsProducts')->getCollection();
        $tempCollection->addFieldToFilter('listing_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $tempInstance = Mage::getModel('M2ePro/ListingsProducts')
                                        ->loadInstance($item['id']);
                $tempInstance->setListing($this);
                $resultArray[] = $tempInstance;
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListingsCategories($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/ListingsCategories')->getCollection();
        $tempCollection->addFieldToFilter('listing_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }

        $tempCollection->getSelect()
                       ->joinLeft(
                           array('cc' => Mage::getSingleton('core/resource')->getTableName('catalog/category')),
                           '`main_table`.`category_id` = `cc`.`entity_id`',
                           array('path_ids'=>'path')
                       );

        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $resultArray[] = Mage::getModel('M2ePro/ListingsCategories')
                                        ->loadInstance($item['id']);
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public function getTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('title');
    }
    
    public function getAttributeSet()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('attribute_set_id');
    }

    public function getStoreId()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return (int)$this->getData('store_id');
    }

    public function isSourceProducts()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('source_products') == self::SOURCE_PRODUCTS_CUSTOM;
    }

    public function isSourceCategories()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('source_products') == self::SOURCE_PRODUCTS_CATEGORIES;
    }

    public function isHideProductsOthersListings()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('hide_products_others_listings') != self::HIDE_PRODUCTS_OTHERS_LISTINGS_NO;
    }

    // ########################################

    public function isSynchronizationNowRun()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->isSynchronizationAlreadyStart() && !$this->isSynchronizationAlreadyStop();
    }

    //-------------------------------

    public function getSynchronizationTimestampStart()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if ($this->getData('synchronization_start_type') == self::SYNCHRONIZATION_START_TYPE_IMMEDIATELY) {
            return strtotime($this->getData('create_date'));
        }
        
        if ($this->getData('synchronization_start_type') == self::SYNCHRONIZATION_START_TYPE_THROUGH) {

            $interval = 60;
            if ($this->getData('synchronization_start_through_metric') == self::SYNCHRONIZATION_START_THROUGH_METRIC_DAYS) {
                $interval = 60*60*24;
            }
            if ($this->getData('synchronization_start_through_metric') == self::SYNCHRONIZATION_START_THROUGH_METRIC_HOURS) {
                $interval = 60*60;
            }
            if ($this->getData('synchronization_start_through_metric') == self::SYNCHRONIZATION_START_THROUGH_METRIC_MINUTES) {
                $interval = 60;
            }
            return strtotime($this->getData('create_date')) + ($interval * $this->getData('synchronization_start_through_value'));
        }

        if ($this->getData('synchronization_start_type') == self::SYNCHRONIZATION_START_TYPE_DATE) {
            return strtotime($this->getData('synchronization_start_date'));
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) + 60*60*24*365*10;
    }

    public function getSynchronizationTimestampStop()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if ($this->getData('synchronization_stop_type') == self::SYNCHRONIZATION_STOP_TYPE_THROUGH) {
            $interval = 60*60*24;
            if ($this->getData('synchronization_stop_through_metric') == self::SYNCHRONIZATION_STOP_THROUGH_METRIC_HOURS) {
                $interval = 60*60;
            }
            if ($this->getData('synchronization_stop_through_metric') == self::SYNCHRONIZATION_STOP_THROUGH_METRIC_MINUTES) {
                $interval = 60;
            }
            return $this->getSynchronizationTimestampStart() + ($interval * $this->getData('synchronization_stop_through_value'));
        }

        if ($this->getData('synchronization_stop_type') == self::SYNCHRONIZATION_STOP_TYPE_DATE) {
            return strtotime($this->getData('synchronization_stop_date'));
        }

        return strtotime($this->getData('create_date')) + 60*60*24*365*10;
    }

    //-------------------------------

    public function isSynchronizationAlreadyStart()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('synchronization_already_start') == self::SYNCHRONIZATION_ALREADY_START_YES;
    }

    public function isSynchronizationAlreadyStop()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        return $this->getData('synchronization_already_stop') == self::SYNCHRONIZATION_ALREADY_STOP_YES;
    }

    //-------------------------------

    public function isSynchronizationOnlyStart()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->getData('is_only_start')))
            return false;

        if (!$this->getData('is_only_start'))
            return false;

        return true;
    }

    public function isSynchronizationOnlyStop()
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (is_null($this->getData('is_only_stop')))
            return false;

        if (!$this->getData('is_only_stop'))
            return false;

        return true;
    }

    //-------------------------------

    public function setSynchronizationAlreadyStart($value = true)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (!in_array((int)$value,array(self::SYNCHRONIZATION_ALREADY_START_YES,self::SYNCHRONIZATION_ALREADY_START_NO)))
            return false;

        $this->setData('synchronization_already_start', (int)$value)->save();
        return true;
    }

    public function setSynchronizationAlreadyStop($value = true)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        if (!in_array((int)$value,array(self::SYNCHRONIZATION_ALREADY_STOP_YES,self::SYNCHRONIZATION_ALREADY_STOP_NO)))
            return false;

        $this->setData('synchronization_already_stop', (int)$value)->save();
        return true;
    }

    //-------------------------------

    public function setSynchronizationOnlyStart($value = true)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->setData('is_only_start', $value);
        return true;
    }

    public function setSynchronizationOnlyStop($value = true)
    {
        if (is_null($this->getId())) {
             throw new Exception('Method require loaded instance first');
        }

        $this->setData('is_only_stop', $value);
        return true;
    }

    // ########################################

    public function addProduct($productId)
    {
        // Check already exist product
        //----------------------------
        if ($this->hasProduct($productId)) {
            return false;
        }
        //----------------------------

        // Add attribute set filter
        //----------------------------
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from(Mage::getSingleton('core/resource')->getTableName('catalog_product_entity'),new Zend_Db_Expr('DISTINCT `entity_id`'))
                             ->where('`entity_id` = ?',(int)$productId)
                             ->where('`attribute_set_id` = ?',(int)$this->getAttributeSet());

        $productArray = Mage::getModel('Core/Mysql4_Config')
                                        ->getReadConnection()
                                        ->fetchCol($dbSelect);

        if (count($productArray) <= 0) {
            return false;
        }
        //----------------------------

        // Hide products others listings
        //----------------------------
        if ($this->isHideProductsOthersListings()) {

            $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                         ->select()
                                         ->from(Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable(),new Zend_Db_Expr('DISTINCT `product_id`'))
                                         ->where('`product_id` = ?',(int)$productId);
            
            $productArray = Mage::getModel('Core/Mysql4_Config')
                                        ->getReadConnection()
                                        ->fetchCol($dbSelect);

            if (count($productArray) > 0) {
                return false;
            }
        }
        //----------------------------

        $data = array(
            'listing_id' => $this->getId(),
            'product_id' => $productId,
            'status'     => Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED,
            'status_changer' => Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_UNKNOWN
        );
        
        $listingProductId = Mage::getModel('M2ePro/ListingsProducts')
                                    ->setData($data)
                                    ->save()
                                    ->getId();

        $listingProductTemp = Mage::getModel('M2ePro/ListingsProducts')->loadInstance($listingProductId);
        Mage::helper('M2ePro/Variations')->updateVariations($listingProductTemp);

        // Add message for listing log
        //------------------------------
        Mage::getModel('M2ePro/ListingsLogs')
            ->addProductMessage($this->getId(),
                 $productId,
                 Ess_M2ePro_Model_ListingsLogs::INITIATOR_UNKNOWN,
                 NULL,
                 Ess_M2ePro_Model_ListingsLogs::ACTION_ADD_PRODUCT_TO_LISTING,
                 // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully added');
                 'Item was successfully added',
                 Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                 Ess_M2ePro_Model_ListingsLogs::PRIORITY_LOW);
        //------------------------------

        return $listingProductTemp;
    }

    public function addProductsFromCategory($categoryId)
    {
        // Make collection
        //----------------------------
        $collection = Mage::getModel('catalog/product')->getCollection();
        //----------------------------

        // Add attribute set filter
        //----------------------------
        $collection->addFieldToFilter('attribute_set_id', (int)$this->getAttributeSet());
        //----------------------------

        // Hide products others listings
        //----------------------------
        if ($this->isHideProductsOthersListings()) {

            $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                 ->select()
                                 ->from(Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable(),new Zend_Db_Expr('DISTINCT `product_id`'));

            $collection->getSelect()->where('`e`.`entity_id` NOT IN ('.$dbSelect->__toString().')');
        }
        //----------------------------

        // Add categories filter
        //----------------------------
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from(Mage::getSingleton('core/resource')->getTableName('catalog_category_product'),new Zend_Db_Expr('DISTINCT `product_id`'))
                             ->where("`category_id` = ?",(int)$categoryId);

        $collection->getSelect()->where('`e`.`entity_id` IN ('.$dbSelect->__toString().')');
        //----------------------------

        // Get categories products
        //----------------------------
        $sqlQuery = $collection->getSelect()->__toString();

        $categoryProductsArray = Mage::getModel('Core/Mysql4_Config')
                                        ->getReadConnection()
                                        ->fetchCol($sqlQuery);
        //----------------------------

        // Add categories products
        //----------------------------
        foreach ($categoryProductsArray as $productTemp) {
            $this->addProduct($productTemp);
        }
        //----------------------------
    }

    protected function hasProduct($productId)
    {
        if (count($this->getListingsProducts(false,array('product_id'=>$productId))) > 0) {
            return true;
        }

        return false;
    }

    // ########################################
}