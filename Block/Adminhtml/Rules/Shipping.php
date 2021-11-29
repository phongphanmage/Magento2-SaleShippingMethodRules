<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog price rules
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Vnecoms\ShippingOrderRules\Block\Adminhtml\Rules;

/**
 * @api
 * @since 100.0.2
 */
class Shipping extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Vnecoms_ShippingOrderRules';
        $this->_controller = 'adminhtml_rules_shipping';
        $this->_headerText = __('Shipping Order Rules');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}
