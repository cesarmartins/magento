<?php

class CesarMartins_OrderHistory_Helper_Data extends
    Mage_Core_Helper_Abstract
{
    const XML_EXPRESS_MAX_WEIGHT = 'carriers/melhorLoja_shipping/express_max_weight';

    /**
     * Get max weight of single item for express shipping
     *
     * @return mixed
     */
    public function getOrderHistory($id)
    {

        $resource = Mage::getSingleton('core/resource')->getConnection('core_write');

        $sql = "select * from sales_flat_order_status_history where parent_id = " . $id;
        $fetchAll = $resource->fetchAll($sql);
        return $fetchAll;
    }
}