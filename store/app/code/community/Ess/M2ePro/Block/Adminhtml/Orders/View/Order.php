<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders_View_Order extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('orderView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_orders_view';
        $this->_mode = 'order';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('View Order Details');
        //------------------------------

        /** @var $order Ess_M2ePro_Model_Orders_Order */
        $order = Mage::registry('M2ePro_data');

        if (!$order->getAccount()->getId()) {
            return;
        }

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'CommonHandlersObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('*/adminhtml_orders/index').'\')',
            'class'     => 'back'
        ));

        if ($order->canShipOnEbay()) {

            $this->_addButton('ship', array(
                'label'     => Mage::helper('M2ePro')->__('Mark as Shipped'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/shipOrderOnEbay', array('id' => $order->getId()))."');",
                'class'     => 'scalable'
            ));

        }

        if ($order->canPayOnEbay()) {

            $this->_addButton('pay', array(
                'label'     => Mage::helper('M2ePro')->__('Mark as Paid'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/payOrderOnEbay', array('id' => $order->getId()))."');",
                'class'     => 'scalable'
            ));

        }

        if ($order->canCreateMagentoOrder(true)) {

            $this->_addButton('order', array(
                'label'     => Mage::helper('M2ePro')->__('Create Order'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/createOrder', array('id' => $order->getId()))."');",
                'class'     => 'scalable'
            ));

        }
        //------------------------------
    }
}