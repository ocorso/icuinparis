<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_Listings extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_listings';
    const TABLE_NAME_NEW = 'm2epro_listings';

    protected $tableNameOldListingsTemplates = '';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_Listings');
        
        $this->tableNameOldListingsTemplates  = Mage::getSingleton('core/resource')->getTableName('m2e');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from(array('l'=>$this->tableNameOld),'*')
                                              ->join(array('lt'=>$this->tableNameOldListingsTemplates),'`l`.`template_id` = `lt`.`ebay_id`',array('attribute_set'));

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldListing = $pdoStmt->fetch()) {

            $sellingFormatTemplateId = $this->tempDbTable->getNewValue('selling_format_templates.id',(int)$oldListing['template_id']);
            if ($sellingFormatTemplateId === false) {
                continue;
            }

            $descriptionTemplateId = $this->tempDbTable->getNewValue('description_templates.id',(int)$oldListing['template_id']);
            if ($descriptionTemplateId === false) {
                continue;
            }

            $listingTemplateId = $this->tempDbTable->getNewValue('listing_templates.id',(int)$oldListing['template_id']);
            if ($listingTemplateId === false) {
                continue;
            }

            $synchronizationTemplateId = $this->tempDbTable->getNewValue('synchronization_templates.id',0);
            if ($synchronizationTemplateId === false) {
                continue;
            }

            $newListing = array(
                'attribute_set_id' => (int)$oldListing['attribute_set'],
                
                'selling_format_template_id' => (int)$sellingFormatTemplateId,
                'description_template_id' => (int)$descriptionTemplateId,
                'listing_template_id' => (int)$listingTemplateId,
                'synchronization_template_id' => (int)$synchronizationTemplateId,
                
                'title' => $oldListing['name'],
                'store_id' => (int)$oldListing['store'],

                'synchronization_start_type' => Ess_M2ePro_Model_Listings::SYNCHRONIZATION_START_TYPE_IMMEDIATELY,
                'synchronization_start_through_metric' => Ess_M2ePro_Model_Listings::SYNCHRONIZATION_START_THROUGH_METRIC_DAYS,
                'synchronization_start_through_value' => 1,

                'synchronization_stop_type' => Ess_M2ePro_Model_Listings::SYNCHRONIZATION_STOP_TYPE_NEVER,
                'synchronization_stop_through_metric' => Ess_M2ePro_Model_Listings::SYNCHRONIZATION_STOP_THROUGH_METRIC_DAYS,
                'synchronization_stop_through_value' => 1,

                'source_products' => Ess_M2ePro_Model_Listings::SOURCE_PRODUCTS_CUSTOM,
                'categories_add_action' => Ess_M2ePro_Model_Listings::CATEGORIES_ADD_ACTION_NONE,
                'categories_delete_action' => Ess_M2ePro_Model_Listings::CATEGORIES_DELETE_ACTION_NONE,
                'hide_products_others_listings' => Ess_M2ePro_Model_Listings::HIDE_PRODUCTS_OTHERS_LISTINGS_NO
            );

            $existListing = $this->getLikeExistItem($newListing,false);
            if (!is_null($existListing)) {
                $this->tempDbTable->addValue('listings.id',(int)$oldListing['id'],(int)$existListing['id']);
            } else {
                $newListing['synchronization_start_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $newListing['synchronization_stop_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $newListing['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $newListing['update_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $this->mySqlWriteConnection->insert($this->tableNameNew,$newListing);
                $newListingId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');
                $this->tempDbTable->addValue('listings.id',(int)$oldListing['id'],(int)$newListingId);
            }
        }
    }

    // ########################################
}