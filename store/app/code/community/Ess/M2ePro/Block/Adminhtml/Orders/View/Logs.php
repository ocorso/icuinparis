<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders_View_Logs extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayOrdersLogsGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        //------------------------------

        /** @var $order Ess_M2ePro_Model_Orders_Order */
        $this->order = Mage::registry('M2ePro_data');
    }

    protected function _prepareCollection()
    {
        $collection = $this->order->getLogsCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('M2ePro')->__('Message'),
            'align'     => 'left',
            'width'     => '*',
            'type'      => 'text',
            'sortable'  => false,
            'filter_index' => 'id',
            'index'     => 'message',
            'frame_callback' => array($this, 'callbackColumnMessage')
        ));

        $this->addColumn('code', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '65px',
            'index'     => 'code',
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Create Date'),
            'align'     => 'left',
            'width'     => '165px',
            'type'      => 'datetime',
            'index'     => 'create_date'
        ));

        return parent::_prepareColumns();
    }

    //##############################################################

    public function callbackColumnMessage($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/LogsBase')->decodeDescription($row->getData('message'));
    }

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        switch ($value) {
            case Ess_M2ePro_Model_Orders_OrderLog::MESSAGE_TYPE_SUCCESS:
                $message = '<span style="color: green;">'.Mage::helper('M2ePro')->__('Success').'</span>';
                break;
            case Ess_M2ePro_Model_Orders_OrderLog::MESSAGE_TYPE_NOTICE:
                $message = '<span style="color: blue;">'.Mage::helper('M2ePro')->__('Notice').'</span>';
                break;
            case Ess_M2ePro_Model_Orders_OrderLog::MESSAGE_TYPE_WARNING:
                $message = '<span style="color: orange;">'.Mage::helper('M2ePro')->__('Warning').'</span>';
                break;
            case Ess_M2ePro_Model_Orders_OrderLog::MESSAGE_TYPE_ERROR:
            default:
                $message = '<span style="color: red;">'.Mage::helper('M2ePro')->__('Error').'</span>';
                break;
        }

        return $message;
    }

    //##############################################################

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridOrderLogs', array('_current' => true));
    }
}