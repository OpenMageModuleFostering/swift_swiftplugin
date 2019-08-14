<?php

require_once(Mage::getBaseDir('lib') . '/SwiftAPI/SwiftAPI.php');

/**
 * Description of OrdersController
 *
 * @author netready
 */
class Swift_Swiftplugin_OrdersController extends Mage_Core_Controller_Front_Action {
	
	public function pastordersAction() {
		
		$key = hex2bin(Mage::helper('swift/Data')->getSwiftPrivateKey());
		
		if (!is_bool($key) && !is_null($key)) {
			
			$domain = $_SERVER['HTTP_HOST'];
			$user = Mage::helper('swift/Data')->generateUserId();
			$url = 'http:'.SwiftApi::SWIFTAPI_CRM_URL;
			$limit = is_numeric($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 50;
			$offset = is_numeric($this->getRequest()->getParam('offset')) ? $this->getRequest()->getParam('offset') : 1;
			$date = $this->getRequest()->getParam('date') ? $this->getRequest()->getParam('date') : date('Y-m-d');
		
			$orderCollection = Mage::getModel('sales/order')->getCollection()
			->addFieldToSelect(array(
				'entity_id',
				'customer_email',
				'customer_firstname',
				'customer_lastName',
				'created_at'
			))
			->addAttributeToFilter('created_at' , array('gt' => date('Y-m-d', strtotime($date.'-2 years'))))
			->setCurPage($offset)
			->setPageSize($limit)
			->setOrder('created_at', 'ASC');
				
			$cacheCollection = Mage::getModel('swift/swiftorders')->getCollection()->addFieldToSelect('*');
			$cacheData = $cacheCollection->getColumnValues('swift_order_id');
			
			if ($offset <= $orderCollection->getLastPageNumber()) {
				
				foreach($orderCollection as $order) {
					
					if ($this->getRequest()->getParam('skip') || array_search($order->getId(), $cacheData) === false) {
						
						$visibleItems = $order->getAllVisibleItems();
						$products = array();

						foreach($visibleItems as $order_item_key => $orderItem) {
							$products[] = array('product' => $orderItem->getId(), 'price' => $orderItem->getPrice(), 'quantity' => $orderItem->getData('qty_ordered'));
						}

						$request = new SwiftAPI_Request_PastOrder($domain, $user, $order->getCustomerEmail(), $order->getCustomerFirstname(), $order->getCustomerLastname(), $products, $order->getId(), null, null, $order->getCreatedAt());

						$options = array (
							'http' => array(
								'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
								'method'  => 'POST',
								'content' => SwiftAPI::Query($request, $key)
							)
						);
	
						$context  = stream_context_create($options);
						file_get_contents($url, false, $context);
						
						if (isset($_GET['report'])) {
							$data = array('swift_order_id' => $order->getId());
							$model = Mage::getModel('swift/swiftorders')->setData($data); 
							$model->save();
						}
						
					}
					
				}
				
				if ($limit > $orderCollection->count()) {
					$response = array();
					$response['status'] = 3;
					$response['message'] = 'No more orders to send at this time, but has not fetched a full '. $limit;
				}
				else {
					$response = array();
					$response['status'] =  1; 
					$response['message'] = 'Past orders successfully sent to swift';
				}
			}
			else {
				$response = array();
				$response['status'] = 2;
				$response['message'] = 'No more orders to send at this time';
			}
		}
		else {
			$response = array();
			$response['status'] = 0;
			$response['message'] = 'You cannot perform this operation as you have not registered your private key with swift';
		}
		
		echo json_encode($response);
		
	}
	
	public function cacheAction() {
		$limit = is_numeric($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 50;
		$offset = is_numeric($this->getRequest()->getParam('offset')) ? $this->getRequest()->getParam('offset') : 1;
		
		$cacheCollection = Mage::getModel('swift/swiftorders')
			->getCollection()
			->addFieldToSelect('*')
			->setCurPage($offset)
			->setPageSize($limit);
		
		$cache = array();
		foreach($cacheCollection as $cacheItem) {
			$cache[$cacheItem->getId()] = array($cacheItem->getSwiftOrderId() => $cacheItem->getCreated());
		}
		
		echo json_encode($cache);
	}
	
	public function deletecacheAction() {
		if (($this->getRequest()->getParam('date') ? strtotime($this->getRequest()->getParam('date')) : false)) {
			$date = $this->getRequest()->getParam('date');
			$cacheCollection = Mage::getModel('swift/swiftorders')
			->getCollection()
			->addFieldToSelect('*')
			->addFieldToFilter('created' , array('lteq' => date('Y-m-d', strtotime($date))));
			
			foreach($cacheCollection as $cacheItem) {
				$cacheItem->delete();
			}
			echo json_encode(array('The cache has deleted from before '.$date.'.'));
		}
		else {
			echo json_encode(array('Invalid parameters.'));
		}
		
	}
	
}
