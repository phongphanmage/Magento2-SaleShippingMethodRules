<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Plugin;
use Magento\Sales\Model\Order\Shipment;

class Shipping
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \Vnecoms\ShippingOrderRules\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * Shipping constructor.
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier
     * @param array $data
     */
    public function __construct(
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->rulesApplier = $rulesApplier;
    }

    /**
     * @param \Magento\Shipping\Model\Shipping $subject
     * @param \Magento\Shipping\Model\Shipping $result
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Shipping\Model\Shipping
     * @throws \Exception
     */
    public function afterCollectRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Magento\Shipping\Model\Shipping $result,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $allItems = $request->getAllItems();

        $resultCollect = $subject->getResult();

        $shippingRates = $resultCollect->getAllRates();
        $allShippingRates = $this->groupShippingRates($shippingRates);
        $itemShippingMethods = [];
        foreach ($allItems as $item) {
            if ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                && $item->getProduct()->getShipmentType()
            ) {
                continue;
            }
            $quote = $item->getQuote();

            $itemRules = $this->rulesApplier->getShippingMethodFromQuoteItem($item->getProduct(), $quote);
            if (!$itemRules) {
                $itemShippingMethods[$item->getId()] = $allShippingRates;
            } else {
                $itemShippingMethods[$item->getId()] = $itemRules;
            }

        }

        if (!$itemShippingMethods) return $result;
        $intersect = $itemShippingMethods;
        if (count($itemShippingMethods) > 1) {
            $intersect = array_intersect(...$itemShippingMethods);
        } else {
            $intersect = array_values($intersect);
            $intersect = $intersect[0];
        }

        if (count($intersect)) {
            $resultCollect->reset();
            foreach ($shippingRates as $shippingRate) {
                if(in_array($shippingRate->getCarrier(), $intersect))
                    $resultCollect->append($shippingRate);
            }
        } else {
             throw new \Exception(__("we couldn't find any shipping method that satisfy the condition"));
        }

        return $result;
    }

    /**
     * @param $shippingRates
     * @return array
     */
    public function groupShippingRates($shippingRates)
    {
        $rates = [];
        foreach ($shippingRates as $rate) {
            if($rate->getCarrier())
            $rates[] = $rate->getCarrier();
        }
        return $rates;
    }
}
