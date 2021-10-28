<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Block\Adminhtml\Rma\Buttons;

use Magento\Backend\Block\Widget\Button;

class SubmitButton extends Button
{
    protected function _beforeToHtml(): Button
    {
        $this->setData('label', __('Submit'));
        $this->setData('class', 'save primary');
        $this->setData('id', 'save');
        $this->setData('onclick', "document.getElementById('form-rma').submit()");

        return parent::_beforeToHtml();
    }
}
