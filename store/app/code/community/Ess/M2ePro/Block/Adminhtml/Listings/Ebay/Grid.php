<?php

/**
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Ebay_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $connRead = NULL;
    
    public function __construct()
    {
        parent::__construct();

        $this->connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        // Initialization block
        //------------------------------
        $this->setId('listingsEbayGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _getAccount()
    {
        $accountId = $this->getRequest()->getParam('account', false);
        return $accountId;
    }

    protected function _getMarketplace()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace', false);
        return $marketplaceId;
    }

    // ####################################

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/EbayListings')->getCollection();

        $collection->getSelect()->joinLeft(
            array('mp' => Mage::getResourceModel('M2ePro/Marketplaces')->getMainTable()),
            'mp.id = main_table.marketplace_id',
            array('marketplace_title' => 'title')
        );

        $collection->getSelect()->joinLeft(
            array('ac' => Mage::getResourceModel('M2ePro/Accounts')->getMainTable()),
            'ac.id = main_table.account_id',
            array('mode')
        );

        // Retrieve all possible marketplaces ids that available in our listing
        // $this->_marketplacesIds = Mage::getModel('M2ePro/EbayListings')->getUsingMarketplacesIds();

        // Add Filter By Account
        if (($accountId = $this->_getAccount()) !== false && $accountId != '') {
            $collection->addFieldToFilter("account_id", $accountId);
        }

        // Add Filter By Marketplace
        if (($marketplaceId = $this->_getMarketplace()) !== false) {
            $collection->addFieldToFilter("marketplace_id", $marketplaceId);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('ebay_title', array(
            'header' => Mage::helper('M2ePro')->__('Product Name'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'ebay_title',
            'filter_index' => 'ebay_title',
            'frame_callback' => array($this, 'callbackColumnProductTitle')
        ));

        $this->addColumn('ebay_item', array(
            'header' => Mage::helper('M2ePro')->__('eBay Item ID'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'ebay_item',
            'filter_index' => 'ebay_item',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
        ));

        $this->addColumn('ebay_qty', array(
            'header' => Mage::helper('M2ePro')->__('eBay Available QTY'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'ebay_qty',
            'filter_index' => 'ebay_qty',
            'frame_callback' => array($this, 'callbackColumnEbayAvailableQty')
        ));

        $this->addColumn('ebay_qty_sold', array(
            'header' => Mage::helper('M2ePro')->__('eBay Sold QTY'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'ebay_qty_sold',
            'filter_index' => 'ebay_qty_sold',
            'frame_callback' => array($this, 'callbackColumnEbayQtySold')
        ));

        $this->addColumn('ebay_price', array(
            'header' => Mage::helper('M2ePro')->__('eBay Price'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'ebay_price',
            'filter_index' => 'ebay_price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('M2ePro')->__('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED => Mage::helper('M2ePro')->__('Listed'),
                Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD => Mage::helper('M2ePro')->__('Sold'),
                Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED => Mage::helper('M2ePro')->__('Stopped'),
                Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED => Mage::helper('M2ePro')->__('Finished')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $this->addColumn('ebay_start_date', array(
             'header' => Mage::helper('M2ePro')->__('eBay Start Date'),
             'align' => 'right',
             'width' => '150px',
             'type' => 'datetime',
             'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
             'index' => 'ebay_start_date',
             'filter_index' => 'main_table.ebay_start_date',
             'frame_callback' => array($this, 'callbackColumnEbayTime')
        ));

        $this->addColumn('ebay_end_date', array(
           'header' => Mage::helper('M2ePro')->__('eBay End Date'),
           'align' => 'right',
           'width' => '150px',
           'type' => 'datetime',
           'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
           'index' => 'ebay_end_date',
           'filter_index' => 'main_table.ebay_end_date',
           'frame_callback' => array($this, 'callbackColumnEbayTime')
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('View Log'),
                    'url'       => array('base'=> '*/adminhtml_logs/ebayListings/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebayListings/index').'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Clear Log'),
                    'url'       => array('base'=> '*/*/clearLog/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebayListings/index').'/'),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                )
            )
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        //--------------------------------
        $this->setMassactionIdField('`main_table`.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('relist', array(
            'label' => Mage::helper('M2ePro')->__('Relist Item(s)'),
            'url' => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('stop', array(
            'label' => Mage::helper('M2ePro')->__('Stop Item(s)'),
            'url' => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }
        
        $url = Mage::helper('M2ePro/Ebay')->getEbayItemUrl($row->getData('ebay_item'),
                                                           $row->getAccount()->getMode(),
                                                           $row->getData('marketplace_id'));
        $value = '<a href="' . $url . '" target="_blank">' . $value . '</a>';

        return $value;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        if (strlen($value) > 60) {
            $value = substr($value, 0, 60) . '...';
        }

        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

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

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }
        
        return Mage::app()->getLocale()->currency($row['ebay_currency'])->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {

            case Ess_M2ePro_Model_ListingsProducts::STATUS_NOT_LISTED:
                $value = '<span style="color: gray;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_ListingsProducts::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_ListingsProducts::STATUS_SOLD:
                $value = '<span style="color: brown;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_ListingsProducts::STATUS_STOPPED:
                $value = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_ListingsProducts::STATUS_FINISHED:
                $value = '<span style="color: blue;">' . $value . '</span>';
                break;

            default:
                break;
        }

        return $value.$this->getViewLogIconHtml($row->getId());
    }

    public function callbackColumnEbayTime($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    //----------------------------------------

    public function getViewLogIconHtml($ebayListingProductId)
    {
        // Get last messages
        //--------------------------
        $dbSelect = $this->connRead->select()
                             ->from(Mage::getResourceModel('M2ePro/EbayListingsLogs')->getMainTable(),array('action_id','action','type','description','create_date','initiator'))
                             ->where('`ebay_listing_id` = ?',(int)$ebayListingProductId)
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
        $html .= '<a title="'.$iconTip.'" id="lpv_grid_help_icon_open_'.(int)$ebayListingProductId.'" href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.viewItemHelp('.(int)$ebayListingProductId.',\''.base64_encode(json_encode($actionsRows)).'\');"><img src="'.$iconSrc.'" /></a>';
        $html .= '<a title="'.$iconTip.'" id="lpv_grid_help_icon_close_'.(int)$ebayListingProductId.'" style="display:none;" href="javascript:void(0);" onclick="EbayItemsGridHandlersObj.hideItemHelp('.(int)$ebayListingProductId.');"><img src="'.$iconSrc.'" /></a>';
        $html .= '</span>';

        return $html;
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['action']) {
            case Ess_M2ePro_Model_EbayListingsLogs::ACTION_RELIST_PRODUCT_ON_EBAY:
                $string = Mage::helper('M2ePro')->__('Relist');
                break;
            case Ess_M2ePro_Model_EbayListingsLogs::ACTION_STOP_PRODUCT_ON_EBAY:
                $string = Mage::helper('M2ePro')->__('Stop');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['initiator']) {
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
        return $this->getUrl('*/*/gridListings', array('_current' => true));
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