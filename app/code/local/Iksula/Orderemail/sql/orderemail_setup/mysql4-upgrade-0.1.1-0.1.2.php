<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute(
        'order',
        'is_specialorder',
        array(
            'type'=>'int',
            'default'=>0,
            'grid' => true,
            'unsigned'=>true,
          )
        );

$installer->endSetup();
	 