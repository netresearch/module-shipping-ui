<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Block\AdditionalFee;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\Totals;
use Magento\Sales\Model\Order;
use Netresearch\ShippingCore\Model\AdditionalFee\Total as TotalModel;
use Netresearch\ShippingUi\ViewModel\Order\Totals\AdditionalFee;

/**
 * Block for displaying the total in admin and customer view
 */
class Total extends Template
{
    protected $_template = 'Netresearch_ShippingUi::additional_fee/total.phtml';

    /**
     * @var AdditionalFee
     */
    private $viewModel;

    public function __construct(Context $context, AdditionalFee $viewModel, array $data = [])
    {
        $this->viewModel = $viewModel;
        parent::__construct($context, $data);
    }

    /**
     * Register this block for service charge total rendering.
     *
     * @return $this
     */
    public function initTotals(): self
    {
        $fee = (float) $this->getParentBlock()->getSource()->getData(TotalModel::SERVICE_CHARGE_TOTAL_CODE);
        if (abs($fee) > 0) {
            /** @var Totals $parentBlock */
            $parentBlock = $this->getParentBlock();
            $parentBlock->addTotalBefore(
                new DataObject(
                    [
                        'block_name' => $this->getNameInLayout(),
                        'code' => TotalModel::SERVICE_CHARGE_TOTAL_CODE,
                    ]
                ),
                'tax'
            );
        }

        return $this;
    }

    /**
     * @return Order|Order\Invoice|Order\Creditmemo
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return AdditionalFee
     */
    public function getViewModel(): AdditionalFee
    {
        return $this->viewModel;
    }
}
