<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_ListingTemplatesShippings extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_listing_item_shipping';
    const TABLE_NAME_NEW = 'm2epro_listings_templates_shippings';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_ListingTemplatesShippings');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldShipping = $pdoStmt->fetch()) {

            $listingTemplateId = $this->tempDbTable->getNewValue('listing_templates.id',(int)$oldShipping['template_id']);
            if ($listingTemplateId === false) {
                continue;
            }

            $oldListingTemplate = $this->getOldListingTemplate((int)$oldShipping['template_id']);
            if (is_null($oldListingTemplate)) {
                continue;
            }

            $newShipping = array(
                'listing_template_id' => (int)$listingTemplateId,

                'priority' => (int)$oldShipping['priority'],
                
                'cost_mode' => (int)$oldShipping['cost_mode'],
                'cost_value' => $oldShipping['cost_value'],
                'cost_additional_items' => $oldShipping['cost_additional_items'],

                'shipping_type' => (int)$oldShipping['shipping_type'],
                'shipping_value' => $oldShipping['shipping_value']
            );

            if ($newShipping['cost_mode'] == Ess_M2ePro_Model_ListingsTemplatesShippings::SHIPPING_FREE) {
                $newShipping['cost_value'] = 0;
                $newShipping['cost_additional_items'] = 0;
            } else if ($newShipping['cost_mode'] == Ess_M2ePro_Model_ListingsTemplatesShippings::SHIPPING_CUSTOM_VALUE) {
                if (is_null($newShipping['cost_additional_items']) || $newShipping['cost_additional_items'] == '') {
                    $newShipping['cost_additional_items'] = $newShipping['cost_value'];
                }
                //$newShipping['cost_value'] = str_replace(',','.',$newShipping['cost_value']);
                //$newShipping['cost_additional_items'] = str_replace(',','.',$newShipping['cost_additional_items']);
                //$newShipping['cost_value'] = (string)round((float)$newShipping['cost_value'],2);
                //$newShipping['cost_additional_items'] = (string)round((float)$newShipping['cost_additional_items'],2);
            }

            if (is_null($oldListingTemplate['ship_to_location']) || $oldListingTemplate['ship_to_location'] == '' ||
                $newShipping['shipping_type'] == Ess_M2ePro_Model_ListingsTemplatesShippings::TYPE_LOCAL) {
                $newShipping['locations'] = json_encode(array());
            } else {
                $shipToLocationNew = array();
                $shipToLocationOld = json_decode($oldListingTemplate['ship_to_location']);
                foreach ($shipToLocationOld as $item) {
                    $item = (array)$item;
                    if (isset($item['name'])) {
                        $shipToLocationNew[] = (string)$item['name'];
                    }
                }
                foreach ($shipToLocationNew as $item) {
                    if (strtolower($item) == 'worldwide') {
                        $shipToLocationNew = array();
                        $shipToLocationNew[] = $item;
                        break;
                    }
                }
                $newShipping['locations'] = json_encode($shipToLocationNew);
            }

            $existShipping = $this->getLikeExistItem($newShipping,false);
            if (!is_null($existShipping)) {
                $this->tempDbTable->addValue('listing_templates_shippings.id',(int)$oldShipping['id'],(int)$existShipping['id']);
            } else {
                $this->mySqlWriteConnection->insert($this->tableNameNew,$newShipping);
                $newShippingId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');
                $this->tempDbTable->addValue('listing_templates_shippings.id',(int)$oldShipping['id'],(int)$newShippingId);
            }
        }
    }

    // ########################################

    protected function getOldListingTemplate($oldListingTemplateId)
    {
        $tableName  = Mage::getSingleton('core/resource')->getTableName('m2e');

        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($tableName,'*')
                                              ->where('`ebay_id` = ?',(int)$oldListingTemplateId);

        $row = $this->mySqlReadConnection->fetchRow($dbSelect);

        if ($row === false) {
            return NULL;
        }

        return $row;
    }

    // ########################################
}