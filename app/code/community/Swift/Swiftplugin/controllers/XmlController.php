<?php

class Swift_Swiftplugin_XmlController extends Mage_Core_Controller_Front_Action {

	public function feedAction() {
		$obj = new Swift_Swiftplugin_Model_XmlProduct();
		$obj->generate_xml();
	}
}

?>