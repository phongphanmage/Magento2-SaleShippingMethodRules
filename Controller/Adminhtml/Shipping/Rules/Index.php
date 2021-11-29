<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules implements HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Shipping'), __('Shipping'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Shipping Cart Rules'));
        $this->_view->renderLayout();
    }
}
