<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Vnecoms\ShippingOrderRules\Model\OrderRulesFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

class Edit extends \Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules implements HttpGetActionInterface
{
    /**
     * @var OrderRules
     */
    protected $orderRulesFactory;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    public function __construct(
        OrderRulesFactory $orderRules,
        \Magento\Backend\Model\Session $session,
        Context $context,
        Registry $coreRegistry,
        Date $dateFilter
    )
    {
        $this->orderRulesFactory = $orderRules;
        $this->session = $session;
        parent::__construct($context, $coreRegistry, $dateFilter);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->orderRulesFactory->create();

        if ($id) {
            try {
                $model->load($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('order_rules/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('current_promo_order_rule', $model);

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Order Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getName() : __('New Rule')
        );

        $breadcrumb = $id ? __('Edit Rule') : __('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }
}
