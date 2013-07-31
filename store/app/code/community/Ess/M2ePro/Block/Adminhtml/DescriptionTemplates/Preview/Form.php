<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_DescriptionTemplates_Preview_Form extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('descriptionTemplatesPreviewForm');
        //------------------------------

        $this->setTemplate('M2ePro/templates/description/preview/form.phtml');
    }
}