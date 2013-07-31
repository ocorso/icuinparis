<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_CmdController extends Ess_M2ePro_Controller_Adminhtml_CmdController
{
    //#############################################

    public function indexAction()
    {
        $this->printCommandsList();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('ebay');
    }

    //#############################################

    /**
     * @title "Test"
     * @description "Command for quick development"
     * @new_line
     */
    public function testAction()
    {
        $this->printBack();
    }

    //#############################################
    
    /**
     * @title "PHP Info"
     * @description "View server phpinfo() information"
     */
    public function phpInfoAction()
    {
        if ($this->getRequest()->getParam('frame')) {
            phpinfo();
            return;
        }

        $this->printBack();
        $urlPhpInfo = $this->getUrl('*/*/*', array('frame' => 'yes'));
        echo '<iframe src="' . $urlPhpInfo . '" style="width:100%; height:90%;" frameborder="no"></iframe>';
    }

    /**
     * @title "ESS Configuration"
     * @description "Go to ess configuration edit page"
     */
    public function goToEditEssConfigAction()
    {
        $this->_redirect('*/adminhtml_config/ess');
    }

    /**
     * @title "M2ePro Configuration"
     * @description "Go to m2epro configuration edit page"
     * @new_line
     */
    public function goToEditM2eProConfigAction()
    {
        $this->_redirect('*/adminhtml_config/m2epro');
    }

    //#############################################

    /**
     * @title "Clear Cache"
     * @description "Clear magento cache"
     * @confirm "Are you sure?"
     */
    public function clearCacheAction($directory = '')
    {
        $directory == '' && $directory = Mage::getBaseDir('var') . '/cache';

        if ($objects = glob($directory.'/*')) {
            foreach ($objects as $object) {
                is_dir($object) ? $this->clearCacheAction($object) : unlink($object);
            }
        }

        is_dir($directory) && rmdir($directory);

        if ($directory == Mage::getBaseDir('var') . '/cache') {
            $this->printBack();
        }
    }

    /**
     * @title "Clear COOKIES"
     * @description "Clear all current cookies"
     * @confirm "Are you sure?"
     */
    public function clearCookiesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', 0, '/');
        }
        $this->_redirect('*/adminhtml_cmd/index');
    }

    /**
     * @title "Update License"
     * @description "Send update license request to server"
     */
    public function licenseUpdateAction()
    {
        $this->printBack();

        Mage::getModel('M2ePro/License_Server')->updateStatus(true);
        Mage::getModel('M2ePro/License_Server')->updateLock(true);
        Mage::getModel('M2ePro/License_Server')->updateMessages(true);
    }

    /**
     * @title "Server Connection"
     * @description "Send test request to server and check connection"
     * @new_line
     */
    public function serverCheckConnectionAction()
    {
        $this->printBack();

        $curlObject = curl_init();

        //set the server we are using
        $serverUrl = Mage::helper('M2ePro/Module')->getServerScriptsPath().'index.php';
        curl_setopt($curlObject, CURLOPT_URL, $serverUrl);

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query(array(),'','&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 300);

        $response = curl_exec($curlObject);

        echo '<h1>Response</h1><pre>';
        print_r($response);
        echo '</pre><h1>Report</h1><pre>';
        print_r(curl_getinfo($curlObject));
        echo '</pre>';

        echo '<h2 style="color:red;">Errors</h2>';
        echo curl_errno($curlObject) . ' ' . curl_error($curlObject) . '<br><br>';

        curl_close($curlObject);
    }

    //#############################################

    private function processSynchTasks($tasks)
    {
        $shutdownFunctionCode = '';
        $configProfiler = Mage::helper('M2ePro/Module')->getConfig()->getAllGroupValues('/synchronization/profiler/');
        foreach ($configProfiler as $key => $value) {
            $shutdownFunctionCode .= "Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/profiler/', '{$key}', '{$value}');";
        }
        $shutdownFunctionInstance = create_function('', $shutdownFunctionCode);
        register_shutdown_function($shutdownFunctionInstance);

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/profiler/', 'mode', '3');
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/profiler/', 'delete_resources', '0');
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/profiler/', 'print_type', '2');

        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->process($tasks, Ess_M2ePro_Model_Synchronization_Runs::INITIATOR_DEVELOPER, array());
    }

    /**
     * @title "Synch Cron Tasks"
     * @description "Run all cron synchronization tasks as developer mode"
     * @confirm "Are you sure?"
     */
    public function synchCronTasksAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
              Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES,
              Ess_M2ePro_Model_Synchronization_Tasks::ORDERS,
              Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS,
              Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES,
              Ess_M2ePro_Model_Synchronization_Tasks::EBAY_LISTINGS
         ));
    }

    /**
     * @title "Synch Defaults"
     * @description "Run only defaults synchronization as developer mode"
     * @confirm "Are you sure?"
     */
    public function synchDefaultsAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS
         ));
    }

    /**
     * @title "Synch Templates"
     * @description "Run only templates synchronization as developer mode"
     * @confirm "Are you sure?"
     */
    public function synchTemplatesAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES
         ));
    }

    /**
     * @title "Synch Orders"
     * @description "Run only orders synchronization as developer mode"
     * @confirm "Are you sure?"
     */
    public function synchOrdersAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::ORDERS
         ));
    }

    /**
     * @title "Synch Feedbacks"
     * @description "Run only feedbacks synchronization as developer mode"
     * @confirm "Are you sure?"
     */
    public function synchFeedbacksAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS
         ));
    }

     /**
     * @title "Synch Messages"
     * @description "Run only messages synchronization as developer mode"
     * @confirm "Are you sure?"
     */
    public function synchMessagesAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES
         ));
    }

    /**
     * @title "Synch Marketplaces"
     * @description "Run only marketplaces synchronization as developer mode"
     * @confirm "Are you sure?"
     */
    public function synchMarketplacesAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::MARKETPLACES
         ));
    }

    /**
     * @title "Synch 3rd Party Listings"
     * @description "Run only 3rd party listings synchronization as developer mode"
     * @confirm "Are you sure?"
     * @new_line
     */
    public function synchEbayListingsAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::EBAY_LISTINGS
         ));
    }

    //#############################################

    

    

    //#############################################
}