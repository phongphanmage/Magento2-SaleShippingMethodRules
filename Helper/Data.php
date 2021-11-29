<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    /**
     * get config enable_tracking_auto
     *
     * @return Ambigous <mixed, string, NULL, multitype:, multitype:Ambigous <string, multitype:, NULL> >
     */
    public function getDefaultShippingMethod()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue('vendors/shipping/default_shipping_method', $storeScope);
    }

}
