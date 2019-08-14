<?php

class Swift_Swiftplugin_Model_Swift extends Mage_Core_Model_Abstract {

	public function __construct() {
		 parent::__construct();
         $this->_init('swift/swift');
	}
}

?>