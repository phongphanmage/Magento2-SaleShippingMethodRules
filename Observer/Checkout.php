<?php
namespace Vnecoms\ShippingOrderRules\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;

class Checkout implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Vnecoms\ShippingOrderRules\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Checkout constructor.
     * @param \Magento\Checkout\Model\Session $session
     * @param \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ){
        $this->session = $session;
        $this->rulesApplier = $rulesApplier;
        $this->url = $url;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action     = $observer->getControllerAction();
        $quote = $this->session->getQuote();
        $itemShippingMethods = [];
        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                && $item->getProduct()->getShipmentType()
            ) {
                continue;
            }

            $itemRules = $this->rulesApplier->getShippingMethodFromQuoteItem($item->getProduct(), $quote);
            if ($itemRules) {
                $itemShippingMethods[$item->getId()] = $itemRules;
            }
        }

        if (count($itemShippingMethods) > 0) {
            $intersect = $itemShippingMethods;
            if (count($itemShippingMethods) > 1) {
                $intersect = array_intersect(...$itemShippingMethods);
            } else {
                $intersect = array_values($intersect);
                $intersect = $intersect[0];
            }

            if (!count($intersect)) {
                $this->messageManager->addError(__("we couldn't find any shipping method that satisfy the condition"));
                $action->getResponse()->setRedirect($this->url->getUrl("checkout/cart"));
            }
        }
    }
}
