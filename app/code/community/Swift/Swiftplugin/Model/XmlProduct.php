<?php

require_once(Mage::getBaseDir('lib') . '/libXML/xml.php');

/**
 * When a request is made to generate to fetch existing products from the database the response is xml
 *
 */
class Swift_Swiftplugin_Model_XmlProduct {

	/**
	 * Retrieves product details and generates the appropriate response to the request
	 *
	 */
	public function generate_xml() {
		//limit the data parsed
		$limit = 100;
		$productCollection = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect(array('product_id','name','description', 'short_description','price','url_path','image','thumbnail', 'small_image','special_price','sku','special_to_date', 'special_from_date'))
		->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))
		->setPageSize($limit);
		
		$xmlRow = array();
		
		$xml = new xml();
		
		for ($i = 1; $i <= $productCollection->getLastPageNumber(); $i++) {
			if ($productCollection->isLoaded()) {
				$productCollection->clear();
				$productCollection->setCurPage($i);
				$productCollection->setPageSize($limit);
			}

			foreach ($productCollection as $product) {
				
				$stock_item = Mage::getModel('cataloginventory/stock_item')->loadByProduct( $product->getId() );
				
				$qty = $stock_item->getData('qty');
				$manageStock = $stock_item->getData('manage_stock');
				$inStock = $stock_item->getData('is_in_stock');
				
				if(!($manageStock == 1 && ($qty<1 || $inStock == 0))) {
				
				
					$tempXml = array();
					$method = 'g:id';
					$tempXml[] = $xml -> $method($product->getId());
					$tempXml[] = $xml -> title(htmlspecialchars($product->getName(), ENT_QUOTES));
					$tempXml[] = $xml -> description(htmlspecialchars($product->getDescription(), ENT_QUOTES));
					$tempXml[] = $xml -> short_description(htmlspecialchars($product->getShortDescription(), ENT_QUOTES));
					
					$parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
					
					if (!empty($parentIds)) {
						
						foreach($parentIds as $parentId) {
							$groupProduct = Mage::getModel('catalog/product')->load($parentId);
							$groupPath = $groupProduct->getProductUrl();
							$tempXml[] = $xml -> link($groupPath);
							break;
						}
						
					}
					else {
						$tempXml[] = $xml -> link($product->getProductUrl());
					}
					
					$method = 'g:image_link';
					$tempXml[] = $xml -> $method($product->getImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()));
					$method = 'g:small_image_link';
					$tempXml[] = $xml -> $method($product->getSmallImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getSmallImage()));
					$method = 'g:additional_image_link';
					$tempXml[] = $xml -> $method($product->getThumbnail() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail()));
					$method = 'g:price';
					$tempXml[] = $xml -> $method($product->getPrice());
					$method = 'g:sale_price';
					
					$special_price = '';
					
					if (!is_null($product->getSpecialPrice())) {
						
						if($today >= strtotime($product->getSpecialFromDate()) && $today <= strtotime($product->getSpecialToDate()) || $today >= strtotime($product->getSpecialFromDate()) && is_null($product->getSpecialToDate())) {
							$special_price = $product->getSpecialPrice();
						}
						
					}
					
					$tempXml[] = $xml -> $method($special_price);
					$categoryId = $product->getCategoryIds();
					$categoryId = array_shift($categoryId);
					$category = Mage::getModel('catalog/category')->load($categoryId);
					$tempXml[] = $xml -> subcategory(is_null($category->getName()) ? '' : htmlspecialchars($category->getName(), ENT_QUOTES));
					$pCategory = Mage::getModel('catalog/category')->load($category->getParentId());
					$tempXml[] = $xml -> parentcategory(is_null($pCategory->getName()) ? '' : htmlspecialchars($pCategory->getName(), ENT_QUOTES));
					$tempXml[] = $xml -> sku(is_null($product->getSku()) ? null : htmlspecialchars($product->getSku(), ENT_QUOTES));
					$xmlRow[] = $xml -> product(implode("",$tempXml));
					
				}
				
			}
		}

		header('Content-Type: application/xml; charset=utf-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>'. "\n" . $xml -> urlset($xml -> products(implode('',$xmlRow)), array('xmlns:g' => "http://base.google.com/ns/1.0"));
		die();
	}
}

?>