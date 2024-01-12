<?php
class TotalMetrica_PayAfter_Model_Mysql4_Payafter extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init("payafter/payafter", "entity_id");
	}
}