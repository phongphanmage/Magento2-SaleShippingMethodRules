<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Model\Rule\Condition;

/**
 * @api
 * @since 100.0.2
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $_productFactory;

    /**
     * Combine constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_productFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType(\Vnecoms\ShippingOrderRules\Model\Rule\Condition\Combine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Magento\CatalogRule\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => \Magento\CatalogRule\Model\Rule\Condition\Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );

        /*
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                    'label' => __('Product attribute combination'),
                ],
                [
                    'value' => \Magento\SalesRule\Model\Rule\Condition\Product\Subselect::class,
                    'label' => __('Products subselection')
                ],
                [
                    'value' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                    'label' => __('Conditions combination')
                ],
            ]
        ); */

        $additional = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch('orderrule_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }
        return $conditions;
    }

}
