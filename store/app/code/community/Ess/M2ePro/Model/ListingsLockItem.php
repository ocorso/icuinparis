<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_ListingsLockItem extends Ess_M2ePro_Model_LockItem
{
    //####################################

    public function __construct($params)
    {
        $this->setNick('listing_'.$params['id']);
        $maxDeactivateTime = (int)Mage::helper('M2ePro/Module')
                                        ->getConfig()
                                        ->getGroupValue('/listings/lockItem/',
                                                        'max_deactivate_time');
        $this->setMaxDeactivateTime($maxDeactivateTime);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ListingsLockItem');
    }

    //####################################
}