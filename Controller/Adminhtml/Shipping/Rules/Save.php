<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Save action for catalog rule
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules implements HttpPostActionInterface
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var \Ecommage\OrderRules\Model\OrderRules
     */
    protected $orderRules;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @param DataPersistorInterface $dataPersistor
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Date $dateFilter,
        DataPersistorInterface $dataPersistor,
        TimezoneInterface $localeDate,
        Json $json,
        \Vnecoms\ShippingOrderRules\Model\OrderRules $orderRules,
        \Magento\Backend\Model\Session $session,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->dataPersistor = $dataPersistor;
        $this->localeDate = $localeDate;
        $this->json = $json;
        $this->orderRules = $orderRules;
        $this->session = $session;
        $this->logger = $logger;
        parent::__construct($context, $coreRegistry, $dateFilter);
    }

    /**
     * Execute save action from catalog rule
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            /** @var \Ecommage\OrderRules\Model\OrderRules $model */
            $model = $this->orderRules;
            try {
                $this->_eventManager->dispatch(
                    'adminhtml_controller_orderrule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPostValue();

                $data["website_id"] = implode(",", $data["website_ids"]);
                $data["customer_group_id"] = implode(",", $data["customer_group_ids"]);
                $data["shipping_method"] = implode(",", $data["shipping_method"]);
                if (!$this->getRequest()->getParam('from_date')) {
                    $data['from_date'] = $this->localeDate->formatDate();
                }
                $filterValues = ['from_date' => $this->_dateFilter];
                if ($this->getRequest()->getParam('to_date')) {
                    $filterValues['to_date'] = $this->_dateFilter;
                }
                $inputFilter = new \Zend_Filter_Input(
                    $filterValues,
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('rule_id');
                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->dataPersistor->set('order_rule', $data);
                    $this->_redirect('vendors/*/edit', ['id' => $model->getId()]);
                    return;
                }

                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }

                unset($data['conditions_serialized']);
                if ($id) {
                    $model = $model->load($id);
                } elseif (!$id) {
                    unset($data['rule_id']);
                    $model->setData($data)->save();
                }

                $model->loadPost($data);

                $this->session->setPageData($data);
                $this->dataPersistor->set('order_rule', $data);
                $model->setData($model->getData())->save();
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->session->setPageData(false);
                $this->dataPersistor->clear('order_rule');

                if ($this->getRequest()->getParam('auto_apply')) {
                    $this->getRequest()->setParam('rule_id', $model->getId());
                    $this->_forward('applyRules');
                }
                if (array_key_exists("back",$this->getRequest()->getParams())) {
                    $this->_redirect('vendors/*/edit', ['id' => $model->getId()]);
                    return;
                }
                return $this->_redirect('vendors/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->session->setPageData($data);
                $this->dataPersistor->set('order_rule', $data);
                $this->_redirect('vendors/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('vendors/*/');
    }

}
