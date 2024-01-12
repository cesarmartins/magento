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
 * Adminhtml tabs
 *
 * @category   MP
 * @package    MP_Warehouse
 * @author     MP Team <mageplugins@gmail.com>
 */
class MP_Warehouse_Block_Adminhtml_Core_Widget_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Model name
     * 
     * @var string
     */
    protected $_modelName;
    /**
     * Child block type prefix
     * 
     * @var string
     */
    protected $_childBlockTypePrefix;
    /**
     * Get core helper
     * 
     * @return MP_Warehouse_Helper_Core_Data
     */
    protected function getCoreHelper()
    {
        return Mage::helper('warehouse/core_data');
    }
    /**
     * Get model name
     * 
     * @return string
     */
    public function getModelName()
    {
        return $this->_modelName;
    }
    /**
     * Get text helper
     * 
     * @return Varien_Object
     */
    public function getTextHelper()
    {
        return $this;
    }
    /**
     * Retrieve registered model
     *
     * @return Varien_Object
     */
    protected function getModel()
    {
        $model = Mage::registry($this->getModelName());
        if (!$model) {
            $model = new Varien_Object();
        }

        return $model;
    }
    /**
     * Translate html content
     * 
     * @param string $html
     * 
     * @return string
     */
    protected function _translateHtml($html)
    {
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        return $html;
    }
    /**
     * Get block content
     * 
     * @param string $block
     * 
     * @return string
     */
    protected function _getBlockContent($block)
    {
        return $this->_translateHtml($this->getLayout()->createBlock($block)->toHtml());
    }
    /**
     * Get child block type prefix
     * 
     * @return string
     */
    protected function getChildBlockTypePrefix()
    {
        return $this->_childBlockTypePrefix;
    }
    /**
     * Get child block content
     * 
     * @param string $name
     * 
     * @return string
     */
    protected function getChildBlockContent($name)
    {
        return $this->_getBlockContent($this->getChildBlockTypePrefix().$name);
    }
}
