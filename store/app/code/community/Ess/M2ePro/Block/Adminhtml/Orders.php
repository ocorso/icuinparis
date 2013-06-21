<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebaySalesEvents');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_orders';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('eBay Orders');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        //------------------------------
    }

    public function _toHtml()
    {
        $orderLogConstants = Mage::helper('M2ePro')->getClassConstantAsJson('Model_Orders_OrderLog');

        $orderViewUrl = $this->getUrl('*/adminhtml_orders/view',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebayListings/index')));

        $viewAllOrderLogsMessage = Mage::helper('M2ePro')->__('View All Order Logs.');

        $successWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Success'));
        $warningWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Warning'));
        $errorWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Error'));
        $closeWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Close'));

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
		M2ePro = {};
		M2ePro.url = {};
		M2ePro.formData = {};
		M2ePro.customData = {};
		M2ePro.text = {};
	}

	M2ePro.url.orderViewUrl = '{$orderViewUrl}';

	M2ePro.text.view_all_order_logs_message = '{$viewAllOrderLogsMessage}';

	M2ePro.text.success_word = '{$successWord}';
	M2ePro.text.warning_word = '{$warningWord}';
	M2ePro.text.error_word = '{$errorWord}';
	M2ePro.text.close_word = '{$closeWord}';

    Event.observe(window, 'load', function() {
        OrdersHandlersObj = new OrdersHandlers('{$this->getId()}Grid');
        OrdersHandlersObj.setConstants('{$orderLogConstants}');
    });

</script>
JAVASCRIPT;

        $filtersBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_switcher');
        $filtersBlock->setUseConfirm(false);

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_orders_help');
        $helpHtml = $helpBlock->toHtml() . $filtersBlock->toHtml();
        
        $startHtmlDivGrid = '<div id="' . $this->getId() . 'Grid">';
        return str_replace($startHtmlDivGrid, $javascriptsMain.$helpHtml.$startHtmlDivGrid, parent::_toHtml());
    }

    /**
     * Check whether it is single account mode
     *
     * @return bool
     */
    public function isSingleAccountMode()
    {
        return Mage::getModel('M2ePro/Accounts')->isSingleAccountMode();
    }
}