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
 * Import
 *
 * @category   MP
 * @package    MP_Shell
 * @author     MP Team <mageplugins@gmail.com>
 */
abstract class MP_Shell_Core_Import
    extends MP_Shell_Core_Abstract
{
    /**
     * File config
     * 
     * @var array
     */
    protected $_fileConfig = array(
        'path'          => '/var/import/', 
        'filename'      => 'localfilename', 
        'delimiter'     => ',', 
        'enclosure'     => '"', 
    );
    /**
     * FTP
     * 
     * @var Varien_Io_Ftp
     */
    protected $_ftp;
    /**
     * FTP config
     * 
     * @var array
     */
    protected $_ftpConfig = array(
        'host'          => 'ftp.yourhost.com', 
        'user'          => 'username', 
        'password'      => 'password', 
        'filename'      => 'remotefilename', 
    );
    /**
     * Get FTP config
     * 
     * @return array
     */
    protected function getFTPConfig()
    {
        return $this->_ftpConfig;
    }
    /**
     * Get FTP filename
     * 
     * @return string
     */
    protected function getFTPFilename()
    {
        $config = $this->getFTPConfig();
        return (isset($config['filename'])) ? $config['filename'] : null;
    }
    /**
     * Get FTP
     * 
     * @return Varien_Io_Ftp
     */
    protected function getFTP()
    {
        if (is_null($this->_ftp)) {
            $ftp = new Varien_Io_Ftp();
            $config = $this->getFTPConfig();
            try {
                $ftp->open($config);
                $this->_ftp = $ftp;
            } catch (Exception $e) {
                $this->printMessage($e->getMessage());
            }
        }

        return $this->_ftp;
    }
    /**
     * Download file
     * 
     * @return bool
     */
    protected function download()
    {
        $isDownloaded = false;
        $this->printMessage('Downloading data file...');
        $ftp = $this->getFTP();
        if (!is_null($ftp)) {
            $file = $this->getFile();
            if (!is_null($file)) {
                $data = $ftp->read($this->getFTPFilename(), $this->getFilePath());
                if (false !== $data) {
                    $isDownloaded = true;
                    $this->printMessage('Downloaded.');
                } else {
                    $this->printMessage("Could not download file: {$this->getFTPFilename()}");
                }
            }
        }

        return $isDownloaded;
    }
    /**
     * Parse FTP arguments
     * 
     * @return bool
     */
    protected function parseFTPArgs()
    {
        $isParsed = true;
        if ($this->getArg('ftp')) {
            $ftpHost = trim($this->getArg('ftp-host'));
            if (!$ftpHost) {
                $this->printMessage('FTP host is required.');
                $isParsed = false;
            } else {
                $this->_ftpConfig['host'] = $ftpHost;
            }

            $ftpUser = trim($this->getArg('ftp-user'));
            if (!$ftpUser) {
                $this->printMessage('FTP user is required.');
                $isParsed = false;
            } else {
                $this->_ftpConfig['user'] = $ftpUser;
            }

            $ftpPassword = trim($this->getArg('ftp-password'));
            if (!$ftpPassword) {
                $this->printMessage('FTP password is required.');
                $isParsed = false;
            } else {
                $this->_ftpConfig['password'] = $ftpPassword;
            }

            $ftpFilename = trim($this->getArg('ftp-filename'));
            if (!$ftpFilename) {
                $this->printMessage('FTP filename is required.');
                $isParsed = false;
            } else {
                $this->_ftpConfig['filename'] = $ftpFilename;
            }
        }

        return $isParsed;
    }
    /**
     * Parse arguments
     * 
     * @return bool
     */
    protected function parseArgs()
    {
        return $this->parseFTPArgs() && $this->parseFileArgs();
    }
    /**
     * Reindex
     * 
     * @return MP_Shell_Core_Import
     */
    protected function reindex()
    {
        $this->printMessage('Reindexing.');
        return $this;
    }
    /**
     * Get row field value
     * 
     * @param array $row
     * @param string $field
     * 
     * @return string
     */
    protected function getRowFieldValue($row, $field)
    {
        return (isset($row[$field])) ? trim($row[$field]) : null;
    }
    /**
     * Import prices
     * 
     * @return MP_Shell_Core_Import
     */
    protected function import()
    {
        if (!$this->parseArgs()) {
            return $this;
        }

        if ($this->getArg('ftp')) {
            $this->download();
        }

        $this->printMessage('Importing data file...');
        $file   = $this->getFile();
        if (!$file) {
            return $this;
        }

        $config = $this->getFileConfig();
        try {
            $file->streamOpen($this->getFileFilename(), 'r');
            $_fieldNames = $file->streamReadCsv($config['delimiter'], $config['enclosure']);
            if (count($_fieldNames)) {
                $fieldNames = array();
                foreach ($_fieldNames as $index => $fieldName) {
                    $fieldNames[$index] = trim(strtolower($fieldName));
                }

                while (($csvData = $file->streamReadCsv($config['delimiter'], $config['enclosure'])) !== false) {
                    if (count($csvData) == 1 && $csvData[0] === null) { 
                        continue; 
                    }

                    $row = array();
                    foreach ($fieldNames as $index => $fieldName) {
                        if (isset($csvData[$index])) {
                            $row[$fieldName] = $csvData[$index];
                        }
                    }

                    $this->importRow($row);
                }

                $this->reindex();
                $this->printMessage('Imported.');
            } else {
                $this->printMessage('Data file header was not found.');
            }
        } catch (Exception $e) {
            $this->printMessage($e->getMessage());
        }

        return $this;
    }
    /**
     * Get help message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f Importer.php -- [options]
  
  ftp <flag>                                    Check if data file should be downloaded from the FTP server first
  ftp-host <ftp-host>                           FTP host
  ftp-user <ftp-user>                           FTP user
  ftp-password <ftp-password>                   FTP password
  ftp-filename <ftp-filename>                   FTP filename
  
  file-path <file-path>                         File path
  file-filename <file-filename>                 File filename
  file-csv-delimiter <file-csv-delimiter>       File CSV delimiter
  file-csv-enclosure <file-csv-enclosure>       File CSV enclosure
  
  help                                          This help
USAGE;
    }
    /**
     * Run script
     */
    public function run()
    {
        if (!$this->getArg('help')) {
            $this->import();
        } else {
            $this->printHelp();
        }
    }
}
