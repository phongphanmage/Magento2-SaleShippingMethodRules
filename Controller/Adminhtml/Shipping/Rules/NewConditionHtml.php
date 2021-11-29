<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;

class NewConditionHtml extends \Vnecoms\ShippingOrderRules\Controller\Adminhtml\Shipping\Rules implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create($type)
                                      ->setId($id)
                                      ->setType($type)
                                      ->setRule( $this->_objectManager->create(\Vnecoms\ShippingOrderRules\Model\OrderRules::class))
                                      ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        $html = '';

        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $this->setJsFormObject($model);
            $html = $model->asHtmlRecursive();
        }


        $this->getResponse()->setBody($html);
    }

    /**
     * Set jsFormObject for the model object
     *
     * @return void
     * @param AbstractCondition $model
     */
    private function setJsFormObject(AbstractCondition $model)
    {
        $requestJsFormName = $this->getRequest()->getParam('form');

        $actualJsFormName = $this->getJsFormObjectName($model->getFormName());
        if ($requestJsFormName === $actualJsFormName) { //new
            $model->setJsFormObject($actualJsFormName);
        }
    }

    /**
     * Get jsFormObject name
     *
     * @param string $formName
     * @return string
     */
    private function getJsFormObjectName($formName)
    {
        return $formName . 'rule_conditions_fieldset_';
    }
}
