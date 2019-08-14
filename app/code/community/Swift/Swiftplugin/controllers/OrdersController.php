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
			$limit = 1000;
		
			$orderCollection = Mage::getModel('sales/order')->getCollection()
			->addFieldToSelect(array(
				'entity_id',
				'customer_email',
				'customer_firstname',
				'customer_lastName',
				'created_at'
			))
			->addAttributeToFilter('created_at' , array('gt' => date('Y-m-d H:i:s', strtotime('-2 years'))))
			->setCurPage($this->getRequest()->getParam('offset'))
			->setPageSize($limit);
				
			$orders_present = false; 
			foreach($orderCollection as $order) {
				
				$visibleItems = $order->getAllVisibleItems();
				$orders_present = true;
				
				$products = array();
				foreach($visibleItems as $order_item_key => $orderItem) {
					$products[] = array('product' => $orderItem->getId(), 'price' => $orderItem->getPrice(), 'quantity' => $orderItem->getData('qty_ordered'));
				}
				$request = new SwiftAPI_Request_PastOrder($domain, $user, $order->getCustomerEmail(),$order->getCustomerFirstname(), $order->getCustomerLastname(), $products, null, $order->getCreatedAt());
				
				$options = array (
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => SwiftAPI::Query($request, $key)
					)
				);
				
				$context  = stream_context_create($options);
				file_get_contents($url, false, $context);
				
			}
			
			if ($limit > $orderCollection->count()) {
				$response = array();
				$response['status'] = 2;
				$response['message'] = 'No more orders to send';
			}
			else {
				$response = array();
				$response['status'] =  $orders_present ? 1 : 0;
				$response['message'] = $orders_present ? 'Past orders successfully sent to swift' : 'No more orders to send';
			}
		}
		else {
			$response = array();
			$response['status'] = 0;
			$response['message'] = 'You cannot perform this operation as you have not registered your private key with swift';
		}		
		echo json_encode($response);
		
	}
	
}
