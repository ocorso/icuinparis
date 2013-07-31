<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Items_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $connRead = NULL;

    // ####################################
    
    public function __construct()
    {
        parent::__construct();

        $this->connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Initialization block
        //------------------------------
        $this->setId('listingsItemsGrid');
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
        // Get collection products in listing
        //--------------------------------
        $collection = Mage::getModel('M2ePro/ListingsProducts')->getCollection();
        $collection->getSelect()->distinct();
        $collection->getSelect()->join(array('l'=>Mage::getResourceModel('M2ePro/Listings')->getMainTable()), '(`l`.`id` = `main_table`.`listing_id`)', array('listing_title'=>'title','store_id','selling_format_template_id'));
        //--------------------------------

        // Communicate with magento product table
        //--------------------------------
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                     ->select()
                                     ->from(Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar'),new Zend_Db_Expr('MAX(`store_id`)'))
                                     ->where("`entity_id` = `main_table`.`product_id`")
                                     ->where("`attribute_id` = `ea`.`attribute_id`")
                                     ->where("`store_id` = 0 OR `store_id` = `l`.`store_id`");

        $collection->getSelect()
                   //->join(array('csi'=>Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')), '(csi.product_id = `main_table`.product_id)',array('qty'))
                   ->join(array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')), '(cpe.entity_id = `main_table`.product_id)',array('sku'))
                   ->join(array('cisi'=>Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')), '(cisi.product_id = `main_table`.product_id)',array('is_in_stock'))
                   ->join(array('cpev'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar')), "( `cpev`.`entity_id` = `main_table`.product_id
                                                                                                                                  AND cpev.store_id = (".$dbSelect->__toString()."))", array('value'))
                   ->join(array('ea'=>Mage::getSingleton('core/resource')->getTableName('eav_attribute')), '(`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\')',array())
                   ->joinLeft(array('ebit'=>Mage::getResourceModel('M2ePro/EbayItems')->getMainTable()), '(`ebit`.`id` = `main_table`.`ebay_items_id`)',array('item_id'));
        //--------------------------------

        //exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'product_id',
            'filter_index' => 'main_table.product_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Listing / SKU'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'value',
            'filter_index' => 'cpev.value',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('stock_availability',
            array(
                'header'=> Mage::helper('M2ePro')->__('Stock Availability'),
                'width' => '100px',
                'index' => 'is_in_stock',
                'filter_index' => 'cisi.is_in_stock',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    1 => Mage::helper('M2ePro')->__('In Stock'),
                    0 => Mage::helper('M2ePro')->__('Out of Stock')
                ),
                'frame_callback' => array($this, 'callbackColumnStockAvailability')
        ));

        $this->addColumn('ebay_item_id', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Item ID'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'text',
            'index'     => 'item_id',
            'filter_index' => 'ebit.item_id',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
        ));

        $this->addColumn('ebay_available_qty', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Available QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'ebay_qty_sold',
            'filter'    => false,
            'sortable'  => false,
            'filter_index' => 'main_table.ebay_qty_sold',
            'frame_callback' => array($this, 'callbackColumnEbayAvailableQty')
        ));

        $this->addColumn('ebay_qty_sold', array(
            'header'    => Mage::helper('M2ePro')->__('eBay Sold QTY'),
            'align'     => 'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'ebay_qty_sold',
            'filter_index' => 'main_table.ebay_qty_sold',
            'frame_callback' => array($this, 'callbackColumnEbayQtySold')
        ));
        
        $this->addColumn('ebay_buyitnow_price', array(
            'header'    => Mage::helper('M2ePro')->__('"Buy It Now" Price'),
            'align'     =>'right',
            'width'     => '50px',
            'type'      => 'number',
            'index'     => 'ebay_buyitnow_price',
            'filter_index' => 'main_table.ebay_buyitnow_price',
            'frame_callback' => array($this, 'callbackColumnBuyItNowPrice')
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('M2ePro')->__('Status'),
                'width' => '100px',
                'index' => 'status',
                'filter_index' => 'main_table.status',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED => Mage::helper('M2ePro')->__('Not Listed'),
                    Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED => Mage::helper('M2ePro')->__('Listed'),
                    Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD => Mage::helper('M2ePro')->__('Sold'),
                    Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED => Mage::helper('M2ePro')->__('Stopped'),
                    Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED => Mage::helper('M2ePro')->__('Finished')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $this->addColumn('ebay_end_date', array(
            'header'    => Mage::helper('M2ePro')->__('eBay End Date'),
            'align'     => 'right',
            'width'     => '130px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'ebay_end_date',
            'filter_index' => 'main_table.ebay_end_date',
            'frame_callback' => array($this, 'callbackColumnEbayEndTime')
        ));

        $this->addColumn('goto_listing_item', array(
            'header'    => Mage::helper('M2ePro')->__('Manage'),
            'align'     => 'center',
            'width'     => '50px',
            'type'      => 'text',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    // ####################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        $productId = (int)$row->getData('product_id');
        $storeId = (int)$row->getData('store_id');

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
            $value = substr($value, 0, 60) . '...';
        }

        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

        $urlParams = array();
        $urlParams['id'] = $row->getData('listing_id');
        $urlParams['back'] = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/items');

        $listingUrl = $this->getUrl('*/adminhtml_listings/view',$urlParams);
        $listingTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('listing_title'));

        if (strlen($listingTitle) > 50) {
            $listingTitle = substr($listingTitle, 0, 50) . '...';
        }

        $value .= '<br/><hr style="border:none; border-top:1px solid silver; margin: 2px 0px;"/>';
        $value .= '<strong>Listing:</strong><a href="'.$listingUrl.'"> '.$listingTitle.'</a>';

        $tempSku = $row->getData('sku');
        is_null($tempSku) && $tempSku = Mage::getModel('M2ePro/MagentoProduct')->setProductId($row->getData('product_id'))->getSku();

        $value .= '<br/><strong>SKU:</strong> '.Mage::helper('M2ePro')->escapeHtml($tempSku);

        return $value;
    }

    public function callbackColumnStockAvailability($value, $row, $column, $isExport)
    {
        if ((int)$row->getData('is_in_stock') <= 0) {
            return '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }
    
    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $value = '<a href="'.$this->getUrl('*/*/gotoEbay/', array('item_id' => $value)).'" target="_blank">'.$value.'</a>';

        return $value;
    }

    public function callbackColumnEbayAvailableQty($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }
        
        $value = $row->getData('ebay_qty') - $row->getData('ebay_qty_sold');
        
        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnEbayQtySold($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnBuyItNowPrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        $sellingFormatTemplate = Mage::getModel('M2ePro/SellingFormatTemplates')->loadInstance($row->getData('selling_format_template_id'));
        
        return Mage::app()->getLocale()->currency($sellingFormatTemplate->getCurrency())->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD:
                $value = '<span style="color: brown;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED:
                $value = '<span style="color: red;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED:
                $value = '<span style="color: blue;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnEbayEndTime($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $altTitle = Mage::helper('M2ePro')->__('Go to listing');
        $iconSrc = $this->getSkinUrl('M2ePro').'/images/goto_listing.png';

        $link = $this->getUrl('*/*/view/',array('id'=>$row->getData('listing_id'),
                                                'filter'=>base64_encode('product_id[from]='.$row->getData('product_id').'&product_id[to]='.$row->getData('product_id'))));

        $html = '<div style="float:right; margin:5px 15px 0 0;">';
        $html .= '<a alt="'.$altTitle.'" title="'.$altTitle.'" target="_blank" href="'.$link.'"><img src="'.$iconSrc.'" /></a>';
        $html .= '</div>';

        return $html;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('cpev.value LIKE ? OR cpe.sku LIKE ? OR l.title LIKE ?', '%'.$value.'%');
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridItems', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}