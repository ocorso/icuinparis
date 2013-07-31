<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsView');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listings_view';
        //------------------------------

        // Set header text
        //------------------------------
        $listingData = Mage::registry('M2ePro_data');
        $headerText = Mage::helper('M2ePro')->__('View Listing "%title%"');
        $this->_headerText = str_replace('%title%', $this->htmlEscape($listingData['title']), $headerText);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlersObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listings/index').'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_listings/index').'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_logs/listings',array('id'=>$listingData['id'],'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/view',array('id'=>$listingData['id'])))).'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'EbayItemsGridHandlersObj.reset_click()',
            'class'     => 'reset'
        ));

        $newListing = $this->getRequest()->getParam('new');

        if (is_null($newListing)) {

            $tempStr = Mage::helper('adminhtml')->__('Are you sure?');
            $this->_addButton('clear_log', array(
                'label'     => Mage::helper('M2ePro')->__('Clear Log'),
                'onclick'   => 'deleteConfirm(\''.$tempStr.'\', \'' . $this->getUrl('*/*/clearLog',array('id'=>$listingData['id'],'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/view',array('id'=>$listingData['id'])))) . '\')',
                'class'     => 'clear_log'
            ));
        }

        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');

        $this->_addButton('delete', array(
            'label'     => Mage::helper('M2ePro')->__('Delete'),
            'onclick'   => 'deleteConfirm(\''. $tempStr.'\', \'' . $this->getUrl('*/*/delete',array('id'=>$listingData['id'])) . '\')',
            'class'     => 'delete'
        ));

        $this->_addButton('edit_templates', array(
            'label'     => Mage::helper('M2ePro')->__('Edit Templates'),
            'onclick'   => '',
            'class'     => 'drop_down edit_template_drop_down'
        ));

        $this->_addButton('edit_settings', array(
            'label'     => Mage::helper('M2ePro')->__('Edit Settings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/*/edit',array('id'=>$listingData['id'],'back'=>Mage::helper('M2ePro')->makeBackUrlParam('view',array('id'=>$listingData['id'])))).'\')',
            'class'     => ''
        ));

        $this->_addButton('add_products', array(
            'label'     => Mage::helper('M2ePro')->__('Add Products'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/*/products',array('id'=>$listingData['id'],'back'=>Mage::helper('M2ePro')->makeBackUrlParam('view',array('id'=>$listingData['id'])))).'\')',
            'class'     => 'add'
        ));

        /*if (!is_null($newListing) && $newListing == 'yes') {
           $this->_addButton('create_ebay_listing', array(
                'label'     => Mage::helper('M2ePro')->__('List All Items'),
                'onclick'   => 'EbayActionsHandlersObj.runListAllProducts()',
                'class'     => 'save'
           ));
        }*/
        //------------------------------
    }

    public function _toHtml()
    {
        $listingData = Mage::registry('M2ePro_data');

        $logViewUrl = $this->getUrl('*/adminhtml_logs/listings',array('id'=>$listingData['id'],'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listings/view',array('id'=>$listingData['id']))));
        $checkLockListing = $this->getUrl('*/adminhtml_listings/checkLockListing');
        $lockListingNow = $this->getUrl('*/adminhtml_listings/lockListingNow');
        $unlockListingNow = $this->getUrl('*/adminhtml_listings/unlockListingNow');
        $getErrorsSummary = $this->getUrl('*/adminhtml_listings/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_listings/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_listings/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_listings/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_listings/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_listings/runStopAndRemoveProducts');

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_view_help');
        $tempDropDownHtml = Mage::helper('M2ePro')->escapeJs($this->getEditTemplateDropDownHtml());

        $taskCompletedMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('"%title%" task has successfully completed.'));
        $taskCompletedWarningMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('"%title%" task has completed with warnings. <a href="%url%">View log</a> for details.'));
        $taskCompletedErrorMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('"%title%" task has completed with errors. <a href="%url%">View log</a> for details.'));

        $sendingDataToEbayMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Sending %execute% product(s) data on eBay.'));
        $viewAllProductLogMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('View All Product Log.'));

        $listingLockedMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('The listing was locked by another process. Please try again later.'));
        $listingEmptyMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Listing is empty.'));

        $listingAllItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Listing All Items On eBay'));
        $listingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Listing Selected Items On eBay'));
        $revisingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Revising Selected Items On eBay'));
        $relistingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Relisting Selected Items On eBay'));
        $stoppingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Stopping Selected Items On eBay'));
        $stoppingAndRemovingSelectedItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Stopping And Removing Selected Items On eBay'));

        $selectItemsMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Please select items.'));
        $selectActionMessage = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Please select action.'));

        $successWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Success'));
        $noticeWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Notice'));
        $warningWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Warning'));
        $errorWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Error'));
        $closeWord = Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Close'));

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    if (typeof M2ePro == 'undefined') {
		M2ePro = {};
		M2ePro.url = {};
		M2ePro.formData = {};
		M2ePro.customData = {};
		M2ePro.text = {};
	}

	M2ePro.url.logViewUrl = '{$logViewUrl}';
	M2ePro.url.checkLockListing = '{$checkLockListing}';
	M2ePro.url.lockListingNow = '{$lockListingNow}';
	M2ePro.url.unlockListingNow = '{$unlockListingNow}';
	M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

	M2ePro.url.runListProducts = '{$runListProducts}';
	M2ePro.url.runReviseProducts = '{$runReviseProducts}';
	M2ePro.url.runRelistProducts = '{$runRelistProducts}';
	M2ePro.url.runStopProducts = '{$runStopProducts}';
	M2ePro.url.runStopAndRemoveProducts = '{$runStopAndRemoveProducts}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
	M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
	M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
	M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

	M2ePro.text.sending_data_message = '{$sendingDataToEbayMessage}';
	M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

	M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
	M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

	M2ePro.text.listing_all_items_message = '{$listingAllItemsMessage}';
	M2ePro.text.listing_selected_items_message = '{$listingSelectedItemsMessage}';
	M2ePro.text.revising_selected_items_message = '{$revisingSelectedItemsMessage}';
	M2ePro.text.relisting_selected_items_message = '{$relistingSelectedItemsMessage}';
	M2ePro.text.stopping_selected_items_message = '{$stoppingSelectedItemsMessage}';
	M2ePro.text.stopping_and_removing_selected_items_message = '{$stoppingAndRemovingSelectedItemsMessage}';

	M2ePro.text.select_items_message = '{$selectItemsMessage}';
	M2ePro.text.select_action_message = '{$selectActionMessage}';

	M2ePro.text.success_word = '{$successWord}';
	M2ePro.text.notice_word = '{$noticeWord}';
	M2ePro.text.warning_word = '{$warningWord}';
	M2ePro.text.error_word = '{$errorWord}';
	M2ePro.text.close_word = '{$closeWord}';

    Event.observe(window, 'load', function() {
        EbayItemsGridHandlersObj = new EbayItemsGridHandlers('listingsViewGrid{$listingData['id']}',1,2);
        EbayActionsHandlersObj = new EbayActionsHandlers({$listingData['id']});
        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');

        $$('.edit_template_drop_down')[0].innerHTML += '{$tempDropDownHtml}';
        
        DropDownObj = new DropDown();
        DropDownObj.prepare($$('.edit_template_drop_down')[0]);
    });

</script>
JAVASCRIPT;

        $startHtmlDivGrid = '<div id="'.$this->getId().'Grid'.$listingData['id'].'">';
        $tempHtml = str_replace($startHtmlDivGrid,$helpBlock->toHtml().$startHtmlDivGrid,parent::_toHtml());

        return $javascriptsMain.
               '<div id="listing_view_progress_bar"></div>'.
               '<div id="listing_view_errors_summary" class="errors_summary" style="display: none;"></div>'.
               '<div id="listing_view_content_container">'.
                $tempHtml.
               '</div>';
    }

    public function getEditTemplateDropDownHtml()
    {
        $listingData = Mage::registry('M2ePro_data');
        $sellingFormatTemplate = Mage::helper('M2ePro')->__('Selling Format Template');
        $descriptionTemplate = Mage::helper('M2ePro')->__('Description Template');
        $generalTemplate = Mage::helper('M2ePro')->__('General Template');
        $synchronizationTemplate = Mage::helper('M2ePro')->__('Synchronization Template');

        return <<<HTML
<ul style="display: none;">
    <li href="{$this->getUrl('*/adminhtml_sellingFormatTemplates/edit',array('id'=>$listingData['selling_format_template_id']))}" target="_blank">{$sellingFormatTemplate}</li>
    <li href="{$this->getUrl('*/adminhtml_descriptionTemplates/edit',array('id'=>$listingData['description_template_id']))}" target="_blank">{$descriptionTemplate}</li>
    <li href="{$this->getUrl('*/adminhtml_listingTemplates/edit',array('id'=>$listingData['listing_template_id']))}" target="_blank">{$generalTemplate}</li>
    <li href="{$this->getUrl('*/adminhtml_synchronizationTemplates/edit',array('id'=>$listingData['synchronization_template_id']))}" target="_blank">{$synchronizationTemplate}</li>
</ul>
HTML;
    }
}