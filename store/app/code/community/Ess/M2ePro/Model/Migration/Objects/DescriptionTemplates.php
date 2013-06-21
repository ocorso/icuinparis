<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_DescriptionTemplates extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e';
    const TABLE_NAME_NEW = 'm2epro_descriptions_templates';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_DescriptionTemplates');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        $oldSeparatedDescriptions = array();
        
        while ($oldListingTemplate = $pdoStmt->fetch()) {

            $oldDescriptionTemplate = $this->getOldDescriptionTemplate($oldListingTemplate);

            $attributeSetTemp = array(
                'template_type' => Ess_M2ePro_Model_TemplatesAttributeSets::TEMPLATE_TYPE_DESCRIPTION,
                'attribute_set_id' => (int)$oldListingTemplate['attribute_set']
            );

            $newDescriptionTemplate = array(
                'image_main_mode' => (int)$oldListingTemplate['product_picture'],
                'image_main_attribute' => $oldListingTemplate['product_image_attribute'],
                'gallery_images_mode' => (int)$oldListingTemplate['gallery_images'],
                'variation_configurable_images' => (string)$oldListingTemplate['variation_images_attribute']
            );

            if ($newDescriptionTemplate['image_main_mode'] != Ess_M2ePro_Model_DescriptionsTemplates::IMAGE_MAIN_MODE_ATTRIBUTE) {
                $newDescriptionTemplate['image_main_attribute'] = '';
            }
            if ($newDescriptionTemplate['image_main_mode'] == Ess_M2ePro_Model_DescriptionsTemplates::IMAGE_MAIN_MODE_NONE) {
                $newDescriptionTemplate['gallery_images_mode'] = 0;
            } else {
                $newDescriptionTemplate['gallery_images_mode'] = $newDescriptionTemplate['gallery_images_mode'] - 1;
                $newDescriptionTemplate['gallery_images_mode'] <= 0 && $newDescriptionTemplate['gallery_images_mode'] = 0;
            }

            if (!is_null($oldDescriptionTemplate)) {
                
                $successExist = false;
                foreach ($oldSeparatedDescriptions as $temp) {
                    if ((int)$oldDescriptionTemplate['ebaytemplates_id'] == (int)$temp['id_old']) {

                        $newDescriptionTemplate2 = $newDescriptionTemplate;
                        $newDescriptionTemplate2['id'] = (int)$temp['id_new'];

                        $existDescriptionTemplate2 = $this->getLikeExistItem($newDescriptionTemplate2,true,$attributeSetTemp);

                        if (!is_null($existDescriptionTemplate2)) {
                            $this->tempDbTable->addValue('description_templates.id',(int)$oldListingTemplate['ebay_id'],(int)$existDescriptionTemplate2['id']);
                            $successExist = true;
                            break;
                        }
                    }
                }

                if ($successExist) {
                    continue;
                }
            }

            $newDescriptionTemplateTemp = array(
                'title' => !is_null($oldDescriptionTemplate) ? $oldListingTemplate['title'].' - '.$oldDescriptionTemplate['title'] : $oldListingTemplate['title'],

                'title_mode' => !is_null($oldDescriptionTemplate) ? (int)$oldDescriptionTemplate['product_name_mode']: (int)$oldListingTemplate['product_name_mode'],
                'title_template' => !is_null($oldDescriptionTemplate) ? $oldDescriptionTemplate['name_template']: $oldListingTemplate['name_template'],

                'subtitle_mode' => !is_null($oldDescriptionTemplate) ? (int)$oldDescriptionTemplate['product_subtitle_mode']: (int)$oldListingTemplate['product_subtitle_mode'],
                'subtitle_template' => !is_null($oldDescriptionTemplate) ? $oldDescriptionTemplate['subtitle_template']: $oldListingTemplate['subtitle_template'],

                'description_mode' => !is_null($oldDescriptionTemplate) ? (int)$oldDescriptionTemplate['mode']: (int)$oldListingTemplate['mode'],
                'description_template' => !is_null($oldDescriptionTemplate) ? $oldDescriptionTemplate['content_text']: $oldListingTemplate['description'],

                'cut_long_titles' => Ess_M2ePro_Model_DescriptionsTemplates::CUT_LONG_TITLE_DISABLED,
                'hit_counter' => 'NoHitCounter',
                'editor_type' => !is_null($oldDescriptionTemplate) ? (int)$oldDescriptionTemplate['editor_type']: (int)$oldListingTemplate['editor_type']
            );
            
            $newDescriptionTemplate = array_merge($newDescriptionTemplate,$newDescriptionTemplateTemp);

            if ($newDescriptionTemplate['title_mode'] != Ess_M2ePro_Model_DescriptionsTemplates::TITLE_MODE_CUSTOM) {
                $newDescriptionTemplate['title_template'] = '';
            }

            if ($newDescriptionTemplate['subtitle_mode'] != Ess_M2ePro_Model_DescriptionsTemplates::SUBTITLE_MODE_CUSTOM) {
                $newDescriptionTemplate['subtitle_template'] = '';
            }

            if ($newDescriptionTemplate['description_mode'] != Ess_M2ePro_Model_DescriptionsTemplates::DESCRIPTION_MODE_CUSTOM) {
                $newDescriptionTemplate['description_template'] = '';
            }

            $existDescriptionTemplate = $this->getLikeExistItem($newDescriptionTemplate,true,$attributeSetTemp);
            if (!is_null($existDescriptionTemplate)) {
                $this->tempDbTable->addValue('description_templates.id',(int)$oldListingTemplate['ebay_id'],(int)$existDescriptionTemplate['id']);
            } else {
                $currentTimestamp = Mage::helper('M2ePro')->getCurrentGmtDate();
                $newDescriptionTemplate['synch_date'] = $currentTimestamp;
                $newDescriptionTemplate['create_date'] = $currentTimestamp;
                $newDescriptionTemplate['update_date'] = $currentTimestamp;

                $this->mySqlWriteConnection->insert($this->tableNameNew,$newDescriptionTemplate);
                $newDescriptionTemplateId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');

                $tasTable = Mage::getResourceModel('M2ePro/TemplatesAttributeSets')->getMainTable();
                $attributeSetTemp['template_id'] = (int)$newDescriptionTemplateId;
                $attributeSetTemp['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $attributeSetTemp['update_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
                $this->mySqlWriteConnection->insert($tasTable,$attributeSetTemp);

                $this->tempDbTable->addValue('description_templates.id',(int)$oldListingTemplate['ebay_id'],(int)$newDescriptionTemplateId);

                if (!is_null($oldDescriptionTemplate)) {
                    $oldSeparatedDescriptions[] = array(
                        'id_old' => (int)$oldDescriptionTemplate['ebaytemplates_id'],
                        'id_new' => $newDescriptionTemplateId
                    );
                }
            }
        }
    }

    // ########################################

    protected function getOldDescriptionTemplate($oldListingTemplate)
    {
        if ((int)$oldListingTemplate['template_title'] <= 0 ||
            (int)$oldListingTemplate['modify_template'] != 0) {
            return NULL;
        }

        $oldId = (int)$oldListingTemplate['template_title'];
        $tableName  = Mage::getSingleton('core/resource')->getTableName('m2etemplates');

        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($tableName,'*')
                                              ->where('`ebaytemplates_id` = ?',(int)$oldId);

        $row = $this->mySqlReadConnection->fetchRow($dbSelect);

        if ($row === false) {
            return NULL;
        }

        return $row;
    }

    // ########################################
}