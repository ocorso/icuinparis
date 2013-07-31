<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Logs_EbayListings_Grid extends Ess_M2ePro_Block_Adminhtml_Logs_LogGridBase
{
    public function __construct()
    {
        parent::__construct();

        $ebayListingData = Mage::registry('M2ePro_data');

        // Initialization block
        //------------------------------
        $this->setId('logsEbayListingsGrid'.(isset($ebayListingData['id'])?$ebayListingData['id']:''));
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        $ebayListingData = Mage::registry('M2ePro_data');

        // Get collection logs
        //--------------------------------
        $collection = Mage::getModel('M2ePro/EbayListingsLogs')->getCollection();
        //--------------------------------

        // Join ebay_listings_table
        //--------------------------------
        $collection->getSelect()->joinLeft(
                       array('el' => Mage::getResourceModel('M2ePro/EbayListings')->getMainTable()),
                       '(`main_table`.ebay_listing_id = `el`.id)',
                       array(
                            'ebay_item'=>'el.ebay_item',
                            'marketplace_id'=>'el.marketplace_id',
                            'account_id'=>'el.account_id'
                       )
                   );
        //--------------------------------

        // Set listing filter
        //--------------------------------
        if (isset($ebayListingData['id'])) {
            $collection->addFieldToFilter('ebay_listing_id', $ebayListingData['id']);
        }
        //--------------------------------

        // we need sort by id also, because create_date may be same for some adjacents entries
        //--------------------------------
        if ($this->getRequest()->getParam('sort', 'create_date') == 'create_date') {
            $collection->setOrder('id', $this->getRequest()->getParam('dir', 'DESC'));
        }
        //--------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $ebayListingData = Mage::registry('M2ePro_data');

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'width'     => '150px',
            'index'     => 'create_date'
        ));

        $this->addColumn('ebay_item', array(
            'header' => Mage::helper('M2ePro')->__('eBay Item ID'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'ebay_item',
            'filter_index' => 'el.ebay_item',
            'frame_callback' => array($this, 'callbackColumnEbayItemId')
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('M2ePro')->__('Product Name'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'left',
            'width'     => '250px',
            'type'      => 'options',
            'index'     => 'action',
            'sortable'  => false,
            'filter_index' => 'main_table.action',
            'options' => Mage::getModel('M2ePro/EbayListingsLogs')->getActionsTitles()
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'description',
            'filter_index' => 'main_table.description',
            'frame_callback' => array($this, 'callbackDescription')
        ));

        $this->addColumn('type', array(
            'header'=> Mage::helper('M2ePro')->__('Type'),
            'width' => '80px',
            'index' => 'type',
            'align' => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogTypeList(),
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        $this->addColumn('priority', array(
            'header'=> Mage::helper('M2ePro')->__('Priority'),
            'width' => '80px',
            'index' => 'priority',
            'align'     => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogPriorityList(),
            'frame_callback' => array($this, 'callbackColumnPriority')
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------
    }

    // ####################################

    public function callbackColumnEbayItemId($value, $row, $column, $isExport)
    {   
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else {
            $url = Mage::helper('M2ePro/Ebay')->getEbayItemUrl($row->getData('ebay_item'),
                                                               Mage::getModel('M2ePro/Accounts')->load($row->getData('account_id'))->getMode(),
                                                               $row->getData('marketplace_id'));
            $value = '<a href="' . $url . '" target="_blank">' . $value . '</a>';
        }

        return $value;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    // ####################################
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridEbayListings', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}