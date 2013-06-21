<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_Marketplaces extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_marketplace';
    const TABLE_NAME_NEW = 'm2epro_marketplaces';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_Marketplaces');
    }

    // ########################################

	public function process()
    {
        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');

        $items = $this->mySqlReadConnection->fetchAll($dbSelect);
        foreach ($items as $item) {
            $this->tempDbTable->addValue('marketplaces.id',(int)$item['site_id'],(int)$item['site_id']);
        }
    }

    // ########################################
}