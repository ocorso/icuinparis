<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_ListingsController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('ebay/manage_listings')
             ->_title(Mage::helper('M2ePro')->__('eBay'))
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Controls/ProgressBar.js')
             ->addCss('M2ePro/css/Controls/ProgressBar.css')
             ->addJs('M2ePro/Controls/AreaWrapper.js')
             ->addCss('M2ePro/css/Controls/AreaWrapper.css')
             ->addJs('M2ePro/Controls/DropDown.js')
             ->addCss('M2ePro/css/Controls/DropDown.css')
             ->addJs('M2ePro/Listings/ListingEditHandlers.js')
             ->addJs('M2ePro/Listings/ProductsGridHandlers.js')
             ->addJs('M2ePro/Listings/CategoriesTreeHandlers.js')
             ->addJs('M2ePro/Listings/EbayItemsGridHandlers.js')
             ->addJs('M2ePro/Listings/EbayActionsHandlers.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay/manage_listings/listings');
    }

    //#############################################

    public function indexAction()
    {
        /*!(bool)Mage::getModel('M2ePro/SellingFormatTemplates')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(Mage::helper('M2ePro')->__('You must create at least one selling format template first.'));

        !(bool)Mage::getModel('M2ePro/DescriptionsTemplates')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(Mage::helper('M2ePro')->__('You must create at least one description template first.'));

        !(bool)Mage::getModel('M2ePro/ListingsTemplates')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(Mage::helper('M2ePro')->__('You must create at least one general template first.'));

        !(bool)Mage::getModel('M2ePro/SynchronizationsTemplates')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(Mage::helper('M2ePro')->__('You must create at least one synchronization template first.'));*/

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listings_listings'))
             ->renderLayout();
    }

    public function gridListingsAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_listings_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function itemsAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listings_items'))
             ->renderLayout();
    }

    public function gridItemsAction()
    {
        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_items_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        // Check listing lock item
        //----------------------------
        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>$id));
        if ($lockItem->isExist()) {
            $this->_getSession()->addWarning(Mage::helper('M2ePro')->__('The listing is locked by another process. Please try again later.'));
        }
        //----------------------------

        Mage::register('M2ePro_data', $model->getData());

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listings_view'))
             ->renderLayout();
    }

    public function gridViewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            Mage::register('M2ePro_data', array());
        } else {
            Mage::register('M2ePro_data', $model->getData());
        }

        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_view_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function addAction()
    {
        // Get step param
        //----------------------------
        $step = $this->getRequest()->getParam('step');

        if (is_null($step)) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // Switch step param
        //----------------------------
        switch ($step) {
            case '1':
                $this->addStepOne();
                break;
            case '2':
                $this->addStepTwo();
                break;
            case '3':
                $this->addStepThree();
                break;
            default:
                $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
                break;
        }
        //----------------------------
    }

    public function addStepOne()
    {
        // Check clear param
        //----------------------------
        $clearAction = $this->getRequest()->getParam('clear');

        if (!is_null($clearAction)) {
            if ($clearAction == 'yes') {
               $_SESSION['M2ePro_data'] = array();
               $_SESSION['temp_listing_categories'] = array();
               $this->_redirect('*/*/add',array('step'=>'1'));
               return;
            } else {
                $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
                return;
            }
        }
        //----------------------------

        // Check exist temp data
        //----------------------------
        if (!isset($_SESSION['M2ePro_data']) || !isset($_SESSION['temp_listing_categories'])) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // If it post request
        //----------------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            $_SESSION['M2ePro_data'] = array(
                'title' => strip_tags($post['title']),
                'store_id' => $post['store_id'],
                'attribute_set_id' => $post['attribute_set_id'],

                'selling_format_template_id' => $post['selling_format_template_id'],
                'listing_template_id'     => $post['listing_template_id'],
                'description_template_id' => $post['description_template_id'],
                'synchronization_template_id'    => $post['synchronization_template_id'],

                'synchronization_start_type' => $post['synchronization_start_type'],
                'synchronization_start_through_metric' => $post['synchronization_start_through_metric'],
                'synchronization_start_through_value' => $post['synchronization_start_through_value'],
                'synchronization_start_date' => $post['synchronization_start_date'],

                'synchronization_stop_type' => $post['synchronization_stop_type'],
                'synchronization_stop_through_metric' => $post['synchronization_stop_through_metric'],
                'synchronization_stop_through_value' => $post['synchronization_stop_through_value'],
                'synchronization_stop_date' => $post['synchronization_stop_date'],

                'source_products' => $post['source_products'],
                'hide_products_others_listings' => $post['hide_products_others_listings']
            );

            $this->_redirect('*/*/add',array('step'=>'2'));
            return;
		}
        //----------------------------

        Mage::register('M2ePro_data', $_SESSION['M2ePro_data']);
        Mage::register('temp_listing_categories', $_SESSION['temp_listing_categories']);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listings_addStepOne'))
             ->renderLayout();
    }

    public function addStepTwo()
    {
        // Check exist temp data
        //----------------------------
        if (!isset($_SESSION['M2ePro_data']) ||
            count($_SESSION['M2ePro_data']) == 0 ||
            !isset($_SESSION['temp_listing_categories'])) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // Get remember param
        //----------------------------
        $rememberCategories = $this->getRequest()->getParam('remember_categories');

        if (!is_null($rememberCategories)) {
            if ($rememberCategories == 'yes') {

                // Get selected_categories param
                //---------------
                $selectedCategoriesIds = array();

                $selectedCategories = $this->getRequest()->getParam('selected_categories');
                if (!is_null($selectedCategories)) {
                    $selectedCategoriesIds = explode(',',$selectedCategories);
                }
                $selectedCategoriesIds = array_unique($selectedCategoriesIds);
                //---------------

                // Save selected categories
                //---------------
                $_SESSION['M2ePro_data']['categories_add_action'] = $this->getRequest()->getParam('categories_add_action');
                $_SESSION['M2ePro_data']['categories_delete_action'] = $this->getRequest()->getParam('categories_delete_action');
                $_SESSION['temp_listing_categories'] = $selectedCategoriesIds;
                //---------------

                // Goto step three
                //---------------
                $this->_redirect('*/*/add',array('step'=>'3'));
                //---------------

                return;

            } else {
                $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
                return;
            }
        }
        //----------------------------

        // Get save param
        //----------------------------
        $save = $this->getRequest()->getParam('save');

        if (!is_null($save)) {
            if ($save == 'yes') {

                // Get selected_products param
                //---------------
                $selectedProductsIds = array();

                $selectedProducts = $this->getRequest()->getParam('selected_products');
                if (!is_null($selectedProducts)) {
                    $selectedProductsIds = explode(',',$selectedProducts);
                }
                $selectedProductsIds = array_unique($selectedProductsIds);
                //---------------

                // Get selected_categories param
                //---------------
                $selectedCategoriesIds = array();

                $selectedCategories = $this->getRequest()->getParam('selected_categories');
                if (!is_null($selectedCategories)) {
                    $selectedCategoriesIds = explode(',',$selectedCategories);
                    $_SESSION['M2ePro_data']['categories_add_action'] = $this->getRequest()->getParam('categories_add_action');
                    $_SESSION['M2ePro_data']['categories_delete_action'] = $this->getRequest()->getParam('categories_delete_action');
                }
                $selectedCategoriesIds = array_unique($selectedCategoriesIds);
                //---------------

                // Get session selected_categories
                //---------------
                $selectedSessionCategoriesIds = $_SESSION['temp_listing_categories'];
                $selectedSessionCategoriesIds = array_unique($selectedSessionCategoriesIds);
                //---------------

                // Prepare listing data
                //---------------
                $tempDate = $_SESSION['M2ePro_data']['synchronization_start_date'];
                if (!is_null($tempDate) && $tempDate != '') {
                    $tempDate = Mage::helper('M2ePro')->timezoneDateToGmt($tempDate);
                }
                $_SESSION['M2ePro_data']['synchronization_start_date'] = $tempDate;

                $tempDate = $_SESSION['M2ePro_data']['synchronization_stop_date'];
                if (!is_null($tempDate) && $tempDate != '') {
                    $tempDate = Mage::helper('M2ePro')->timezoneDateToGmt($tempDate);
                }
                $_SESSION['M2ePro_data']['synchronization_stop_date'] = $tempDate;
                //---------------

                // Add new listing
                //---------------
                $listingId = Mage::getModel('M2ePro/Listings')
                                       ->setData($_SESSION['M2ePro_data'])
                                       ->save()
                                       ->getId();
                $listingModel = Mage::getModel('M2ePro/Listings')->loadInstance($listingId);
                //---------------

                // Set message to log
                //---------------
                Mage::getModel('M2ePro/ListingsLogs')
                    ->addListingMessage( $listingId,
                                         Ess_M2ePro_Model_LogsBase::INITIATOR_USER,
                                         NULL,
                                         Ess_M2ePro_Model_ListingsLogs::ACTION_ADD_LISTING,
                                         // Parser hack -> Mage::helper('M2ePro')->__('Listing was successfully added');
                                         'Listing was successfully added',
                                         Ess_M2ePro_Model_ListingsLogs::TYPE_NOTICE,
                                         Ess_M2ePro_Model_ListingsLogs::PRIORITY_HIGH );
                //---------------

                // Add products
                //---------------
                if (count($selectedProductsIds) > 0 && count($selectedCategoriesIds) == 0 && count($selectedSessionCategoriesIds) == 0) {
                    foreach ($selectedProductsIds as $productId) {
                        $listingModel->addProduct($productId);
                    }
                }
                //---------------

                // Add categories
                //---------------
                if (count($selectedProductsIds) == 0 && count($selectedCategoriesIds) > 0 && count($selectedSessionCategoriesIds) == 0) {
                    foreach ($selectedCategoriesIds as $categoryId) {
                        $listingModel->addProductsFromCategory($categoryId);
                        Mage::getModel('M2ePro/ListingsCategories')
                                           ->setData(array('listing_id'=>$listingId,'category_id'=>$categoryId))
                                           ->save();
                    }
                }
                //---------------

                // Add categories and products
                //---------------
                if (count($selectedProductsIds) > 0 && count($selectedCategoriesIds) == 0 && count($selectedSessionCategoriesIds) > 0) {
                    foreach ($selectedSessionCategoriesIds as $categoryId) {
                        Mage::getModel('M2ePro/ListingsCategories')
                                           ->setData(array('listing_id'=>$listingId,'category_id'=>$categoryId))
                                           ->save();
                    }
                    foreach ($selectedProductsIds as $productId) {
                        $listingModel->addProduct($productId);
                    }
                }
                //---------------

                // Clear session data
                //---------------
                $_SESSION['M2ePro_data'] = array();
                $_SESSION['temp_listing_categories'] = array();
                //---------------

                $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Listing was successfully added.'));

                if ($this->getRequest()->getParam('back') == 'list') {
                    $this->_redirect('*/*/index');
                } else {
                    $this->_redirect('*/*/view',array('id'=>$listingId,'new'=>'yes'));
                }

                return;

            } else {
                $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
                return;
            }
        }
        //----------------------------

        Mage::register('M2ePro_data', $_SESSION['M2ePro_data']);
        Mage::register('temp_listing_categories', $_SESSION['temp_listing_categories']);

        // Load layout and start render
        //----------------------------
        $this->_initAction();

        if ($_SESSION['M2ePro_data']['source_products'] == Ess_M2ePro_Model_Listings::SOURCE_PRODUCTS_CUSTOM) {
            $blockContent = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_addStepTwoProducts');
        } else if ($_SESSION['M2ePro_data']['source_products'] == Ess_M2ePro_Model_Listings::SOURCE_PRODUCTS_CATEGORIES) {
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $blockContent = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_addStepTwoCategories');
        } else {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }

        $this->_addContent($blockContent);

        $this->renderLayout();
        //----------------------------
    }

    public function addStepThree()
    {
        // Check exist temp data
        //----------------------------
        if (!isset($_SESSION['M2ePro_data']) ||
            count($_SESSION['M2ePro_data']) == 0 ||
            !isset($_SESSION['temp_listing_categories']) ||
            count($_SESSION['temp_listing_categories']) == 0) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        Mage::register('M2ePro_data', $_SESSION['M2ePro_data']);
        Mage::register('temp_listing_categories', $_SESSION['temp_listing_categories']);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listings_addStepThree'))
             ->renderLayout();
    }

    //#############################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        // If it post request
        //----------------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            $dataForUpdate = array(
                'title' => $post['title'],

                'selling_format_template_id' => $post['selling_format_template_id'],
                'listing_template_id' => $post['listing_template_id'],
                'description_template_id' => $post['description_template_id'],
                'synchronization_template_id' => $post['synchronization_template_id'],

                'synchronization_start_type' => $post['synchronization_start_type'],
                'synchronization_start_through_metric' => $post['synchronization_start_through_metric'],
                'synchronization_start_through_value' => $post['synchronization_start_through_value'],
                'synchronization_start_date' => $post['synchronization_start_date'],

                'synchronization_stop_type' => $post['synchronization_stop_type'],
                'synchronization_stop_through_metric' => $post['synchronization_stop_through_metric'],
                'synchronization_stop_through_value' => $post['synchronization_stop_through_value'],
                'synchronization_stop_date' => $post['synchronization_stop_date'],

                'categories_add_action' => $post['categories_add_action'],
                'categories_delete_action' => $post['categories_delete_action']
            );

            // Prepare listing data
            //---------------
			$tempDate = $dataForUpdate['synchronization_start_date'];
            if (!is_null($tempDate) && $tempDate != '') {
                $tempDate = Mage::helper('M2ePro')->timezoneDateToGmt($tempDate);
            }
            $dataForUpdate['synchronization_start_date'] = $tempDate;

            $tempDate = $dataForUpdate['synchronization_stop_date'];
            if (!is_null($tempDate) && $tempDate != '') {
                $tempDate = Mage::helper('M2ePro')->timezoneDateToGmt($tempDate);
            }
            $dataForUpdate['synchronization_stop_date'] = $tempDate;
			//---------------

			// Prepare listing data
			//---------------
            if ($model->getData('synchronization_template_id') != $dataForUpdate['synchronization_template_id']) {

                $model->setSynchronizationAlreadyStart(false);
                $model->setSynchronizationAlreadyStop(false);
            }

            if ($model->getData('synchronization_start_type') != $dataForUpdate['synchronization_start_type'] ||
                $model->getData('synchronization_start_through_metric') != $dataForUpdate['synchronization_start_through_metric'] ||
                $model->getData('synchronization_start_through_value') != $dataForUpdate['synchronization_start_through_value'] ||
                $model->getData('synchronization_start_date') != $dataForUpdate['synchronization_start_date']) {

                $model->setSynchronizationAlreadyStart(false);
            }

            if ($model->getData('synchronization_stop_type') != $dataForUpdate['synchronization_stop_type'] ||
                $model->getData('synchronization_stop_through_metric') != $dataForUpdate['synchronization_stop_through_metric'] ||
                $model->getData('synchronization_stop_through_value') != $dataForUpdate['synchronization_stop_through_value'] ||
                $model->getData('synchronization_stop_date') != $dataForUpdate['synchronization_stop_date']) {

                $model->setSynchronizationAlreadyStop(false);
            }
            //---------------

            $model->addData($dataForUpdate)->save();

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The listing was successfully saved.'));

            Mage::getModel('M2ePro/ListingsLogs')->updateListingTitle($id,$dataForUpdate['title']);

            $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
            return;
		}
        //----------------------------

        Mage::register('M2ePro_data', $model->getData());

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listings_edit'))
             ->renderLayout();
    }

    //#############################################

    public function productsAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist..'));
            return $this->_redirect('*/*/index');
        }

        // Get save param
        //----------------------------
        if ($this->getRequest()->isPost()) {

            // Get selected_products param
            //---------------
            $selectedProductsIds = array();

            $selectedProducts = $this->getRequest()->getParam('selected_products');
            if (!is_null($selectedProducts)) {
                $selectedProductsIds = explode(',',$selectedProducts);
            }
            $selectedProductsIds = array_unique($selectedProductsIds);
            //---------------

            // Add products
            //---------------
            foreach ($selectedProductsIds as $productId) {
                $model->addProduct($productId);
            }
            //---------------

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The products were added to listing.'));
            $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list'));
            return;
        }
        //----------------------------

        Mage::register('M2ePro_data', $model->getData());
        Mage::register('temp_listing_categories', array());

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listings_products'))
             ->renderLayout();
    }

    public function gridProductsAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!is_null($id)) {
            if (!is_null($model->getId())) {
                Mage::register('M2ePro_data', $model->getData());
            } else {
                Mage::register('M2ePro_data', array());
            }
            Mage::register('temp_listing_categories',array());
        } else {
            if (isset($_SESSION['M2ePro_data'])) {
                Mage::register('M2ePro_data', $_SESSION['M2ePro_data']);
            } else {
                Mage::register('M2ePro_data', array());
            }
            if (isset($_SESSION['temp_listing_categories'])) {
                Mage::register('temp_listing_categories', $_SESSION['temp_listing_categories']);
            } else {
                Mage::register('temp_listing_categories', array());
            }
        }

        $response = $this->getLayout()->createBlock('M2ePro/adminhtml_listings_products_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function clearLogAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to clear'));
            return $this->_redirect('*/*/index');
        }

        $idsForClear = array();
        !is_null($id) && $idsForClear[] = (int)$id;
        !is_null($ids) && $idsForClear = array_merge($idsForClear,(array)$ids);

        foreach ($idsForClear as $id) {
            Mage::getModel('M2ePro/ListingsLogs')->clearMessages($id);
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The listing(s) log was successfully cleaned.'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list'));
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to remove'));
            return $this->_redirect('*/*/index');
        }

        $idsForDelete = array();
        !is_null($id) && $idsForDelete[] = (int)$id;
        !is_null($ids) && $idsForDelete = array_merge($idsForDelete,(array)$ids);

        $deleted = $locked = 0;
        foreach ($idsForDelete as $id) {
            $template = Mage::getModel('M2ePro/Listings')->loadInstance($id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%count% listing(s) were successfully deleted');
        $deleted && $this->_getSession()->addSuccess(str_replace('%count%',$deleted,$tempString));

        $tempString = Mage::helper('M2ePro')->__('%count% listing(s) have listed items and can not be deleted');
        $locked && $this->_getSession()->addError(str_replace('%count%',$locked,$tempString));
        
        $this->_redirect('*/*/index');
    }

    //#############################################

    public function goToSellingFormatTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $this->_redirect('*/adminhtml_sellingFormatTemplates/edit', array('id' => $model->getData('selling_format_template_id'),'back'=>Mage::helper('M2ePro')->getBackUrlParam('list')));
    }

    public function goToListingTemplateAction()
	{
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $this->_redirect('*/adminhtml_listingTemplates/edit', array('id' => $model->getData('listing_template_id'),'back'=>Mage::helper('M2ePro')->getBackUrlParam('list')));
    }

    public function goToDescriptionTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $this->_redirect('*/adminhtml_descriptionTemplates/edit', array('id' => $model->getData('selling_format_template_id'),'back'=>Mage::helper('M2ePro')->getBackUrlParam('list')));
    }

    public function goToSynchronizationTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listings')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $this->_redirect('*/adminhtml_synchronizationTemplates/edit', array('id' => $model->getData('synchronization_template_id'),'back'=>Mage::helper('M2ePro')->getBackUrlParam('list')));
    }

    //--------------------

    public function goToEbayAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        if (is_null($itemId)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested eBay Item ID is not found.'));
            $this->_redirect('*/*/index');
            return;
        }

        $listingProductModel = Mage::getModel('M2ePro/ListingsProducts')->getInstanceByEbayItem($itemId);

        if ($listingProductModel === false) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested eBay Item ID is not found.'));
            $this->_redirect('*/*/index');
            return;
        }

        $listingTemplateModel = $listingProductModel->getListingTemplate();

        $account = Mage::getModel('M2ePro/Accounts')->load( $listingTemplateModel->getData('account_id') );

        $url = Mage::helper('M2ePro/Ebay')->getEbayItemUrl($itemId,
                                                            $account['mode'],
                                                            $listingTemplateModel->getData('marketplace_id'));

        $this->_redirectUrl($url);
    }

    //#############################################

    public function checkLockListingAction()
    {
        $listingId = (int)$this->getRequest()->getParam('id');

        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>$listingId));

        if ($lockItem->isExist()) {
            exit('locked');
        }

        exit('unlocked');
    }

    public function lockListingNowAction()
    {
        $listingId = (int)$this->getRequest()->getParam('id');

        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>$listingId));

        if (!$lockItem->isExist()) {
            $lockItem->create();
        }

        exit();
    }

    public function unlockListingNowAction()
    {
        $listingId = (int)$this->getRequest()->getParam('id');

        $lockItem = Mage::getModel('M2ePro/ListingsLockItem',array('id'=>$listingId));

        if ($lockItem->isExist()) {
            $lockItem->remove();
        }

        exit();
    }

    public function getErrorsSummaryAction()
    {
        $blockParams = array(
            'action_ids' => $this->getRequest()->getParam('action_ids'),
            'table_name' => Mage::getResourceModel('M2ePro/ListingsLogs')->getMainTable(),
            'type_log' => 'listings'
        );
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_logs_errorsSummary','',$blockParams);
        exit($block->toHtml());
    }

    //#############################################

    protected function processConnector($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            exit('You should select products');
        }

        $params['status_changer'] = Ess_M2ePro_Model_ListingsProducts::STATUS_CHANGER_USER;

        $listingsProductsIds = explode(',', $listingsProductsIds);

        $dispatcherObject = Mage::getModel('M2ePro/Connectors_Ebay_Item_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingsProductsIds, $params);
        $actionId = (int)$dispatcherObject->getLogsActionId();

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_ERROR) {
            exit(json_encode(array('result'=>'error','action_id'=>$actionId)));
        }

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_WARNING) {
            exit(json_encode(array('result'=>'warning','action_id'=>$actionId)));
        }

        if ($result == Ess_M2ePro_Model_Connectors_Ebay_Item_Abstract::STATUS_SUCCESS) {
            exit(json_encode(array('result'=>'success','action_id'=>$actionId)));
        }

        exit(json_encode(array('result'=>'error','action_id'=>$actionId)));
    }

    //--------------------

    public function runListProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_LIST);
    }

    public function runReviseProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_REVISE);
    }

    public function runRelistProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_RELIST);
    }

    public function runStopProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP);
    }

    public function runStopAndRemoveProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connectors_Ebay_Item_Dispatcher::ACTION_STOP, array('remove' => true));
    }

    //#############################################
}