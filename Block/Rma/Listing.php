<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Block\Rma;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Netresearch\ShippingUi\ViewModel\Rma\CustomerListing;

class Listing extends Template
{
    /**
     * @var CustomerListing
     */
    private $viewModel;

    public function __construct(
        Context $context,
        CustomerListing $viewModel,
        array $data = []
    ) {
        $this->viewModel = $viewModel;

        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $collection = $this->viewModel->getTrackCollection();
        $pager = $this->getLayout()->createBlock(Pager::class, 'nrshipping.rma.listing.pager');
        $pager->setCollection($collection);

        $this->setChild('pager', $pager);

        return $this;
    }

    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }
}
