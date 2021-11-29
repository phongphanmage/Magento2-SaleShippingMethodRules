<?php
/* File: app/code/Atwix/CatalogAttribute/Block/CartItemBrandBlock.php */
namespace Vnecoms\ShippingOrderRules\Block;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Checkout\Block\Cart\Additional\Info as AdditionalBlockInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template as ViewTemplate;
use Magento\Framework\View\Element\Template\Context;
/**
 * Class CartItemBrandBlock
 */
class CartItemShippingRule extends ViewTemplate
{
    /**
     * Product
     *
     * @var ProductInterface|null
     */
    protected $product = null;
    /**
     * Product Factory
     *
     * @var ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var \Vnecoms\ShippingOrderRules\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * CartItemShippingRule constructor.
     * @param Context $context
     * @param ProductInterfaceFactory $productFactory
     * @param \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier
     */
    public function __construct(
        Context $context,
        ProductInterfaceFactory $productFactory,
        \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->rulesApplier = $rulesApplier;
    }

    /**
     * @return string
     */
    public function getShippingRule()
    {
        $item = $this->getProduct();
        if ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            && $item->getProduct()->getShipmentType()
        ) {
            return null;
        }

        $quote=  $item->getQuote();
        $string = $this->rulesApplier->getDesFromQuoteItem($item->getProduct(), $quote);
        if (!$string) return null;
        return $string;
    }

    /**
     * Get product from quote item
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        if ($this->product instanceof ProductInterface) {
            return $this->product;
        }
        try {
            $layout = $this->getLayout();
        } catch (LocalizedException $e) {
            $this->product = $this->productFactory->create();
            return $this->product;
        }
        /** @var AdditionalBlockInfo $block */
        $block = $layout->getBlock('additional.product.info');
        if ($block instanceof AdditionalBlockInfo) {
            $item = $block->getItem();
            $this->product = $item;
        }
        return $this->product;
    }
}
