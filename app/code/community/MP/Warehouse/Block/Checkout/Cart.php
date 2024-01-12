<?php
class MP_Warehouse_Block_Checkout_Cart extends Mage_Checkout_Block_Cart
{

    private $_docaItens;
    private $_rates2;
    private $_shipping;

    public function __construct()
    {
    	//$this->_shipping = Mage::getBlockSingleton('checkout/cart_shipping');
    	parent::__construct();
    }

    private function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }

    private function getProductHelper()
    {
        return $this->getWarehouseHelper()->getProductHelper();
    }


    private function getShippingBlock()
    {
        return Mage::getBlockSingleton('checkout/cart_shipping');
    }

     /**
     * Retrieve shipping price for current address and rate
     *
     * @param decimal $price
     * @param boolean $flag show include tax price flag
     * @return string
     */
    public function getShippingPrice2($stockId, $price, $flag)
    {
        //return $this->_shipping->getShippingPrice2($stockId, $price, $flag);
    }

    public function getItensDocas()
    {
        if (!$this->_docaItens) {
           
            foreach ($this->getItems() as $item) {
                $product  = $item->getProduct();
                $stockIds = $this->getProductHelper()->getInStockStockIds($product);
                foreach ($stockIds as $stockId) {
                    if (isset($this->_docaItens[$stockId])) {
                        $this->_docaItens[$stockId]['itens'][$item->getId()] = $item;
                        continue;
                    }
                    $this->_docaItens[$stockId] = [
                        'warehouse'      => $this->getWarehouseHelper()->getWarehouseByStockId($stockId),
                       // 'rates'          => $this->_shipping->getShippingRates2($stockId),
                        //'shippingMethod' => $this->_shipping->getAddressShippingMethod2($stockId),
                        'itens'          => [
                            $item->getId() => $item,
                        ],
                    ];
                }
            }
        }
        return $this->_docaItens;
    }

    public function getItemHtml(Mage_Sales_Model_Quote_Item $item, $warehouse = null)
    {
        $renderer = $this->getItemRenderer($item->getProductType())
            ->setItem($item)
            ->setWarehouse($warehouse);
        return $renderer->toHtml();
    }


     public function getShippingHtml($stockId)
    {
        $renderer = $this->getItemRenderer('shipping')
            ->setStockId($stockId);
        return $renderer->toHtml();
    }

    /**
     * Return customer quote items
     *
     * @return array
     */
    public function getItems()
    {
        if ($this->getCustomItems()) {
            return $this->getCustomItems();
        }

        return parent::getItems();
    }

}
