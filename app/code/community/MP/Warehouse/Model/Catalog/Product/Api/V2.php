<?php
class MP_Warehouse_Model_Catalog_Product_Api_V2 extends Mage_Catalog_Model_Product_Api_V2
{

    protected function _prepareDataForSave ($product, $productData)
    {
    	$newProductData = $productData;

        if (property_exists($productData, 'additional_attributes')) {           
            if (property_exists($productData->additional_attributes, 'multi_data')) {
                foreach ($productData->additional_attributes->multi_data as $pKet => $_attribute) {
                	$attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $_attribute->key);
                    if($attribute->getFrontendInput() != 'multiselect'){
	                	$pAttribute = $_attribute;
	                    $pAttribute->value = $_attribute->value[0];
            			if (!property_exists($productData->additional_attributes, 'single_data')) {
	                    	$newProductData->additional_attributes->single_data = [];
	                    }
	                    $newProductData->additional_attributes->single_data[] = $pAttribute;
	                    unset($newProductData->additional_attributes->multi_data[$pKet]);
                    }
                }
            }
       }

       if (property_exists($newProductData, 'description') && property_exists($newProductData, 'name')) {      
            $newProductData->name = $newProductData->description;
       }

       if (property_exists($newProductData, 'price') && 
            (!property_exists($newProductData, 'special_price') || 
            $newProductData->special_price <= 0
            )
        ) {      
            $newProductData->special_price = false;
            //$this->_fault('333');
       }

       
       return parent::_prepareDataForSave($product, $newProductData);
   }
}