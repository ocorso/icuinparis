<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_ListingTemplates_Edit_Tabs_Refund extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingTemplatesTabsRefund');
        //------------------------------

        $this->setTemplate('M2ePro/templates/listing/refund.phtml');
    }
}