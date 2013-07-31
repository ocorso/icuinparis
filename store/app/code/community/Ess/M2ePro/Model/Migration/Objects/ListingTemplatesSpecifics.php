<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_ListingTemplatesSpecifics extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_item_specifics';
    const TABLE_NAME_NEW = 'm2epro_listings_templates_specifics';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_ListingTemplatesSpecifics');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldSpecific = $pdoStmt->fetch()) {

            $listingTemplateId = $this->tempDbTable->getNewValue('listing_templates.id',(int)$oldSpecific['template_id']);
            if ($listingTemplateId === false) {
                continue;
            }

            /** @var $newListingTemplateModel Ess_M2ePro_Model_ListingsTemplates */
            $newListingTemplateModel = Mage::getModel('M2ePro/ListingsTemplates')->loadInstance($listingTemplateId);
            
            $srcCategory = $newListingTemplateModel->getCategoriesSource();
            if ($srcCategory['mode'] != Ess_M2ePro_Model_ListingsTemplates::CATEGORIES_MODE_EBAY) {
                continue;
            }

            $mode = Ess_M2ePro_Model_ListingsTemplatesSpecifics::MODE_ITEM_SPECIFICS;
            $modeRelationId = (int)$srcCategory['main_value'];

            $attributeId = $oldSpecific['name'];
            $attributeTitle = $oldSpecific['name'];

            $valueMode = (int)$oldSpecific['type'];
            (int)$oldSpecific['type'] == 2 && $valueMode = 3;
            (int)$oldSpecific['type'] == 3 && $valueMode = 2;

            if ($valueMode != Ess_M2ePro_Model_ListingsTemplatesSpecifics::VALUE_MODE_NONE &&
                $oldSpecific['content'] == '') {
                continue;
            }
            
            if ($valueMode == Ess_M2ePro_Model_ListingsTemplatesSpecifics::VALUE_MODE_EBAY_RECOMMENDED) {
                $valueEbayRecommendedId = $oldSpecific['content'];
                $valueEbayRecommendedValue = $oldSpecific['content'];
            }

            if ((int)$oldSpecific['name']{0} > 0) {
                
                $mode = Ess_M2ePro_Model_ListingsTemplatesSpecifics::MODE_ATTRIBUTE_SET;
                $tempData = $this->getDictionaryByCategoryId($newListingTemplateModel->getMarketplace()->getId(),
                                                             (int)$srcCategory['main_value']);
                if (is_null($tempData['attribute_set_id'])) {
                    continue;
                }
                $modeRelationId = (int)$tempData['attribute_set_id'];

                $attributeId = $oldSpecific['name'];
                foreach ($tempData['attribute_set'] as $tempItem) {
                    if ((int)$tempItem['id'] == (int)$attributeId) {
                        $attributeTitle = $tempItem['title'];
                        break;
                    }
                }

                if ($valueMode == Ess_M2ePro_Model_ListingsTemplatesSpecifics::VALUE_MODE_EBAY_RECOMMENDED) {
                    $valueEbayRecommendedId = $oldSpecific['content'];
                    foreach ($tempData['attribute_set'] as $tempItem) {
                        if ((int)$tempItem['id'] == (int)$attributeId) {
                            foreach ($tempItem['values'] as $tempValue) {
                                if ((int)$tempValue['id'] == (int)$valueEbayRecommendedId) {
                                    $valueEbayRecommendedValue = $tempValue['value'];
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }

            $newSpecific = array(
                'listing_template_id' => (int)$listingTemplateId,

                'mode' => (int)$mode,
                'mode_relation_id' => (int)$modeRelationId,

                'attribute_id' => $attributeId,
                'attribute_title' => $attributeTitle,

                'value_mode' => $valueMode,
                'value_ebay_recommended' => $valueMode == 1 ? json_encode(array(array('id'=>$valueEbayRecommendedId,'value'=>$valueEbayRecommendedValue))) : json_encode(array()),
                'value_custom_value' => $valueMode == 2 ? $oldSpecific['content'] : '',
                'value_custom_attribute' => $valueMode == 3 ? $oldSpecific['content'] : ''
            );

            $existSpecific = $this->getLikeExistItem($newSpecific,false);
            if (!is_null($existSpecific)) {
                $this->tempDbTable->addValue('listing_templates_specifics.id',(int)$oldSpecific['id'],(int)$existSpecific['id']);
            } else {
                $this->mySqlWriteConnection->insert($this->tableNameNew,$newSpecific);
                $newSpecificId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');
                $this->tempDbTable->addValue('listing_templates_specifics.id',(int)$oldSpecific['id'],(int)$newSpecificId);
            }
        }
    }

    // ########################################

    protected function getDictionaryByCategoryId($marketplaceId, $categoryId)
    {
        $tableDictCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_categories');

        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($tableDictCategories,'*')
                                              ->where('`marketplace_id` = ?',(int)$marketplaceId)
                                              ->where('`category_id` = ?',(int)$categoryId);

        $row = $this->mySqlReadConnection->fetchRow($dbSelect);

        if ($row === false) {
            return NULL;
        }

        if (!is_null($row['attribute_set_id'])) {
            if (is_null($row['attribute_set'])) {
                
                $attributeSet = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                                            ->processVirtual('marketplace','get','attributesCS',
                                                              array('marketplace'=>$marketplaceId,'attribute_set_id'=>(int)$row['attribute_set_id']),'specifics',
                                                              NULL,NULL,NULL);
                if (!is_null($attributeSet)) {
                    $tempData = array(
                        'marketplace_id' => (int)$marketplaceId,
                        'category_id' => (int)$categoryId,
                        'attribute_set' => json_encode($attributeSet)
                    );
                    $this->mySqlWriteConnection->insertOnDuplicate($tableDictCategories, $tempData);
                } else {
                    $attributeSet = array();
                }

                $row['attribute_set'] = $attributeSet;
            } else {
                $row['attribute_set'] = json_decode($row['attribute_set'],true);
            }
        }

        return $row;
    }

    // ########################################
}