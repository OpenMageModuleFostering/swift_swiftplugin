<?php

class Swift_Swiftplugin_Model_Mysql4_Swiftorders_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct()
    {
            $this->_init('swift/swiftorders');
    }
}

?>