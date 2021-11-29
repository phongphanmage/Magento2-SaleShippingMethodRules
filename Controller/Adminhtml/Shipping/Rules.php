<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend Catalog Price Rules controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

abstract class Rules extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Vnecoms_ShippingOrderRules::shipping_order_rules';

    /**
     * Dirty rules notice message
     *
     *
     * @var string
     */
    protected $_dirtyRulesNoticeMessage;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Date filter instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     */
    public function __construct(Context $context, Registry $coreRegistry, Date $dateFilter)
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_dateFilter = $dateFilter;
    }

    /**
     * Init action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Vnecoms_Vendors::marketplace'
        )->_addBreadcrumb(
            __('Shipping Order Rules'),
            __('Shipping Order Rules')
        );
        return $this;
    }
}
