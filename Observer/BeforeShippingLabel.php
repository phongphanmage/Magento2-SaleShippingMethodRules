<?php
namespace Vnecoms\ShippingOrderRules\Observer;

use Magento\Framework\Event\ObserverInterface;

class BeforeShippingLabel implements ObserverInterface
{
    /**
     * @var Vnecoms\ShippingOrderRules\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Vnecoms\ShippingOrderRules\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * BeforeShippingLabel constructor.
     * @param \Vnecoms\ShippingOrderRules\Helper\Data $dataHelper
     * @param \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier
     */
    public function __construct(
        \Vnecoms\ShippingOrderRules\Helper\Data $dataHelper,
        \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier
    ) {
        $this->dataHelper = $dataHelper;
        $this->rulesApplier = $rulesApplier;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getTransport();
        $order = $transport->getOrder();
        $shippingMethod = $this->rulesApplier->getShippingMethodFromRule($order);
        if (!$shippingMethod) {
            $shippingMethod = $this->dataHelper->getDefaultShippingMethod();
        }
        $transport->setCarrierCode($shippingMethod);
    }
}
