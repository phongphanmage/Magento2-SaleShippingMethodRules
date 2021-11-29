<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Model\ResourceModel\Grid;

class Collection extends \Vnecoms\ShippingOrderRules\Model\ResourceModel\OrderRules\Collection
{
    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addWebsitesToResult();
        return $this;
    }
}
