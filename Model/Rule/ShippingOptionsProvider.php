<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Model\Rule;

class ShippingOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     * ShippingOptionsProvider constructor.
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_shippingConfig = $shippingConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $activeCarriers = $this->_shippingConfig->getActiveCarriers();
        $methods = [];

        foreach($activeCarriers as $carrierCode => $carrierModel) {
            //if (!$carrierModel->isShippingLabelsAvailable()) continue;
            $carrierTitle = $this->scopeConfig
                ->getValue('carriers/'.$carrierCode.'/title');
            $methods[] = ['label' => $carrierTitle , 'value' => $carrierCode];
        }

        return $methods;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $activeCarriers = $this->_shippingConfig->getActiveCarriers();
        $methods = [];

        foreach($activeCarriers as $carrierCode => $carrierModel) {
            //if (!$carrierModel->isShippingLabelsAvailable()) continue;
            $carrierTitle = $this->scopeConfig
                ->getValue('carriers/'.$carrierCode.'/title');
            $methods[$carrierCode] = $carrierTitle;
        }

        return $methods;
    }
}
