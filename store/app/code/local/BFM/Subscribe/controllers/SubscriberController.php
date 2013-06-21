<?php

class BFM_Subscribe_SubscriberController extends Mage_Core_Controller_Front_Action {

	public function newAction()
	{
		if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
			$session = Mage::getSingleton('core/session');
			$customerSession = Mage::getSingleton('customer/session');
			$email = (string) $this->getRequest()->getPost('email');

			try {
				$error = true;
				if (!Zend_Validate::is($email, 'EmailAddress')) {
					Mage::throwException(Mage::helper('bfmall')->__('Please enter a valid email address.'));
				}

				if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
						!$customerSession->isLoggedIn()) {
					Mage::throwException(Mage::helper('bfmall')->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
				}

				$ownerId = Mage::getModel('customer/customer')
						->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
						->loadByEmail($email)
						->getId();
				if ($ownerId !== null && $ownerId != $customerSession->getId()) {
					Mage::throwException(Mage::helper('bfmall')->__('This email address is already assigned to another user.'));
				}

				$status = Mage::getModel('newsletter/subscriber')->subscribe($email);
				if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
					$error = false;
					$result = Mage::helper('bfmall')->__('Confirmation request has been sent.');
				} else {
					$error = false;
					$result = Mage::helper('bfmall')->__('You are now subscribed to receive updates from ICUinParis.com');
				}
			} catch (Exception $e) {
				$result = $e->getMessage();
			}

			$content = Zend_Json::encode(array('error' => $error, 'result' => $result));
			$response = $this->getResponse();
			$response->setHeader('HTTP/1.1 200 OK', '');
			$response->setHeader('Pragma', 'public', true);
			$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
			$response->setHeader('Last-Modified', date('r'));
			$response->setHeader('Accept-Ranges', 'bytes');
			$response->setHeader('Content-Length', strlen($content));
			$response->setHeader('Content-type', 'text/json');
			$response->setBody($content);
			$response->sendResponse();
			die;
		}
	}

}
