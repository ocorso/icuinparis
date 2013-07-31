<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_DescriptionsTemplates extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('M2ePro/DescriptionsTemplates', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $currentTimestamp = Mage::helper('M2ePro')->getCurrentGmtDate();

        if (is_null($object->getOrigData())) {
            $object->setData('create_date',$currentTimestamp);
            $object->setData('synch_date',$currentTimestamp);
        }

        $object->setData('update_date',$currentTimestamp);

        if ($object->getOrigData('synch_date') != $object->getData('synch_date') &&
            $object->getData('synch_date') == $object->getOrigData('update_date')) {
            $object->setData('synch_date',$object->getData('update_date'));
        }

        return $this;
    }
}