<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Products_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::registry('M2ePro_data');

        // Initialization block
        //------------------------------
        $this->setId('listingsProductsGrid'.(isset($listingData['id'])?$listingData['id']:''));
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        $listingData = Mage::registry('M2ePro_data');

        // Get collection
        //----------------------------
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->joinField('qty',
                    'cataloginventory/stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left')
                ->joinField('is_in_stock',
                    'cataloginventory/stock_item',
                    'is_in_stock',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left');

        /*$collection->getSelect()->joinLeft(
            array('cisi' => Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item')),
            '(cisi.product_id = e.entity_id) AND (cisi.stock_id = 1)',
            array('qty','is_in_stock')
        );*/
        //----------------------------

        // Add attribute set filter
        //----------------------------
        $collection->addFieldToFilter('attribute_set_id', (int)$listingData['attribute_set_id']);
        //----------------------------

        // Set filter store
        //----------------------------
        $store = $this->_getStore();
        
        if ($store->getId()) {
            $collection->addStoreFilter($store);
            $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
            $collection->joinAttribute('thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('status');
            $collection->addAttributeToSelect('visibility');
            $collection->addAttributeToSelect('thumbnail');
        }
        //----------------------------

        // Hide products others listings
        //----------------------------
        $hideProductsOthersListings = (bool)$listingData['hide_products_others_listings'];

        if ($hideProductsOthersListings) {

            $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                             ->select()
                             ->from(Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable(),new Zend_Db_Expr('DISTINCT `product_id`'));

            $collection->getSelect()->where('`e`.`entity_id` NOT IN ('.$dbSelect->__toString().')');

        } else {

            if (isset($listingData['id'])) {

                $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                     ->select()
                                     ->from(Mage::getResourceModel('M2ePro/ListingsProducts')->getMainTable(),new Zend_Db_Expr('DISTINCT `product_id`'))
                                     ->where('`listing_id` = ?',(int)$listingData['id']);

                $collection->getSelect()->where('`e`.`entity_id` NOT IN ('.$dbSelect->__toString().')');
            }
        }
        //----------------------------
        
        // Add categories filter
        //----------------------------
        $categoriesData = Mage::registry('temp_listing_categories');

        if (count($categoriesData) > 0)
        {
            $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                 ->select()
                                 ->from(Mage::getSingleton('core/resource')->getTableName('catalog_category_product'),new Zend_Db_Expr('DISTINCT `product_id`'))
                                 ->where('`category_id` IN ('.implode(',',$categoriesData).')');

            $collection->getSelect()->where('`e`.`entity_id` IN ('.$dbSelect->__toString().')');
        }
        //----------------------------

        //exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToResult();

        return $this;
    }

    protected function _prepareColumns()
    {
        $listingData = Mage::registry('M2ePro_data');

        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'filter_index' => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title'),
            'align'     => 'left',
            //'width'     => '100px',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle')
        ));

        $tempTypes = Mage::getSingleton('catalog/product_type')->getOptionArray();
        if (isset($tempTypes['virtual'])) {
            unset($tempTypes['virtual']);
        }
        if (isset($tempTypes['downloadable'])) {
            unset($tempTypes['downloadable']);
        }

        $this->addColumn('type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'type_id',
            'filter_index' => 'type_id',
            'options' => $tempTypes
        ));

        $this->addColumn('is_in_stock', array(
            'header'    => Mage::helper('M2ePro')->__('Is In Stock'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'is_in_stock',
            'filter_index' => 'is_in_stock',
            'options' => array(
                '1' => Mage::helper('M2ePro')->__('In Stock'),
                '0' => Mage::helper('M2ePro')->__('Out of Stock')
            ),
            'frame_callback' => array($this, 'callbackColumnIsInStock')
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('M2ePro')->__('SKU'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'text',
            'index'     => 'sku',
            'filter_index' => 'sku'
        ));

        $store = $this->_getStore();

        $this->addColumn('price', array(
            'header'    => Mage::helper('M2ePro')->__('Price'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index'     => 'price',
            'filter_index' => 'price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('M2ePro')->__('Qty'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'qty',
            'filter_index' => 'qty',
            'frame_callback' => array($this, 'callbackColumnQty')
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('M2ePro')->__('Visibility'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'visibility',
            'filter_index' => 'visibility',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray()
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('M2ePro')->__('Status'),
            'align'     => 'left',
            'width'     => '90px',
            'type'      => 'options',
            'sortable'  => false,
            'index'     => 'status',
            'filter_index' => 'status',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        if (!Mage::app()->isSingleStoreMode()) {

            $this->addColumn('websites', array(
                'header'    => Mage::helper('M2ePro')->__('Websites'),
                'align'     => 'left',
                'width'     => '90px',
                'type'      => 'options',
                'sortable'  => false,
                'index'     => 'websites',
                'filter_index' => 'websites',
                'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash()
            ));

        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $listingData = Mage::registry('M2ePro_data');

        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set fake action
        //--------------------------------
        $this->getMassactionBlock()->addItem('attributes', array(
            'label' => '&nbsp;&nbsp;&nbsp;&nbsp;',
            'url'   => $this->getUrl('*/*/massStatus', array('_current'=>true)),
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        $listingData = Mage::registry('M2ePro_data');

        $productId = (int)$value;
        $storeId = (int)$listingData['store_id'];

        $withoutImageHtml = '<a href="'.$this->getUrl('adminhtml/catalog_product/edit', array('id' => $productId)).'" target="_blank">'.$productId.'</a>';

        $showProductsThumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/products/settings/','show_thumbnails');
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_MagentoProduct */
        $magentoProduct = Mage::getModel('M2ePro/MagentoProduct');
        $magentoProduct->setProductId($productId);
        $magentoProduct->setStoreId($storeId);

        $imageUrlResized = $magentoProduct->getThumbnailImageLink();
        if (is_null($imageUrlResized)) {
            return $withoutImageHtml;
        }

        $imageHtml = $productId.'<hr/><img src="'.$imageUrlResized.'" />';
        $withImageHtml = str_replace('>'.$productId.'<','>'.$imageHtml.'<',$withoutImageHtml);

        return $withImageHtml;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        if (strlen($value) > 60) {
            return substr($value, 0, 60) . '...';
        }
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnIsInStock($value, $row, $column, $isExport)
    {
        switch ($value) {

            case 'In Stock':
                //$value = '<span style="color: gray;">'.$value.'</span>';
                break;

            case 'Out of Stock':
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            default:

                break;
        }

        return $value;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        $rowVal = $row->getData();

        if (!isset($rowVal['price']) || (float)$rowVal['price'] <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }
        return $value;
    }

    public function callbackColumnQty($value, $row, $column, $isExport)
    {
        if ($value <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($value) {

            case 'Enabled':
                //$value = '<span style="color:gray;">'.$value.'</span>';
                break;

            case 'Disabled':
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            default:

                break;
        }

        return $value;
    }

    // ####################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites',
                    'catalog/product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _getStore()
    {
        $listingData = Mage::registry('M2ePro_data');

        // Get store filter
        //----------------------------
        $storeId = 0;
        if (isset($listingData['store_id'])) {
            $storeId = (int)$listingData['store_id'];
        }
        //----------------------------

        return Mage::app()->getStore((int)$storeId);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridProducts', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
    
    public function _toHtml()
    {
        $cssBefore = <<<CSS
<style type="text/css">
    table.massaction div.right {
        display: none;
    }
</style>
CSS;
        $listingData = Mage::registry('M2ePro_data');
        $suffixGridId = (isset($listingData['id'])?$listingData['id']:'');

        $selectItemsMessage = Mage::helper('M2ePro')->__('Please select items.');

        $javascriptsBefore = <<<JAVASCRIPT
<script type="text/javascript">
    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.text.select_items_message = '{$selectItemsMessage}';

    ProductsGridHandlersObj = new ProductsGridHandlers();
    ProductsGridHandlersObj.setGridId('listingsProductsGrid{$suffixGridId}');
</script>
JAVASCRIPT;

        return $cssBefore.$javascriptsBefore.parent::_toHtml();
    }

    // ####################################
}