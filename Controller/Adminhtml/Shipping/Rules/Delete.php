<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Vnecoms\ShippingOrderRules\Model\OrderRules;

class Delete extends \Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules implements HttpPostActionInterface
{
    /**
     * @var OrderRules
     */
    protected $orderRules;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @param OrderRules $orderRules
     */
    public function __construct(Context $context, Registry $coreRegistry, Date $dateFilter, OrderRules $orderRules)
    {
        parent::__construct($context, $coreRegistry, $dateFilter);
        $this->orderRules = $orderRules;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $orderRule = $this->orderRules->load($id);
                $orderRule->delete();

                $this->messageManager->addSuccessMessage(__('You deleted the rule.'));
                $this->_redirect('vendors/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete this rule right now. Please review the log and try again.')
                );
                $this->_redirect('vendors/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule to delete.'));
        $this->_redirect('vendors/*/');
    }
}
