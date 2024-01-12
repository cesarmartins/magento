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
 * Math helper
 * 
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Helper_Core_Math
    extends Mage_Core_Helper_Abstract
{
    /**
     * Get distance units
     * 
     * @return array
     */
    public function getDistanceUnits()
    {
        return array(
            'mi' => array(
                'name'  => 'Mile', 
                'ratio' => 1
            ), 
            'nmi' => array(
                'name'  => 'Nautical Mile', 
                'ratio' => 0.8684
            ), 
            'km' => array(
                'name'  => 'Kilometer', 
                'ratio' => 1.609344
            ), 
        );
    }
    /**
     * Get distance
     * 
     * @param float $latitude1
     * @param float $longitude1
     * @param float $latitude2
     * @param float $longitude2
     * @param string $unitCode
     * 
     * @return float
     */
    public function getDistance($latitude1, $longitude1, $latitude2, $longitude2, $unitCode = 'mi') 
    {
        $longitudeDelta = $longitude1 - $longitude2;
        $distance = 60 * 1.1515 * rad2deg(
            acos(
                (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + 
                (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($longitudeDelta)))
            )
        );
        $distanceUnits = $this->getDistanceUnits();
        $ratio = 1;
        if (isset($distanceUnits[$unitCode])) {
            $ratio = $distanceUnits[$unitCode]['ratio'];
        }

        return $ratio * $distance;
    }
}
