<?php

class TotalMetrica_PayAfter_Adminhtml_PayafterController extends Mage_Adminhtml_Controller_Action
{
	protected function _isAllowed()
	{
		//return Mage::getSingleton('admin/session')->isAllowed('payafter/payafter');
		return true;
	}

	protected function _initAction()
	{
		$this->loadLayout()
		->_setActiveMenu("payafter/payafter")
		->_addBreadcrumb(
			Mage::helper("adminhtml")->__("Payafter  Manager"),
			Mage::helper("adminhtml")->__("Payafter Manager")
		);
		
		return $this;
	}
	public function indexAction()
	{
		$this->_title($this->__("PayAfter"));
		$this->_title($this->__("Manager Payafter"));

		$this->_initAction();
		$this->renderLayout();
	}

	public function editAction(){}
	public function newAction(){}
	public function saveAction(){}
	public function deleteAction(){}
}
