<?php
class TotalMetrica_PayAfter_Block_Adminhtml_Payafter extends Mage_Adminhtml_Block_Widget_Grid_Container{
	public function __construct()
	{
		$this->_controller = "adminhtml_payafter";
		$this->_blockGroup = "payafter";
		$this->_headerText = Mage::helper("payafter")->__("Payafter Manager");
		//$this->_addButtonLabel = Mage::helper("payafter")->__("Add New Item");
		parent::__construct();
		$this->_removeButton('add');
	}

}