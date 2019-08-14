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
		$productCollection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect(array('product_id','name','description', 'short_description','price','url_path','image','thumbnail', 'small_image','special_price','sku','special_to_date', 'special_from_date'))
		->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
		->setCurPage($this->offset)
		->setPageSize($this->limit);
		
		$xmlRow = array();
		$xml = new xml();
		
		$today = time();
		
		if ($this->offset <= $productCollection->getLastPageNumber()) {
		
			foreach ($productCollection as $product) {
					
				$stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct( $product->getId() );
					
				$qty = $stock_item->getData('qty');
				$manageStock = $stock_item->getData('manage_stock');
				$inStock = $stock_item->getData('is_in_stock');
					
				if(!($manageStock == 1 && ($qty<1 || $inStock == 0))) {
					
					
					$tempXml = array();
					$method = 'g:id';
					$tempXml[] = $xml -> $method(base64_encode($product->getId()));
					$tempXml[] = $xml -> title(base64_encode(htmlspecialchars($product->getName(), ENT_QUOTES)));
					$tempXml[] = $xml -> description(base64_encode(htmlspecialchars($product->getDescription(), ENT_QUOTES)));
					$tempXml[] = $xml -> short_description(base64_encode(htmlspecialchars($product->getShortDescription(), ENT_QUOTES)));
						
					$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
						
					if (!empty($parentIds)) {
							
						foreach($parentIds as $parentId) {
							$groupProduct = Mage::getModel('catalog/product')->load($parentId);
							$groupPath = $groupProduct->getProductUrl();
							$tempXml[] = $xml -> link(base64_encode($groupPath));
							break;
						}
							
					}
					else {
						$tempXml[] = $xml -> link(base64_encode($product->getProductUrl()));
					}
						
					$method = 'g:image_link';
					$tempXml[] = $xml -> $method(base64_encode($product->getImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage())));
					$method = 'g:small_image_link';
					$tempXml[] = $xml -> $method(base64_encode($product->getSmallImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getSmallImage())));
					$method = 'g:additional_image_link';
					$tempXml[] = $xml -> $method(base64_encode($product->getThumbnail() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail())));
					$method = 'g:price';
					$tempXml[] = $xml -> $method(base64_encode($product->getPrice()));
					$method = 'g:sale_price';
						
					$special_price = '';
						
					if (!is_null($product->getSpecialPrice())) {
							
						if($today >= strtotime($product->getSpecialFromDate()) && $today <= strtotime($product->getSpecialToDate()) || $today >= strtotime($product->getSpecialFromDate()) && is_null($product->getSpecialToDate())) {
							$special_price = $product->getSpecialPrice();
						}
							
					}
						
					$tempXml[] = $xml -> $method(base64_encode($special_price));
					$categoryId = $product->getCategoryIds();
					$categoryId = array_shift($categoryId);
					$category = Mage::getModel('catalog/category')->load($categoryId);
					$tempXml[] = $xml -> subcategory(base64_encode(is_null($category->getName()) ? '' : htmlspecialchars($category->getName(), ENT_QUOTES)));
					$pCategory = Mage::getModel('catalog/category')->load($category->getParentId());
					$tempXml[] = $xml -> parentcategory(base64_encode(is_null($pCategory->getName()) ? '' : htmlspecialchars($pCategory->getName(), ENT_QUOTES)));
					$tempXml[] = $xml -> sku(base64_encode(is_null($product->getSku()) ? null : htmlspecialchars($product->getSku(), ENT_QUOTES)));
					$xmlRow[] = $xml -> product(implode($tempXml));
					
				}
					
			}
		}
		
		header('Content-Type: application/xml; charset=utf-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>'. "\n" . $xml -> urlset($xml->version(self::SWIFT_XML_PRODUCT_VERSION).$xml -> products(implode($xmlRow)), array('xmlns:g' => "http://base.google.com/ns/1.0"));
		die();
	}
}

?>