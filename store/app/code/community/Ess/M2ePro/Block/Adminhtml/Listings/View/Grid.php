<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $connRead = NULL;
    private $sellingFormatTemplate = NULL;

    // ####################################
    
    public function __construct()
    {
        parent::__construct();

        $this->connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingData = Mage::registry('M2ePro_data');

        // Initialization block
        //------------------------------
        $this->setId('listingsViewGrid'.$listingData['id']);
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------

        $this->sellingFormatTemplate = Mage::getModel('M2ePro/SellingFormatTemplates')
                                                 ->load($listingData['selling_format_template_id']);
    }

    // ####################################
    
    protected function _prepareCollection()
    {
        $listingData = Mage::registry('M2ePro_data');

        // Get collection products in listing
        //--------------------------------
        $collection = Mage::getModel('M2ePro/ListingsProducts')->getCollection();
        $collection->getSelect()->distinct();
        $collection->getSelect()->where("`main_table`.`listing_id` = ?",(int)$listingData['id']);
        //->addFieldToFilter('main_table.listing_id', (int)$listingData['id']);
        //--------------------------------

        // Communicate with magento product table
        //--------------------------------
        $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                                     ->select()
                                     ->from(Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar'),new Zend_Db_Expr('MAX(`store_id`)'))
                                     ->where("`entity_id` = `main_table`.`product_id`")
                                     ->where("`attribute_id` = `ea`.`attribute_id`")
                                     ->where("`store_id` = 0 OR `store_id` = ?",(int)$listingData['store_id']);

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
            'header'    => Mage::helper('M2ePro')->__('Product Title / SKU'),
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

        if ($this->sellingFormatTemplate->isListingTypeAuction() ||
            $this->sellingFormatTemplate->isListingTypeAttribute()) {

            $this->addColumn('ebay_start_price', array(
                'header'    => Mage::helper('M2ePro')->__('"Start" Price'),
                'align'     => 'right',
                'width'     => '50px',
                'type'      => 'number',
                'index'     => 'ebay_start_price',
                'filter_index' => 'main_table.ebay_start_price',
                'frame_callback' => array($this, 'callbackColumnStartPrice')
            ));

            /*$this->addColumn('ebay_reserve_price', array(
                'header'    => Mage::helper('M2ePro')->__('"Reserve" Price'),
                'align'     =>'right',
                'width'     => '50px',
                'type'      => 'number',
                'index'     => 'ebay_reserve_price',
                'filter_index' => 'main_table.ebay_reserve_price',
                'frame_callback' => array($this, 'callbackColumnReservePrice')
            ));*/
        }
        
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

        if (Mage::getIsDeveloperMode()) {
            $this->addColumn('developer_action', array(
                'header'    => Mage::helper('M2ePro')->__('Actions'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'text',
                'index'     => 'value',
                'filter'    => false,
                'sortable'  => false,
                'filter_index' => 'cpev.value',
                'frame_callback' => array($this, 'callbackColumnDeveloperAction')
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('list', array(
             'label'    => Mage::helper('M2ePro')->__('List Item(s)'),
             'url'      => '',
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('revise', array(
             'label'    => Mage::helper('M2ePro')->__('Revise Item(s)'),
             'url'      => '',
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('relist', array(
             'label'    => Mage::helper('M2ePro')->__('Relist Item(s)'),
             'url'      => '',
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('stop', array(
             'label'    => Mage::helper('M2ePro')->__('Stop Only Item(s)'),
             'url'      => '',
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('stop_and_remove', array(
             'label'    => Mage::helper('M2ePro')->__('Stop & Remove Item(s)'),
             'url'      => '',
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        $listingData = Mage::registry('M2ePro_data');

        $productId = (int)$row->getData('product_id');
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
            $value = substr($value, 0, 60) . '...';
        }

        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

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

    public function callbackColumnStartPrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        return Mage::app()->getLocale()->currency($this->sellingFormatTemplate->getCurrency())->toCurrency($value);
    }

    public function callbackColumnReservePrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }
        
        return Mage::app()->getLocale()->currency($this->sellingFormatTemplate->getCurrency())->toCurrency($value);
    }

    public function callbackColumnBuyItNowPrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }
        
        return Mage::app()->getLocale()->currency($this->sellingFormatTemplate->getCurrency())->toCurrency($value);
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

        return $value.$this->getViewLogIconHtml($row->getId(),
                                                $row->getData('listing_id'),
                                                $row->getData('product_id'));
    }

    public function callbackColumnEbayEndTime($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    public function callbackColumnDeveloperAction($value, $row, $column, $isExport)
    {
        $value = '';

        if ($row->getData('status') != Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED) {
            $value != '' && $value .= '<br/>';
            $value .= '<a href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.selectByRowId('.$row->getData('id').'); EbayActionsHandlersObj.runListProducts();">List</a>';
        }

        if ($row->getData('status') == Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED) {
            $value != '' && $value .= '<br/>';
            $value .= '<a href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.selectByRowId('.$row->getData('id').'); EbayActionsHandlersObj.runReviseProducts();">Revise</a>';
        }

        if ($row->getData('status') != Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED &&
            $row->getData('status') != Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED) {
            $value != '' && $value .= '<br/>';
            $value .= '<a href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.selectByRowId('.$row->getData('id').'); EbayActionsHandlersObj.runRelistProducts();">Relist</a>';
        }

        if ($row->getData('status') == Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED) {
            $value != '' && $value .= '<br/>';
            $value .= '<a href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.selectByRowId('.$row->getData('id').'); EbayActionsHandlersObj.runStopProducts();">Stop</a>';
        }

        $value != '' && $value .= '<br/>';
        $value .= '<a href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.selectByRowId('.$row->getData('id').'); EbayActionsHandlersObj.runStopAndRemoveProducts();">Remove</a>';

        return Mage::helper('M2ePro')->__($value);
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('cpev.value LIKE ? OR cpe.sku LIKE ?', '%'.$value.'%');
    }

    //----------------------------------------

    public function getViewLogIconHtml($listingProductId, $listingId, $productId)
    {
        // Get last messages
        //--------------------------
        $dbSelect = $this->connRead->select()
                             ->from(Mage::getResourceModel('M2ePro/ListingsLogs')->getMainTable(),array('action_id','action','type','description','create_date','initiator'))
                             ->where('`listing_id` = ?',(int)$listingId)
                             ->where('`product_id` = ?',(int)$productId)
                             ->where('`action_id` IS NOT NULL')
                             ->order(array('id DESC'))
                             ->limit(30);

        $logRows = $this->connRead->fetchAll($dbSelect);
        //--------------------------
        
        // Get grouped messages by action_id
        //--------------------------
        $actionsRows = array();
        $tempActionRows = array();
        $lastActionId = false;
        
        foreach ($logRows as $row) {

            $row['description'] = Mage::helper('M2ePro')->escapeHtml($row['description']);
            $row['description'] = Mage::getModel('M2ePro/LogsBase')->decodeDescription($row['description']);

            if ($row['action_id'] !== $lastActionId) {
                if (count($tempActionRows) > 0) {
                    $actionsRows[] = array(
                        'type' => $this->getMainTypeForActionId($tempActionRows),
                        'date' => $this->getMainDateForActionId($tempActionRows),
                        'action' => $this->getActionForAction($tempActionRows[0]),
                        'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                        'items' => $tempActionRows
                    );
                    $tempActionRows = array();
                }
                $lastActionId = $row['action_id'];
            }
            $tempActionRows[] = $row;
        }

        if (count($tempActionRows) > 0) {
            $actionsRows[] = array(
                'type' => $this->getMainTypeForActionId($tempActionRows),
                'date' => $this->getMainDateForActionId($tempActionRows),
                'action' => $this->getActionForAction($tempActionRows[0]),
                'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                'items' => $tempActionRows
            );
        }

        if (count($actionsRows) <= 0) {
            return '';
        }

        $actionsRows = array_slice($actionsRows,0,3);
        $lastActionRow = $actionsRows[0];
        //--------------------------

        // Get log icon
        //--------------------------
        $icon = 'normal';
        $iconTip = Mage::helper('M2ePro')->__('Last action was completed successfully.');

        if ($lastActionRow['type'] == Ess_M2ePro_Model_LogsBase::TYPE_ERROR) {
            $icon = 'error';
            $iconTip = Mage::helper('M2ePro')->__('Last action was completed with error(s).');
        }
        if ($lastActionRow['type'] == Ess_M2ePro_Model_LogsBase::TYPE_WARNING) {
            $icon = 'warning';
            $iconTip = Mage::helper('M2ePro')->__('Last action was completed with warning(s).');
        }

        $iconSrc = $this->getSkinUrl('M2ePro').'/images/log_statuses/'.$icon.'.png';
        //--------------------------

        $html = '<span style="float:right;">';
        $html .= '<a title="'.$iconTip.'" id="lpv_grid_help_icon_open_'.(int)$listingProductId.'" href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.viewItemHelp('.(int)$listingProductId.',\''.base64_encode(json_encode($actionsRows)).'\');"><img src="'.$iconSrc.'" /></a>';
        $html .= '<a title="'.$iconTip.'" id="lpv_grid_help_icon_close_'.(int)$listingProductId.'" style="display:none;" href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.hideItemHelp('.(int)$listingProductId.');"><img src="'.$iconSrc.'" /></a>';
        $html .= '</span>';

        return $html;
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case Ess_M2ePro_Model_ListingsLogs::ACTION_LIST_PRODUCT_ON_EBAY:
                $string = Mage::helper('M2ePro')->__('List');
                break;
            case Ess_M2ePro_Model_ListingsLogs::ACTION_RELIST_PRODUCT_ON_EBAY:
                $string = Mage::helper('M2ePro')->__('Relist');
                break;
            case Ess_M2ePro_Model_ListingsLogs::ACTION_RESIVE_PRODUCT_ON_EBAY:
                $string = Mage::helper('M2ePro')->__('Revise');
                break;
            case Ess_M2ePro_Model_ListingsLogs::ACTION_STOP_PRODUCT_ON_EBAY:
                $string = Mage::helper('M2ePro')->__('Stop');
                break;
            case Ess_M2ePro_Model_ListingsLogs::ACTION_STOP_AND_REMOVE_PRODUCT:
                $string = Mage::helper('M2ePro')->__('Stop And Remove');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['initiator']) {
            case Ess_M2ePro_Model_LogsBase::INITIATOR_UNKNOWN:
                $string = '';
                break;
            case Ess_M2ePro_Model_LogsBase::INITIATOR_USER:
                $string = Mage::helper('M2ePro')->__('Manual');
                break;
            case Ess_M2ePro_Model_LogsBase::INITIATOR_EXTENSION:
                $string = Mage::helper('M2ePro')->__('Automatic');
                break;
        }

        return $string;
    }

    public function getMainTypeForActionId($actionRows)
    {
        $type = Ess_M2ePro_Model_LogsBase::TYPE_SUCCESS;

        foreach ($actionRows as $row) {
            if ($row['type'] == Ess_M2ePro_Model_LogsBase::TYPE_ERROR) {
                $type = Ess_M2ePro_Model_LogsBase::TYPE_ERROR;
                break;
            }
            if ($row['type'] == Ess_M2ePro_Model_LogsBase::TYPE_WARNING) {
                $type = Ess_M2ePro_Model_LogsBase::TYPE_WARNING;
            }
        }

        return $type;
    }

    public function getMainDateForActionId($actionRows)
    {
        return Mage::helper('M2ePro')->gmtDateToTimezone($actionRows[0]['create_date']);
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridView', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    public function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof EbayItemsGridHandlersObj != 'undefined') {
        EbayItemsGridHandlersObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            EbayItemsGridHandlersObj.afterInitPage();
        }, 350);
    });

</script>
JAVASCRIPT;

        return parent::_toHtml().$javascriptsMain;
    }

    // ####################################
}