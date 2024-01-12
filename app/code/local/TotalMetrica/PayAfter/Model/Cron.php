<?php
class TotalMetrica_PayAfter_Model_Cron{
    private $_x;
	public function ProcessOrders(){
		$allPendingPayments = Mage::getModel("payafter/payafter")->getCollection()
						->addFieldToFilter('status',array('in'=>array('0')));

		$groupedOrders = [];

		foreach ($allPendingPayments as $pendingPyment) {
			$gpOrder = [];

			if(isset($groupedOrders[$pendingPyment->getSharedId()])){
				$gpOrder = $groupedOrders[$pendingPyment->getSharedId()];
			}else{
				$gpOrder = ['total'=>0,'params'=>'','orders'=>[],'pa_ids'=>[]];
			}
			$thisOrder = Mage::getModel('sales/order')->loadByIncrementId($pendingPyment->getOrderId());

			$gpOrder['orders'][] = $thisOrder;

			$gpOrder['pa'][] = $pendingPyment;

			if(!$gpOrder['params'] && $pendingPyment->getParams())
				$gpOrder['params'] = $pendingPyment->getParams();

			$gpOrder['total'] += $thisOrder->getGrandTotal();

			$groupedOrders[$pendingPyment->getSharedId()] = $gpOrder;

			$pendingPyment->setStatus(2);
            $pendingPyment->save();
		}

		foreach ($groupedOrders as $orders) {

			$oldparams = unserialize($orders['params']);

			$params = $this->copyParams($oldparams);

			$itens = [];
			$this->_x = 1;

			//Last Payment
			$payments = [];

			//Merge Params Values
			foreach ($orders['orders'] as $order) {
				/** @var RicardoMartins_PagSeguro_Helper_Params $pHelper */
        		$pHelper = Mage::helper('ricardomartins_pagseguro/params');

				$extraAmount = $pHelper->getExtraAmount($order);

				$params['reference'] = $order->getIncrementId();


				$shipping = $pHelper->getAddressParams($order, 'shipping');

				$params['shippingCost'] += $shipping['shippingCost'];

				if(isset($shipping['extraAmount'])){
					$params['extraAmount'] += $shipping['extraAmount'];
				}else{
					$params['extraAmount'] += $extraAmount;
				}


				$payments[] = $order->getPayment();

				$itens = array_merge($itens, $this->getItemsParams($order));

			}
            $params['extraAmount'] = number_format($params['extraAmount'], 2, '.', '');
            $params['shippingCost'] = number_format($params['shippingCost'], 2, '.', '');
            $params['installmentValue'] = number_format($params['installmentValue'], 2, '.', '');

			//Products Data
			$params = array_merge($params, $itens);

			//call API


       		$returnXml = Mage::getModel('ricardomartins_pagseguro/payment_cc')->callApi($params, $payments[0]);

       		foreach ($payments as $payment) {
       			$order 		 = $payment->getOrder();
       			$incrementId = $order->getIncrementId();
       			$error = false;
       			$resultXMLCopy = $returnXml;

       			if (isset($returnXml->reference)){
       				$resultXMLCopy->reference = $incrementId;
       			}
       			try {
                    $pagseguro = Mage::getModel('ricardomartins_pagseguro/payment_cc');
		            $pagseguro->proccessNotificatonResult($resultXMLCopy);
                    Mage::unregister('sales_order_invoice_save_after_event_triggered');

		        } catch (Mage_Core_Exception $e) {
		            $error = true;
		        }

		        $payment->setSkipOrderProcessing(true);

		        if (isset($returnXml->code)) {
		            $additional = array('transaction_id'=>(string)$returnXml->code);
		            if ($existing = $payment->getAdditionalInformation()) {
		                if (is_array($existing)) {
		                    $additional = array_merge($additional, $existing);
		                }
		            }
		            $payment->setAdditionalInformation($additional);
		            $payment->save();
		        }else{
		            $error = true;
		        }
       		}

       		foreach ($orders['pa'] as $payAfter) {
       			if (isset($returnXml->code)) {
       				$payAfter->setTransationCode((string)$returnXml->code);
       			}
       			if($error){
       				$payAfter->setStatus(3);
       			}else{
       				$payAfter->setStatus(1);
       			}
       			$payAfter->setResponse(json_encode($returnXml));
       			$payAfter->save();
       		}

		}
	}

	private function getItemsParams(Mage_Sales_Model_Order $order)
    {
        $return = array();
        $items = $order->getAllVisibleItems();
        if ($items) {
            $itemsCount = count($items);
            for ($y=0; ($y+1) <= $itemsCount; $y++) {
                $x = $this->_x;
                $itemPrice = $items[$y]->getPrice();
                $qtyOrdered = $items[$y]->getQtyOrdered();
                $return['itemId'.$x] = $items[$y]->getId();
                $return['itemDescription'.$x] = substr($items[$y]->getName(), 0, 100);
                $return['itemAmount'.$x] = number_format($itemPrice, 2, '.', '');
                $return['itemQuantity'.$x] = (int)$qtyOrdered;

                if ($items[$y]->getIsQtyDecimal()) {
                    $txtUnDesc = ' (' . $items[$y]->getQtyOrdered() . ' un.)';
                    $return['itemDescription'.$x] = substr($items[$y]->getName(), 0, 100-strlen($txtUnDesc));
                    $return['itemDescription'.$x] .= $txtUnDesc;
                    $itemPrice = $items[$y]->getRowTotalInclTax();
                    $return['itemAmount'.$x] = number_format($itemPrice, 2, '.', '');
                    $return['itemQuantity'.$x] = 1;
                }

                //We can't send 0.00 as value to PagSeguro. Will be discounted on extraAmount.
                if ($itemPrice == 0) {
                    $return['itemAmount'.$x] = 0.01;
                }
                $this->_x++;
            }
        }

        return $return;
    }

    private function copyParams($oldparams){
    	$return = array(
	            'email'             => isset($oldparams['email'])?$oldparams['email']:'',
	            'token'             => isset($oldparams['token'])?$oldparams['token']:'',
	            'paymentMode'       => 'default',
	            'paymentMethod'     => 'creditCard',
	            'receiverEmail'     =>  isset($oldparams['receiverEmail'])?$oldparams['receiverEmail']:'',
	            'currency'          => 'BRL',
	            'extraAmount'       => 0.00,
	            'creditCardToken'   => isset($oldparams['creditCardToken'])?$oldparams['creditCardToken']:'',
	            'notificationURL'   => isset($oldparams['notificationURL'])?$oldparams['notificationURL']:'',

	            //Sender Data
	            'senderName'    	=> isset($oldparams['senderName'])?$oldparams['senderName']:'',
	            'senderEmail'   	=> isset($oldparams['senderEmail'])?$oldparams['senderEmail']:'',
	            'senderHash'    	=> isset($oldparams['senderHash'])?$oldparams['senderHash']:'',
	            'senderCPF'     	=> isset($oldparams['senderCPF'])?$oldparams['senderCPF']:'',
	            'senderAreaCode'	=> isset($oldparams['senderAreaCode'])?$oldparams['senderAreaCode']:'',
	            'senderPhone'   	=> isset($oldparams['senderPhone'])?$oldparams['senderPhone']:'',
	            'isSandbox'     	=> isset($oldparams['isSandbox'])?$oldparams['isSandbox']:'',
	            'senderIp'     		=> isset($oldparams['senderIp'])?$oldparams['senderIp']:'',

	            //Shipping Data
	            'shippingAddressStreet'     => isset($oldparams['shippingAddressStreet'])?$oldparams['shippingAddressStreet']:'',
	            'shippingAddressNumber'     => isset($oldparams['shippingAddressNumber'])?$oldparams['shippingAddressNumber']:'',
	            'shippingAddressComplement' => isset($oldparams['shippingAddressComplement'])?$oldparams['shippingAddressComplement']:'',
	            'shippingAddressDistrict'   => isset($oldparams['shippingAddressDistrict'])?$oldparams['shippingAddressDistrict']:'',
	            'shippingAddressPostalCode' => isset($oldparams['shippingAddressPostalCode'])?$oldparams['shippingAddressPostalCode']:'',
	            'shippingAddressCity'       => isset($oldparams['shippingAddressCity'])?$oldparams['shippingAddressCity']:'',
	            'shippingAddressState'      => isset($oldparams['shippingAddressState'])?$oldparams['shippingAddressState']:'',
	            'shippingAddressCountry'    => isset($oldparams['shippingAddressCountry'])?$oldparams['shippingAddressCountry']:'',
	            'shippingType'				=> isset($oldparams['shippingType'])?$oldparams['shippingType']:'',
	            'shippingCost'				=> 0.00,

	            //Billing Data
	            'billingAddressStreet'    	=> isset($oldparams['billingAddressStreet'])?$oldparams['billingAddressStreet']:'',
	            'billingAddressNumber'    	=> isset($oldparams['billingAddressNumber'])?$oldparams['billingAddressNumber']:'',
	            'billingAddressComplement'	=> isset($oldparams['billingAddressComplement'])?$oldparams['billingAddressComplement']:'',
	            'billingAddressDistrict'  	=> isset($oldparams['billingAddressDistrict'])?$oldparams['billingAddressDistrict']:'',
	            'billingAddressPostalCode'	=> isset($oldparams['billingAddressPostalCode'])?$oldparams['billingAddressPostalCode']:'',
	            'billingAddressCity'      	=> isset($oldparams['billingAddressCity'])?$oldparams['billingAddressCity']:'',
	            'billingAddressState'     	=> isset($oldparams['billingAddressState'])?$oldparams['billingAddressState']:'',
	            'billingAddressCountry'   	=> isset($oldparams['billingAddressCountry'])?$oldparams['billingAddressCountry']:'',

	            //Credcard Data
	            'creditCardHolderName'      => isset($oldparams['creditCardHolderName'])?$oldparams['creditCardHolderName']:'',
	            'creditCardHolderBirthDate' => isset($oldparams['creditCardHolderBirthDate'])?$oldparams['creditCardHolderBirthDate']:'',
	            'creditCardHolderCPF'       => isset($oldparams['creditCardHolderCPF'])?$oldparams['creditCardHolderCPF']:'',
	            'creditCardHolderAreaCode'  => isset($oldparams['creditCardHolderAreaCode'])?$oldparams['creditCardHolderAreaCode']:'',
	            'creditCardHolderPhone'     => isset($oldparams['creditCardHolderPhone'])?$oldparams['creditCardHolderPhone']:'',

	            //Installment Data
	            'installmentQuantity'   => isset($oldparams['installmentQuantity'])?$oldparams['installmentQuantity']:'',
                'installmentValue'      => isset($oldparams['installmentValue'])?$oldparams['installmentValue']:'',
	        
        	);
    	if(!isset($return['senderIp'])|| !$return['senderIp'])unset($return['senderIp']);

    	return $return;
    }

}