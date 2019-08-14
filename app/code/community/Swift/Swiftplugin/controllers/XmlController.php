<?php

class Swift_Swiftplugin_XmlController extends Mage_Core_Controller_Front_Action {

	public function feedAction() {
		$obj = new Swift_Swiftplugin_Model_XmlProduct();
		$obj->generate_xml();
    }
	
	public function sendpastproductAction() {
		
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
			echo 'Past orders successfully sent to swift';
		}
		else {
			echo 'You cannot perform this operation as you have not registered your private key with swift';
		}		
		
	}
	
}

?>