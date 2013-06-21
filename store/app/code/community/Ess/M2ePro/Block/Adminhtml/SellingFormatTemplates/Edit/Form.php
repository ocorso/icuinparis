<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_SellingFormatTemplates_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('sellingFormatTemplatesForm');
        //------------------------------

        $this->setTemplate('M2ePro/templates/selling_format/form.phtml');
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
        //------------------------------
        $attributesSets = Mage::getModel('eav/entity_attribute_set')
                                    ->getCollection()
                                    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                                    ->setOrder('attribute_set_name', 'ASC')
                                    ->toArray();

        $this->setData('attributes_sets', $attributesSets['items']);
        //------------------------------

        //------------------------------
        $this->attribute_set_locked = false;
        if (Mage::registry('M2ePro_data') && Mage::registry('M2ePro_data')->getId()) {
            $this->attribute_set_locked = Mage::registry('M2ePro_data')->isLocked();
        }
        //------------------------------

        //------------------------------
        $this->setData('currencies', Mage::helper('M2ePro/Module')->getConfig()->getAllGroupValues('/ebay/currency/', Ess_M2ePro_Model_ConfigBase::SORT_VALUE_ASC));
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'attribute_sets_select_all_button',
                                'label'   => Mage::helper('M2ePro')->__('Select All'),
                                'onclick' => 'AttributeSetsHandlersObj.selectAllAttributeSets();',
                                'class' => 'attribute_sets_select_all_button'
                            ) );
        $this->setChild('attribute_sets_select_all_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'attribute_sets_confirm_button',
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'SellingFormatTemplatesHandlersObj.attribute_sets_confirm();',
                                'class' => 'attribute_sets_confirm_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------
        
        return parent::_beforeToHtml();
    }
}