<?php
class TotalMetrica_PayAfter_Model_Payafter extends Mage_Core_Model_Abstract
{
	protected function _construct(){
		$this->_init("payafter/payafter");
	}

	protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
        	$this->setStatus(0);
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        
        return $this;
    }

    public function insertOrders($orders){

	    $shared_id = md5(uniqid(rand(),true));
        
        $paParams =  unserialize(Mage::getSingleton('core/session')->getPagSeguroParans());

        foreach ($orders as $order){

            if(!isset($paParams[$order->getid()]) || ($paParams[$order->getId()]['reference'] != $order->getIncrementId())){
                
                $payment = $order->getPayment();

                if(!$order->getCustomer()){
                    $custommer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                    $order->setCustomer($custommer);
                }

                $params = Mage::helper('ricardomartins_pagseguro/internal')->getCreditCardApiCallParams($order, $payment);

            }else{

                $params = $paParams[$order->getid()];
            }

            $this->setid(null)
                ->setSharedId($shared_id)
                ->setOrderId($order->getIncrementId())
                ->setParams(serialize($params))
                ->save();

        }
    }
}
