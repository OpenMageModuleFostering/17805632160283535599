<?php
class Iksula_Orderemail_Model_Mysql4_Codorders extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("orderemail/codorders", "id");
    }
}