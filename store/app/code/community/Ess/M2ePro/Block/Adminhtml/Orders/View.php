<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders_View extends Mage_Adminhtml_Block_Widget
{
    protected $_loadedModel = null;
    protected $_accountMode = Ess_M2ePro_Model_Accounts::MODE_PRODUCTION;
    protected $_hasEbayAccount = true;

    public function __construct($attributes)
    {
        parent::__construct();

        $this->setTemplate('M2ePro/orders/view.phtml');

        if (isset($attributes['model'])) {
            $this->_loadedModel = $attributes['model'];

            if ($this->_loadedModel->getAccount()->getId()) {
                $this->_accountMode = $this->_loadedModel->getAccount()->getMode();
            } else {
                $this->_accountMode = Ess_M2ePro_Model_Accounts::MODE_PRODUCTION;
                $this->_hasEbayAccount = false;
            }

            $this->assign('model', $this->_loadedModel);
        }
    }

    public function getHasEbayAccount()
    {
        return $this->_hasEbayAccount;
    }

    public function getBackButtonHtml()
    {
        $html = '';

        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('back')->setLabel(Mage::helper('adminhtml')->__('Back'))
                ->setOnClick("setLocation('" . $this->getUrl('*/*/index') . "')")
                ->toHtml();

        return $html;
    }

    public function getOrderButtonHtml($eBayOrderId)
    {
        $html = '';

        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('scalable')->setLabel(Mage::helper('M2ePro')->__('Create Order'))
                ->setOnClick("setLocation('" . $this->getUrl('*/*/createOrder', array('id' => $eBayOrderId)) . "')")
                ->toHtml();

        return $html;
    }

    public function getInvoiceButtonHtml($eBayOrderId)
    {
        $html = '';

        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('scalable')->setLabel(Mage::helper('M2ePro')->__('Mark as Paid'))
                ->setOnClick("setLocation('" . $this->getUrl('*/*/doShipPaid', array('id' => $eBayOrderId, 'action' => 'paid')) . "')")
                ->toHtml();

        return $html;
    }

    public function getShipButtonHtml($eBayOrderId)
    {
        $html = '';

        $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('scalable')->setLabel(Mage::helper('M2ePro')->__('Mark as Shipped'))
                ->setOnClick("setLocation('" . $this->getUrl('*/*/doShipPaid', array('id' => $eBayOrderId, 'action' => 'ship')) . "')")
                ->toHtml();

        return $html;
    }

    public function getHeaderText()
    {
        return Mage::helper('M2ePro')->__('View Order Details');
    }

    public function getOrderItemsHtml()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_items', null,
                                               array(
                                                    'order' => $this->_loadedModel,
                                                    'accountMode' => $this->_accountMode
                                               ))->toHtml();
    }

    public function getExcludedTaxSuffix()
    {
        if ($this->_loadedModel->hasVat()) {
            $temp = Mage::helper('M2ePro')->__('Incl. Tax');
        } else {
            $temp = Mage::helper('M2ePro')->__('Excl. Tax');
        }
        return ' ('.$temp.') ';
    }

    public function getLogsHtml()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_logs', null,
                                               array(
                                                    'order' => $this->_loadedModel,
                                               ))->toHtml();
    }

    public function getExternalTransactionsHtml()
    {
        return $this->getLayout()->createBlock('M2ePro/adminhtml_orders_view_external', null,
                                               array(
                                                    'order' => $this->_loadedModel,
                                               ))->toHtml();
    }

}

