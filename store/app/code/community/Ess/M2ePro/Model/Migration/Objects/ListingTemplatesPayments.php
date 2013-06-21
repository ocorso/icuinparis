<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_ListingTemplatesPayments extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_p_to_p';
    const TABLE_NAME_NEW = 'm2epro_listings_templates_payments';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_ListingTemplatesPayments');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');

        /** @var $pdoStmt Zend_Db_Statement_Interface */
        $pdoStmt = $this->mySqlReadConnection->query($dbSelect);
        $pdoStmt->setFetchMode(Zend_Db::FETCH_ASSOC);

        while ($oldPayment = $pdoStmt->fetch()) {

            $listingTemplateId = $this->tempDbTable->getNewValue('listing_templates.id',(int)$oldPayment['project_id']);
            if ($listingTemplateId === false) {
                continue;
            }

            $newPayment = array(
                'listing_template_id' => (int)$listingTemplateId,
                'payment_id' => $oldPayment['payment_id']
            );

            $existPayment = $this->getLikeExistItem($newPayment,false);
            if (!is_null($existPayment)) {
                $this->tempDbTable->addValue('listing_templates_payments.id',(int)$oldPayment['id'],(int)$existPayment['id']);
            } else {
                $this->mySqlWriteConnection->insert($this->tableNameNew,$newPayment);
                $newPaymentId = $this->mySqlWriteConnection->lastInsertId($this->tableNameNew,'id');
                $this->tempDbTable->addValue('listing_templates_payments.id',(int)$oldPayment['id'],(int)$newPaymentId);
            }
        }
    }

    // ########################################
}