<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table tm_pay_after(
	entity_id int not null auto_increment,
	order_id int, 
	shared_id varchar(100), 
	status int, 
	transation_code varchar(100), 
	params text, 
	created_at varchar(100), 
	updated_at varchar(100), 
	primary key(entity_id)
);	
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
