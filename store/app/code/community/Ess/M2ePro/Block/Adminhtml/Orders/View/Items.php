<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Orders_View_Items extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayOrdersItemsGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        //------------------------------

        /** @var $order Ess_M2ePro_Model_Orders_Order */
        $this->order = Mage::registry('M2ePro_data');
    }

    protected function _prepareCollection()
    {
        $collection = $this->order->getItemsCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product'),
            'align'     => 'left',
            'width'     => '*',
            'index'     => 'product_id',
            'frame_callback' => array($this, 'callbackColumnProduct')
        ));

        $this->addColumn('original_price', array(
            'header'    => Mage::helper('M2ePro')->__('Original Price'),
            'align'     => 'left',
            'width'     => '80px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnOriginalPrice')
        ));

        $this->addColumn('price', array(
            'header'    => Mage::helper('M2ePro')->__('Price'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'price',
            'frame_callback' => array($this, 'callbackColumnPrice')
        ));

        $this->addColumn('qty_sold', array(
            'header'    => Mage::helper('M2ePro')->__('Qty'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'qty_purchased'
        ));

        $this->addColumn('row_total', array(
            'header'    => Mage::helper('M2ePro')->__('Row Total'),
            'align'     => 'left',
            'width'     => '80px',
            'frame_callback' => array($this, 'callbackColumnRowTotal')
        ));

        return parent::_prepareColumns();
    }

    //##############################################################

    public function callbackColumnProduct($value, $row, $column, $isExport)
    {
        $returnString = '<b>'.Mage::helper('M2ePro')->escapeHtml($row->getData('item_title')).'</b><br />';

        $variations = $row->getOptions(true);
        if (count($variations)) {
            foreach ($variations as $variationName => $variationValue) {
                $returnString .= '<span style="font-weight: bold; font-style: italic; padding-left: 10px;">' . Mage::helper('M2ePro')->escapeHtml($variationName) . ': </span>';
                $returnString .= Mage::helper('M2ePro')->escapeHtml($variationValue) . '<br />';
            }
        }

        $eBayItemUrl = Mage::helper('M2ePro/Ebay')->getEbayItemUrl($row->getData('item_id'), $this->order->getAccount()->getMode(), $this->order->getData('marketplace_id'));

        $returnString .= '<a href="'.$eBayItemUrl.'" target="_blank">'.Mage::helper('M2ePro')->__('View on eBay').'</a>';

        if ($productId = $row->getData('product_id')) {
            $returnString .= ' | <a href="'.$this->getUrl('adminhtml/catalog_product/edit/id/'.$productId).'" target="_blank">'.Mage::helper('M2ePro')->__('View').'</a>';
        }
        
        return $returnString;
    }

    public function callbackColumnOriginalPrice($value, $row, $column, $isExport)
    {
        $productId = $row->getData('product_id');
        $formattedPrice = '0';

        if ($productId && $product = Mage::getModel('catalog/product')->load($productId)) {
            $formattedPrice = $product->getFormatedPrice();
        }

       return $formattedPrice;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->convertCurrencyNameToCode($row->getData('currency'), $row->getData('price'));
    }

    public function callbackColumnRowTotal($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->convertCurrencyNameToCode($row->getData('currency'), ($row->getData('qty_purchased')*$row->getData('price')));
    }    

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridOrderItems', array('_current' => true));
    }
}