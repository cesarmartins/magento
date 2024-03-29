<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class Codazon_Flexibletheme_Block_Adminhtml_Content extends Mage_Adminhtml_Block_Widget_Grid_Container
{	
	public function __construct()
	{
		$helper = Mage::helper("flexibletheme");
		$this->_blockGroup = "flexibletheme";
		$this->_controller = "adminhtml_content";
		$this->_headerText = $helper->__("Manage Main Contents");
		$this->_addButtonLabel = $helper->__("Add New Item");
		parent::__construct();
	}
}