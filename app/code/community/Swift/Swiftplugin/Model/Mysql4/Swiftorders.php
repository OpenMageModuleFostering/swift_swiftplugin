<?php

class Swift_Swiftplugin_Model_Mysql4_Swiftorders extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct()
    {
        $this->_init('swift/swiftorders', 'entity_id');
    }
}

?>