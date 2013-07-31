<?php

/**
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listings_Ebay extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingsEbay');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listings_ebay';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('3rd Party Listings');
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
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_logs/ebayListings',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebayListings/index'))).'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlersObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    public function _toHtml()
    {
        // Filter Blocks
        //------------------------------
        $marketplacesIds = Mage::getModel('M2ePro/EbayListings')->getUsingMarketplacesIds();

        $filtersAccountBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_accounts_switcher');
        $filtersAccountBlock->setUseConfirm(false);

        $filtersMarketplaceBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplaces_switcher');
        $filtersMarketplaceBlock->setUseConfirm(false);
        
        if (!is_null($marketplacesIds)) {
            $filtersMarketplaceBlock->setMarketplacesIds($marketplacesIds);
        }

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_ebay_help');

        $filterBlock = "<div class='filter_block'>" .
                       $filtersAccountBlock->toHtml() .
                       $filtersMarketplaceBlock->toHtml() .
                       "</div>";

        $startHtmlDivGrid = '<div id="'.$this->getId().'Grid">';
        $gridHtml = str_replace($startHtmlDivGrid, $helpBlock->toHtml().$filterBlock.$startHtmlDivGrid, parent::_toHtml());
        //------------------------------

        // Grid ebay actions
        //------------------------------
        $logViewUrl = $this->getUrl('*/adminhtml_logs/ebayListings',array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebayListings/index')));
        $checkLockListing = $this->getUrl('*/adminhtml_ebayListings/checkLockListing');
        $lockListingNow = $this->getUrl('*/adminhtml_ebayListings/lockListingNow');
        $unlockListingNow = $this->getUrl('*/adminhtml_ebayListings/unlockListingNow');
        $getErrorsSummary = $this->getUrl('*/adminhtml_ebayListings/getErrorsSummary');

        $runListProducts = $this->getUrl('*/adminhtml_ebayListings/runListProducts');
        $runReviseProducts = $this->getUrl('*/adminhtml_ebayListings/runReviseProducts');
        $runRelistProducts = $this->getUrl('*/adminhtml_ebayListings/runRelistProducts');
        $runStopProducts = $this->getUrl('*/adminhtml_ebayListings/runStopProducts');
        $runStopAndRemoveProducts = $this->getUrl('*/adminhtml_ebayListings/runStopAndRemoveProducts');

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
        EbayItemsGridHandlersObj = new EbayItemsGridHandlers('listingsEbayGrid',2,1);
        EbayActionsHandlersObj = new EbayActionsHandlers('listingsEbay');
        ListingProgressBarObj = new ProgressBar('listing_view_progress_bar');
        GridWrapperObj = new AreaWrapper('listing_view_content_container');
    });

</script>
JAVASCRIPT;
        //------------------------------

        return $javascriptsMain.
               '<div id="listing_view_progress_bar"></div>'.
               '<div id="listing_view_errors_summary" class="errors_summary" style="display:none;"></div>'.
               '<div id="listing_view_content_container">'.
               $gridHtml.
               '</div>';
    }
}