<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_SellingFormatTemplates extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e';
    const TABLE_NAME_NEW = 'm2epro_selling_formats_templates';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_SellingFormatTemplates');
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

            $oldPriceTemplate = $this->getOldPriceTemplate($oldListingTemplate);

            $attributeSetTemp = array(
                'template_type' => Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_SELLING_FORMAT,
                'attribute_set_id' => (int)$oldListingTemplate['attribute_set']
            );

            $newSellingFormat = array(
                'title' => !is_null($oldPriceTemplate) ? $oldListingTemplate['title'].' - '.$oldPriceTemplate['title'] : $oldListingTemplate['title'],

                'listing_type' => (int)$this->getListingType($oldListingTemplate['listingType'], $oldListingTemplate['auction_type']),
                'listing_type_attribute' => $oldListingTemplate['action_type_attribute'],

                'listing_is_private' => (int)$oldListingTemplate['listing_private'],

                'duration_ebay' => (int)$oldListingTemplate['duration'],
                'duration_attribute' => '',

                'qty_mode' => (int)$oldListingTemplate['item_qty_mode'] + 1,
                'qty_custom_value' => (int)$oldListingTemplate['qty_template'],
                'qty_custom_attribute' => $oldListingTemplate['select_attributes_for_qty'],

                'currency' => !is_null($oldPriceTemplate) ? $oldPriceTemplate['currency']: $oldListingTemplate['currency'],

                'price_variation_mode' => Ess_M2ePro_Model_SellingFormatTemplates::PRICE_VARIATION_MODE_PARENT,

                'buyitnow_price_mode' => !is_null($oldPriceTemplate) ? (int)$this->getPriceMode($oldPriceTemplate['start_price_mode']) :
                                                                    (int)$this->getPriceMode($oldListingTemplate['start_price_mode']),
                'buyitnow_price_coefficient' => !is_null($oldPriceTemplate) ? $oldPriceTemplate['price_coeficient'] : $oldListingTemplate['price_coeficient'],
                'buyitnow_price_custom_attribute' => !is_null($oldPriceTemplate) ? ((int)$oldPriceTemplate['start_price_mode'] == 4 ? $oldPriceTemplate['use_price_from'] : '') :
                                                                                ((int)$oldListingTemplate['start_price_mode'] == 4 ? $oldListingTemplate['use_price_from'] : ''),

                'reserve_price_mode' => !is_null($oldPriceTemplate) ? (int)$this->getPriceMode($oldPriceTemplate['reserve_price_mode']) :
                                                                      (int)$this->getPriceMode($oldListingTemplate['reserve_price_mode']),
                'reserve_price_coefficient' => !is_null($oldPriceTemplate) ? $oldPriceTemplate['reserve_coeficient'] : $oldListingTemplate['reserve_coeficient'],
                'reserve_price_custom_attribute' => !is_null($oldPriceTemplate) ? ((int)$oldPriceTemplate['reserve_price_mode'] == 4 ? $oldPriceTemplate['use_reserve_from'] : '') :
                                                                                  ((int)$oldListingTemplate['reserve_price_mode'] == 4 ? $oldListingTemplate['use_reserve_from'] : ''),

                'start_price_mode' => !is_null($oldPriceTemplate) ? (int)$this->getPriceMode($oldPriceTemplate['buynow_price_mode']) :
                                                                       (int)$this->getPriceMode($oldListingTemplate['buynow_price_mode']),
                'start_price_coefficient' => !is_null($oldPriceTemplate) ? $oldPriceTemplate['now_coeficient'] : $oldListingTemplate['now_coeficient'],
                'start_price_custom_attribute' => !is_null($oldPriceTemplate) ? ((int)$oldPriceTemplate['buynow_price_mode'] == 4 ? $oldPriceTemplate['use_now_from'] : '') :
                                                                                   ((int)$oldListingTemplate['buynow_price_mode'] == 4 ? $oldListingTemplate['use_now_from'] : ''),

                'best_offer_mode' => (int)$oldListingTemplate['use_best_offer'],
                'best_offer_accept_mode' => (int)$oldListingTemplate['accept_offer_mode'],
                'best_offer_accept_value' => (int)$oldListingTemplate['accept_offer_mode'] == Ess_M2ePro_Model_SellingFormatTemplates::BEST_OFFER_ACCEPT_MODE_PERCENTAGE ? $oldListingTemplate['best_accp_input'] : '',
                'best_offer_accept_attribute' => (int)$oldListingTemplate['accept_offer_mode'] == Ess_M2ePro_Model_SellingFormatTemplates::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE ? $oldListingTemplate['best_acc_attribute'] : '',
                'best_offer_reject_mode' => (int)$oldListingTemplate['reject_offer_mode'],
                'best_offer_reject_value' => (int)$oldListingTemplate['reject_offer_mode'] == Ess_M2ePro_Model_SellingFormatTemplates::BEST_OFFER_REJECT_MODE_PERCENTAGE ? $oldListingTemplate['best_regp_input'] : '',
                'best_offer_reject_attribute' => (int)$oldListingTemplate['reject_offer_mode'] == Ess_M2ePro_Model_SellingFormatTemplates::BEST_OFFER_REJECT_MODE_ATTRIBUTE ? $oldListingTemplate['best_reg_attribute'] : ''
            );

            if (!in_array($newSellingFormat['start_price_mode'],array(Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT,Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL))) {
                $newSellingFormat['start_price_coefficient'] = '';
            }
            if (!in_array($newSellingFormat['reserve_price_mode'],array(Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT,Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL))) {
                $newSellingFormat['reserve_price_coefficient'] = '';
            }
            if (!in_array($newSellingFormat['buyitnow_price_mode'],array(Ess_M2ePro_Model_SellingFormatTemplates::PRICE_PRODUCT,Ess_M2ePro_Model_SellingFormatTemplates::PRICE_SPECIAL))) {
                $newSellingFormat['buyitnow_price_coefficient'] = '';
            }

            if ($newSellingFormat['start_price_mode'] != Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                $newSellingFormat['start_price_custom_attribute'] = '';
            }
            if ($newSellingFormat['reserve_price_mode'] != Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                $newSellingFormat['reserve_price_custom_attribute'] = '';
            }
            if ($newSellingFormat['buyitnow_price_mode'] != Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE) {
                $newSellingFormat['buyitnow_price_custom_attribute'] = '';
            }

            if (!in_array($newSellingFormat['qty_mode'],array(Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_SINGLE,Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_NUMBER))) {
                $newSellingFormat['qty_custom_value'] = 1;
            }
            if ($newSellingFormat['qty_mode'] != Ess_M2ePro_Model_SellingFormatTemplates::QTY_MODE_ATTRIBUTE) {
                $newSellingFormat['qty_custom_attribute'] = '';
            }

            if ($newSellingFormat['listing_type'] != Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_ATTRIBUTE) {
                $newSellingFormat['listing_type_attribute'] = '';
            }

            $existSellingFormatTemplate = $this->getLikeExistItem($newSellingFormat,true,$attributeSetTemp);
            if (!is_null($existSellingFormatTemplate)) {
                $this->tempDbTable->addValue('selling_format_templates.id',(int)$oldListingTemplate['ebay_id'],(int)$existSellingFormatTemplate['id']);
            } else {
                $currentTimestamp = Mage::helper('M2ePro')->getCurrentGmtDate();
                $newSellingFormat['synch_date'] = $currentTimestamp;
                $newSellingFormat['create_date'] = $currentTimestamp;
                $newSellingFormat['update_date'] = $currentTimestamp;
                
                $this->mySqlWriteConnection->insert($this->tableNameNew,$newSellingFormat);
                $newSellingFormatId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');

                $tasTable = Mage::getResourceModel('M2ePro/TemplatesAttributeSets')->getMainTable();
                $attributeSetTemp['template_id'] = (int)$newSellingFormatId;
                $attributeSetTemp['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $attributeSetTemp['update_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $this->mySqlWriteConnection->insert($tasTable,$attributeSetTemp);

                $this->tempDbTable->addValue('selling_format_templates.id',(int)$oldListingTemplate['ebay_id'],(int)$newSellingFormatId);
            }
        }
    }

    // ########################################

    protected function getOldPriceTemplate($oldListingTemplate)
    {
        if ((int)$oldListingTemplate['template_price_title'] <= 0 ||
            (int)$oldListingTemplate['modify_pricetemplate'] != 0) {
            return NULL;
        }

        $oldId = (int)$oldListingTemplate['template_price_title'];
        $tableName  = Mage::getSingleton('core/resource')->getTableName('m2epricetemplates');

        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($tableName,'*')
                                              ->where('`id` = ?',(int)$oldId);

        $row = $this->mySqlReadConnection->fetchRow($dbSelect);

        if ($row === false) {
            return NULL;
        }

        return $row;
    }

    //------------------------------

    protected function getListingType($oldValueListingMode, $oldValueListingType)
    {
        if ($oldValueListingMode == 1) {
            return Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_ATTRIBUTE;
        }
        return ($oldValueListingType == Ess_M2ePro_Model_SellingFormatTemplates::EBAY_LISTING_TYPE_FIXED) ?
                Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_FIXED :
                Ess_M2ePro_Model_SellingFormatTemplates::LISTING_TYPE_AUCTION;
    }

    protected function getPriceMode($oldValue)
    {
        if ($oldValue == 4) {
            return Ess_M2ePro_Model_SellingFormatTemplates::PRICE_ATTRIBUTE;
        }
        return $oldValue;
    }

    // ########################################
}