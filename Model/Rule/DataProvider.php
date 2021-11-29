<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\ShippingOrderRules\Model\Rule;

use Vnecoms\OrdeShippingOrderRulesrRules\Model\ResourceModel\OrderRules\Collection;
use Vnecoms\ShippingOrderRules\Model\ResourceModel\OrderRules\CollectionFactory;
use Vnecoms\ShippingOrderRules\Model\OrderRules;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Json $json,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $collectionFactory->create();
        $this->_json = $json;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var OrderRules $rule */
        foreach ($items as $rule) {
            $rule->load($rule->getId());
            $this->loadedData[$rule->getId()] = $rule->getData();
        }

        $data = $this->dataPersistor->get('order_rule');
        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getId()] = $rule->getData();
            $this->dataPersistor->clear('order_rule');
        }
        $data = $this->loadedData;
        if ($data) {
            foreach ($data as $key => $value) {
                if ($data[$key]["website_id"]){
                    $data[$key]["website_ids"] = explode(",", $data[$key]["website_id"]);
                }
                if ($data[$key]["customer_group_id"]){
                    $data[$key]["customer_group_ids"] = explode(",", $data[$key]["customer_group_id"]);
                }

            }
        }
        return $data;
    }

}
