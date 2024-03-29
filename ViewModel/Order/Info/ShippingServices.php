<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Order\Info;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\InputInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\Selection\AssignedSelectionInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOption\Selection\SelectionInterface;
use Netresearch\ShippingCore\Api\Data\ShippingSettings\ShippingOptionInterface;
use Netresearch\ShippingCore\Api\ShippingSettings\OrderDataProviderInterface;
use Netresearch\ShippingCore\Model\ShippingSettings\ShippingOption\Codes;
use Netresearch\ShippingCore\Model\ShippingSettings\ShippingOption\Selection\OrderSelectionRepository;

/**
 * @deprecated obsolete, replaced
 *
 * @see ServiceSelection
 * @see DeliveryLocation
 */
class ShippingServices implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var OrderDataProviderInterface
     */
    private $orderDataProvider;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderSelectionRepository
     */
    private $selectionRepository;

    /**
     * @var Order|null
     */
    private $order;

    /**
     * @var ShippingOptionInterface[]|null
     */
    private $serviceOptions;

    /**
     * @var SelectionInterface[]|null
     */
    private $selections;

    /**
     * @var string[]|null
     */
    private $pickupLocationAddress;

    public function __construct(
        Registry $registry,
        OrderDataProviderInterface $orderDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        OrderSelectionRepository $selectionRepository
    ) {
        $this->registry = $registry;
        $this->orderDataProvider = $orderDataProvider;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->selectionRepository = $selectionRepository;
    }

    /**
     * @return string[][]
     *      [
     *          'label' => (string) Display label of the selected service,
     *          'value' => (string) Display value of the selected service,
     *      ]
     */
    public function getSelectedServices(): array
    {
        $shippingAddress = $this->getOrder()->getShippingAddress();
        if (!$shippingAddress || !$shippingAddress->getId()) {
            return [];
        }

        $selections = $this->getSelections($shippingAddress);
        $serviceOptions = $this->getServiceOptions();
        $result = [];
        foreach ($selections as $selection) {
            $shippingOptionCode = $selection->getShippingOptionCode();
            $serviceOption = $serviceOptions[$shippingOptionCode];
            if (!$serviceOption) {
                continue;
            }

            $input = $serviceOption->getInputs()[$selection->getInputCode()] ?? null;
            if (!$input || $input->getInputType() === 'hidden') {
                continue;
            }

            $displayValue = $this->renderDisplayValue(
                $input,
                $result[$shippingOptionCode]['value'] ?? ''
            );

            $result[$shippingOptionCode] = [
                'label' => $serviceOption->getLabel(),
                'value' => $displayValue,
            ];
        }

        return $result;
    }

    /**
     * @param int $orderId
     * @return string[][]
     *      [
     *          'label' => (string) Display label of the selected service,
     *          'value' => (string) Display value of the selected service,
     *      ]
     */
    public function getSelectedServicesByOrderId(int $orderId): array
    {
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        $this->order = $order;

        return $this->getSelectedServices();
    }

    /**
     * @return string[] The address of the selected pickup location split by lines, or an empty array.
     */
    public function getPickupLocationAddress(): array
    {
        if ($this->pickupLocationAddress !== null) {
            return $this->pickupLocationAddress;
        }

        $this->pickupLocationAddress = [];

        $inputs = $this->getLocationFinderInputs();

        if (isset($inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_COMPANY])) {
            $this->pickupLocationAddress[] = $inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_COMPANY]->getDefaultValue();
        }
        if (isset($inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_STREET])) {
            $this->pickupLocationAddress[] = $inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_STREET]->getDefaultValue();
        }
        if (isset($inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_POSTAL_CODE], $inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_CITY])) {
            $this->pickupLocationAddress[] = implode(' ', [
                $inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_POSTAL_CODE]->getDefaultValue(),
                $inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_CITY]->getDefaultValue()
            ]);
        }
        if (isset($inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_COUNTRY_CODE])) {
            $this->pickupLocationAddress[] = $inputs[Codes::SERVICE_INPUT_DELIVERY_LOCATION_COUNTRY_CODE]->getDefaultValue();
        }

        return $this->pickupLocationAddress;
    }

    /**
     * @param Order\Address $shippingAddress
     * @return SelectionInterface[]
     */
    private function getSelections(Order\Address $shippingAddress): array
    {
        if ($this->selections === null) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(
                    AssignedSelectionInterface::PARENT_ID,
                    $shippingAddress->getId()
                )->create();
            $this->selections = $this->selectionRepository->getList($searchCriteria)->getItems();
        }

        return $this->selections;
    }

    /**
     * Render a selection value label from the available options and other information
     *
     * @param InputInterface $input
     * @param string $existingValue
     * @return string
     */
    private function renderDisplayValue(
        InputInterface $input,
        $existingValue
    ): string {
        $displayValue = $input->getDefaultValue();

        foreach ($input->getOptions() as $option) {
            if ($option->getValue() === $input->getDefaultValue()) {
                $displayValue = $option->getLabel();
            }
        }

        if ($input->getDefaultValue() === '1') {
            $displayValue = __('Yes')->render();
        }

        /**
         * If a previous selection already added a value here, append the new value.
         */
        if ($existingValue) {
            $displayValue = implode(', ', [$existingValue, $displayValue]);
        }

        return $displayValue;
    }

    /**
     * The only way to find all inputs that belong to a shop finder
     * is to search for a ShippingOption with an input of type
     * 'locationfinder' and return all of its inputs.
     *
     * @return InputInterface[]
     */
    private function getLocationFinderInputs(): array
    {
        $shippingAddress = $this->getOrder()->getShippingAddress();
        if (!$shippingAddress || !$shippingAddress->getId()) {
            return [];
        }

        $serviceOptions = $this->getServiceOptions();
        $selections = $this->getSelections($shippingAddress);

        foreach ($selections as $selection) {
            $serviceOption = $serviceOptions[$selection->getShippingOptionCode()];
            $hasLocationFinderInputs = static function (InputInterface $input) {
                return $input->getInputType() === Codes::INPUT_TYPE_LOCATION_FINDER;
            };

            if ($serviceOption && array_filter($serviceOption->getInputs(), $hasLocationFinderInputs)) {
                return $serviceOption->getInputs();
            }
        }

        return [];
    }

    /**
     * @return ShippingOptionInterface[]
     */
    private function getServiceOptions(): array
    {
        if ($this->serviceOptions !== null) {
            return $this->serviceOptions;
        }

        $this->serviceOptions = [];

        $shippingAddress = $this->getOrder()->getShippingAddress();
        if (!$shippingAddress || !$shippingAddress->getId()) {
            return $this->serviceOptions;
        }

        $carrierData = $this->orderDataProvider->getShippingOptions($this->getOrder());
        if ($carrierData === null) {
            return $this->serviceOptions;
        }

        $this->serviceOptions = $carrierData->getServiceOptions() ?? [];

        return $this->serviceOptions;
    }

    private function getOrder(): Order
    {
        if (!$this->order) {
            $this->order = $this->registry->registry('current_order');
        }
        return $this->order;
    }
}
