<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Block\Rma;

use Magento\Customer\Block\Account\SortLink;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;
use Netresearch\ShippingUi\ViewModel\Rma\CustomerListing;

class Link extends SortLink
{
    /**
     * @var CustomerListing
     */
    private $viewModel;

    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        CustomerListing $viewModel,
        array $data = []
    ) {
        $this->viewModel = $viewModel;

        parent::__construct($context, $defaultPath, $data);
    }

    #[\Override]
    protected function _toHtml(): string
    {
        if (!$this->viewModel->getTrackCollection()->getSize()) {
            return '';
        }

        return parent::_toHtml();
    }
}
