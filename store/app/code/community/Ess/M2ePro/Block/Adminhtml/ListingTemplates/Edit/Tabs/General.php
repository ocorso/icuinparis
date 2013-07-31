<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_ListingTemplates_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingTemplatesTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/templates/listing/general.phtml');
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
        $marketplaces = Mage::getModel('M2ePro/Marketplaces')
            ->getCollection()
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplaces::STATUS_ENABLE)
            ->setOrder('title', 'ASC')
            ->toArray();
        $this->setData('marketplaces', $marketplaces['items']);
        //------------------------------

		//-------------------------------
        $accounts = Mage::getModel('M2ePro/Accounts')
                            ->getCollection()
                            ->setOrder('title', 'ASC')
                            ->toArray();
        $this->setData('accounts', $accounts['items']);
        //-------------------------------

        //------------------------------
        $this->attribute_set_locked = false;
        if (Mage::registry('M2ePro_data')->getId()) {
            $this->attribute_set_locked = Mage::registry('M2ePro_data')->isLocked();
        }
        //------------------------------

        //------------------------------
        $formData = Mage::registry('M2ePro_data');
        $formData = $formData ? $formData->toArray() : array();

        empty($formData['categories_main_id']) || $this->generateCategories('main', $formData);
        empty($formData['categories_secondary_id']) || $this->generateCategories('secondary', $formData);
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
                                'onclick' => 'ListingTemplatesHandlersObj.attribute_sets_confirm();',
                                'class' => 'attribute_sets_confirm_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'CategoriesHandlersObj.confirmCategory(\'main\');',
                                'class' => 'confirm_main_category_button'
                            ) );
        $this->setChild('confirm_main_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'main_ebay_category_change_button',
                                'label'   => Mage::helper('M2ePro')->__('Change Category'),
                                'onclick' => 'CategoriesHandlersObj.initCategoryEdit(\'main\');',
                                'class' => 'change_main_category_button',
                                'style' => 'display: none; margin-right: 5px;'
                            ) );
        $this->setChild('change_main_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'main_ebay_category_cancel_button',
                                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                                'onclick' => 'CategoriesHandlersObj.cancelCategoryEdit(\'main\');',
                                'class' => 'cancel_main_category_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('cancel_main_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'CategoriesHandlersObj.selectCategoryById(\'main\');',
                                'class' => 'select_main_category_by_id_button'
                            ) );
        $this->setChild('select_main_category_by_id_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'CategoriesHandlersObj.confirmCategory(\'secondary\');',
                                'class' => 'confirm_secondary_category_button'
                            ) );
        $this->setChild('confirm_secondary_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'secondary_ebay_category_change_button',
                                'label'   => Mage::helper('M2ePro')->__('Change Category'),
                                'onclick' => 'CategoriesHandlersObj.initCategoryEdit(\'secondary\');',
                                'class' => 'change_secondary_category_button',
                                'style' => 'display: none; margin-right: 5px;'
                            ) );
        $this->setChild('change_secondary_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'secondary_ebay_category_empty_button',
                                'label'   => Mage::helper('M2ePro')->__('Reset Category'),
                                'onclick' => 'CategoriesHandlersObj.emptyCategory(\'secondary\');',
                                'class' => 'reset_secondary_category_button',
                                'style' => 'display: none; margin-right: 5px;'
                            ) );
        $this->setChild('reset_secondary_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'secondary_ebay_category_cancel_button',
                                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                                'onclick' => 'CategoriesHandlersObj.cancelCategoryEdit(\'secondary\');',
                                'class' => 'cancel_secondary_category_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('cancel_secondary_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'CategoriesHandlersObj.selectCategoryById(\'secondary\');',
                                'class' => 'select_secondary_category_by_id_button'
                            ) );
        $this->setChild('select_secondary_category_by_id_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh Store Categories'),
                                'onclick' => 'ListingTemplatesHandlersObj.updateEbayStoreByAccount_click();',
                                'class' => 'update_ebay_store_button'
                            ) );
        $this->setChild('update_ebay_store_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    protected function generateCategories($type, $formData)
    {
        $key = $type == 'main' ? 'categories_main_id' : 'categories_secondary_id';
        
        $breadcrumbs = array();
        $selectedIds = array();

        $id = $formData[$key];
	    $selectedId = $id;
        for ($i = 1; $i < 8; $i++) {

            $category = Mage::getModel('M2ePro/Marketplaces')
                                ->loadInstance($formData['marketplace_id'])
                                ->getCategory($id);

            if (!isset($category['title'])) {
                break;
            }

            $breadcrumbs[] = $category['title'];
            $selectedIds[] = $category['category_id'];

            if (!$category['parent_id']) {
                break; // root node
            }

            $id = $category['parent_id'];
        }

        $categoryData = array('breadcrumbs' => implode(' > ', array_reverse($breadcrumbs)) . " ($selectedId)" );
        $parentIds = $selectedIds;
        $parentIds[] = 0;
        $selectedIds = array_reverse($selectedIds);
        $parentIds = array_reverse($parentIds);
        array_pop($parentIds);

        $i = 1;
        foreach ($parentIds as $id) {
            $categories = Mage::getModel('M2ePro/Marketplaces')->loadInstance($formData['marketplace_id'])->getChildCategories($id);

            $categoryData["select-$i"] = '<select name="' . $type . '_ebay_category-' . $i . '" id="' . $type . '_ebay_category-' . $i . '" class="ebay-cat ' . $type . '-ebay-category-hidden">';
            foreach ($categories as $category) {
                $categoryData["select-$i"] .= '<option is_leaf="' . $category['is_leaf'] . '" value="' . $category['category_id'] . '"' . ($selectedIds[$i - 1] == $category['category_id'] ? ' selected="selected"' : '') . '>' . $category['title'] . '</option>' . PHP_EOL;
            }
            $categoryData["select-$i"] .= '</select>';
            $i++;
        }

        $this->setData($type . '_category', $categoryData);
    }
}