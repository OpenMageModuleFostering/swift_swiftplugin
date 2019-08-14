<?php

require_once(Mage::getBaseDir('lib')  . '/SwiftAPI/SwiftAPI.php');
require_once(Mage::getBaseDir('lib')  . '/SwiftAPI/SwiftAPI_Request_Ping.php');

/**
 * Administration of swift plugin
 */
class Swift_Swiftplugin_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action {

	/**
	 * Renders the form by default, if record exists in the database then retrieve that value and load it to the page
	 */
	public function indexAction() {
		$data = Mage::helper('swift/Data')->getSwiftPrivateData();
		$swiftId = 0;
		if (!is_bool($data) && is_array($data)) {
			if (isset($data['swift_id'])) {
				$swiftId = $data['swift_id'];
			}
		}
		$swiftModel = Mage::getModel('swift/swift')->load($swiftId);
		if ($swiftModel->getId() > 0 || $swiftId == 0) {
			Mage::register('swift_data', $swiftModel);
			$this->loadLayout();
			$this->_addContent($this->getLayout()->createBlock('swift/adminhtml_swift_edit'))->_addLeft($this->getLayout()->createBlock('swift/adminhtml_swift_edit_tabs'));
			$this->renderLayout();
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError('SwiftCRM key does not exist');
			$this->_redirect('*/*/');
		}
	}
	
	/**
	 * Redirects to indexAction
	 *
	 */
	public function editAction() {
		$this->_forward('index');
	}
	
	/**
	 * Redirects to indexAction
	 *
	 */
	public function newAction() {
		$this->_forward('edit');
	}
	
	/**
	 * Perform validation and conversion on post variables before saving to database
	 *
	 */
	public function saveAction() {
		if ($this->getRequest()->getPost()) {
			try {
				$postData = $this->getRequest()->getPost();
				$testModel = Mage::getModel('swift/swift');
				$existingModels = Mage::getModel('swift/swift')->getCollection();
				if (count($existingModels) == 0 || $this->getRequest()->getParam('id')) {
					if  (ctype_xdigit ($postData['swift_private_key']) && strlen($postData['swift_private_key']) == 64) {
						$postData['swift_send_history'] = isset($postData['swift_send_history']) ? '1' : '0';
						$testModel->addData($postData)->setId($this->getRequest()->getParam('id'))->save();
						Mage::getSingleton('adminhtml/session')->addSuccess('successfully saved');
						// if successful ping send past orders to swiftcrm
						if ($this->pingSwiftSystem($postData['swift_private_key']) == 1) {
							$this->_forward('pastproduct');
						}
					}
					else {
						throw new Exception('Invalid string input');
					}
					$this->_redirect('*/*/');
				}
				else {
					throw new Exception('Only one SwiftCRM key is allowed in the system.');
				}				
				return;
			} catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		$this->_redirect('*/*/');
	}
	
	/**
	 * Deletes the record from the database
	 * NOTE: Unused
	 */
	public function deleteAction() {
		if($this->getRequest()->getParam('id') > 0) {
			try {
				$testModel = Mage::getModel('swift/swift');
				$testModel->setId($this->getRequest()->getParam('id'))->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess('successfully deleted');
				$this->_redirect('*/*/');
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
	
	/**
	 *	Send Past orders to Swiftcrm
	 */
	public function pastproductAction() {
	
		$key = hex2bin(Mage::helper('swift/Data')->getSwiftPrivateKey());
		if (!is_bool($key) && !is_null($key)) {
			$version = Mage::getConfig()->getNode()->modules->Swift_Swiftplugin->version;
			$domain = $_SERVER['HTTP_HOST'];
			$user = Mage::helper('swift/Data')->generateUserId();
			$url = 'http:'.SwiftApi::SWIFTAPI_CRM_URL;
		
			$orderCollection = Mage::getModel('sales/order')->getCollection()
			->addAttributeToFilter('created_at' , array('gt' => date('Y-m-d H:i:s', strtotime('-2 years'))));
			foreach($orderCollection as $order_key => $order) {
				$visibleItems = $order->getAllVisibleItems();
				$products = array();
				foreach($visibleItems as $order_item_key => $orderItem) {
					$products[] = array('product' => $orderItem->getId(), 'price' => $orderItem->getPrice(), 'quantity' => $orderItem->getData('qty_ordered'));
				}
				$request = new SwiftAPI_Request_PastOrder($domain, $user, $order->getCustomerEmail(),$order->getCustomerFirstname(), $order->getCustomerLastname(), $products);
				
				$options = array (
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => SwiftAPI::Query($request, $key)
					)
				);

				$context  = stream_context_create($options);
				$result = file_get_contents($url, false, $context);
				echo $result;
			}
			Mage::getSingleton('adminhtml/session')->addSuccess('Past orders successfully sent to swift');
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError('You cannot perform this operation as you have not registered your private key with swift');
		}
		$this->_redirect('*/*/');		
	}
	
	
	public function pingSwiftSystem($key) {
		$domain = $_SERVER['HTTP_HOST'];
		$user = Mage::helper('swift/Data')->generateUserId();
		$url = 'http:'.SwiftApi::SWIFTAPI_CRM_URL;
		$request = new SwiftAPI_Request_Ping($domain, $user, $key);
		$options = array (
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => SwiftAPI::Query($request, hex2bin($key))
			)
		);
		
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return $result;
		// optional extra: send proper feedback to plugin in case something goes wrong with their setup
	}
}

?>
