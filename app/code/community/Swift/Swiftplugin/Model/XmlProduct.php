<?php

require_once(Mage::getBaseDir('lib') . '/libXML/xml.php');

/**
 * When a request is made to generate to fetch existing products from the database the response is xml
 *
 */
class Swift_Swiftplugin_Model_XmlProduct {

	const SWIFT_XML_PRODUCT_VERSION = 2;

	protected $offset;
	
	protected $limit;
	
	public function __construct() {
		$this->offset = 1;
		$this->limit = 100;
	}
	
	public function setOffset($offset = 1) {
		if (is_numeric($offset)) {
			$this->offset = $offset;
		}
	}
	
	public function setLimit($limit = 100) {
		if (is_numeric($limit)) {
			$this->limit = $limit;
		}
	}
	
	/**
	 * Retrieves product details and generates the appropriate response to the request
	 *
	 */
	public function generate_xml() {
		//limit the data parsed
		$_productCollection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect(array('tax_class_id', 'visibility', 'product_id','name','description', 'short_description','price','url_path','image','thumbnail', 'small_image','special_price','sku','special_to_date', 'special_from_date'))
		->addAttributeToFilter('status', array('in' => Mage::getSingleton('catalog/product_status')->getSaleableStatusIds()))
		->setCurPage($this->offset)
		->setPageSize($this->limit);
		
		$xmlRow = array();
		$xml = new xml();
		
		$today = time();
		
		$_coreHelper = Mage::helper('core');
		$_weeeHelper = Mage::helper('weee');
		$_taxHelper = Mage::helper('tax');
		
		if ($this->offset <= $_productCollection->getLastPageNumber()) {
		
			foreach ($_productCollection as $_product) {
					
				$stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct( $_product->getId() );
				
				// check if in stock
				$qty = $stock_item->getData('qty');
				$manageStock = $stock_item->getData('manage_stock');
				$inStock = $stock_item->getData('is_in_stock');
					
				if(!($manageStock == 1 && ($qty<1 || $inStock == 0))) {
					
					$_store = $_product->getStore();
					
										
					$tempXml = array();
					$method = 'g:id';
					$tempXml[] = $xml -> $method(base64_encode($_product->getId()));
					$tempXml[] = $xml -> title(base64_encode(htmlspecialchars($_product->getName(), ENT_QUOTES)));
					$tempXml[] = $xml -> description(base64_encode(htmlspecialchars($_product->getDescription(), ENT_QUOTES)));
					$tempXml[] = $xml -> short_description(base64_encode(htmlspecialchars($_product->getShortDescription(), ENT_QUOTES)));
									
					// if the product is a grouped product then fetch it's parent
					$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($_product->getId());
										
					// we only want products with visibilty or has a parent, if not we need to skip it
					if ($_product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
						if (empty($parentIds)) {
							continue;
						}
					}
					
					$groupProduct = null;
					
					// get parents and use them for the parent url
					if (!empty($parentIds)) {
							
						foreach($parentIds as $parentId) {
							$groupProduct = Mage::getModel('catalog/product')->load($parentId);
							$groupPath = $groupProduct->getProductUrl();
							$tempXml[] = $xml -> link(base64_encode($groupPath));
							break;
						}

					}
					else {
						$tempXml[] = $xml -> link(base64_encode($_product->getProductUrl()));
					}

					// if the product does not have an image assigned then we want to use the parent					
					if (!empty($parentIds) && ($_product->getImage() == 'no_selection' || $_product->getSmallImage() == 'no_selection' || $_product->getThumbnail() == 'no_selection')) {
						
						if (is_null($groupProduct)) {
							continue;
						}

						// group product 
						$method = 'g:image_link';
						$tempXml[] = $xml -> $method(base64_encode($groupProduct->getImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($groupProduct->getImage())));
						$method = 'g:small_image_link';
						$tempXml[] = $xml -> $method(base64_encode($groupProduct->getSmallImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($groupProduct->getSmallImage())));
						$method = 'g:additional_image_link';
						$tempXml[] = $xml -> $method(base64_encode($groupProduct->getThumbnail() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($groupProduct->getThumbnail())));
						
					}
					else {
						// get normal products
						$method = 'g:image_link';
						$tempXml[] = $xml -> $method(base64_encode($_product->getImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getImage())));
						$method = 'g:small_image_link';
						$tempXml[] = $xml -> $method(base64_encode($_product->getSmallImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getSmallImage())));
						$method = 'g:additional_image_link';
						$tempXml[] = $xml -> $method(base64_encode($_product->getThumbnail() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getThumbnail())));
					}
					
					// calculate price, based on final price, tax is calculated, system given chance to diaplay amount with/without tax
					$method = 'g:price';
					$_price = $_taxHelper->getPrice($_product, $_product->getFinalPrice());
					if ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices()) {	
						// as magento config specifies displaying display both prices or prices with tax included make sure we check if tax is applied
						$_price = $_taxHelper->getPrice($_product, $_product->getFinalPrice(), true);
					}

					$tempXml[] = $xml -> $method(base64_encode($_price));
										
					$method = 'g:sale_price';
					$special_price = '';
					if (!is_null($_product->getSpecialPrice())) {
							
						if($today >= strtotime($_product->getSpecialFromDate()) && $today <= strtotime($_product->getSpecialToDate()) || $today >= strtotime($_product->getSpecialFromDate()) && is_null($_product->getSpecialToDate())) {
							
							// calculate price, based on final price, tax is calculated, system given chance to diaplay amount with/without tax
							$special_price = $_taxHelper->getPrice($_product,$_product->getSpecialPrice(), true);
							if ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices()) {
								// as magento config specifies displaying display both prices or prices with tax included make sure we check if tax is applied
								$special_price = $_taxHelper->getPrice($_product, $_product->getSpecialPrice(), true);
							}
							
						}
							
					}				
						
					$tempXml[] = $xml -> $method(base64_encode($special_price));
					$categoryId = $_product->getCategoryIds();
					$categoryId = array_shift($categoryId);
					$category = Mage::getModel('catalog/category')->load($categoryId);
					$tempXml[] = $xml -> subcategory(base64_encode(is_null($category->getName()) ? '' : htmlspecialchars($category->getName(), ENT_QUOTES)));
					$pCategory = Mage::getModel('catalog/category')->load($category->getParentId());
					$tempXml[] = $xml -> parentcategory(base64_encode(is_null($pCategory->getName()) ? '' : htmlspecialchars($pCategory->getName(), ENT_QUOTES)));
					$tempXml[] = $xml -> sku(base64_encode(is_null($_product->getSku()) ? null : htmlspecialchars($_product->getSku(), ENT_QUOTES)));
					$xmlRow[] = $xml -> product(implode($tempXml));
					
				}
					
			}
		}
		
		header('Content-Type: application/xml; charset=utf-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>'. "\n" . $xml -> urlset($xml->version(self::SWIFT_XML_PRODUCT_VERSION).$xml -> products(implode($xmlRow)), array('xmlns:g' => "http://base.google.com/ns/1.0"));
		die();
	}
}