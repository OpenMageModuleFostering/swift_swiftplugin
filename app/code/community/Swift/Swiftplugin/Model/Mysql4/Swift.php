<?php

class Swift_Swiftplugin_Model_Mysql4_Swift extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct() {
         $this->_init('swift/swift', 'swift_id');
     }
}

?>