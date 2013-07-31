<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_ListingTemplates_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $attributeSets = array();

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingTemplatesGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------

        $this->attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                                    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                                    ->load()->toOptionHash();
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection of general templates
        $collection = Mage::getModel('M2ePro/ListingsTemplates')->getCollection();

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

        $this->addColumn('attribute_sets', array(
            'header' => Mage::helper('M2ePro')->__('Attribute Sets'),
            'align'  => 'left',
            'width'  => '200px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnAttributeSets')
        ));

        $data = Mage::getModel('M2ePro/Accounts')
                            ->getCollection()
                            ->setOrder('title', 'ASC')
                            ->toArray();

        $options = array();
        foreach ($data['items'] as $item) {
            $options[$item['id']] = $item['title'];
        }

        $this->addColumn('account_id', array(
            'header'         => Mage::helper('M2ePro')->__('Account'),
            'align'          => 'left',
            'width'          => '130px',
            'type'           => 'options',
            'index'          => 'account_id',
            'options'        => $options,
            'frame_callback' => array($this, 'callbackColumnAccount')
        ));

        $data = Mage::getModel('M2ePro/Marketplaces')
                            ->getCollection()
                            ->setOrder('title', 'ASC')
                            ->toArray();

        $options = array();
        foreach ($data['items'] as $item) {
            $options[$item['id']] = Mage::helper('M2ePro')->__($item['title']);
        }
        
        $this->addColumn('marketplace_id', array(
            'header'         => Mage::helper('M2ePro')->__('Marketplace'),
            'align'          => 'left',
            'width'          => '130px',
            'type'           => 'options',
            'index'          => 'marketplace_id',
            'options'        => $options,
            'frame_callback' => array($this, 'callbackColumnMarketplace')
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

        $this->addColumn('update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '75px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array('base'=> '*/*/edit'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete'),
                    'url'       => array('base'=> '*/*/delete'),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
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

        // Set delete action
        //--------------------------------
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
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

    public function callbackColumnAccount($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }
    
    public function callbackColumnAttributeSets($value, $row, $column, $isExport)
    {
        $attributeSets = Mage::getModel('M2ePro/TemplatesAttributeSets')->getCollection()
                                    ->addFieldToFilter('template_type',Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_LISTING)
                                    ->addFieldToFilter('template_id',(int)$row->getId())
                                    ->toArray();

        $value = '';
        foreach ($attributeSets['items'] as $attributeSet) {
            if (strlen($value) > 100) {
                $value .= ', <strong>...</strong>';
                break;
            }
            if (isset($this->attributeSets[$attributeSet['attribute_set_id']])) {
                $value != '' && $value .= ', ';
                $value .= $this->attributeSets[$attributeSet['attribute_set_id']];
            }
        }

        return $value;
    }
    
    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridListing', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    // ####################################
}