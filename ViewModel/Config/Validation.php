<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Config;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Netresearch\ShippingCore\Api\Data\Config\ItemValidator\ResultInterface;
use Netresearch\ShippingCore\Api\Data\Config\ValidationResultInterface;

class Validation implements ArgumentInterface
{
    /**
     * @var ValidationResultInterface
     */
    private $result;

    public function __construct(ValidationResultInterface $result)
    {
        $this->result = $result;
    }

    /**
     * @return ResultInterface[]
     */
    public function getResult(): array
    {
        return $this->result->get();
    }
}
