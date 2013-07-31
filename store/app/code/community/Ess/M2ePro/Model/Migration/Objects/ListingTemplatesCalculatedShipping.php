<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_ListingTemplatesCalculatedShipping extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_calculated_shipping';
    const TABLE_NAME_NEW = 'm2epro_listings_templates_calculated_shipping';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_ListingTemplatesCalculatedShipping');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldCalculatedShipping = $pdoStmt->fetch()) {

            $listingTemplateId = $this->tempDbTable->getNewValue('listing_templates.id',(int)$oldCalculatedShipping['template_id']);
            if ($listingTemplateId === false) {
                continue;
            }

            $tempCollection = Mage::getModel('M2ePro/ListingsTemplatesCalculatedShipping')->getCollection();
            $tempCollection->addFieldToFilter('listing_template_id', (int)$listingTemplateId);
            if ((int)$tempCollection->getSize() > 0) {
                continue;
            }

            /** @var $listingTemplateModel Ess_M2ePro_Model_ListingsTemplates */
            $listingTemplateModel = Mage::getModel('M2ePro/ListingsTemplates')->loadInstance($listingTemplateId);
            $marketplaceId = $listingTemplateModel->getMarketplace()->getId();

            $measurementSystem = 0;
            $marketplaceId == 0 && $measurementSystem = 1;
            in_array($marketplaceId,array(2,15,210)) && $measurementSystem = 2;

            $newCalculatedShipping = array(
                'listing_template_id' => (int)$listingTemplateId,

                'measurement_system' => $measurementSystem,
                'originating_postal_code' => $oldCalculatedShipping['post_code'],

                'package_size_mode' => (int)$oldCalculatedShipping['package_size_mode'],
                'package_size_ebay' => $oldCalculatedShipping['package_size'],
                'package_size_attribute' => $oldCalculatedShipping['package_size_attribute'],

                'dimension_mode' => (int)$oldCalculatedShipping['dimentions_mode'],
                'dimension_width' => $oldCalculatedShipping['width'],
                'dimension_width_attribute' => $oldCalculatedShipping['dimentions_attribute_width'],
                'dimension_height' => $oldCalculatedShipping['height'],
                'dimension_height_attribute' => $oldCalculatedShipping['dimentions_attribute_height'],
                'dimension_depth' => $oldCalculatedShipping['depth'],
                'dimension_depth_attribute' => $oldCalculatedShipping['dimentions_attribute_depth'],

                'weight_mode' => (int)$oldCalculatedShipping['calculation_weght_mode'],
                'weight_minor' => $oldCalculatedShipping['weight_lbs'],
                'weight_major' => $oldCalculatedShipping['weight_oz'],
                'weight_attribute' => $oldCalculatedShipping['weight_attribute_oz'],

                'local_handling_cost_mode' => (int)$oldCalculatedShipping['handling_feed_mode'],
                'local_handling_cost_value' => $oldCalculatedShipping['domestic_fee'],
                'local_handling_cost_attribute' => $oldCalculatedShipping['handling_feed_attribute'],

                'international_handling_cost_mode' => (int)$oldCalculatedShipping['inthandling_feed_mode'],
                'international_handling_cost_value' => $oldCalculatedShipping['international_fee'],
                'international_handling_cost_attribute' => $oldCalculatedShipping['international_handling_fee']
            );

            if ($newCalculatedShipping['package_size_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::PACKAGE_SIZE_EBAY) {
                $newListingTemplate['package_size_ebay'] = '';
            }
            if ($newCalculatedShipping['package_size_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::PACKAGE_SIZE_CUSTOM_ATTRIBUTE) {
                $newListingTemplate['package_size_attribute'] = '';
            }

            if ($newCalculatedShipping['dimension_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::DIMENSIONS_CUSTOM_VALUE) {
                $newListingTemplate['dimension_width'] = '';
                $newListingTemplate['dimension_height'] = '';
                $newListingTemplate['dimension_depth'] = '';
            }
            if ($newCalculatedShipping['dimension_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::DIMENSIONS_CUSTOM_ATTRIBUTE) {
                $newListingTemplate['dimension_width_attribute'] = '';
                $newListingTemplate['dimension_height_attribute'] = '';
                $newListingTemplate['dimension_depth_attribute'] = '';
            }

            if ($newCalculatedShipping['weight_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::WEIGHT_CUSTOM_VALUE) {
                $newListingTemplate['weight_minor'] = '';
                $newListingTemplate['weight_major'] = '';
            }
            if ($newCalculatedShipping['weight_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::WEIGHT_CUSTOM_ATTRIBUTE) {
                $newListingTemplate['weight_attribute'] = '';
            }

            if ($newCalculatedShipping['local_handling_cost_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::HANDLING_CUSTOM_VALUE) {
                $newListingTemplate['local_handling_cost_value'] = '';
            }
            if ($newCalculatedShipping['local_handling_cost_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::HANDLING_CUSTOM_ATTRIBUTE) {
                $newListingTemplate['local_handling_cost_attribute'] = '';
            }

            if ($newCalculatedShipping['international_handling_cost_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::HANDLING_CUSTOM_VALUE) {
                $newListingTemplate['international_handling_cost_value'] = '';
            }
            if ($newCalculatedShipping['international_handling_cost_mode'] != Ess_M2ePro_Model_ListingsTemplatesCalculatedShipping::HANDLING_CUSTOM_ATTRIBUTE) {
                $newListingTemplate['international_handling_cost_attribute'] = '';
            }
            
            $existCalculatedShipping = $this->getLikeExistItem($newCalculatedShipping,false);
            if (!is_null($existCalculatedShipping)) {
                $this->tempDbTable->addValue('listing_templates_calculated_shipping.id',(int)$oldCalculatedShipping['id'],(int)$existCalculatedShipping['listing_template_id']);
            } else {
                $this->mySqlWriteConnection->insert($this->tableNameNew,$newCalculatedShipping);
                $this->tempDbTable->addValue('listing_templates_calculated_shipping.id',(int)$oldCalculatedShipping['id'],(int)$newCalculatedShipping['listing_template_id']);
            }
        }
    }

    // ########################################
}