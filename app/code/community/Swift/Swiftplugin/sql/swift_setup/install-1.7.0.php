<?php

$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE {$this->getTable('swift_orders')} (
  entity_id int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  swift_order_id int(10) unsigned NOT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE {$this->getTable('swift_swiftplugin')} (
  swift_id int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  swift_private_key char(64) NOT NULL,
  swift_send_history TINYINT(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup(); 

?>