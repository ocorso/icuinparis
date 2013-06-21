<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders_View_Order_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('orderForm');
        //------------------------------

        /** @var $order Ess_M2ePro_Model_Orders_Order */
        $this->order = Mage::registry('M2ePro_data');

        $this->setTemplate('M2ePro/orders/view.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // Magento order data
        // ---------------
        $this->real_magento_order_id = NULL;

        if (!is_null($magentoOrderId = $this->order->getData('magento_order_id'))) {
            $magentoOrder = Mage::getModel('sales/order')->load($magentoOrderId);

            if ($magentoOrder->getId()) {
                $this->real_magento_order_id = $magentoOrder->getRealOrderId();
            }
        }
        // ---------------

        // Shipping data
        // ---------------
        $this->shipping_address = NULL;
        $shippingAddress = $this->order->getShippingAddress(true);

        if (count($shippingAddress)) {
            if (isset($shippingAddress['country_id']) && $shippingAddress['country_id'] != '') {
                $country = Mage::getModel('directory/country')->load($shippingAddress['country_id']);
                !is_null($country->getId()) && $shippingAddress['country_id'] = $country->getName();
            }

            $this->shipping_address = $shippingAddress;
        }
        // ---------------

        $this->setChild('order_items', $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_items'));
        $this->setChild('order_logs', $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_logs'));
        $this->setChild('order_external_transactions', $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_external'));

        return parent::_beforeToHtml();
    }

    public function getTaxSuffix()
    {
        if ($this->order->hasVat()) {
            return ' (' . Mage::helper('M2ePro')->__('Incl. Tax') .') ';
        } else if ($this->order->hasTax()) {
            return ' (' . Mage::helper('M2ePro')->__('Excl. Tax') .') ';
        }
        return '';
    }
}