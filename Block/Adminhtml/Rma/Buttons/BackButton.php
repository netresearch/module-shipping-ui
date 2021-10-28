<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Block\Adminhtml\Rma\Buttons;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Netresearch\ShippingUi\ViewModel\Rma\AdminUrlProvider;

class BackButton extends Button
{
    /**
     * @var AdminUrlProvider
     */
    private $urlProvider;

    public function __construct(
        Context $context,
        AdminUrlProvider $urlProvider,
        array $data = []
    ) {
        $this->urlProvider = $urlProvider;

        parent::__construct($context, $data);
    }

    protected function _beforeToHtml(): Button
    {
        $this->setData('label', __('Back'));
        $this->setData('class', 'back');
        $this->setData('id', 'back');
        $this->setData('onclick', sprintf("setLocation('%s')", $this->urlProvider->getBackUrl()));

        return parent::_beforeToHtml();
    }
}
