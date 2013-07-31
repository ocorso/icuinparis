<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Migration_Objects_Accounts extends Ess_M2ePro_Model_Migration_Abstract
{
    const TABLE_NAME_OLD = 'm2e_account_list';
    const TABLE_NAME_NEW = 'm2epro_accounts';

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Migration_Objects_Accounts');
    }

    // ########################################

	public function process()
    {
        
    }

    // ########################################

    public function migrateGetCurrentAccount($reset = false)
    {
        if ($reset) {
            unset($_SESSION['migrate_accounts_current_id']);
            unset($_SESSION['migrate_accounts_current_completed']);
        }

        $lastAccountId = NULL;
        if (isset($_SESSION['migrate_accounts_current_id'])) {
            $lastAccountId = (int)$_SESSION['migrate_accounts_current_id'];
        }

        $whereSql = '';
        if (!is_null($lastAccountId)) {
            if (!isset($_SESSION['migrate_accounts_current_completed'])) {
                $whereSql .= ' `id` = '.$lastAccountId.' ';
            } else {
                $whereSql .= ' `id` > '.$lastAccountId.' ';
            }
        }

        $dbSelect = $this->mySqlReadConnection->select()
                                              ->from($this->tableNameOld,'*');
        $whereSql != '' && $dbSelect->where($whereSql);
        $dbSelect->order(array('id ASC'))
                 ->limit(1);

        $accounts = $this->mySqlReadConnection->fetchAll($dbSelect);
        if ($accounts === false || !isset($accounts[0])) {
            unset($_SESSION['migrate_accounts_current_id']);
            unset($_SESSION['migrate_accounts_current_completed']);
            return false;
        }

        $_SESSION['migrate_accounts_current_id'] = (int)$accounts[0]['id'];
        unset($_SESSION['migrate_accounts_current_completed']);

        return $accounts[0];
    }

    public function migrateCompleteCurrentAccount()
    {
        $_SESSION['migrate_accounts_current_completed'] = true;
    }

    // ########################################
}