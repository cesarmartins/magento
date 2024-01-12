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
 * Database helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Database
    extends Mage_Core_Helper_Abstract
{
    /**
     * MySQL column - Table DDL type pairs
     *
     * @var array
     */
    protected $_ddlColumnTypes      = array(
        'boolean'               => 'bool', 
        'smallint'              => 'smallint', 
        'integer'               => 'int', 
        'bigint'                => 'bigint', 
        'float'                 => 'float', 
        'decimal'               => 'decimal', 
        'numeric'               => 'decimal', 
        'date'                  => 'date', 
        'timestamp'             => 'timestamp', 
        'datetime'              => 'datetime', 
        'text'                  => 'text', 
        'blob'                  => 'blob', 
        'varbinary'             => 'blob', 
    );
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
        return $this->getCoreHelper()
            ->getVersionHelper();
    }
    /**
     * Replace unique key
     * 
     * @param Mage_Core_Model_Resource_Setup $setup
     * @param string $tableName
     * @param string $keyName
     * @param array $keyAttributes
     * 
     * @return MP_Warehouse_Helper_Core_Database
     */
    public function replaceUniqueKey($setup, $tableName, $keyName, $keyAttributes)
    {
        $connection             = $setup->getConnection();
        $versionHelper          = $this->getVersionHelper();
        $table                  = $setup->getTable($tableName);
        if ($versionHelper->isGe1600()) {
            $indexTypeUnique        = Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE;
            $indexes                = $connection->getIndexList($table);
            foreach ($indexes as $index) {
                if ($index['INDEX_TYPE'] == $indexTypeUnique) {
                    $connection->dropIndex($table, $index['KEY_NAME']);
                }
            }

            $keyName                = $setup->getIdxName($tableName, $keyAttributes, $indexTypeUnique);
            $connection->addIndex($table, $keyName, $keyAttributes, $indexTypeUnique);
        } else {
            $connection->addKey($table, $keyName, $keyAttributes, 'unique');
        }

        return $this;
    }
    /**
     * Get table
     * 
     * @param string $entityName
     * 
     * @return string 
     */
    public function getTable($entityName)
    {
        return Mage::getSingleton('core/resource')
            ->getTableName($entityName);
    }
    /**
     * Get column type by DDL
     *
     * @param array $definition
     * 
     * @return string
     */
    protected function getColumnTypeByDdl($definition)
    {
        switch ($definition['DATA_TYPE']) {
            case 'bool': 
                return 'boolean';
            case 'tinytext': 
            case 'char': 
            case 'varchar': 
            case 'text': 
            case 'mediumtext': 
            case 'longtext': 
                return 'text';
            case 'blob': 
            case 'mediumblob': 
            case 'longblob': 
                return 'blob';
            case 'tinyint': 
            case 'smallint': 
                return 'smallint';
            case 'mediumint': 
            case 'int': 
                return 'integer';
            case 'bigint': 
                return 'bigint';
            case 'datetime': 
                return 'datetime';
            case 'timestamp': 
                return 'timestamp';
            case 'date': 
                return 'date';
            case 'float': 
                return 'float';
            case 'decimal': 
            case 'numeric': 
                return 'decimal';
        }
    }
    /**
     * Get column by DDL
     *
     * @param array $options
     * 
     * @return array
     */
    public function getColumnByDdl($options)
    {
        $options                = array_change_key_case($options, CASE_UPPER);
        $options['COLUMN_TYPE'] = $this->getColumnTypeByDdl($options);
        if (array_key_exists('DEFAULT', $options) && 
            is_null($options['DEFAULT'])
        ) {
            unset($options['DEFAULT']);
        }

        return $options;
    }
    /**
     * Parse text size
     *
     * @param string|int $size
     * 
     * @return int
     */
    protected function parseTextSize($size)
    {
        $size                   = trim($size);
        $last                   = strtolower(substr($size, -1));
        switch ($last) {
            case 'k': 
                $size                   = (int) $size * 1024;
                break;
            case 'm': 
                $size                   = (int) $size * 1024 * 1024;
                break;
            case 'g': 
                $size                   = (int) $size * 1024 * 1024 * 1024;
                break;
        }

        if (empty($size)) {
            return 1024;
        }

        if ($size >= 2147483648) {
            return 2147483648;
        }

        return (int) $size;
    }
    /**
     * Get column definition
     * 
     * @param Zend_Db_Adapter_Abstract $adapter
     * @param array $options
     * 
     * @return string
     */
    public function getColumnDefinition($adapter, $options) 
    {
        $options                = array_change_key_case($options, CASE_UPPER);
        $cType                  = null;
        $cUnsigned              = false;
        $cNullable              = true;
        $cDefault               = false;
        $cIdentity              = false;
        $ddlType                = $options['COLUMN_TYPE'];
        if (empty($ddlType) || !isset($this->_ddlColumnTypes[$ddlType])) {
            throw new Zend_Db_Exception('Invalid column definition data');
        }

        $cType                  = $this->_ddlColumnTypes[$ddlType];
        switch ($ddlType) {
            case 'smallint': 
            case 'integer': 
            case 'bigint': 
                if (!empty($options['UNSIGNED'])) {
                    $cUnsigned              = true;
                }
                break;
            case 'decimal': 
            case 'numeric': 
                $precision              = 10;
                $scale                  = 0;
                $match                  = array();
                if (!empty($options['LENGTH']) && preg_match('#^\(?(\d+),(\d+)\)?$#', $options['LENGTH'], $match)) {
                    $precision              = $match[1];
                    $scale                  = $match[2];
                } else {
                    if (isset($options['SCALE']) && is_numeric($options['SCALE'])) {
                        $scale                  = $options['SCALE'];
                    }

                    if (isset($options['PRECISION']) && is_numeric($options['PRECISION'])) {
                        $precision              = $options['PRECISION'];
                    }
                }

                $cType                  .= sprintf('(%d,%d)', $precision, $scale);
                break;
            case 'text': 
            case 'blob': 
            case 'varbinary': 
                if (empty($options['LENGTH'])) {
                    $length                 = 1024;
                } else {
                    $length                 = $this->parseTextSize($options['LENGTH']);
                }

                if ($length <= 255) {
                    $cType                  = $ddlType == 'text' ? 'varchar' : 'varbinary';
                    $cType                  = sprintf('%s(%d)', $cType, $length);
                } else if ($length > 255 && $length <= 65536) {
                    $cType                  = $ddlType == 'text' ? 'text' : 'blob';
                } else if ($length > 65536 && $length <= 16777216) {
                    $cType                  = $ddlType == 'text' ? 'mediumtext' : 'mediumblob';
                } else {
                    $cType                  = $ddlType == 'text' ? 'longtext' : 'longblob';
                }
                break;
        }

        if (array_key_exists('DEFAULT', $options)) {
            $cDefault               = $options['DEFAULT'];
        }

        if (array_key_exists('NULLABLE', $options)) {
            $cNullable              = (bool)$options['NULLABLE'];
        }

        if (!empty($options['IDENTITY']) || !empty($options['AUTO_INCREMENT'])) {
            $cIdentity              = true;
        }

        if ($cDefault !== null && strlen($cDefault)) {
            $cDefault               = str_replace("'", '', $cDefault);
        }

        if ($ddlType == 'timestamp') {
            if ($cDefault === null) {
                $cDefault               = new Zend_Db_Expr('NULL');
            } elseif ($cDefault == 'TIMESTAMP_INIT') {
                $cDefault               = new Zend_Db_Expr('CURRENT_TIMESTAMP');
            } else if ($cDefault == 'TIMESTAMP_UPDATE') {
                $cDefault               = new Zend_Db_Expr('0 ON UPDATE CURRENT_TIMESTAMP');
            } else if ($cDefault == 'TIMESTAMP_INIT_UPDATE') {
                $cDefault               = new Zend_Db_Expr('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
            } else if ($cNullable && !$cDefault) {
                $cDefault               = new Zend_Db_Expr('NULL');
            } else {
                $cDefault               = new Zend_Db_Expr('0');
            }
        } else if (is_null($cDefault) && $cNullable) {
            $cDefault               = new Zend_Db_Expr('NULL');
        }

        if (empty($options['COMMENT'])) {
            $comment                = '';
        } else {
            $comment                = $options['COMMENT'];
        }

        $after                  = null;
        if (!empty($options['AFTER'])) {
            $after                  = $options['AFTER'];
        }

        return sprintf(
            '%s%s%s%s%s COMMENT %s %s', 
            $cType, 
            $cUnsigned ? ' UNSIGNED' : '', 
            $cNullable ? ' NULL' : ' NOT NULL', 
            $cDefault !== false ? $adapter->quoteInto(' default ?', $cDefault) : '', 
            $cIdentity ? ' auto_increment' : '', 
            $adapter->quote($comment), 
            $after ? 'AFTER '.$adapter->quoteIdentifier($after) : ''
        );
    }
}
