<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Listings_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsListingsGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Update statistic table values
        Mage::getModel('M2ePro/Listings')->getResource()->updateStatisticColumns();

        // Get collection of listings
        $collection = Mage::getModel('M2ePro/Listings')->getCollection();

        // Set global filters
        //--------------------------
        $filterSellingFormatTemplate = $this->getRequest()->getParam('filter_selling_format_template');
        $filterDescriptionTemplate = $this->getRequest()->getParam('filter_description_template');
        $filterListingTemplate = $this->getRequest()->getParam('filter_listing_template');
        $filterSynchronizationTemplate = $this->getRequest()->getParam('filter_synchronization_template');

        !is_null($filterSellingFormatTemplate) && $filterSellingFormatTemplate != 0 && $collection->getSelect()->where('`selling_format_template_id` = ?',(int)$filterSellingFormatTemplate);
        !is_null($filterDescriptionTemplate) && $filterDescriptionTemplate != 0 && $collection->getSelect()->where('`description_template_id` = ?',(int)$filterDescriptionTemplate);
        !is_null($filterListingTemplate) && $filterListingTemplate != 0 && $collection->getSelect()->where('`listing_template_id` = ?',(int)$filterListingTemplate);
        !is_null($filterSynchronizationTemplate) && $filterSynchronizationTemplate != 0 && $collection->getSelect()->where('`synchronization_template_id` = ?',(int)$filterSynchronizationTemplate);
        //--------------------------

        //exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'id',
            'filter_index' => 'main_table.id'
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('M2ePro')->__('Title'),
            'align'     => 'left',
            //'width'     => '200px',
            'type'      => 'text',
            'index'     => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('products_total_count', array(
            'header'    => Mage::helper('M2ePro')->__('Total Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_total_count',
            'filter_index' => 'main_table.products_total_count',
            'frame_callback' => array($this, 'callbackColumnTotalProducts')
        ));

        $this->addColumn('products_listed_count', array(
            'header'    => Mage::helper('M2ePro')->__('Listed Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_listed_count',
            'filter_index' => 'main_table.products_listed_count',
            'frame_callback' => array($this, 'callbackColumnListedProducts')
        ));

        $this->addColumn('products_sold_count', array(
            'header'    => Mage::helper('M2ePro')->__('Sold Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_sold_count',
            'filter_index' => 'main_table.products_sold_count',
            'frame_callback' => array($this, 'callbackColumnSoldProducts')
        ));

        $this->addColumn('products_inactive_count', array(
            'header'    => Mage::helper('M2ePro')->__('Inactive Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_inactive_count',
            'filter_index' => 'main_table.products_inactive_count',
            'frame_callback' => array($this, 'callbackColumnInactiveProducts')
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                //array(
                //    'caption'   => Mage::helper('M2ePro')->__('View Products'),
                //    'url'       => array('base'=> '*/*/view/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                //    'field'     => 'id'
                //),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Add Products'),
                    'url'       => array('base'=> '*/*/products/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Settings'),
                    'url'       => array('base'=> '*/*/edit/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete Listing'),
                    'url'       => array('base'=> '*/*/delete'),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('View Log'),
                    'url'       => array('base'=> '*/adminhtml_logs/listings/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Clear Log'),
                    'url'       => array('base'=> '*/*/clearLog/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Selling Format Template'),
                    'url'       => array('base'=> '*/*/goToSellingFormatTemplate/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Description Template'),
                    'url'       => array('base'=> '*/*/goToDescriptionTemplate/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit General Template'),
                    'url'       => array('base'=> '*/*/goToListingTemplate/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Synchronization Template'),
                    'url'       => array('base'=> '*/*/goToSynchronizationTemplate/back/'.Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index').'/'),
                    'field'     => 'id'
                )
            )
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

        // Set clear log action
        //--------------------------------
        $this->getMassactionBlock()->addItem('clear_logs', array(
             'label'    => Mage::helper('M2ePro')->__('Clear Log(s)'),
             'url'      => $this->getUrl('*/*/clearLog',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index'))),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        // Set remove listings action
        //--------------------------------
        $this->getMassactionBlock()->addItem('delete_listings', array(
             'label'    => Mage::helper('M2ePro')->__('Delete Listing(s)'),
             'url'      => $this->getUrl('*/*/delete'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnTotalProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnListedProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnSoldProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
        }

        return $value;
    }

    public function callbackColumnInactiveProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = 0;
            $value = '<span style="color: red;">'.$value.'</span>';
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
        return $this->getUrl('*/*/view', array('id' => $row->getId(),'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/index')));
    }

    // ####################################
}