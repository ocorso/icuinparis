<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Logs_Synchronizations_Help extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationsLogsHelp');
        //------------------------------

        $this->setTemplate('M2ePro/logs/help/synchronizations.phtml');
    }
}