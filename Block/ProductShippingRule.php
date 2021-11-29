<?php
/* File: app/code/Atwix/CatalogAttribute/Block/CartItemBrandBlock.php */
namespace Vnecoms\ShippingOrderRules\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template as ViewTemplate;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * Class CartItemBrandBlock
 */
class ProductShippingRule extends ViewTemplate
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
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * CartItemShippingRule constructor.
     * @param Context $context
     * @param ProductInterfaceFactory $productFactory
     * @param \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        ProductInterfaceFactory $productFactory,
        \Vnecoms\ShippingOrderRules\Model\RulesApplier $rulesApplier,
        \Magento\Customer\Model\Session $customerSession,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->rulesApplier = $rulesApplier;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession     = $customerSession;
    }

    /**
     * @return |null
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShippingRule()
    {
        $product = $this->getProduct();
        if ($product->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            && $product->getProduct()->getShipmentType()
        ) {
            return null;
        }

        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customerGroupId = $this->_customerSession->getCustomerGroupId();

        $string = $this->rulesApplier->getDesFromProduct($product, $websiteId, $customerGroupId);
        if (!$string) return null;
        return $string;
    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }
}
