<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_SynchronizationsTemplates extends Mage_Core_Model_Abstract
{
    const START_AUTO_LIST_NONE = 0;
    const START_AUTO_LIST_YES  = 1;

    const END_AUTO_STOP_NONE = 0;
    const END_AUTO_STOP_YES  = 1;

    const REVISE_UPDATE_EBAY_QTY_NONE = 0;
    const REVISE_UPDATE_EBAY_QTY_YES  = 1;

    const REVISE_UPDATE_EBAY_PRICE_NONE = 0;
    const REVISE_UPDATE_EBAY_PRICE_YES  = 1;

    const REVISE_UPDATE_TITLE_NONE = 0;
    const REVISE_UPDATE_TITLE_YES  = 1;

    const REVISE_UPDATE_DESCRIPTION_NONE = 0;
    const REVISE_UPDATE_DESCRIPTION_YES  = 1;

    const REVISE_UPDATE_SUB_TITLE_NONE = 0;
    const REVISE_UPDATE_SUB_TITLE_YES  = 1;

    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES  = 1;

    const REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_DESCRIPTION_TEMPLATE_YES  = 1;

    const REVISE_CHANGE_LISTING_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_LISTING_TEMPLATE_YES  = 1;

    const RELIST_FILTER_USER_LOCK_NONE = 0;
    const RELIST_FILTER_USER_LOCK_YES  = 1;

    const RELIST_SEND_DATA_NONE = 0;
    const RELIST_SEND_DATA_YES  = 1;

    const RELIST_MODE_NONE = 0;
    const RELIST_MODE_YES  = 1;

    const RELIST_STATUS_ENABLED_NONE = 0;
    const RELIST_STATUS_ENABLED_YES  = 1;

    const RELIST_IS_IN_STOCK_NONE = 0;
    const RELIST_IS_IN_STOCK_YES  = 1;

    const RELIST_QTY_NONE    = 0;
    const RELIST_QTY_LESS    = 1;
    const RELIST_QTY_BETWEEN = 2;
    const RELIST_QTY_MORE    = 3;

    const RELIST_SCHEDULE_TYPE_IMMEDIATELY     = 0;
    const RELIST_SCHEDULE_TYPE_THROUGH = 1;
    const RELIST_SCHEDULE_TYPE_WEEK     = 2;

    const RELIST_SCHEDULE_THROUGH_METRIC_NONE    = 0;
    const RELIST_SCHEDULE_THROUGH_METRIC_MINUTES = 1;
    const RELIST_SCHEDULE_THROUGH_METRIC_HOURS   = 2;
    const RELIST_SCHEDULE_THROUGH_METRIC_DAYS    = 3;

    const STOP_STATUS_DISABLED_NONE = 0;
    const STOP_STATUS_DISABLED_YES  = 1;

    const STOP_OUT_OFF_STOCK_NONE = 0;
    const STOP_OUT_OFF_STOCK_YES  = 1;

    const STOP_QTY_NONE    = 0;
    const STOP_QTY_LESS    = 1;
    const STOP_QTY_BETWEEN = 2;
    const STOP_QTY_MORE    = 3;

    // ########################################
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/SynchronizationsTemplates');
    }

    // ########################################

    /**
     * @throws LogicException
     * @param  int $id
     * @return Ess_M2ePro_Model_SynchronizationsTemplates
     */
    public function loadInstance($id)
    {
        $this->load($id);

        if (is_null($this->getId())) {
             throw new Exception('Synchronization template does not exist. Probably it was deleted.');
        }

        return $this;
    }

    /**
     * @throws LogicException
     * @param  int $listingId
     * @return Ess_M2ePro_Model_SynchronizationsTemplates
     */
    public function loadByListing($listingId)
    {
         $tempModel = Mage::getModel('M2ePro/Listings')->load($listingId);
         
         if (is_null($tempModel->getId())) {
             throw new Exception('Listing does not exist. Probably it was deleted.');
         }

         return $this->loadInstance($tempModel->getData('synchronization_template_id'));
    }

    // ########################################

    /**
     * @return bool
     */
    public function isLocked()
    {
        if (!$this->getId()) {
            return false;
        }

        return (bool)Mage::getModel('M2ePro/Listings')
                            ->getCollection()
                            ->addFieldToFilter('synchronization_template_id', $this->getId())
                            ->getSize();
    }

    /**
     * @return bool
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
    }
    
    // ########################################

    /**
     * @throws LogicException
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getListings($asObjects = false, array $filters = array())
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        $tempCollection = Mage::getModel('M2ePro/Listings')->getCollection();
        $tempCollection->addFieldToFilter('synchronization_template_id', $this->getId());
        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }
        $tempArray = $tempCollection->toArray();

        if ($asObjects === true) {
            $resultArray = array();
            foreach ($tempArray['items'] as $item) {
                $tempInstance = Mage::getModel('M2ePro/Listings')
                                        ->loadInstance($item['id']);
                $tempInstance->setSynchronizationTemplate($this);
                $resultArray[] = $tempInstance;
            }
            return $resultArray;
        } else {
            return $tempArray['items'];
        }
    }

    // ########################################

    public function isStartAutoList()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('start_auto_list') != self::START_AUTO_LIST_NONE;
    }

    public function isEndAutoStop()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('end_auto_stop') != self::END_AUTO_STOP_NONE;
    }

    //------------------------

    public function isReviseWhenChangeQty()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('revise_update_ebay_qty') != self::REVISE_UPDATE_EBAY_QTY_NONE;
    }

    public function isReviseWhenChangePrice()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('revise_update_ebay_price') != self::REVISE_UPDATE_EBAY_PRICE_NONE;
    }

    public function isReviseWhenChangeTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('revise_update_title') != self::REVISE_UPDATE_TITLE_NONE;
    }

    public function isReviseWhenChangeSubTitle()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('revise_update_sub_title') != self::REVISE_UPDATE_SUB_TITLE_NONE;
    }

    public function isReviseWhenChangeDescription()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('revise_update_description') != self::REVISE_UPDATE_DESCRIPTION_NONE;
    }

    public function isReviseSellingFormatTemplate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('revise_change_selling_format_template') != self::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE;
    }

    public function isReviseDescriptionTemplate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('revise_change_description_template') != self::REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE;
    }

    public function isReviseListingTemplate()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('revise_change_listing_template') != self::REVISE_CHANGE_LISTING_TEMPLATE_NONE;
    }

    //------------------------

    public function isRelistMode()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_mode') != self::RELIST_MODE_NONE;
    }

    public function isRelistFilterUserLock()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_filter_user_lock') != self::RELIST_FILTER_USER_LOCK_NONE;
    }

    public function isRelistSendData()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_send_data') == self::RELIST_SEND_DATA_YES;
    }

    public function isRelistStatusEnabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_status_enabled') != self::RELIST_STATUS_ENABLED_NONE;
    }

    public function isRelistIsInStock()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_is_in_stock') != self::RELIST_IS_IN_STOCK_NONE;
    }

    public function isRelistWhenQtyHasValue()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_qty') != self::RELIST_QTY_NONE;
    }

    public function isRelistShedule()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_schedule_type') != self::RELIST_SCHEDULE_TYPE_IMMEDIATELY;
    }

    //------------------------

    public function isRelistSheduleWeekDayNow()
    {
        $synchronizationDaysOfWeek = $this->getRelistSheduleWeek();
        $synchronizationDaysOfWeek = explode('_',$synchronizationDaysOfWeek);

        $enabledDaysOfWeek = array();
        foreach ($synchronizationDaysOfWeek as $item) {
            if (!isset($item{2}) || (int)$item{2} != 1) {
                continue;
            }
            $enabledDaysOfWeek[] = $item{0}.$item{1};
        }
        $synchronizationDaysOfWeek = $enabledDaysOfWeek;

        foreach ($synchronizationDaysOfWeek as &$item) {
            $item = strtolower($item);
            switch ($item) {
                case 'mo': $item = 'monday'; break;
                case 'tu': $item = 'tuesday'; break;
                case 'we': $item = 'wednesday'; break;
                case 'th': $item = 'thursday'; break;
                case 'fr': $item = 'friday'; break;
                case 'sa': $item = 'saturday'; break;
                case 'su': $item = 'sunday'; break;
            }
        }

        $todayDayOfWeek = getdate(Mage::helper('M2ePro')->getCurrentGmtDate(true));
        $todayDayOfWeek = strtolower($todayDayOfWeek['weekday']);

        if (!in_array($todayDayOfWeek,$synchronizationDaysOfWeek)) {
            return false;
        }

        return true;
    }

    public function isRelistSheduleWeekTimeNow()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        if (is_null($this->getData('relist_schedule_week_start_time')) ||
            $this->getData('relist_schedule_week_start_time') == '' ||
            is_null($this->getData('relist_schedule_week_end_time')) ||
            $this->getData('relist_schedule_week_end_time') == '') {
            return true;
        }

        $tempStartTime = explode(':',$this->getData('relist_schedule_week_start_time'));
        $tempEndTime = explode(':',$this->getData('relist_schedule_week_end_time'));

        if (!is_array($tempStartTime) || count($tempStartTime) < 2 ||
            !is_array($tempEndTime) || count($tempEndTime) < 2) {
            return true;
        }

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        
        $startTimeStampCurrentDay = mktime(0, 0, 0, date('m',$currentTimeStamp),
                                                    date('d',$currentTimeStamp),
                                                    date('Y',$currentTimeStamp)) +
                                    (int)$tempStartTime[0]*60*60 +
                                    (int)$tempStartTime[1]*60;
        $endTimeStampCurrentDay = mktime(0, 0, 0, date('m',$currentTimeStamp),
                                                  date('d',$currentTimeStamp),
                                                  date('Y',$currentTimeStamp)) +
                                    (int)$tempEndTime[0]*60*60 +
                                    (int)$tempEndTime[1]*60;

        if ($currentTimeStamp < $startTimeStampCurrentDay ||
            $currentTimeStamp > $endTimeStampCurrentDay) {
            return false;
        }

        return true;
    }

    //------------------------

    public function isStopStatusDisabled()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('stop_status_disabled') != self::STOP_STATUS_DISABLED_NONE;
    }

    public function isStopOutOfStock()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('stop_out_off_stock') != self::STOP_OUT_OFF_STOCK_NONE;
    }

    public function isStopWhenQtyHasValue()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('stop_qty') != self::STOP_QTY_NONE;
    }

    // ########################################

    public function getRelistWhenQtyHasValueType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_qty');
    }

    public function getRelistWhenQtyHasValueMin()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_qty_value');
    }

    public function getRelistWhenQtyHasValueMax()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_qty_value_max');
    }

    public function getRelistSheduleType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_schedule_type');
    }

    public function getRelistSheduleThroughMetric()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_schedule_through_metric');
    }

    public function getRelistSheduleThroughValue()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_schedule_through_value');
    }

    public function getRelistSheduleWeek()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('relist_schedule_week');
    }

    //------------------------

    public function getStopWhenQtyHasValueType()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('stop_qty');
    }

    public function getStopWhenQtyHasValueMin()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('stop_qty_value');
    }

    public function getStopWhenQtyHasValueMax()
    {
        if (is_null($this->getId())) {
             throw new Exception('Load instance first');
        }

        return $this->getData('stop_qty_value_max');
    }
    
    // ########################################
}