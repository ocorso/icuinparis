<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Logs_EbayListings_Help extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingsLogsHelp');
        //------------------------------

        $this->setTemplate('M2ePro/logs/help/ebay_listings.phtml');
    }
}