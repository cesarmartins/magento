<?php
/**
 * Magmodules.eu - http://www.magmodules.eu.
 *
 * NOTICE OF LICENSE
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.magmodules.eu/MM-LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_Googleshopping
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2019 (http://www.magmodules.eu)
 * @license       https://www.magmodules.eu/terms.html  Single Service License
 */

class Magmodules_Googleshopping_Model_Adminhtml_System_Config_Source_Identifier
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = array(
                array('value' => '', 'label' => Mage::helper('adminhtml')->__('Deactivate')),
                array('value' => '1', 'label' => Mage::helper('adminhtml')->__('Activate when less than two')),
                array('value' => '2', 'label' => Mage::helper('adminhtml')->__('Activate')),
            );
        }

        return $this->options;
    }

}