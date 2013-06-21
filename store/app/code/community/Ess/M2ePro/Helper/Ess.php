<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Ess extends Mage_Core_Helper_Abstract
{
    // ########################################

    /**
     * @return Ess_M2ePro_Model_EssConfig
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/EssConfig');
    }

    // ########################################

    public function getServerBaseUrl()
    {
        return $this->getConfig()->getGroupValue('/server/','baseurl');
    }

    public function getModules()
    {
        return $this->getConfig()->getAllGroupValues('/modules/');
    }

    // ########################################

    public function getAdminKey()
    {
        return (string)$this->getConfig()->getGroupValue('/server/','admin_key');
    }

    // ########################################
}