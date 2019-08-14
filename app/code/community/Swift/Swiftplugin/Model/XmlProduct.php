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
		$productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(array('product_id','name','description','price','url_path','image','thumbnail','special_price'))->setPageSize($limit);	
		$xmlRow = array();
		for ($i = 1; $i <= $productCollection->getLastPageNumber(); $i++) {
			if ($productCollection->isLoaded()) {
				$productCollection->clear();
				$productCollection->setCurPage($i);
				$productCollection->setPageSize($limit);
			}

			foreach ($productCollection as $product) {
				$tempXml = array();
				$method = 'g:id';
				$tempXml[] = xml::$method($product->getId());
				$tempXml[] = xml::title(htmlspecialchars($product->getName(), ENT_QUOTES));
				$tempXml[] = xml::description(htmlspecialchars($product->getDescription(), ENT_QUOTES));
				$tempXml[] = xml::link($product->getProductUrl());
				$method = 'g:image_link';
				$tempXml[] = xml::$method($product->getImage() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()));
				$method = 'g:additional_image_link';
				$tempXml[] = xml::$method($product->getThumbnail() == 'no_selection' ? '' : Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail()));
				$method = 'g:price';
				$tempXml[] = xml::$method($product->getPrice());
				$method = 'g:sale_price';
				$tempXml[] = xml::$method(is_null($product->getSpecialPrice()) ? '' : $product->getSpecialPrice());
				$categoryId = $product->getCategoryIds();
				$categoryId = array_shift($categoryId);
				$category = Mage::getModel('catalog/category')->load($categoryId);
				$tempXml[] = xml::subcategory(is_null($category->getName()) ? '' : htmlspecialchars($category->getName(), ENT_QUOTES));
				$pCategory = Mage::getModel('catalog/category')->load($category->getParentId());
				$tempXml[] = xml::parentcategory(is_null($pCategory->getName()) ? '' : htmlspecialchars($pCategory->getName(), ENT_QUOTES));
				$xmlRow[] = xml::product(implode("",$tempXml));
			}
		}

		header('Content-Type: application/xml; charset=utf-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>'. "\n" . xml::urlset(xml::products(implode('',$xmlRow)), array('xmlns:g' => "http://base.google.com/ns/1.0"));
		die();
	}
}

?>