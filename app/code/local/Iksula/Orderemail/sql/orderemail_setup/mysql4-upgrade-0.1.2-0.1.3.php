<?php
$installer = $this;

//instantiate required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

// insert values
$installer->getConnection()->insertArray(
        $statusTable,
        array('status','label'),
        array(array('status'=> "cod",'label'=>"Cash On Delivery"))
        );

// Insert state abd mapping of statuses to state
$installer->getConnection()->insertArray(
        $statusStateTable,
        array('status','state','is_default'),
        array(array('status'=>'cod','state'=>'new','is_default'=> 0 ))
        );

