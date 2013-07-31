<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_SynchronizationTemplates_Edit_Tabs_Revise extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronizationTemplatesTabsRevise');
        //------------------------------

        $this->setTemplate('M2ePro/templates/synchronization/revise.phtml');
    }
}