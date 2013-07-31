<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_DescriptionTemplates_Templates_Gallery extends Ess_M2ePro_Block_Adminhtml_DescriptionTemplates_Templates_AbstractBase
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('descrTemplatesGallery');
        //------------------------------

        $this->setTemplate('M2ePro/templates/description/templates/gallery.phtml');
    }
}