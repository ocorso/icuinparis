<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_DescriptionTemplates_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('descriptionTemplatesForm');
        //------------------------------
        
        $this->setTemplate('M2ePro/templates/description/form.phtml');
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
        if (Mage::registry('M2ePro_data')->getId()) {
            $this->attribute_set_locked = Mage::registry('M2ePro_data')->isLocked();
        }
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => 'DescriptionTemplatesHandlersObj.openInsertImageWindow();',
                                'class' => 'insert_image_window_button'
                            ) );
        $this->setChild('insert_image_window_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => "AttributeSetsHandlersObj.appendToText('select_attributes_for_subtitle', 'subtitle_template');",
                                'class' => 'add_subtitle_button'
                            ) );
        $this->setChild('add_subtitle_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'attribute_sets_select_all_button',
                                'label'   => Mage::helper('M2ePro')->__('Select All'),
                                'onclick' => 'AttributeSetsHandlersObj.selectAllAttributeSets();',
                                'class' => 'select_all_attribute_sets_button'
                            ) );
        $this->setChild('select_all_attribute_sets_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'attribute_sets_confirm_button',
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'DescriptionTemplatesHandlersObj.attribute_sets_confirm();',
                                'class' => 'attribute_sets_confirm_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label' => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => "AttributeSetsHandlersObj.appendToText('select_attributes_for_title', 'title_template');",
                                'class' => 'select_attributes_for_title_button'
                            ) );
        $this->setChild('select_attributes_for_title_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'toggletext',
                                'label' => Mage::helper('M2ePro')->__('Show / Hide Editor'),
                                'class' => 'show_hide_mce_button',
                            ) );
        $this->setChild('show_hide_mce_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => "AttributeSetsHandlersObj.appendToTextarea('#' + $('select_attributes').value + '#');",
                                'class' => 'add_product_attribute_button',
                            ) );
        $this->setChild('add_product_attribute_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => 'DescriptionTemplatesHandlersObj.insertGallery();',
                                'class' => 'insert_gallery_button',
                            ) );
        $this->setChild('insert_gallery_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}