<?php

class TotalMetrica_PayAfter_Block_Adminhtml_Payafter_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId("payafterGrid");
		$this->setDefaultSort("entity_id");
		$this->setDefaultDir("DESC");
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel("payafter/payafter")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareColumns()
	{
		$this->addColumn("entity_id", array(
			"header" => Mage::helper("payafter")->__("ID"),
			"align" =>"right",
			"width" => "50px",
			"type" => "number",
			"index" => "entity_id",
		));

		$this->addColumn("order_id", array(
			"header" => Mage::helper("payafter")->__("Order"),
			"index" => "order_id",
		));

		$this->addColumn("shared_id", array(
			"header" => Mage::helper("payafter")->__("Shared Id"),
			"index" => "shared_id",
		));
		
		$this->addColumn('status', array(
			'header' => Mage::helper('payafter')->__('status'),
			'index' => 'status',
			'type' => 'options',
			'options'=>TotalMetrica_PayAfter_Block_Adminhtml_Payafter_Grid::getOptionStatus(),
		));

		$this->addColumn("transation_code", array(
			"header" => Mage::helper("payafter")->__("Transation Code"),
			"index" => "transation_code",
		));

		// $this->addColumn("params", array(
		// 	"header" => Mage::helper("payafter")->__("Params"),
		// 	"index" => "params",
		// ));

		$this->addColumn('created_at', array(
			'header'    => Mage::helper('payafter')->__('Created At'),
			'index'     => 'created_at',
			'type'      => 'datetime',
		));

		$this->addColumn('updated_at', array(
			'header'    => Mage::helper('payafter')->__('Created At'),
			'index'     => 'created_at',
			'type'      => 'datetime',
		));
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return '#';
	}

	static public function getOptionStatus()
	{
		$data_array=array(); 
		$data_array[0]='Pending';
		$data_array[1]='Complete';
		$data_array[2]='Processing';
		$data_array[3]='Error';
		return($data_array);
	}
	static public function getValueStatus()
	{
		$data_array=array();
		foreach(TotalMetrica_PayAfter_Block_Adminhtml_Payafter_Grid::getOptionStatus() as $k=>$v){
			$data_array[]=array('value'=>$k,'label'=>$v);
		}
		return($data_array);

	}


}