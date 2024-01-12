<?php

class MelhorLoja_Shipping_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'melhorLoja_shipping';

    /**
     * Returns available shipping rates for MelhorLoja Shipping carrier
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        /** @var MelhorLoja_Shipping_Helper_Data $expressMaxProducts */
        $expressMaxWeight = Mage::helper('melhorLoja_shipping')->getExpressMaxWeight();

        $preco = Mage::getStoreConfig('carriers/melhorLoja_shipping/preco_entrega_valor');
        $apartirDe = Mage::getStoreConfig('carriers/melhorLoja_shipping/preco_entrega_apartir_de');

        $expressAvailable = true;
        foreach ($request->getAllItems() as $item) {
            $valorProduto += $item->getPrice();
        }

        if($valorProduto >= $apartirDe){
            $preco = '0.0';
        }

        $faixaCep = Mage::getStoreConfig('carriers/melhorLoja_shipping/faixa_cep_inicio');
        $variosCep = explode(";",$faixaCep);

        $postcode = str_replace('-','',$request->getDestPostcode());

        //$mostrar = false;
        foreach ($variosCep as $cep){
            if(!empty($cep)){
                $dest = $this->trataCepValor($cep);
                if (($postcode >= $dest["cep"][0] && $postcode <= $dest["cep"][1])) {
                    //$mostrar = true;
                    if ($expressAvailable) {
                        $result->append($this->_getExpressRate($request, $dest["valor"]));
                    }
                }
            }
        }


        //$result->append($this->_getStandardRate());

        return $result;
    }

    public function trataCepValor($cepValor){
        $dest = explode(",",$cepValor);
        $valor = explode("=",$dest[1]);
        $returnArray["cep"][0] = $dest[0];
        $returnArray["cep"][1] = $valor[0];
        $returnArray["valor"] = $valor[1] . "," . $dest[2];
        return $returnArray;
    }

    /**
     * Returns Allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'standard'    =>  'Standard delivery',
            'express'     =>  'Express delivery',
        );
    }

    /**
     * Get Standard rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('large');
        $rate->setMethodTitle('Standard delivery');
        $rate->setPrice(1.23);
        $rate->setCost(0);

        return $rate;
    }

    /**
     * Get Express rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getExpressRate($request, $valor)
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('melhorLoja_express');
        $rate->setMethodTitle('De segunda a sexta, das 9h às 18h');
        //$rate->setPrice($dados->valor);
        $rate->setPrice($valor);
        $rate->setCost(0);

        return $rate;
    }

}