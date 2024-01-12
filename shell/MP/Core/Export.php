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

require_once rtrim(dirname(__FILE__), '/').'/Abstract.php';

/**
 * Export
 *
 * @category   MP
 * @package    MP_Shell
 * @author     MP Team <mageplugins@gmail.com>
 */
abstract class MP_Shell_Core_Export
    extends MP_Shell_Core_Abstract
{
    /**
     * File config
     * 
     * @var array
     */
    protected $_fileConfig = array(
        'path'          => '/var/export/', 
        'filename'      => 'localfilename', 
        'delimiter'     => ',', 
        'enclosure'     => '"', 
    );
    /**
     * Parse arguments
     * 
     * @return bool
     */
    protected function parseArgs()
    {
        return $this->parseFileArgs();
    }
    /**
     * Get field names
     * 
     * @return array
     */
    abstract protected function getFieldNames();
    /**
     * Get rows
     * 
     * @return array
     */
    abstract protected function getRows();
    /**
     * Export
     * 
     * @return MP_Shell_Core_Export
     */
    protected function export()
    {
        if (!$this->parseArgs()) {
            return $this;
        }

        $this->printMessage('Exporting to data file...');
        $file               = $this->getFile();
        if (!$file) {
            return $this;
        }

        $config             = $this->getFileConfig();
        try {
            $file->streamOpen($this->getFileFilename(), 'w');
            $fieldNames         = $this->getFieldNames();
            $file->streamWriteCsv($fieldNames, $config['delimiter'], $config['enclosure']);
            foreach ($this->getRows() as $row) {
                $csvDatum           = array();
                foreach ($fieldNames as $index => $fieldName) {
                    if (isset($row[$fieldName])) {
                        $csvDatum[$index]   = $row[$fieldName];
                    } else {
                        $csvDatum[$index]   = null;
                    }
                }

                $file->streamWriteCsv($csvDatum, $config['delimiter'], $config['enclosure']);
            }

            $this->printMessage('Exported.');
            $file->streamClose();
        } catch (Exception $e) {
            $this->printMessage($e->getMessage());
        }

        return $this;
    }
    /**
     * Run script
     */
    public function run()
    {
        if (!$this->getArg('help')) {
            $this->export();
        } else {
            $this->printHelp();
        }
    }
    /**
     * Get help message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f Export.php -- [options]
  
  file-path <file-path>                         File path
  file-filename <file-filename>                 File filename
  file-csv-delimiter <file-csv-delimiter>       File CSV delimiter
  file-csv-enclosure <file-csv-enclosure>       File CSV enclosure
  
  help                                          This help
USAGE;
    }
}
