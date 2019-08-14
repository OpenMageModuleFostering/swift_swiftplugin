<?php

require_once(Mage::getBaseDir('lib') . '/SwiftAPI/SwiftAPI.php');

/**
*	Handles processing formatting and outputting of mageneto plugin on public side
*/
class Swift_Swiftplugin_Block_Swiftblock extends Mage_Core_Block_Template {

	/**
	* Checks what operation to perform
	*/
	private $operation;
	
	/**
	* Swift data retrieved from the database
	*/
	private $swiftData;
	
	/**
	* Request output
	*/
	private $request;
	
	/**
	* Generate Request
	*/
	public function generateRequest() {
		$this->loadSwiftKey();
		$swiftData = $this->getSwiftData();
		if (!is_bool($swiftData)) {
			$this->initialiseOperation();
			$this->formatRequest();
			return $this->scriptResponse();
		}
	}
	
	/**
	* Returns the swift data to ensure that it not empty and carry on the request in swiftplugin.phtml
	*/
	public function getSwiftData() {
		return $this->swiftData;
	}
	
	/**
	* Loads swift data from database
	*/
	public function loadSwiftKey() {
		$this->swiftData = Mage::helper('swift/Data')->getSwiftPrivateKey();
	}
	
	/**
	* Works out the operation to perform
	*/
	public function initialiseOperation() {
		$this->operation = '';
		// detect when a product has been added to the cart
		if (Mage::getSingleton('core/session')->getSwiftProductAddedToCartFlag(true)) {
			$this->operation = SwiftAPI::OPERATION_CART;
		}
		// check when an order is successful
		else if (Mage::getSingleton('core/session')->getProductOrderSuccessFlag(true)) {
			$this->operation = SwiftAPI::OPERATION_ORDER;
		}
		// check when someone has signed up to the newsletter
		else if (Mage::getSingleton('core/session')->getSwiftSubscribeToNewsletterFlag(true)) {
			$this->operation = SwiftAPI::OPERATION_SUBSCRIPTION;
		}
		else if (Mage::getSingleton('core/session')->getSwiftEmailRequestFlag(true)) {
			$this->operation = SwiftAPI::OPERATION_VIEWMAIL;
		}
		// if a product is being viewed
		else if (!is_null(Mage::registry('current_product'))) {
			$this->operation = SwiftAPI::OPERATION_PRODUCT;
		}
		// finally viewing the homepage
		else if (Mage::getSingleton('cms/page')->getIdentifier() == 'home') {
			$this->operation = SwiftAPI::OPERATION_HOME;
		}
	}
	
	/**
	* Formats the data into a request object
	*/
	public function formatRequest() {
	
		$version = SwiftAPI::VERSION;
		$domain = $_SERVER['HTTP_HOST'];
		$user = Mage::helper('swift/Data')->generateUserId();
		$swiftEmail = $this->getSwiftEmail();

		switch($this->operation) {
			case SwiftAPI::OPERATION_HOME:
				$this->request = new SwiftAPI_Request_Home($domain, $user, $_SERVER['REQUEST_URI'], $swiftEmail, $version);
				break;
			case SwiftAPI::OPERATION_PRODUCT:
				$product = $this->getProductIdData();
				$this->request = new SwiftAPI_Request_Product($domain, $user, $product, $swiftEmail, $version);
				break;
			case SwiftAPI::OPERATION_CART:
				$data = $this->getAddedCartData();
				$this->request = new SwiftAPI_Request_Cart($domain, $user, $data['products'], $swiftEmail, $version);
				break;
			case SwiftAPI::OPERATION_ORDER:
				$data = $this->getOrderData();
				$this->request = new SwiftAPI_Request_Order($domain, $user, $swiftEmail, $data['forename'], $data['surname'], $data['products'], $data['order_id'], null, $version, $data['created']);
				break;
			case SwiftAPI::OPERATION_SUBSCRIPTION:
				$this->request = new SwiftAPI_Request_Subscription($domain, $user, $swiftEmail, $version);
				break;
			default:
				$this->request = false;
		}
	}
	
	/**
	* Scripts the response
	*/
	public function scriptResponse() {
		if($this->request) {
			$key = hex2bin($this->swiftData);
			return SwiftAPI::Script($this->request, $key);
		}
		return '';
	}
	
	/**
	* Returns the product Id of a item
	*/
	private function getProductIdData() {
		$product = Mage::registry('current_product');
		if (!is_null($product)) {
			return $product->getId();
		}
		return false;
	}
	
	/**
	* Returns the data added to the cart
	*/
	private function getAddedCartData() {
		// set swift product id
		$data = array();
		$product = Mage::getSingleton('core/session')->getSwiftProduct(true);
		$data['products'][] = new SwiftAPI_Product($product['product'], $product['quantity'], $product['price']);
		$swiftEmail = $this->getSwiftEmail();
		is_null($swiftEmail) ? null : $data['email'] = $swiftEmail; 
		return $data;
	}
		
	/**
	* Returns the order data
	*/
	private function getOrderData() {
		$data = array();
		$data['order_id'] = Mage::getSingleton('core/session')->getSwiftOrderId(true);
		$data['created'] = Mage::getSingleton('core/session')->getSwiftOrderCreated(true);
		$data['forename'] = Mage::getSingleton('core/session')->getSwiftForename(true);
		$data['surname'] = Mage::getSingleton('core/session')->getSwiftSurname(true);
		$data['products'] = array();
		$products = Mage::getSingleton('core/session')->getSwiftProductsList(true);
		foreach($products as $product) {
			$data['products'][] = new SwiftAPI_Product($product['product'], $product['quantity'], $product['price']);
		}	
		return $data;
	}
	
	/**
	 * Returns the swift email
	 */
	private function getSwiftEmail() {
		return Mage::getSingleton('core/session')->getSwiftEmail();
	}
}

?>
