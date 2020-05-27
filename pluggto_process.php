<?php

$fileName = basename(__FILE__);

require 'app/Mage.php';

Mage::init();

Mage::getModel('pluggto/bulkexport')->runBulkExport();
Mage::getModel('pluggto/line')->playline();






