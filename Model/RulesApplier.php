<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Model;

use Vnecoms\ShippingOrderRules\Model\ResourceModel\OrderRules\CollectionFactory;

/**
 * Rule applier model
 */
class RulesApplier
{
    protected $_collectionFactory;

    /**
     * RulesApplier constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @param $item
     * @param $address
     * @return bool
     */
    public function getShippingMethodFromQuoteItem($item, $address)
    {
        $shippingMethod = false;
        $rules = $this->_getRules($address->getStore()->getWebsiteId(), $address->getCustomerGroupId());

        foreach ($rules as $rule) {
            if ($rule->getConditions()->validate($item)) {
                $shippingMethod = $rule->getShippingMethod();
            }
            if ($shippingMethod) {
                $shippingMethod = explode(",", $shippingMethod);
                break;
            }
        }

        return $shippingMethod;
    }

    /**
     * @param $item
     * @param $address
     * @return bool
     */
    public function getDesFromQuoteItem($item, $address)
    {
        $des = false;
        $rules = $this->_getRules($address->getStore()->getWebsiteId(), $address->getCustomerGroupId());

        foreach ($rules as $rule) {
            if ($rule->getConditions()->validate($item)) {
                $des = $rule->getData('short_description');
            }
        }

        return $des;
    }

    /**
     * @param $item
     * @param $websiteId
     * @param $customerGrroupId
     * @return bool
     */
    public function getDesFromProduct($item, $websiteId, $customerGrroupId)
    {
        $des = false;
        $rules = $this->_getRules($websiteId, $customerGrroupId);

        foreach ($rules as $rule) {
            if ($rule->getConditions()->validate($item)) {
                $des = $rule->getData('description');
            }
        }

        return $des;
    }


    /**
     * @param $websiteId
     * @param $cusomerGroupId
     * @return mixed
     */
    protected function _getRules($websiteId, $cusomerGroupId)
    {
        $rule = $this->_collectionFactory->create()
            ->setValidationFilter(
                $websiteId,
                $cusomerGroupId,
                null
            )
            ->addFieldToFilter('is_active', 1);
        return $rule;
    }
}
