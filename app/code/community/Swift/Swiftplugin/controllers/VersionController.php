<?php

/**
 * Description of VersionController
 *
 * @author netready
 */
class Swift_Swiftplugin_VersionController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		echo Mage::helper('swift/Data')->getExtensionInformation();
		die();
	}
	
	
}
