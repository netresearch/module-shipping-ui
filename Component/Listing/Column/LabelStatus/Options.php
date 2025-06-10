<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Component\Listing\Column\LabelStatus;

use Magento\Framework\Data\OptionSourceInterface;
use Netresearch\ShippingCore\Api\LabelStatus\LabelStatusManagementInterface;

class Options implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    #[\Override]
    public function toOptionArray(): array
    {
        $options = [
            [
                'value' => LabelStatusManagementInterface::LABEL_STATUS_PENDING,
                'label' => __('Pending'),
            ],
            [
                'value' => LabelStatusManagementInterface::LABEL_STATUS_PARTIAL,
                'label' => __('Partial'),
            ],
            [
                'value' => LabelStatusManagementInterface::LABEL_STATUS_PROCESSED,
                'label' => __('Processed'),
            ],
            [
                'value' => LabelStatusManagementInterface::LABEL_STATUS_FAILED,
                'label' => __('Failed'),
            ]
        ];

        return $options;
    }
}
