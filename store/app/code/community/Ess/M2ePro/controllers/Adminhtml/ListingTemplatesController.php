<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_ListingTemplatesController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/templates')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Templates'))
             ->_title(Mage::helper('M2ePro')->__('General Templates'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Templates/AttributeSetsHandlers.js')
             ->addJs('M2ePro/Templates/ListingTemplates/TabsHandlers.js')
             ->addJs('M2ePro/Templates/ListingTemplates/CategoriesHandlers.js')
             ->addJs('M2ePro/Templates/ListingTemplates/SpecificsHandlers.js')
             ->addJs('M2ePro/Templates/ListingTemplates/ShippingsHandlers.js')
             ->addJs('M2ePro/Templates/ListingTemplatesHandlers.js');

        return $this;
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/templates/listing');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates'))
             ->renderLayout();
    }

    public function gridListingAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        // Check Exist Marketplaces
        //-------------------------
        if (Mage::getModel('M2ePro/Marketplaces')->getCollection()->addFieldToFilter('status',1)->getSize() == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select and update eBay marketplaces before adding new General Templates'));
            return $this->_redirect('*/*/index');
        }
        //-------------------------

        // Check Exist Accounts
        //-------------------------
        if (Mage::getModel('M2ePro/Accounts')->getCollection()->getSize() == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please add eBay accounts before adding new General Templates'));
            return $this->_redirect('*/*/index');
        }
        //-------------------------

        $id    = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/ListingsTemplates')->load($id);

        if ($id && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist'));
            return $this->_redirect('*/*/index');
        }

        $data = array();

        // Parse product details
        //-------------------------
        $tempProductsDetails = $model->getData('product_details');
        if ($tempProductsDetails != '') {
            $data = array_merge($data,json_decode($tempProductsDetails,true));
        }
        //-------------------------

        // Load payments
        //-------------------------
        $payments = Mage::getModel('M2ePro/ListingsTemplatesPayments')
                                ->getCollection()
                                ->addFieldToFilter('listing_template_id', $id)
                                ->toArray();
        $data['payments'] = array();
        foreach ($payments['items'] as $payment) {
            $data['payments'][] = $payment['payment_id'];
        }
        //-------------------------

        // Load shipping methods
        //-------------------------
        $shippings = Mage::getModel('M2ePro/ListingsTemplatesShippings')
                                ->getCollection()
                                ->addFieldToFilter('listing_template_id', $id)
                                ->setOrder('priority', 'ASC')
                                ->toArray();
        $data['shippings'] = $shippings['items'];
        //-------------------------

        // Load calculated shipping
        //-------------------------
        $calculatedShipping = Mage::getModel('M2ePro/ListingsTemplatesCalculatedShipping')->load($id)->toArray();
        $data = array_merge($data, $calculatedShipping);
        //-------------------------

        // Load item specifics
        //-------------------------
        $itemSpecifics = Mage::getModel('M2ePro/ListingsTemplatesSpecifics')
                                ->getCollection()
                                ->addFieldToFilter('listing_template_id', $id)
                                ->toArray();
        $data['item_specifics'] = array();
        foreach ($itemSpecifics['items'] as $specific) {
            
            if ($specific['value_mode'] == Ess_M2ePro_Model_ListingsTemplatesSpecifics::VALUE_MODE_EBAY_RECOMMENDED) {
                $specific['value_data'] = json_decode($specific['value_ebay_recommended'],true);
            }
            unset($specific['value_ebay_recommended']);

            if ($specific['value_mode'] == Ess_M2ePro_Model_ListingsTemplatesSpecifics::VALUE_MODE_CUSTOM_VALUE) {
                $specific['value_data'] = $specific['value_custom_value'];
            }
            unset($specific['value_custom_value']);

            if ($specific['value_mode'] == Ess_M2ePro_Model_ListingsTemplatesSpecifics::VALUE_MODE_CUSTOM_ATTRIBUTE) {
                $specific['value_data'] = $specific['value_custom_attribute'];
            }
            unset($specific['value_custom_attribute']);

            unset($specific['id']);
            unset($specific['listing_template_id']);
            unset($specific['update_date']);
            unset($specific['create_date']);
            
            $data['item_specifics'][] = $specific;
        }
        //-------------------------

        $model->addData($data);

        $templateAttributeSetsCollection = Mage::getModel('M2ePro/TemplatesAttributeSets')->getCollection();
        $templateAttributeSetsCollection->addFieldToFilter('template_id', $id)
                                        ->addFieldToFilter('template_type', Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_LISTING);

        $templateAttributeSetsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                                     ->columns('attribute_set_id');
        $templateAttributeSets = $templateAttributeSetsCollection->toArray();

        $attributeSetsIds = array();
        foreach ($templateAttributeSets['items'] as $attributeSet) {
            $attributeSetsIds[] = $attributeSet['attribute_set_id'];
        }

        $model->addData(array('attribute_sets' => $attributeSetsIds));

        Mage::register('M2ePro_data', $model);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_edit'))
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_listingTemplates_edit_tabs'))
             ->renderLayout();
    }

    //#############################################

    public function getMarketplaceInfoAction()
    {
        $id = $this->getRequest()->getParam('id');

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_marketplaces');
        $tableDictShipping = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_shippings');
        $tableDictShippingCategory = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_shippings_categories');

        $dbSelect = $connRead->select()
                             ->from($tableDictMarketplace,'*')
                             ->where('`marketplace_id` = ?',(int)$id);
        $marketplace = $connRead->fetchRow($dbSelect);

        $dbSelect = $connRead->select()
                             ->from($tableDictShipping,'*')
                             ->where('`marketplace_id` = ?',(int)$id)
                             ->order(array('title ASC'));
        $shippings = $connRead->fetchAll($dbSelect);

        $dbSelect = $connRead->select()
                             ->from($tableDictShippingCategory,'*')
                             ->where('`marketplace_id` = ?',(int)$id)
                             ->order(array('title ASC'));
        $shippingCategories = $connRead->fetchAll($dbSelect);
        
        $dataShippings = array();
        foreach ($shippingCategories as $category) {
            $dataShippings[$category['ebay_id']] = array(
                'title'   => $category['title'],
                'methods' => array(),
            );
        }

        foreach ($shippings as $shipping) {
            $shipping['data'] = json_decode($shipping['data'], true);
            $dataShippings[$shipping['category']]['methods'][] = $shipping;
        }

        exit(json_encode(array(
            'dispatch'           => json_decode($marketplace['dispatch'], true),
            'packages'           => json_decode($marketplace['packages'], true),
            'return_policy'      => json_decode($marketplace['return_policy'], true),
            'listing_features'   => json_decode($marketplace['listing_features'], true),
            'payments'           => json_decode($marketplace['payments'], true),
            'shipping'           => $dataShippings,
            'shipping_locations' => json_decode($marketplace['shipping_locations'], true),
            'shipping_locations_exclude' => json_decode($marketplace['shipping_locations_exclude'], true)
        )));
    }

    //#############################################

    public function getChildCategoriesAction()
    {
        $marketplaceId  = $this->getRequest()->getParam('marketplace_id',0);
        $parentCategoryId  = $this->getRequest()->getParam('parent_id',0);

        $data = Mage::getModel('M2ePro/Marketplaces')
                        ->loadInstance($marketplaceId)
                        ->getChildCategories($parentCategoryId);

        exit(json_encode($data));
    }
    
    public function getCategoriesTreeByCategoryIdAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id',0);
        $categoryId  = $this->getRequest()->getParam('category_id');

        $data = array();
        $selectedIds = array();

        for ($i = 1; $i < 8; $i++) {
            
            $category = Mage::getModel('M2ePro/Marketplaces')
                                    ->loadInstance($marketplaceId)
                                    ->getCategory($categoryId);
            
            if (!$category) {
                $data['error'] = Mage::helper('M2ePro')->__('Category with ID: %id% is not found. Ensure that you entered correct ID.');
                $data['error'] = str_replace('%id%',$categoryId,$data['error']);
                break;
            }

            if ($i == 1 && !$category['is_leaf']) {
                $data['error'] = Mage::helper('M2ePro')->__('Category with ID: %id% is non leaf and cannot be used.');
                $data['error'] = str_replace('%id%',$categoryId,$data['error']);
                break;
            }

            $selectedIds[] = (int)$category['category_id'];

            if (!$category['parent_id']) {
                break;
            }

            $categoryId = (int)$category['parent_id'];
        }

        $data['selected'] = array_reverse($selectedIds);

        exit(json_encode($data));
    }

    public function getCategoryInformationAction()
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_categories');
        $tableDictMarketplaces = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_marketplaces');

        // Prepare input data
        //------------------------
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id', 0);
        $selectedCategoriesIds = (string)$this->getRequest()->getParam('category_id', 0);
        $selectedCategoriesIds = explode(',', $selectedCategoriesIds);
        
        if (count($selectedCategoriesIds) == 0) {
            return '';
        }
        //------------------------

        // Get categories features defaults
        //------------------------
        $dbSelect = $connRead->select()
                             ->from($tableDictMarketplaces,'categories_features_defaults')
                             ->where('`marketplace_id` = ?',(int)$marketplaceId);
        $categoriesFeaturesDefaults = $connRead->fetchRow($dbSelect);
        $categoriesFeaturesDefaults = json_decode($categoriesFeaturesDefaults['categories_features_defaults']);
        //------------------------

        // Get categories features
        //------------------------
        $dbSelect = $connRead->select()
                             ->from($tableDictCategories,'*')
                             ->where('`marketplace_id` = ?',(int)$marketplaceId);

        $sqlClauseCategories = '';
        foreach ($selectedCategoriesIds as $categoryId) {
            if ($sqlClauseCategories != '') {
                $sqlClauseCategories .= ' OR ';
            }
            $sqlClauseCategories .= ' `category_id` = '.(int)$categoryId;
        }

        $dbSelect->where('('.$sqlClauseCategories.')')
                 ->order(array('level ASC'));

        $resultCategoriesRows = $connRead->fetchAll($dbSelect);
        //------------------------

        // Merge features defaults with categories
        //------------------------
        $rowLeafCategory = NULL;
        $response = (array)$categoriesFeaturesDefaults;
        
        foreach ($resultCategoriesRows as $rowCategory) {
            if (!is_null($rowCategory['features'])) {
                $response =  array_merge($response, (array)json_decode($rowCategory['features'], true));
            }
            if ((bool)$rowCategory['is_leaf']) {
                $rowLeafCategory = $rowCategory;
            }
        }
        //------------------------

        // Get Item specifics
        //------------------------
        $itemSpecific = array(
            'mode' => Ess_M2ePro_Model_ListingsTemplatesSpecifics::MODE_ITEM_SPECIFICS,
            'mode_relation_id' => 0,
            'specifics' => array()
        );

        if (!is_null($rowLeafCategory)) {

            if (isset($response['item_specifics_enabled'])) {
                if ((bool)$response['item_specifics_enabled']) {

                    $itemSpecific['mode'] = Ess_M2ePro_Model_ListingsTemplatesSpecifics::MODE_ITEM_SPECIFICS;
                    $itemSpecific['mode_relation_id'] = (int)$rowLeafCategory['category_id'];

                    //---------
                    if (!is_null($rowLeafCategory['item_specifics'])) {
                        $itemSpecific['specifics'] = json_decode($rowLeafCategory['item_specifics'],true);
                    } else {
                        $itemSpecific['specifics'] = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                                            ->processVirtual('marketplace','get','categorySpecifics',
                                                                              array('marketplace'=>$rowLeafCategory['marketplace_id'],'category_id'=>$rowLeafCategory['category_id']),'specifics',
                                                                              NULL,NULL,NULL);

                        if (!is_null($itemSpecific['specifics'])) {
                            
                            $tempData = array(
                                'marketplace_id' => (int)$rowLeafCategory['marketplace_id'],
                                'category_id' => (int)$rowLeafCategory['category_id'],
                                'item_specifics' => json_encode($itemSpecific['specifics'])
                            );

                            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                            $connWrite->insertOnDuplicate($tableDictCategories, $tempData);
                            
                        } else {
                            $itemSpecific['specifics'] = array();
                        }
                    }
                    //---------
                }
            }

            if (count($itemSpecific['specifics']) == 0) {
                if (isset($response['attribute_conversion_enabled'])) {
                    if ((bool)$response['attribute_conversion_enabled']) {

                        $itemSpecific['mode'] = Ess_M2ePro_Model_ListingsTemplatesSpecifics::MODE_ATTRIBUTE_SET;
                        $itemSpecific['mode_relation_id'] = (int)$rowLeafCategory['attribute_set_id'];

                        //---------
                        if (!is_null($rowLeafCategory['attribute_set'])) {
                            $itemSpecific['specifics'] = json_decode($rowLeafCategory['attribute_set'],true);
                        } else {
                            $itemSpecific['specifics'] = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                                                ->processVirtual('marketplace','get','attributesCS',
                                                                                  array('marketplace'=>$rowLeafCategory['marketplace_id'],'attribute_set_id'=>(int)$rowLeafCategory['attribute_set_id']),'specifics',
                                                                                  NULL,NULL,NULL);

                            if (!is_null($itemSpecific['specifics'])) {

                                $tempData = array(
                                    'marketplace_id' => (int)$rowLeafCategory['marketplace_id'],
                                    'category_id' => (int)$rowLeafCategory['category_id'],
                                    'attribute_set' => json_encode($itemSpecific['specifics'])
                                );

                                $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                                $connWrite->insertOnDuplicate($tableDictCategories, $tempData);

                            } else {
                                $itemSpecific['specifics'] = array();
                            }
                        }
                        //---------
                    }
                }
            }
        }

        $response['item_specifics'] = $itemSpecific;
        //------------------------

        exit(json_encode($response));
    }

    //#############################################

    public function getEbayStoreByAccountAction()
    {
        // Get selected account
        //------------------------------
        $accountId = $this->getRequest()->getParam('account_id');
        $accountModel = Mage::getModel('M2ePro/Accounts')->loadInstance($accountId);
        //------------------------------

        // Get ebay store information
        //------------------------------
        $store = array(
            'title' => Mage::helper('M2ePro')->escapeHtml($accountModel->getEbayStoreTitle()),
            'url' => $accountModel->getEbayStoreUrl(),
            'subscription_level' => Mage::helper('M2ePro')->escapeHtml($accountModel->getEbayStoreSubscriptionLevel()),
            'description' => Mage::helper('M2ePro')->escapeHtml($accountModel->getEbayStoreDescription())
        );
        //------------------------------

        // Get ebay store categories
        //------------------------------
        $categories = $accountModel->getEbayStoreCategories();
        $treeTemp = array(); $treeFinal = array();
        foreach ($categories as $category) {
            $treeTemp[$category['parent_id']][$category['category_id']] = $category;
        }
        $this->ebayStoreBuildTree($treeTemp, $treeFinal);
        //------------------------------

        exit(json_encode(array(
            'information'   => $store,
            'categories' => $treeFinal
        )));
    }

    public function updateEbayStoreByAccountAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $accountModel = Mage::getModel('M2ePro/Accounts')->loadInstance($accountId);
        $accountModel->updateEbayStoreInfo();
    }

    private function ebayStoreBuildTree($tree, &$flatTree, $pid = 0, $level = 0)
    {
        if (empty($tree[$pid])) {
            return;
        }

        foreach ($tree[$pid] as $i => $c) {

	        $id = $tree[$pid][$i]['category_id'];
	        $isLeaf = empty($tree[$id]) ? 1 : 0;

	        $prefix = '';
	        if($level > 0) {
                $prefix = str_repeat('&nbsp;', $isLeaf ? 2:  4);
                $prefix .= str_repeat('|---&nbsp;&nbsp;', $level);
	        }
	        
	        $flatTree[] = array(
                'id'    => $id,
                'title' => $prefix . $tree[$pid][$i]['title'],
	            'is_leaf' => $isLeaf
            );
            
            $this->ebayStoreBuildTree($tree, $flatTree, $tree[$pid][$i]['category_id'], $level + 1);
        }
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/*/index');
        }

        $id = $this->getRequest()->getParam('id');
        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');

        // Base prepare
        //--------------------
        $data = array();
        //--------------------

        // tab: general
        //--------------------
        $keys = array(
            'title',

            'account_id',
            'marketplace_id',

            'categories_mode',
            'categories_main_id',
            'categories_main_attribute',
            'categories_secondary_id',
            'categories_secondary_attribute',

            'store_categories_main_mode',
            'store_categories_main_attribute',
            'store_categories_secondary_mode',
            'store_categories_secondary_attribute',

            'sku_mode',

            'variation_enabled',
            'variation_ignore'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);
        
        $data['store_categories_main_id'] = isset($post['store_categories_main_id']) ? $post['store_categories_main_id'] : '';
        $data['store_categories_secondary_id'] = isset($post['store_categories_secondary_id']) ? $post['store_categories_secondary_id'] : '';

        if ((int)$data['categories_mode'] == Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_ATTRIBUTE) {
            $data['variation_enabled'] = Ess_M2ePro_Model_ListingsTemplates::VARIATION_ENABLED;
        }
        //--------------------

        // tab: specifics
        //--------------------
        $keys = array(
            'product_details_isbn_mode',
            'product_details_isbn_cv',
            'product_details_isbn_ca',

            'product_details_epid_mode',
            'product_details_epid_cv',
            'product_details_epid_ca',

            'product_details_upc_mode',
            'product_details_upc_cv',
            'product_details_upc_ca',

            'product_details_ean_mode',
            'product_details_ean_cv',
            'product_details_ean_ca'
        );

        $data['product_details'] = array();
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data['product_details'][$key] = $post[$key];
            }
        }
        $data['product_details'] = json_encode($data['product_details']);

        $data['condition_value'] = isset($post['condition_value']) ? $post['condition_value'] : '';
        $data['condition_attribute'] = isset($post['condition_attribute']) ? $post['condition_attribute'] : '';
        //--------------------

        // tab: listing upgrades
        //--------------------
        isset($post['enhancement']) || $post['enhancement'] = array();
        $data['enhancement'] = implode(',', $post['enhancement']);
        $data['gallery_type'] = $post['gallery_type'];
        //--------------------

        // tab: shipping
        //--------------------
        $data['country'] = $post['country'];
        $data['postal_code'] = $post['postal_code'];
        $data['address'] = $post['address'];

        $data['use_ebay_local_shipping_rate_table'] = isset($post['use_ebay_local_shipping_rate_table']) ? $post['use_ebay_local_shipping_rate_table'] : 0;
        $data['use_ebay_tax_table'] = isset($post['use_ebay_tax_table']) ? $post['use_ebay_tax_table'] : 0;
        $data['vat_percent'] = isset($post['vat_percent']) ? str_replace(',', '.', $post['vat_percent']) : 0;

        $data['get_it_fast'] = isset($post['get_it_fast']) ? $post['get_it_fast'] : Ess_M2ePro_Model_ListingsTemplates::GET_IT_FAST_DISABLED;
        $data['dispatch_time'] = isset($post['dispatch_time']) ? $post['dispatch_time'] : '';

        $data['local_shipping_mode'] = $post['local_shipping_mode'];
        $data['local_shipping_discount_mode'] = empty($post['local_shipping_discount_mode']) ? 0 : 1;

        $data['international_shipping_mode'] = isset($post['international_shipping_mode']) ? $post['international_shipping_mode'] : Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_NO_INTERNATIONAL;
        $data['international_shipping_discount_mode'] = empty($post['international_shipping_discount_mode']) ? 0 : 1;
        //--------------------

        // tab: payment
        //--------------------
        $data['pay_pal_email_address'] = $post['pay_pal_email_address'];
        $data['pay_pal_immediate_payment'] =  isset($post['pay_pal_immediate_payment']) ? $post['pay_pal_immediate_payment'] : 0;
        //--------------------

        // tab: return policy
        //--------------------
        $data['refund_accepted'] = $post['refund_accepted'];
        $data['refund_option'] = isset($post['refund_option']) ? $post['refund_option'] : '';
        $data['refund_within'] = isset($post['refund_within']) ? $post['refund_within'] : '';
        $data['refund_shippingcost'] =isset($post['refund_shippingcost']) ? $post['refund_shippingcost'] : '';
        $data['refund_description'] = isset($post['refund_description']) ? $post['refund_description'] : '';
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::getModel('M2ePro/ListingsTemplates');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
        //--------------------

        // Attribute sets
        //--------------------
        $oldAttributeSets = Mage::getModel('M2ePro/TemplatesAttributeSets')
                                    ->getCollection()
                                    ->addFieldToFilter('template_type',Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_LISTING)
                                    ->addFieldToFilter('template_id',(int)$id)
                                    ->getItems();
        foreach ($oldAttributeSets as $oldAttributeSet) {
            /** @var $oldAttributeSet Ess_M2ePro_Model_TemplatesAttributeSets */
            $oldAttributeSet->deleteInstance();
        }

        if (!is_array($post['attribute_sets'])) {
            $post['attribute_sets'] = explode(',', $post['attribute_sets']);
        }
        foreach ($post['attribute_sets'] as $newAttributeSet) {
            $dataForAdd = array(
                'template_type' => Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_LISTING,
                'template_id' => (int)$id,
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/TemplatesAttributeSets')->setData($dataForAdd)->save();
        }
        //--------------------

        // tab: item specifics
        //--------------------
        $connWrite->delete(Mage::getResourceModel('M2ePro/ListingsTemplatesSpecifics')->getMainTable(),
                           array('listing_template_id = ?'=>(int)$id));

        $itemSpecifics = array();
        for ($i=0; true; $i++) {
            if (!isset($post['item_specifics_mode_'.$i])) {
                break;
            }
            $ebayRecommendedTemp = array();
            if (isset($post['item_specifics_value_ebay_recommended_'.$i])) {
                $ebayRecommendedTemp = (array)$post['item_specifics_value_ebay_recommended_'.$i];
            }
            foreach ($ebayRecommendedTemp as $key=>$temp) {
                $tempParsed = explode('-|-||-|-',$temp);
                $ebayRecommendedTemp[$key] = array(
                    'id' => base64_decode($tempParsed[0]),
                    'value' => base64_decode($tempParsed[1])
                );
            }
            $itemSpecifics[] = array(
                'listing_template_id'    => (int)$id,
                'mode'                   => (int)$post['item_specifics_mode_'.$i],
                'mode_relation_id'       => (int)$post['item_specifics_mode_relation_id_'.$i],
                'attribute_id'           => $post['item_specifics_attribute_id_'.$i],
                'attribute_title'        => $post['item_specifics_attribute_title_'.$i],
                'value_mode'             => (int)$post['item_specifics_value_mode_'.$i],
                'value_ebay_recommended' => json_encode($ebayRecommendedTemp),
                'value_custom_value'     => $post['item_specifics_value_custom_value_'.$i],
                'value_custom_attribute' => $post['item_specifics_value_custom_attribute_'.$i]
            );
        }

        if (count($itemSpecifics) > 0) {
            $connWrite->insertMultiple($coreRes->getTableName('M2ePro/ListingsTemplatesSpecifics'), $itemSpecifics);
        }
        //--------------------

        // tab: shipping
        //--------------------
        $connWrite->delete(Mage::getResourceModel('M2ePro/ListingsTemplatesShippings')->getMainTable(),
                           array('listing_template_id = ?'=>(int)$id));

        $shippings = array();
        foreach ($post['cost_mode'] as $i => $costMode) {

            if ($i === '%i%') { // NB! do not remove 3rd "="
                continue; // this is template, not real data
            }

            isset($post['shippingLocation'][$i]) || $post['shippingLocation'][$i] = array();
            $locations = array();
            foreach ($post['shippingLocation'][$i] as $location) {
                $locations[] = $location;
            }

            $valA = isset($post['shipping_cost_attribute'][$i]) ? $post['shipping_cost_attribute'][$i] : '';
            $valC = isset($post['shipping_cost_value'][$i]) ? $post['shipping_cost_value'][$i] : '';

            $val2A = isset($post['shipping_cost_additional_attribute'][$i]) ? $post['shipping_cost_additional_attribute'][$i] : '';
            $val2C = isset($post['cost_additional_items'][$i]) ? $post['cost_additional_items'][$i] : '';

            $shippings[] = array(
                'listing_template_id'   => $id,
                'cost_mode'             => $costMode, // 0 - free, 1 - cv, 2 - ca, 3 - calc
                'cost_value'            => $costMode == 2 ? $valA : $valC,
                'shipping_value'        => $post['shipping_service'][$i],
                'shipping_type'         => $post['shipping_type'][$i] == 'local' ? 0 : 1,
                'cost_additional_items' => $costMode == 2 ? $val2A : $val2C,
                'priority'              => $post['shipping_priority'][$i],
                'locations'             => json_encode($locations)
            );
        }
        $shippings && $connWrite->insertMultiple($coreRes->getTableName('M2ePro/ListingsTemplatesShippings'), $shippings);

        $connWrite->delete(Mage::getResourceModel('M2ePro/ListingsTemplatesCalculatedShipping')->getMainTable(),
                           array('listing_template_id = ?'=>(int)$id));

        if ($post['local_shipping_mode'] == Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_CALCULATED ||
            $post['international_shipping_mode'] == Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_CALCULATED) {

            $keys = array(
                'measurement_system',
                'originating_postal_code',

                'package_size_mode',
                'package_size_ebay',
                'package_size_attribute',

                'dimension_mode',
                'dimension_width',
                'dimension_height',
                'dimension_depth',
                'dimension_width_attribute',
                'dimension_height_attribute',
                'dimension_depth_attribute',

                'weight_mode',
                'weight_minor',
                'weight_major',
                'weight_attribute',

                'local_handling_cost_mode',
                'local_handling_cost_value',
                'local_handling_cost_attribute',

                'international_handling_cost_mode',
                'international_handling_cost_value',
                'international_handling_cost_attribute'
            );

            $calculatedShipping = array('listing_template_id' => (int)$id);
            foreach ($keys as $key) {
                $calculatedShipping[$key] = isset($post[$key]) ? $post[$key] : '';
            }

            Mage::getModel('M2ePro/ListingsTemplatesCalculatedShipping')->setData($calculatedShipping)->save();
        }
        //--------------------

        // tab: payment
        //--------------------
        $connWrite->delete(Mage::getResourceModel('M2ePro/ListingsTemplatesPayments')->getMainTable(),
                           array('listing_template_id = ?'=>(int)$id));

        isset($post['payments']) || $post['payments'] = array();

        $payments = array();
        foreach ($post['payments'] as $payment) {
            $payments[] = array(
                'listing_template_id' => $id,
                'payment_id' => $payment
            );
        }
        $payments && $connWrite->insertMultiple($coreRes->getTableName('M2ePro/ListingsTemplatesPayments'), $payments);
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to remove'));
            return $this->_redirect('*/*/index');
        }

        $idsForDelete = array();
        !is_null($id) && $idsForDelete[] = (int)$id;
        !is_null($ids) && $idsForDelete = array_merge($idsForDelete,(array)$ids);

        $deleted = $locked = 0;
        foreach ($idsForDelete as $id) {
            $template = Mage::getModel('M2ePro/ListingsTemplates')->loadInstance($id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%count% record(s) were successfully deleted');
        $deleted && $this->_getSession()->addSuccess(str_replace('%count%',$deleted,$tempString));

        $tempString = Mage::helper('M2ePro')->__('%count% record(s) are in use in Listing(s). Template must not be in use.');
        $locked && $this->_getSession()->addError(str_replace('%count%',$locked,$tempString));

        $this->_redirect('*/*/index');
    }

    //#############################################
}