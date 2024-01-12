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
 * Model helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Model
    extends MP_Warehouse_Helper_Core_Abstract
{
    /**
     * Get core helper
     * 
     * @return MP_Warehouse_Helper_Core_Data
     */
    public function getCoreHelper()
    {
        return Mage::helper('warehouse/core_data');
    }
    /**
     * Get version helper
     * 
     * @return MP_Warehouse_Helper_Core_Version
     */
    public function getVersionHelper()
    {
        return $this->getCoreHelper()->getVersionHelper();
    }
    /**
     * Cast value
     * 
     * @param mixed $value
     * @param string $type
     * 
     * @return mixed
     */
    public function castValue($value, $type)
    {
        switch ($type) {
            case 'int': 
                $value = (int) $value;
                break;
            case 'float': 
                $value = (float) $value;
                break;
            case 'string': 
                $value = (string) $value;
                break;
            case 'array': 
                $value = (is_array($value)) ? $value : array($value);
                break;
            default: 
                $value = (string) $value;
                break;
        }

        return $value;
    }
    /**
     * Save child data
     * 
     * @param Mage_Core_Model_Abstract $model
     * @param string $modelClass
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function saveChildData(
        $model, 
        $modelClass, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        if (!$model || !($model instanceof $modelClass)) {
            return $this;
        }

        $modelId        = $model->getId();
        $resource       = $model->getResource();
        $dataTable      = $resource->getTable($dataTableName);
        $adapter        = $resource->getWriteConnection();
        $_data          = $model->getData($dataAttributeCode);
        if (!$_data) {
            $_data = array();
        }

        $data           = array();
        $oldData        = array();
        foreach ($_data as $value) {
            $value = $this->castValue($value, $dataValueType);
            if (($dataValueType == 'string') && !$value) {
                continue;
            }

            $data[$value] = array(
                $modelIdAttributeCode   => $modelId, 
                $dataValueAttributeCode => $value, 
            );
        }

        $select = $adapter
            ->select()
            ->from($dataTable)
            ->where($modelIdAttributeCode.' = ?', $modelId);
        $query = $adapter->query($select);
        while ($item = $query->fetch()) {
            $value = $item[$dataValueAttributeCode];
            $oldData[$value] = $item;
        }

        foreach ($oldData as $value => $item) {
            if (!isset($data[$value])) {
                $adapter->delete(
                    $dataTable, array(
                    $adapter->quoteInto($modelIdAttributeCode.' = ?', $modelId), 
                    $adapter->quoteInto($dataValueAttributeCode.' = ?', $value)
                    )
                );
            }
        }

        foreach ($data as $value => $item) {
            if (!isset($oldData[$value])) {
                $adapter->insert($dataTable, $item);
            }
        }

        return $this;
    }
    /**
     * Save child data
     * 
     * @param Mage_Core_Model_Abstract $model
     * @param string $modelClass
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function saveChildData2(
        $model, 
        $modelClass, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        if (!$model || !($model instanceof $modelClass)) {
            return $this;
        }

        $modelId        = $model->getId();
        $resource       = $model->getResource();
        $dataTable      = $resource->getTable($dataTableName);
        $adapter        = $resource->getWriteConnection();
        $_data          = $model->getData($dataAttributeCode);
        if (!$_data) {
            $_data = array();
        }

        if (count($_data)) {
            $data           = array();
            $oldData        = array();
            foreach ($_data as $item) {
                if (isset($item[$dataKeyAttributeCode]) && isset($item[$dataValueAttributeCode])) {
                    $key        = $item[$dataKeyAttributeCode];
                    $value      = $this->castValue($item[$dataValueAttributeCode], $dataValueType);
                    if ((($dataValueType == 'string') && !$value) || 
                        (($dataValueType == 'array') && !count($value))
                    ) {
                        continue;
                    }

                    if ($dataValueType == 'array') {
                        foreach ($value as $_value) {
                            $data[$key][$_value] = array(
                                $modelIdAttributeCode       => $modelId, 
                                $dataKeyAttributeCode       => $key, 
                                $dataValueAttributeCode     => $_value, 
                            );
                        }
                    } else {
                        $data[$key] = array(
                            $modelIdAttributeCode       => $modelId, 
                            $dataKeyAttributeCode       => $key, 
                            $dataValueAttributeCode     => $value, 
                        );
                    }
                }
            }

            $select = $adapter
                ->select()
                ->from($dataTable)
                ->where($modelIdAttributeCode.' = ?', $modelId);
            $query = $adapter->query($select);
            while ($item = $query->fetch()) {
                $key = $item[$dataKeyAttributeCode];
                if ($dataValueType == 'array') {
                    $value  = $item[$dataValueAttributeCode];
                    $oldData[$key][$value] = $item;
                } else {
                    $oldData[$key] = $item;
                }
            }

            if ($dataValueType == 'array') {
                foreach ($oldData as $key => $_data) {
                    foreach ($_data as $value => $item) {
                        if (!(isset($data[$key]) && isset($data[$key][$value]))) {
                            $adapter->delete(
                                $dataTable, array(
                                $adapter->quoteInto($modelIdAttributeCode.' = ?', $modelId), 
                                $adapter->quoteInto($dataKeyAttributeCode.' = ?', $key), 
                                $adapter->quoteInto($dataValueAttributeCode.' = ?', $value)
                                )
                            );
                        }
                    }
                }
            } else {
                foreach ($oldData as $key => $item) {
                    if (!isset($data[$key])) {
                        $adapter->delete(
                            $dataTable, array(
                            $adapter->quoteInto($modelIdAttributeCode.' = ?', $modelId), 
                            $adapter->quoteInto($dataKeyAttributeCode.' = ?', $key)
                            )
                        );
                    }
                }
            }

            if ($dataValueType == 'array') {
                foreach ($data as $key => $_data) {
                    foreach ($_data as $value => $item) {
                        if (!(isset($oldData[$key]) && isset($oldData[$key][$value]))) {
                            $adapter->insert($dataTable, $item);
                        }
                    }
                }
            } else {
                foreach ($data as $key => $item) {
                    if (!isset($oldData[$key])) {
                        $adapter->insert($dataTable, $item);
                    } else {
                        $adapter->update(
                            $dataTable, $item, array(
                            $adapter->quoteInto($modelIdAttributeCode.' = ?', $modelId), 
                            $adapter->quoteInto($dataKeyAttributeCode.' = ?', $key), 
                            )
                        );
                    }
                }
            }
        }

        return $this;
    }
    /**
     * Save child data
     * 
     * @param Mage_Core_Model_Abstract $model
     * @param string $modelClass
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataKey2AttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function saveChildData3(
        $model, 
        $modelClass, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataKey2AttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        if (!$model || !($model instanceof $modelClass)) {
            return $this;
        }

        $modelId        = $model->getId();
        $resource       = $model->getResource();
        $dataTable      = $resource->getTable($dataTableName);
        $adapter        = $resource->getWriteConnection();
        $_data          = $model->getData($dataAttributeCode);
        if (!$_data) {
            $_data = array();
        }

        if (count($_data)) {
            $data           = array();
            $oldData        = array();
            foreach ($_data as $item) {
                if (isset($item[$dataKeyAttributeCode]) && 
                    isset($item[$dataKey2AttributeCode]) && 
                    isset($item[$dataValueAttributeCode])
                ) {
                    $key        = $item[$dataKeyAttributeCode];
                    $key2       = $item[$dataKey2AttributeCode];
                    $value      = $this->castValue($item[$dataValueAttributeCode], $dataValueType);
                    if ((($dataValueType == 'string') && !$value) || 
                        (($dataValueType == 'array') && !count($value))
                    ) {
                        continue;
                    }

                    if ($dataValueType == 'array') {
                        foreach ($value as $_value) {
                            $data[$key][$key2][$_value] = array(
                                $modelIdAttributeCode       => $modelId, 
                                $dataKeyAttributeCode       => $key, 
                                $dataKey2AttributeCode      => $key2, 
                                $dataValueAttributeCode     => $_value, 
                            );
                        }
                    } else {
                        $data[$key][$key2] = array(
                            $modelIdAttributeCode       => $modelId, 
                            $dataKeyAttributeCode       => $key, 
                            $dataKey2AttributeCode      => $key2, 
                            $dataValueAttributeCode     => $value, 
                        );
                    }
                }
            }

            $select = $adapter
                ->select()
                ->from($dataTable)
                ->where($modelIdAttributeCode.' = ?', $modelId);
            $query = $adapter->query($select);
            while ($item = $query->fetch()) {
                $key    = $item[$dataKeyAttributeCode];
                $key2   = $item[$dataKey2AttributeCode];
                if ($dataValueType == 'array') {
                    $value  = $item[$dataValueAttributeCode];
                    $oldData[$key][$key2][$value] = $item;
                } else {
                    $oldData[$key][$key2] = $item;
                }
            }

            if ($dataValueType == 'array') {
                foreach ($oldData as $key => $_data) {
                    foreach ($_data as $key2 => $__data) {
                        foreach ($__data as $value => $item) {
                            if (!(
                                    isset($data[$key]) && 
                                    isset($data[$key][$key2]) && 
                                    isset($data[$key][$key2][$value])
                                )
                            ) {
                                $adapter->delete(
                                    $dataTable, array(
                                    $adapter->quoteInto($modelIdAttributeCode.' = ?', $modelId), 
                                    $adapter->quoteInto($dataKeyAttributeCode.' = ?', $key), 
                                    $adapter->quoteInto($dataKey2AttributeCode.' = ?', $key2), 
                                    $adapter->quoteInto($dataValueAttributeCode.' = ?', $value), 
                                    )
                                );
                            }
                        }
                    }
                }
            } else {
                foreach ($oldData as $key => $_data) {
                    foreach ($_data as $key2 => $item) {
                        if (!(
                                isset($data[$key]) && 
                                isset($data[$key][$key2])
                            )
                        ) {
                            $adapter->delete(
                                $dataTable, array(
                                $adapter->quoteInto($modelIdAttributeCode.' = ?', $modelId), 
                                $adapter->quoteInto($dataKeyAttributeCode.' = ?', $key), 
                                $adapter->quoteInto($dataKey2AttributeCode.' = ?', $key2), 
                                )
                            );
                        }
                    }
                }
            }

            if ($dataValueType == 'array') {
                foreach ($data as $key => $_data) {
                    foreach ($_data as $key2 => $__data) {
                        foreach ($__data as $value => $item) {
                            if (!(
                                    isset($oldData[$key]) && 
                                    isset($oldData[$key][$key2]) && 
                                    isset($oldData[$key][$key2][$value])
                                )
                            ) {
                                $adapter->insert($dataTable, $item);
                            }
                        }
                    }
                }
            } else {
                foreach ($data as $key => $_data) {
                    foreach ($_data as $key2 => $item) {
                        if (!(
                                isset($oldData[$key]) && 
                                isset($oldData[$key][$key2])
                            )
                        ) {
                            $adapter->insert($dataTable, $item);
                        } else {
                            $adapter->update(
                                $dataTable, $item, array(
                                $adapter->quoteInto($modelIdAttributeCode.' = ?', $modelId), 
                                $adapter->quoteInto($dataKeyAttributeCode.' = ?', $key), 
                                $adapter->quoteInto($dataKey2AttributeCode.' = ?', $key2), 
                                )
                            );
                        }
                    }
                }
            }
        }

        return $this;
    }
    /**
     * Add child data
     * 
     * @param Mage_Core_Model_Abstract $model
     * @param string $modelClass
     * @param array $data
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    public function addChildData(
        $model, 
        $modelClass, 
        $array, 
        $dataAttributeCode
    ) {
        if (!$model || !($model instanceof $modelClass)) {
            return $this;
        }

        if (!isset($array[$dataAttributeCode]) || !$array[$dataAttributeCode]) {
            $model->setData($dataAttributeCode, array());
        }

        return $this;
    }
    /**
     * Load child data
     * 
     * @param Mage_Core_Model_Abstract $model
     * @param string $modelClass
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadChildData(
        $model, 
        $modelClass, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        if (!$model || 
            !($model instanceof $modelClass) || 
            $model->hasData($dataAttributeCode)
        ) {
            return $this;
        }

        $resource   = $model->getResource();
        $dataTable  = $resource->getTable($dataTableName);
        $adapter    = $resource->getWriteConnection();
        $select     = $adapter->select()
            ->from($dataTable)
            ->where($modelIdAttributeCode.' = ?', $model->getId());
        $query      = $adapter->query($select);
        $data       = array();
        while ($item = $query->fetch()) {
            $value           = $item[$dataValueAttributeCode];
            if ($dataValueType == 'string') {
                $data[] = $value;
            } else {
                $data[$value]  = $value;
            }
        }

        $model->setData($dataAttributeCode, $data);
        return $this;
    }
    /**
     * Load child data
     * 
     * @param Mage_Core_Model_Abstract $model
     * @param string $modelClass
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadChildData2(
        $model, 
        $modelClass, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) { 
        if (!$model || 
            !($model instanceof $modelClass) || 
            $model->hasData($dataAttributeCode)
        ) {
            return $this;
        }

        $resource       = $model->getResource();
        $dataTable      = $resource->getTable($dataTableName);
        $adapter        = $resource->getWriteConnection();
        $select         = $adapter->select()
            ->from($dataTable)
            ->where($modelIdAttributeCode.' = ?', $model->getId());
        $query          = $adapter->query($select);
        $data           = array();
        while ($item = $query->fetch()) {
            $key            = $item[$dataKeyAttributeCode];
            $value          = $item[$dataValueAttributeCode];
            if ($dataValueType == 'array') {
                $data[$key][$value] = $value;
            } else {
                $data[$key]  = $value;
            }
        }

        $model->setData($dataAttributeCode, $data);
        return $this;
    }
    
    /**
     * Load child data
     * 
     * @param Mage_Core_Model_Abstract $model
     * @param string $modelClass
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataKey2AttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadChildData3(
        $model, 
        $modelClass, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataKey2AttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) { 
        if (!$model || 
            !($model instanceof $modelClass) || 
            $model->hasData($dataAttributeCode)
        ) {
            return $this;
        }

        $resource       = $model->getResource();
        $dataTable      = $resource->getTable($dataTableName);
        $adapter        = $resource->getWriteConnection();
        $select         = $adapter->select()
            ->from($dataTable)
            ->where($modelIdAttributeCode.' = ?', $model->getId());
        $query          = $adapter->query($select);
        $data           = array();
        while ($item = $query->fetch()) {
            $key            = $item[$dataKeyAttributeCode];
            $key2           = $item[$dataKey2AttributeCode];
            $value          = $item[$dataValueAttributeCode];
            if ($dataValueType == 'array') {
                $data[$key][$key2][$value] = $value;
            } else {
                $data[$key][$key2]  = $value;
            }
        }

        $model->setData($dataAttributeCode, $data);
        return $this;
    }
    /**
     * Load collection child data
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadCollectionChildData(
        $collection, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        if (!$collection) {
            return $this;
        }

        $modelIds = array();
        foreach ($collection as $model) {
            array_push($modelIds, $model->getId());
        }

        if (!count($modelIds)) {
            return $this;
        }

        $dataTable  = $collection->getTable($dataTableName);
        $adapter    = $collection->getConnection();
        $select     = $adapter->select()
            ->from($dataTable)
            ->where($adapter->quoteInto($modelIdAttributeCode.' IN (?)', $modelIds));
        $query      = $adapter->query($select);
        $modelData  = array();
        while ($item = $query->fetch()) {
            $modelId    = $item[$modelIdAttributeCode];
            $value      = $item[$dataValueAttributeCode];
            if ($dataValueType == 'string') {
                $modelData[$modelId][] = $value;
            } else {
                $modelData[$modelId][$value] = $value;
            }
        }

        foreach ($collection as $model) {
            $modelId  = $model->getId();
            $data     = array();
            if (isset($modelData[$modelId])) {
                $data = $modelData[$modelId];
            }

            $model->setData($dataAttributeCode, $data);
        }

        return $this;
    }
    /**
     * Load collection child data
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadCollectionChildData2(
        $collection, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        if (!$collection) {
            return $this;
        }

        $modelIds = array();
        foreach ($collection as $model) {
            array_push($modelIds, $model->getId());
        }

        if (count($modelIds)) {
            $dataTable      = $collection->getTable($dataTableName);
            $adapter        = $collection->getConnection();
            $select         = $adapter->select()
                ->from($dataTable)
                ->where($adapter->quoteInto($modelIdAttributeCode.' IN (?)', $modelIds));
            $query          = $adapter->query($select);
            $modelData      = array();
            while ($item = $query->fetch()) {
                $modelId        = $item[$modelIdAttributeCode];
                $key            = $item[$dataKeyAttributeCode];
                $value          = $item[$dataValueAttributeCode];
                if ($dataValueType == 'array') {
                    $modelData[$modelId][$key][$value] = $value;
                } else {
                    $modelData[$modelId][$key] = $value;
                }
            }

            foreach ($collection as $model) {
                $modelId        = $model->getId();
                $data           = array();
                if (isset($modelData[$modelId])) {
                    $data = $modelData[$modelId];
                }

                $model->setData($dataAttributeCode, $data);
            }
        }

        return $this;
    }
    /**
     * Load collection child data
     * 
     * @param Varien_Data_Collection_Db $collection
     * @param string $modelIdAttributeCode
     * @param string $dataTableName
     * @param string $dataAttributeCode
     * @param string $dataKeyAttributeCode
     * @param string $dataKey2AttributeCode
     * @param string $dataValueAttributeCode
     * @param string $dataValueType
     * 
     * @return self
     */
    public function loadCollectionChildData3(
        $collection, 
        $modelIdAttributeCode, 
        $dataTableName, 
        $dataAttributeCode, 
        $dataKeyAttributeCode, 
        $dataKey2AttributeCode, 
        $dataValueAttributeCode, 
        $dataValueType = 'string'
    ) {
        if (!$collection) {
            return $this;
        }

        $modelIds = array();
        foreach ($collection as $model) {
            array_push($modelIds, $model->getId());
        }

        if (count($modelIds)) {
            $dataTable      = $collection->getTable($dataTableName);
            $adapter        = $collection->getConnection();
            $select         = $adapter->select()
                ->from($dataTable)
                ->where($adapter->quoteInto($modelIdAttributeCode.' IN (?)', $modelIds));
            $query          = $adapter->query($select);
            $modelData      = array();
            while ($item = $query->fetch()) {
                $modelId        = $item[$modelIdAttributeCode];
                $key            = $item[$dataKeyAttributeCode];
                $key2           = $item[$dataKey2AttributeCode];
                $value          = $item[$dataValueAttributeCode];
                if ($dataValueType == 'array') {
                    $modelData[$modelId][$key][$key2][$value] = $value;
                } else {
                    $modelData[$modelId][$key][$key2] = $value;
                }
            }

            foreach ($collection as $model) {
                $modelId        = $model->getId();
                $data           = array();
                if (isset($modelData[$modelId])) {
                    $data = $modelData[$modelId];
                }

                $model->setData($dataAttributeCode, $data);
            }
        }

        return $this;
    }
    /**
     * Remove child data
     * 
     * @param Mage_Core_Model_Abstract $model
     * @param string $modelClass
     * @param string $dataAttributeCode
     * 
     * @return self
     */
    public function removeChildData(
        $model, 
        $modelClass, 
        $dataAttributeCode
    ) {
        if (!$model || !($model instanceof $modelClass)) {
            return $this;
        }

        $model->unsetData($dataAttributeCode);
        return $this;
    }
}