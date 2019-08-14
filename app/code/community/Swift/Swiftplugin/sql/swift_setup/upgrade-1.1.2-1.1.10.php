<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE {$this->getTable('swift_swiftplugin')}
MODIFY COLUMN swift_private_key char(64) NOT NULL
");
$installer->endSetup(); 

?>