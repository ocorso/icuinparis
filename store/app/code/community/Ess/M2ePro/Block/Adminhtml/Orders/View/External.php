<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders_View_External extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayOrdersExternalGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        //------------------------------

        /** @var $order Ess_M2ePro_Model_Orders_Order */
        $this->order = Mage::registry('M2ePro_data');
    }

    protected function _prepareCollection()
    {
        $collection = $this->order->getExternalTransactionsCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('ebay_id', array(
            'header' => Mage::helper('M2ePro')->__('Transaction #'),
            'align' => 'left',
            'width' => '*',
            'index' => 'ebay_id',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnTransactionId')
        ));

        $this->addColumn('fee', array(
            'header' => Mage::helper('M2ePro')->__('Fee'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'fee',
            'type' => 'number',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnFee')
        ));

        $this->addColumn('sum', array(
            'header' => Mage::helper('M2ePro')->__('Amount'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'sum',
            'type' => 'number',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnAmount')
        ));

        $this->addColumn('time', array(
            'header' => Mage::helper('M2ePro')->__('Date'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'time',
            'type' => 'datetime',
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function callbackColumnTransactionId($value, $row, $column, $isExport)
    {
        return '<a href="'.$this->getUrl('*/*/goToPaypalTransactionPage', array('transaction_id' => $value)).'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnFee($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->convertCurrencyNameToCode($this->order->getData('currency'), $value);
    }

    public function callbackColumnAmount($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->convertCurrencyNameToCode($this->order->getData('currency'), $value);
    }

    public function getRowUrl($row)
    {
        return '';
    }
}