<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_ListingTemplates extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e';
    const TABLE_NAME_NEW = 'm2epro_listings_templates';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_ListingTemplates');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldListingTemplate = $pdoStmt->fetch()) {

            $accountId = $this->tempDbTable->getNewValue('accounts.id',(int)$oldListingTemplate['account_id']);
            if ($accountId === false) {
                continue;
            }

            $tempOldMarketplaceId = Mage::getModel('M2ePro/Marketplaces')->getIdByCode($oldListingTemplate['marketplace']);
            $marketplaceId = $this->tempDbTable->getNewValue('marketplaces.id',(int)$tempOldMarketplaceId);
            if ($marketplaceId === false) {
                continue;
            }

            $isEnabledVariation = $oldListingTemplate['use_multivariation'] || $oldListingTemplate['use_multivariation_custom'] ||
                                  $oldListingTemplate['use_multivariation_bundle'] || $oldListingTemplate['use_multivariation_grouped'];

            $attributeSetTemp = array(
                'template_type' => Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_LISTING,
                'attribute_set_id' => (int)$oldListingTemplate['attribute_set']
            );

            $newListingTemplate = array(
                'account_id' => (int)$accountId,
                'marketplace_id' => (int)$marketplaceId,

                'title' => $oldListingTemplate['title'],

                'categories_mode' => (int)$oldListingTemplate['category_selected_type'],
                'categories_main_id' => (int)$oldListingTemplate['category'],
                'categories_main_attribute' => ($oldListingTemplate['category_selected_type'] == Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_ATTRIBUTE) ? $oldListingTemplate['main_category_attribute'] : '',
                'categories_secondary_id' => (int)$oldListingTemplate['second_category'],
                'categories_secondary_attribute' => ($oldListingTemplate['category_selected_type'] == Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_ATTRIBUTE) ? $oldListingTemplate['secondary_category_attribute'] : '',

                'store_categories_main_mode' => (int)$oldListingTemplate['storecategory_selected_type'],
                'store_categories_main_id' => (int)$oldListingTemplate['shop_category_id'],
                'store_categories_main_attribute' => $oldListingTemplate['store_category_attribute'],
                'store_categories_secondary_mode' => (int)$oldListingTemplate['storecategory_selected_type2'],
                'store_categories_secondary_id' => (int)$oldListingTemplate['shop_category_id2'],
                'store_categories_secondary_attribute' => $oldListingTemplate['store_category_attribute2'],

                'sku_mode' => (int)$oldListingTemplate['sku_settings'],
                'variation_enabled' => (int)$isEnabledVariation,
                'variation_ignore' => (int)!$isEnabledVariation,

                'condition_value' => $oldListingTemplate['condition'],
                'condition_attribute' => $oldListingTemplate['item_condition_attr'],

                'product_details' => json_encode(array(
                      'product_details_isbn_mode' => $this->getPreparedProductDetailMode($oldListingTemplate['product_details_isbn_src'],$oldListingTemplate['product_details_isbn_cv'],$oldListingTemplate['product_details_isbn_ca']),
                      'product_details_isbn_cv' => $this->getPreparedProductDetailCV($oldListingTemplate['product_details_isbn_src'],$oldListingTemplate['product_details_isbn_cv'],$oldListingTemplate['product_details_isbn_ca']),
                      'product_details_isbn_ca' => $this->getPreparedProductDetailCA($oldListingTemplate['product_details_isbn_src'],$oldListingTemplate['product_details_isbn_cv'],$oldListingTemplate['product_details_isbn_ca']),

                      'product_details_epid_mode' => $this->getPreparedProductDetailMode($oldListingTemplate['product_details_epid_src'],$oldListingTemplate['product_details_epid_cv'],$oldListingTemplate['product_details_epid_ca']),
                      'product_details_epid_cv' => $this->getPreparedProductDetailCV($oldListingTemplate['product_details_epid_src'],$oldListingTemplate['product_details_epid_cv'],$oldListingTemplate['product_details_epid_ca']),
                      'product_details_epid_ca' => $this->getPreparedProductDetailCA($oldListingTemplate['product_details_epid_src'],$oldListingTemplate['product_details_epid_cv'],$oldListingTemplate['product_details_epid_ca']),

                      'product_details_upc_mode' => $this->getPreparedProductDetailMode($oldListingTemplate['product_details_upc_src'],$oldListingTemplate['product_details_upc_cv'],$oldListingTemplate['product_details_upc_ca']),
                      'product_details_upc_cv' => $this->getPreparedProductDetailCV($oldListingTemplate['product_details_upc_src'],$oldListingTemplate['product_details_upc_cv'],$oldListingTemplate['product_details_upc_ca']),
                      'product_details_upc_ca' => $this->getPreparedProductDetailCA($oldListingTemplate['product_details_upc_src'],$oldListingTemplate['product_details_upc_cv'],$oldListingTemplate['product_details_upc_ca']),

                      'product_details_ean_mode' => $this->getPreparedProductDetailMode($oldListingTemplate['product_details_ean_src'],$oldListingTemplate['product_details_ean_cv'],$oldListingTemplate['product_details_ean_ca']),
                      'product_details_ean_cv' => $this->getPreparedProductDetailCV($oldListingTemplate['product_details_ean_src'],$oldListingTemplate['product_details_ean_cv'],$oldListingTemplate['product_details_ean_ca']),
                      'product_details_ean_ca' => $this->getPreparedProductDetailCA($oldListingTemplate['product_details_ean_src'],$oldListingTemplate['product_details_ean_cv'],$oldListingTemplate['product_details_ean_ca'])
                 )),

                'enhancement' => $oldListingTemplate['enhancement'],
                'gallery_type' => (int)$oldListingTemplate['gallery_picture'],

                'country' => $oldListingTemplate['country'],
                'postal_code' => '',
                'address' => $oldListingTemplate['adress'],

                'use_ebay_tax_table' => (int)$oldListingTemplate['use_tax_table'],
                'vat_percent' => (float)$oldListingTemplate['vat_percent'],
                'get_it_fast' => Ess_M2ePro_Model_ListingsTemplates::GET_IT_FAST_DISABLED,
                'dispatch_time' => (int)$oldListingTemplate['dispatch_time'],

                'local_shipping_mode' => Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_LOCAL,
                'local_shipping_discount_mode' => (int)$oldListingTemplate['use_local_shipping_discount'],
                'international_shipping_mode' => Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_NO_INTERNATIONAL,
                'international_shipping_discount_mode' => (int)$oldListingTemplate['use_int_shipping_discount'],

                'pay_pal_email_address' => $oldListingTemplate['pp_adress'],
                'pay_pal_immediate_payment' => (int)$oldListingTemplate['immediate_payment'],
                
                'refund_accepted' => $oldListingTemplate['refund_accepted'],
                'refund_option' => $oldListingTemplate['refund'],
                'refund_within' => $oldListingTemplate['refund_within'],
                'refund_description' => $oldListingTemplate['refund_description'],
                'refund_shippingcost' => $oldListingTemplate['refund_shippingcost']
            );

            if ($newListingTemplate['categories_mode'] != Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_EBAY) {
                $newListingTemplate['categories_main_id'] = 0;
                $newListingTemplate['categories_secondary_id'] = 0;
            }
            if ($newListingTemplate['categories_mode'] != Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_ATTRIBUTE) {
                $newListingTemplate['categories_main_attribute'] = '';
                $newListingTemplate['categories_secondary_attribute'] = '';
                $newListingTemplate['condition_attribute'] = '';
            }

            if ($newListingTemplate['store_categories_main_mode'] == 1) {
                $newListingTemplate['store_categories_main_mode'] = Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_CUSTOM_ATTRIBUTE;
            } else if ($newListingTemplate['store_categories_main_mode'] == 0 && $newListingTemplate['store_categories_main_id'] != 0) {
                $newListingTemplate['store_categories_main_mode'] = Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_EBAY_VALUE;
            } else {
                $newListingTemplate['store_categories_main_mode'] = Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_NONE;
            }

            if ($newListingTemplate['store_categories_secondary_mode'] == 1) {
                $newListingTemplate['store_categories_secondary_mode'] = Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_CUSTOM_ATTRIBUTE;
            } else if ($newListingTemplate['store_categories_secondary_mode'] == 0 && $newListingTemplate['store_categories_secondary_id'] != 0) {
                $newListingTemplate['store_categories_secondary_mode'] = Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_EBAY_VALUE;
            } else {
                $newListingTemplate['store_categories_secondary_mode'] = Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_NONE;
            }

            if ($newListingTemplate['store_categories_main_mode'] != Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_CUSTOM_ATTRIBUTE) {
                $newListingTemplate['store_categories_main_attribute'] = '';
            }
            if ($newListingTemplate['store_categories_secondary_mode'] != Ess_M2ePro_Model_ListingsTemplates::STORE_CATEGORY_CUSTOM_ATTRIBUTE) {
                $newListingTemplate['store_categories_secondary_attribute'] = '';
            }

            if ($oldListingTemplate['use_local_shipping'] == 1) {
                $oldListingTemplate['use_domestic_calculated'] == 0 && $newListingTemplate['local_shipping_mode'] = Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_FLAT;
                $oldListingTemplate['use_domestic_calculated'] == 1 && $newListingTemplate['local_shipping_mode'] = Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_CALCULATED;
            }

            if ($oldListingTemplate['use_international_shipping'] == 1) {
                $oldListingTemplate['use_int_calculated'] == 0 && $newListingTemplate['international_shipping_mode'] = Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_FLAT;
                $oldListingTemplate['use_int_calculated'] == 1 && $newListingTemplate['international_shipping_mode'] = Ess_M2ePro_Model_ListingsTemplates::SHIPPING_TYPE_CALCULATED;
            }

            $existListingTemplate = $this->getLikeExistItem($newListingTemplate,true,$attributeSetTemp);
            if (!is_null($existListingTemplate)) {
                $this->tempDbTable->addValue('listing_templates.id',(int)$oldListingTemplate['ebay_id'],(int)$existListingTemplate['id']);
            } else {
                $currentTimestamp = Mage::helper('M2ePro')->getCurrentGmtDate();
                $newListingTemplate['synch_date'] = $currentTimestamp;
                $newListingTemplate['create_date'] = $currentTimestamp;
                $newListingTemplate['update_date'] = $currentTimestamp;

                $this->mySqlWriteConnection->insert($this->tableNameNew,$newListingTemplate);
                $newListingTemplateId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');

                $tasTable = Mage::getResourceModel('M2ePro/TemplatesAttributeSets')->getMainTable();
                $attributeSetTemp['template_id'] = (int)$newListingTemplateId;
                $attributeSetTemp['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $attributeSetTemp['update_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $this->mySqlWriteConnection->insert($tasTable,$attributeSetTemp);

                $this->tempDbTable->addValue('listing_templates.id',(int)$oldListingTemplate['ebay_id'],(int)$newListingTemplateId);
            }
        }
    }

    // ########################################

    private function getPreparedProductDetailMode($oldMode, $oldCV, $oldCA)
    {
        $result = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_NONE;

        if ($oldMode == 0) {
            if ($oldCV == '' || is_null($oldCV)) {
                $result = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_NONE;
            } else {
                $result = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_VALUE;
            }
        }

        if ($oldMode == 1) {
            if ($oldCA == '' || is_null($oldCA)) {
                $result = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_NONE;
            } else {
                $result = Ess_M2ePro_Model_ListingsTemplates::PRODUCT_DETAIL_MODE_CUSTOM_ATTRIBUTE;
            }
        }

        return $result;
    }

    private function getPreparedProductDetailCV($oldMode, $oldCV, $oldCA)
    {
        $result = '';

        if ($oldMode == 0) {
            if ($oldCV == '' || is_null($oldCV)) {
                $result = '';
            } else {
                $result = $oldCV;
            }
        }

        if ($oldMode == 1) {
            $result = '';
        }

        return $result;
    }

    private function getPreparedProductDetailCA($oldMode, $oldCV, $oldCA)
    {
        $result = '';

        if ($oldMode == 0) {
            $result = '';
        }

        if ($oldMode == 1) {
            if ($oldCA == '' || is_null($oldCA)) {
                $result = '';
            } else {
                $result = $oldCA;
            }
        }

        return $result;
    }

    // ########################################
}