<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Order\Info;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\InputInterface;
use Netresearch\ShippingCore\Api\ShippingSettings\OrderDataProviderInterface;
use Netresearch\ShippingCore\Model\ShippingSettings\ShippingOption\Selection\OrderSelectionManager;

class ServiceSelection implements ArgumentInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderSelectionManager
     */
    private $selectionManager;

    /**
     * @var OrderDataProviderInterface
     */
    private $orderDataProvider;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderSelectionManager $selectionManager,
        OrderDataProviderInterface $orderDataProvider,
        Registry $registry
    ) {
        $this->orderRepository = $orderRepository;
        $this->selectionManager = $selectionManager;
        $this->orderDataProvider = $orderDataProvider;
        $this->registry = $registry;
    }

    public function getOrder(): ?Order
    {
        $order = $this->registry->registry('current_order');
        if ($order instanceof Order) {
            return $order;
        }

        return null;
    }

    private function getDisplayValue(InputInterface $input): string
    {
        switch ($input->getInputType()) {
            case InputInterface::INPUT_TYPE_CHECKBOX:
            case InputInterface::INPUT_TYPE_LOCATION_FINDER:
                return $input->getDefaultValue() ? __('Yes')->render() : __('No')->render();
            case InputInterface::INPUT_TYPE_RADIO:
            case InputInterface::INPUT_TYPE_SELECT:
                foreach ($input->getOptions() as $option) {
                    if ($option->getValue() === $input->getDefaultValue()) {
                        return $option->getLabel();
                    }
                }

                return $input->getDefaultValue();
            case InputInterface::INPUT_TYPE_TEXT:
            case InputInterface::INPUT_TYPE_NUMBER:
            default:
                return $input->getDefaultValue();
        }
    }

    /**
     * Obtain selected service values, grouped by shipping option.
     *
     * Format: [
     *     'label' => (string) Display label of the selected service,
     *     'value' => (string) Display value of the selected service,
     * ]
     *
     * @param int $orderId
     * @return string[][]
     */
    public function getValues(int $orderId): array
    {
        $values = [];

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress || !$shippingAddress->getId()) {
            // no valid shipping address for given order, no services to display.
            return $values;
        }

        $selections = $this->selectionManager->load((int)$shippingAddress->getId());
        if (empty($selections)) {
            // no services selected for this shipping address.
            return $values;
        }

        $carrierData = $this->orderDataProvider->getShippingOptions($order);
        if (empty($carrierData)) {
            // no shipping settings defined for the given order's carrier.
            return $values;
        }

        $serviceOptions = $carrierData->getServiceOptions() ?? [];
        foreach ($selections as $selection) {
            $shippingOptionCode = $selection->getShippingOptionCode();

            $serviceOption = $serviceOptions[$shippingOptionCode] ?? null;
            if (!$serviceOption) {
                // the selected service is no longer defined in shipping settings, cannot determine labels.
                continue;
            }

            $serviceOptionInputs = $serviceOption->getInputs();
            $input = $serviceOptionInputs[$selection->getInputCode()] ?? null;
            if (!$input instanceof InputInterface || $input->getInputType() === 'hidden') {
                // input no longer defined or a hidden field. not to be displayed.
                continue;
            }

            $values[$shippingOptionCode]['label'] = $serviceOption->getLabel();
            $values[$shippingOptionCode]['value'] = isset($values[$shippingOptionCode]['value'])
                ? implode(', ', [$values[$shippingOptionCode]['value'], $this->getDisplayValue($input)])
                : $this->getDisplayValue($input);
        }

        return array_values($values);
    }
}
