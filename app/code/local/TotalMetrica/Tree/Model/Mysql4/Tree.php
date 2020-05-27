<?php
 
class Seaway_Tree_Model_Mysql4_Tree extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('tree/tree', 'id');
	}
}