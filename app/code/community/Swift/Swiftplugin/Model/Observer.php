<?php
/**
 * Our class name should follow the directory structure of our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores. i.e. app/code/local/Swift/SwiftPlugin/Model/Observer.php
 */
class Swift_SwiftPlugin_Model_Observer
{
    /**
	* Called when flag_product_added_to_cart() is called this is set in config.xml
	* Flags when a product has been added to a cart and pass it to the SwiftApi
	*/
    public function flag_product_added_to_cart(Varien_Event_Observer $observer) {
       	// make checks in case something goes in as null
		if (!is_null($observer)) {
			$event = $observer->getEvent();
			if (!is_null($event)) {
				$product = $observer->getEvent()->getProduct();
				if (!is_null($product)) {
					// check still a valid object
					if ($product->getEntityId() > 0 && $product->getQty() > 0) {
						// tells swift that a product has been added to cart
						Mage::getSingleton('core/session')->setSwiftProductAddedToCartFlag(true);
						// set swift product id
						Mage::getSingleton('core/session')->setSwiftProduct(array('product' => $product->getId(), 'quantity' => $product->getQty(), 'price' => $product->getPrice()));
					}
				}
			}
		}
    }
	
	/**
	* Called when newsletter_subscriber_save_after() is called this is set in config.xml
	* Flags when a user has subscribed to a newsletter and pass it to the SwiftApi
	*/
	public function flag_subscribe_to_newsletter(Varien_Event_Observer $observer) {
        // make checks in case something goes in as null
		if (!is_null($observer)) {
			$event = $observer->getEvent();
			if (!is_null($event)) {
				$subscriber = $event->getSubscriber();
				if (!is_null($subscriber)) {
					$data = $subscriber->getData();
					// checks to confirm whether it is a valid email 
					if (filter_var( $data['subscriber_email'], FILTER_VALIDATE_EMAIL )) {
						// flag newsletter details
						Mage::getSingleton('core/session')->setSwiftSubscribeToNewsletterFlag(true);
						Mage::getSingleton('core/session')->setSwiftEmail($data['subscriber_email']);
					}
				}
			}
		}
	}
	
	/**
	* Called when checkout_submit_all_after() is called this is set in config.xml
	* Flags the order was a success and collects data about the user and their order to pass into SwiftApi
	*/
	public function flag_product_order_success(Varien_Event_Observer $observer) {
		// make checks in case something goes in as null
		if (!is_null($observer)) {
			$event = $observer->getEvent();
			if (!is_null($event)) {
				$quote = $event->getQuote();
				$order = $event->getOrder();
				
				
				if (!is_null($quote)) {
					// collect data about the order
					$orderdata = $quote->getData();
					$orderdata2 = $order->getData();
					
					$items = $quote->getAllVisibleItems();
					// collect data about the orderer and assign them to session
					Mage::getSingleton('core/session')->setProductOrderSuccessFlag(true);
					Mage::getSingleton('core/session')->setSwiftOrderId($orderdata2['entity_id']);
					Mage::getSingleton('core/session')->setSwiftOrderCreated($orderdata['created_at']);
					Mage::getSingleton('core/session')->setSwiftForename($orderdata['customer_firstname']);
					Mage::getSingleton('core/session')->setSwiftSurname($orderdata['customer_lastname']);
					Mage::getSingleton('core/session')->setSwiftEmail($orderdata['customer_email']);
					// collect item data and assign them to session
					$productData = array();
					foreach($items as $item) {
							$productData[] = array('product' => $item->getProductId(), 'quantity' => $item->getQty(), 'price' => $item->getPrice());
					}
					Mage::getSingleton('core/session')->setSwiftProductsList($productData);
				}
			}
		}
	}
	
}

?>