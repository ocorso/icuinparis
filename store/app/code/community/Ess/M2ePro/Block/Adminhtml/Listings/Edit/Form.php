<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsEditForm');
        //------------------------------

        $this->setTemplate('M2ePro/listings/form.phtml');
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
        // Get attribute sets
        //------------------------------
        $attributesSets = Mage::getModel('eav/entity_attribute_set')
                                    ->getCollection()
                                    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                                    ->setOrder('attribute_set_name', 'ASC')
                                    ->toArray();
        $this->attributesSets = $attributesSets['items'];
        //------------------------------

        // Add synchronizations templates
        //----------------------------
        $templates = Mage::getModel('M2ePro/SynchronizationsTemplates')
                                        ->getCollection()
                                        ->setOrder('title', 'ASC')
                                        ->toArray();

        foreach ($templates['items'] as $key => $value) {
            $templates['items'][$key]['title'] = Mage::helper('M2ePro')->escapeHtml($templates['items'][$key]['title']);
        }

        $this->synchronizationsTemplates = $templates['items'];
        //----------------------------

        // Get selected categories
        //----------------------------
        if ($listingId = $this->getRequest()->getParam('id')) {
            $listingsModel = Mage::getModel('M2ePro/Listings')->loadInstance($listingId);
            $listingCategories = $listingsModel->getListingsCategories();
            $storeId = $listingsModel->getStoreId();

            // Getting paths for categories ids
            //----------------------------
            $categoriesPaths = array();
            $categoriesPathIds = array();

            foreach ($listingCategories as $listingCategory) {
                $tempItemPath = array_slice(explode('/', $listingCategory['path_ids']), 1);
                $categoriesPaths[] = $tempItemPath;
                $categoriesPathIds = array_merge($tempItemPath, $categoriesPathIds);
            }

            $categoriesPathIds = array_unique($categoriesPathIds);
            //----------------------------

            // Getting names for categories in paths and making breadcrumbs
            //----------------------------
            $collection = Mage::getModel('catalog/category')->getCollection()
                                                            ->setProductStoreId($storeId)
                                                            ->setStoreId($storeId)
                                                            ->addAttributeToSelect('name')
                                                            ->addIdFilter($categoriesPathIds);

            $categoryItems = $collection->getItems();
            $selectedCategories = array();

            foreach ($categoriesPaths as $categoryPath) {
                $breadcrumb = array();

                foreach ($categoryPath as $categoryId) {
                    if (isset($categoryItems[$categoryId]) && !is_null($categoryName = $categoryItems[$categoryId]->getName())) {
                        $breadcrumb[] = $categoryName;
                    }
                }

                if (count($breadcrumb)) {
                    $breadcrumb = implode(' > ', $breadcrumb);
                    $selectedCategories[] = $breadcrumb;
                }
            }
            //----------------------------

            $this->selectedCategories = $selectedCategories;
        }
        //----------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add New'),
                                'onclick' => 'ListingEditHandlersObj.openWindow(\'' . $this->getUrl('*/adminhtml_sellingFormatTemplates/new') . '\');',
                                'class' => 'add add_new_selling_format_template_button'
                            ) );
        $this->setChild('add_new_selling_format_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'ListingEditHandlersObj.reloadSellingFormatTemplates();',
                                'class' => 'reload_selling_format_templates_button'
                            ) );
        $this->setChild('reload_selling_format_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add New'),
                                'onclick' => 'ListingEditHandlersObj.openWindow(\'' . $this->getUrl('*/adminhtml_listingTemplates/new') . '\');',
                                'class' => 'add add_new_listing_template_button'
                            ) );
        $this->setChild('add_new_listing_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'ListingEditHandlersObj.reloadListingTemplates();',
                                'class' => 'reload_listing_templates_button'
                            ) );
        $this->setChild('reload_listing_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add New'),
                                'onclick' => 'ListingEditHandlersObj.openWindow(\'' . $this->getUrl('*/adminhtml_descriptionTemplates/new') . '\');',
                                'class' => 'add add_new_description_template_button'
                            ) );
        $this->setChild('add_new_description_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'ListingEditHandlersObj.reloadDescriptionTemplates();',
                                'class' => 'reload_description_templates_button'
                            ) );
        $this->setChild('reload_description_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add New'),
                                'onclick' => 'ListingEditHandlersObj.openWindow(\'' . $this->getUrl('*/adminhtml_synchronizationTemplates/new') . '\');',
                                'class' => 'add add_new_synchronization_template_button'
                            ) );
        $this->setChild('add_new_synchronization_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'ListingEditHandlersObj.reloadSynchronizationTemplates();',
                                'class' => 'reload_synchronization_templates_button'
                            ) );
        $this->setChild('reload_synchronization_templates_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}