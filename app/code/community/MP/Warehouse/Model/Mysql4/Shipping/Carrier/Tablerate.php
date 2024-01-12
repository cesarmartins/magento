<?php
/**
 * Mage Plugins, Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mage Plugins Commercial License (MPCL 1.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://mageplugins.net/commercial-license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mageplugins@gmail.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to http://www.mageplugins.net for more information.
 *
 * @category   MP
 * @package    MP_Warehouse
 * @copyright  Copyright (c) 2017-2018 Mage Plugins, Co. and affiliates (https://mageplugins.net/)
 * @license    https://mageplugins.net/commercial-license/ Mage Plugins Commercial License (MPCL 1.0)
 */

/**
 * Shipping carrier table rates resource
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Model_Mysql4_Shipping_Carrier_Tablerate 
    extends Mage_Shipping_Model_Mysql4_Carrier_Tablerate
{
    /**
     * Array of warehouses keyed by identifier
     *
     * @var array
     */
    protected $_importWarehouses;
    /**
     * Get warehouse helper
     *
     * @return  MP_Warehouse_Helper_Data
     */
    protected function getWarehouseHelper()
    {
        return Mage::helper('warehouse');
    }
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    protected function getVersionHelper()
    {
        return $this->getWarehouseHelper()->getVersionHelper();
    }
    /**
     * Load warehouses
     *
     * @return MP_Warehouse_Model_Mysql4_Shipping_Carrier_Tablerate
     */
    protected function _loadWarehouses()
    {
        if (!is_null($this->_importWarehouses) && !is_null($this->_importWarehouses)) {
            return $this;
        }

        $this->_importWarehouses = Mage::getSingleton('warehouse/warehouse')->getCollection()->toOptionHash();
        return $this;
    }
    /**
     * Return table rate array or false by rate request
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * 
     * @return array|false
     */
    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        $helper     = $this->getWarehouseHelper();
        $adapter    = $this->_getReadAdapter();
        $bind = array(
            ':website_id'   => (int) $request->getWebsiteId(), 
            ':country_id'   => $request->getDestCountryId(), 
            ':region_id'    => $request->getDestRegionId(), 
            ':postcode'     => $request->getDestPostcode(), 
            ':warehouse_id' => (int) $request->getWarehouseId(), 
            ':method_id'    => (int) $request->getMethodId(), 
        );
        if ($helper->getVersionHelper()->isGe1800()) {
            $order = array('dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC', 'condition_value DESC');
        } else {
            $order = array('dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC');
        }

        if (is_array($request->getConditionName())) {
            $pieces = array();
            foreach ($request->getConditionName() as $index => $conditionName) {
                array_push($pieces, "WHEN condition_name = '{$conditionName}' THEN '{$index}'");
            }

            array_push($order, '(CASE '.implode(' ', $pieces).' END) ASC');
        }

        array_push($order, 'condition_value DESC');
        array_push($order, 'warehouse_id DESC');
        $where = implode(
            ' AND ', array(
            '(website_id = :website_id)', 
            "(warehouse_id = :warehouse_id OR warehouse_id = '0')", 
            '(method_id = :method_id)'
            )
        );
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where($where)
            ->order($order)
            ->limit(1);
        $orWhere = '(' . implode(
            ') OR (', array(
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode", 
            "dest_country_id = :country_id AND dest_region_id = :region_id AND (dest_zip = '' OR dest_zip = '*')", 
            "dest_country_id = :country_id AND dest_region_id = 0 AND (dest_zip = '' OR dest_zip = '*')", 
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode", 
            "dest_country_id = '0' AND dest_region_id = 0 AND (dest_zip = '' OR dest_zip = '*')", 
            )
        ).')';
        $select->where($orWhere);
        if (is_array($request->getConditionName())) {
            $orWhere     = array();
            $i           = 0;
            foreach ($request->getConditionName() as $conditionName) {
                $bindNameKey          = sprintf(':condition_name_%d', $i);
                $bindValueKey         = sprintf(':condition_value_%d', $i);
                $orWhere[]            = "(condition_name = {$bindNameKey} AND condition_value <= {$bindValueKey})";
                $bind[$bindNameKey]   = $conditionName;
                $bind[$bindValueKey]  = $request->getData($conditionName);
                $i++;
            }

            if ($orWhere) {
                $select->where(implode(' OR ', $orWhere));
            }
        } else {
            $bind[':condition_name']  = $request->getConditionName();
            $bind[':condition_value'] = $request->getData($request->getConditionName());
            $select->where('condition_name = :condition_name');
            $select->where('condition_value <= :condition_value');
        }

        return $adapter->fetchRow($select, $bind);
    }
    /**
     * Upload table rate file and import data from it
     *
     * @param Varien_Object $object
     * @throws Mage_Core_Exception
     * 
     * @return MP_Warehouse_Model_Mysql4_Shipping_Carrier_Tablerate
     */
    public function uploadAndImport(Varien_Object $object)
    {
        if (empty($_FILES['groups']['tmp_name']['tablerate']['fields']['import']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['tablerate']['fields']['import']['value'];
        $website = Mage::app()->getWebsite($object->getScopeId());
        $this->_importWebsiteId     = (int)$website->getId();
        $this->_importUniqueHash    = array();
        $this->_importErrors        = array();
        $this->_importedRows        = 0;
        $io = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');
        $headers = $io->streamReadCsv();
        if ($headers === false || count($headers) < 7) {
            $io->streamClose();
            Mage::throwException(Mage::helper('shipping')->__('Invalid Table Rates File Format'));
        }

        if ($object->getData('groups/tablerate/fields/condition_name/inherit') == '1') {
            $conditionName = (string)Mage::getConfig()->getNode('default/carriers/tablerate/condition_name');
        } else {
            $conditionName = $object->getData('groups/tablerate/fields/condition_name/value');
        }

        $this->_importConditionName = $conditionName;
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            $rowNumber  = 1;
            $importData = array();
            $this->_loadDirectoryCountries();
            $this->_loadDirectoryRegions();
            $this->_loadWarehouses();
            $condition = array('website_id = ?'     => $this->_importWebsiteId, 'condition_name = ?' => $this->_importConditionName, );
            $adapter->delete($this->getMainTable(), $condition);
            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber ++;
                if (empty($csvLine)) continue;
                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) $importData[] = $row;
                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }

            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException(Mage::helper('shipping')->__('An error occurred while import table rates.'));
        }

        $adapter->commit();
        if ($this->_importErrors) {
            if ($this->getVersionHelper()->isGe1700()) {
                $error = Mage::helper('shipping')->__('File has not been imported. See the following list of errors: %s', implode(" \n", $this->_importErrors));
            } else {
                $error = Mage::helper('shipping')->__(
                    '%1$d records have been imported. See the following list of errors for each record that has not been imported: %2$s',
                    $this->_importedRows, implode(" \n", $this->_importErrors)
                );
            }
           
            Mage::throwException($error);
        }

        return $this;
    }
    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * 
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        $helper             = $this->getWarehouseHelper();
        
        if (count($row) < 7) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Table Rates format in the Row #%s', $rowNumber);
            return false;
        }

        $warehouseIndex     = 0;
        $countryIndex       = 1;
        $regionIndex        = 2;
        $zipCodeIndex       = 3;
        $valueIndex         = 4;
        $methodIndex        = 5;
        $priceIndex         = 6;
        // warehouse
        $warehouseId        = trim($row[$warehouseIndex]);
        if (isset($this->_importWarehouses[$warehouseId])) {
            $warehouse = $this->_importWarehouses[$warehouseId];
        } else if ($warehouseId == '*' || $warehouseId == '' || $warehouseId == '0') {
            $warehouseId = '0';
        } else {
            $this->_importErrors[] = $this->getWarehouseHelper()->__(
                'Invalid Warehouse "%s" in the Row #%s.', $warehouseId, $rowNumber
            );
            return false;
        }

        // country
        $country = trim($row[$countryIndex]);
        if (isset($this->_importIso2Countries[$country])) $countryId = $this->_importIso2Countries[$country];
        else if (isset($this->_importIso3Countries[$country])) $countryId = $this->_importIso3Countries[$country];
        else if ($country == '*' || $country == '') $countryId = '0';
        else {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s.', $country, $rowNumber);
            return false;
        }

        // region
        $region = trim($row[$regionIndex]);
        if ($countryId != '0' && isset($this->_importRegions[$countryId][$region])) $regionId = $this->_importRegions[$countryId][$region];
        else if ($region == '*' || $region == '') $regionId = 0;
        else {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s.', $row[$regionIndex], $rowNumber);
            return false;
        }

        // zip code
        $zipCode = trim($row[$zipCodeIndex]);
        if ($zipCode == '*' || $zipCode == '') $zipCode = '';
        else $zipCode = $zipCode;
        // value
        $value = $this->_parseDecimalValue(trim($row[$valueIndex]));
        if ($value === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__(
                'Invalid %s "%s" in the Row #%s.', 
                $this->_getConditionFullName($this->_importConditionName), $row[$valueIndex], $rowNumber
            );
            return false;
        }

        // method
        $methodId = trim($row[$methodIndex]);
        $tablerateMethod = $helper->getShippingTablerateMethodByCode($methodId);
        if (!$tablerateMethod) {
            $tablerateMethod = $helper->getShippingTablerateMethod($methodId);
        }

        if ($tablerateMethod) {
            $methodId = $tablerateMethod->getId();
        } else {
            $this->_importErrors[] = $this->getWarehouseHelper()->__('Invalid Method "%s" in the Row #%s.', $methodId, $rowNumber);
            return false;
        }

        // price
        $price = $this->_parseDecimalValue(trim($row[$priceIndex]));
        if ($price === false) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid Shipping Price "%s" in the Row #%s.', $row[$priceIndex], $rowNumber);
            return false;
        }

        // protect from duplicate
        $hash = sprintf("%d-%s-%d-%s-%F-%d", $warehouseId, $countryId, $regionId, $zipCode, $value, $methodId);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('warehouse')->__(
                'Duplicate Row #%s (Warehouse "%s", Country "%s", Region/State "%s", Zip "%s", Value "%s" and Method "%s").', 
                $rowNumber, 
                $warehouseId, 
                $country, 
                $region, 
                $zipCode, 
                $value, 
                $methodId
            );
            return false;
        }

        $this->_importUniqueHash[$hash] = true;
        return array(
            $this->_importWebsiteId, 
            $countryId, 
            $regionId, 
            $zipCode, 
            $this->_importConditionName, 
            $value, 
            $price, 
            $warehouseId, 
            $methodId, 
        );
    }
    /**
     * Save import data batch
     *
     * @param array $data
     * 
     * @return MP_Warehouse_Model_Mysql4_Shipping_Carrier_Tablerate
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array(
                'website_id', 
                'dest_country_id', 
                'dest_region_id', 
                'dest_zip', 
                'condition_name', 
                'condition_value', 
                'price', 
                'warehouse_id', 
                'method_id', 
            );
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }
}
