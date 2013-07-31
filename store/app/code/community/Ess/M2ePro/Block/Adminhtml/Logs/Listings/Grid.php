<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Logs_Listings_Grid extends Ess_M2ePro_Block_Adminhtml_Logs_LogGridBase
{
    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::registry('M2ePro_data');

        // Initialization block
        //------------------------------
        $this->setId('logsListingsGrid'.(isset($listingData['id'])?$listingData['id']:''));
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
        $listingData = Mage::registry('M2ePro_data');

        // Get collection logs
        //--------------------------------
        $collection = Mage::getModel('M2ePro/ListingsLogs')->getCollection();
        //--------------------------------

        // Set listing filter
        //--------------------------------
        if (isset($listingData['id'])) {
            $collection->addFieldToFilter('listing_id', $listingData['id']);
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
        $listingData = Mage::registry('M2ePro_data');

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'width'     => '150px',
            'index'     => 'create_date'
        ));

        if (!isset($listingData['id'])) {

            $this->addColumn('listing_id', array(
                'header'    => Mage::helper('M2ePro')->__('Listing ID'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'listing_id',
                'filter_index' => 'main_table.listing_id'
            ));

            $this->addColumn('listing_title', array(
                'header'    => Mage::helper('M2ePro')->__('Listing Title'),
                'align'     => 'left',
                //'width'     => '300px',
                'type'      => 'text',
                'index'     => 'listing_title',
                'filter_index' => 'main_table.listing_title',
                'frame_callback' => array($this, 'callbackColumnListingTitle')
            ));
        }

        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'product_id',
            'filter_index' => 'main_table.product_id'
        ));

        $this->addColumn('product_title', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'product_title',
            'filter_index' => 'main_table.product_title',
            'frame_callback' => array($this, 'callbackColumnProductTitle')
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'left',
            'width'     => '250px',
            'type'      => 'options',
            'index'     => 'action',
            'sortable'  => false,
            'filter_index' => 'main_table.action',
            'options' => Mage::getModel('M2ePro/ListingsLogs')->getActionsTitles()
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

    public function callbackColumnListingTitle($value, $row, $column, $isExport)
    {
        if ($row->getData('listing_id')) {

            $listingTable = Mage::getResourceModel('M2ePro/Listings')->getMainTable();
            $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                              ->select()
                              ->from($listingTable,'id')
                              ->where('`id` = ?',(int)$row->getData('listing_id'));
            $listingArray = Mage::getModel('Core/Mysql4_Config')->getReadConnection()->fetchCol($dbSelect);

            if (count($listingArray) > 0) {
                $value = '<a href="'.$this->getUrl('*/adminhtml_listings/view', array('id' => $row->getData('listing_id'))).'">'.
                         Mage::helper('M2ePro')->escapeHtml($value).
                         '</a>';
            }
        }
        return $value;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        if (strlen($value) > 50) {
            $value = substr($value, 0, 50) . '...';
        }

        if ($row->getData('product_id')) {

            $table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
            $dbSelect = Mage::getModel('Core/Mysql4_Config')->getReadConnection()
                              ->select()
                              ->from($table,'entity_id')
                              ->where('`entity_id` = ?',(int)$row->getData('product_id'));
            $productArray = Mage::getModel('Core/Mysql4_Config')->getReadConnection()->fetchCol($dbSelect);

            if (count($productArray) > 0) {
                $value = '<a href="'.$this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getData('product_id'))).'" target="_blank">'.
                         Mage::helper('M2ePro')->escapeHtml($value).
                         '</a>';
            }
        }
        
        return $value;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridListings', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}