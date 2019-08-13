<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
Alter table cod_orders MODIFY COLUMN expire_date DATETIME;
Alter table cod_orders ADD COLUMN expirekey text;
		
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 