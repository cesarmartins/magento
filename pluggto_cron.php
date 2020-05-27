<?php

/*
 *
 * Cron class to pluggto
 *
 */


require 'app/Mage.php';

$baseDir = dirname(__FILE__);

Mage::init();

Mage::getSingleton('pluggto/bulkexport')->runBulkExport();

shell_exec("php $baseDir/pluggto_process.php > /dev/null 2>&1 &");


if(Mage::getStoreConfig('pluggto/configs/multi_queues')){

    sleep(3);

    $quantity = Mage::getStoreConfig('pluggto/configs/multi_queues_quantity');

    if(empty($quantity)){
        $quantity = 1;
    }

    for($i=0;$i < $quantity;$i++){
        shell_exec("php $baseDir/pluggto_process.php > /dev/null 2>&1 &");
        sleep(3);
    }

    // limpa transaçoes que ficaram travadas no status 3
    Mage::getModel('pluggto/line')->cleanStackForMultiQueue();

}


// limpa transações antigas da fila
Mage::getModel('pluggto/line')->clearQueue();







