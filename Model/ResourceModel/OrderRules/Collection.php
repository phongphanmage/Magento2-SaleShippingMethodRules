<?php

namespace Vnecoms\ShippingOrderRules\Model\ResourceModel\OrderRules;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Serialize\Serializer\Json;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'rule_id';

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var 
     */
    protected $dateApplier;

    /**
     * @var 
     */
    protected $_date;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory, \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->serializer = $json;
        $this->_date = $timezone;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\ShippingOrderRules\Model\OrderRules', 'Vnecoms\ShippingOrderRules\Model\ResourceModel\OrderRules');
    }


    /**
     * Filter collection by specified website, customer group, coupon code, date.
     * Filter collection to use only active rules.
     * Involved sorting by sort_order column.
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string|null $now
     * @throws \Zend_Db_Select_Exception
     * @use $this->addWebsiteGroupDateFilter()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return \Vnecoms\ShippingOrderRules\Model\ResourceModel\OrderRules\Collection
     */
    public function setValidationFilter(
        $websiteId,
        $customerGroupId,
        $now = null
    ) {
        if (!$this->getFlag('validation_filter')) {
            $this->prepareSelect($websiteId, $customerGroupId, $now);
            $this->setOrder('sort_order', self::SORT_ORDER_ASC);
            $this->setFlag('validation_filter', true);
        }

        return $this;
    }

    /**
     * Recreate the default select object for specific needs of salesrule evaluation with coupon codes.
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $now
     */
    private function prepareSelect($websiteId, $customerGroupId, $now)
    {
        $this->getSelect()->reset();
        parent::_initSelect();
        $this->addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now);
    }

    /**
     * Filter collection by website(s), customer group(s) and date.
     * Filter collection to only active rules.
     * Sorting is not involved
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string|null $now
     * @use $this->addWebsiteFilter()
     * @return \Vnecoms\ShippingOrderRules\Model\ResourceModel\OrderRules\Collection
     */
    public function addWebsiteGroupDateFilter($websiteId, $customerGroupId, $now = null)
    {
        if (!$this->getFlag('website_group_date_filter')) {
            if ($now === null) {
                $now = $this->_date->date()->format('Y-m-d');
            }

            $this->addFieldToFilter("website_id", ['finset' => $websiteId]);
            $this->addFieldToFilter("customer_group_id", ['finset' => $customerGroupId]);

            $this->getDateApplier()->applyDate($this->getSelect(), $now);

            $this->addIsActiveFilter();

            $this->setFlag('website_group_date_filter', true);
        }

        return $this;
    }

    /**
     * @param null $flag
     * @return $this
     */
    public function addWebsitesToResult($flag = null)
    {
        $flag = $flag === null ? true : $flag;
        $this->setFlag('add_websites_to_result', $flag);
        return $this;
    }

    /**
     * @param $attributeCode
     * @return $this
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr($this->serializer->serialize(['attribute' => $attributeCode]), 1, -1));

        $this->addFieldToFilter('conditions_serialized', ['like' => $match]);

        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function addFilterSerialize($field, $value)
    {
        $this->addFieldToFilter($field, ['like' => '%"'. $value .'"%']);
        return $this;
    }

    /**
     * Getter for dateApplier property
     *
     * @return DateApplier
     * @deprecated 100.1.0
     */
    private function getDateApplier()
    {
        if (null === $this->dateApplier) {
            $this->dateApplier = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\SalesRule\Model\ResourceModel\Rule\DateApplier::class);
        }

        return $this->dateApplier;
    }

    /**
     * @param int $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive = 1)
    {
        if (!$this->getFlag('is_active_filter')) {
            $this->addFieldToFilter('is_active', (int)$isActive ? 1 : 0);
            $this->setFlag('is_active_filter', true);
        }
        return $this;
    }
}
