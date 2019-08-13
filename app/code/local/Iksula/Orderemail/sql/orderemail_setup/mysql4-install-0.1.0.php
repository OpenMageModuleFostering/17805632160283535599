<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table cod_orders(id int not null auto_increment, increment_id varchar(50),customer_id int(11),expire_date date,primary key(id));
		
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 