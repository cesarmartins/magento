<?php
 
class Seaway_Tree_Model_Tree_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('tree/tree');
	}
}