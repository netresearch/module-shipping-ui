<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Order\Info;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Netresearch\ShippingCore\Model\ShippingSettings\ShippingOption\Codes;
use Netresearch\ShippingCore\Model\ShippingSettings\ShippingOption\Selection\OrderSelectionManager;

class DeliveryLocation implements ArgumentInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderSelectionManager
     */
    private $selectionManager;

    public function __construct(OrderRepositoryInterface $orderRepository, OrderSelectionManager $selectionManager)
    {
        $this->orderRepository = $orderRepository;
        $this->selectionManager = $selectionManager;
    }

    /**
     * Read address details from the order's service selection.
     *
     * @param int $orderId
     * @return string[]
     */
    public function getAddressLines(int $orderId): array
    {
        $order = $this->orderRepository->get($orderId);
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress || !$shippingAddress->getId()) {
            return [];
        }

        $selections = $this->selectionManager->load((int) $shippingAddress->getId());

        // find all delivery location selections.
        $values = [];
        foreach ($selections as $selection) {
            if ($selection->getShippingOptionCode() !== Codes::SERVICE_OPTION_DELIVERY_LOCATION) {
                continue;
            }

            $values[$selection->getInputCode()] = $selection->getInputValue();
        }

        // check if the service was enabled.
        if (empty($values['enabled'])) {
            return [];
        }

        // define fields and sort order
        $lines = [
            Codes::SERVICE_INPUT_DELIVERY_LOCATION_COMPANY => '',
            Codes::SERVICE_INPUT_DELIVERY_LOCATION_STREET => '',
            Codes::SERVICE_INPUT_DELIVERY_LOCATION_POSTAL_CODE => '',
            Codes::SERVICE_INPUT_DELIVERY_LOCATION_CITY => '',
            Codes::SERVICE_INPUT_DELIVERY_LOCATION_COUNTRY_CODE => '',
        ];

        $values = array_merge($lines, $values); // sort
        $values = array_intersect_key($values, $lines); // keep only address lines

        return array_filter($values);
    }
}
