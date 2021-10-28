<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Netresearch\ShippingCore\Api\Config\CarrierConfigInterface;
use Netresearch\ShippingCore\Api\Data\RecipientStreetInterface;
use Netresearch\ShippingCore\Api\SplitAddress\RecipientStreetLoaderInterface;
use Netresearch\ShippingCore\Api\Util\OrderProviderInterface;
use Netresearch\ShippingCore\Model\BulkShipment\ReturnShipmentConfiguration;

/**
 * Abstract view model class for creating a return shipment.
 */
abstract class AbstractForm implements ArgumentInterface
{
    /**
     * @var OrderProviderInterface
     */
    private $orderProvider;

    /**
     * @var RecipientStreetLoaderInterface
     */
    private $recipientStreetLoader;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var CarrierConfigInterface
     */
    private $carrierConfig;

    /**
     * @var ReturnShipmentConfiguration
     */
    private $returnShipmentConfig;

    public function __construct(
        OrderProviderInterface $orderProvider,
        RecipientStreetLoaderInterface $recipientStreetLoader,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        ShipmentRepositoryInterface $shipmentRepository,
        DirectoryHelper $directoryHelper,
        CarrierConfigInterface $carrierConfig,
        ReturnShipmentConfiguration $returnShipmentConfig
    ) {
        $this->orderProvider = $orderProvider;
        $this->recipientStreetLoader = $recipientStreetLoader;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->shipmentRepository = $shipmentRepository;
        $this->directoryHelper = $directoryHelper;
        $this->carrierConfig = $carrierConfig;
        $this->returnShipmentConfig = $returnShipmentConfig;
    }

    /**
     * Obtain the country select markup, optionally initialized with given country.
     *
     * @param string $defaultCountry
     * @param string $name
     * @return string
     */
    abstract public function getCountrySelectHtml(string $defaultCountry = '', string $name = 'country_id'): string;

    abstract public function getBackUrl(): string;

    abstract public function getSubmitUrl(): string;

    /**
     * Obtain the order's shipments if RMA creation is enabled in module config.
     *
     * @return ShipmentInterface[]
     */
    public function getShipments(): array
    {
        $order = $this->orderProvider->getOrder();
        if (!$order instanceof OrderInterface) {
            return [];
        }

        $orderIdFilter = $this->filterBuilder
            ->setField(ShipmentInterface::ORDER_ID)
            ->setValue($order->getEntityId())
            ->setConditionType('eq')
            ->create();

        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter($orderIdFilter)->create();
        $searchResult = $this->shipmentRepository->getList($searchCriteria);
        return $searchResult->getItems();
    }

    /**
     * Obtain the order's shipping address.
     *
     * @return OrderAddressInterface|null
     */
    public function getAddress(): ?OrderAddressInterface
    {
        $order = $this->orderProvider->getOrder();
        if (!$order instanceof Order) {
            return null;
        }

        return $order->getShippingAddress();
    }

    /**
     * Get split Address for RMA Form.
     *
     * @return RecipientStreetInterface
     */
    public function getRecipientStreet(): RecipientStreetInterface
    {
        $shippingAddress = $this->getAddress();
        return $this->recipientStreetLoader->load($shippingAddress);
    }

    /**
     * Obtain possible regions as JSON data.
     *
     * @return string
     */
    public function getRegionJson(): string
    {
        try {
            return $this->directoryHelper->getRegionJson();
        } catch (NoSuchEntityException $exception) {
            return 'false';
        }
    }

    /**
     * Obtain all carrier codes that are able to create return labels.
     *
     * Note that the list does not (yet) consider the carriers' ability to ship the current order.
     *
     * @return string[]
     */
    public function getCarriers(): array
    {
        $storeId = $this->orderProvider->getOrder()->getStoreId();
        $carrierCodes = $this->returnShipmentConfig->getCarrierCodes();
        $carrierNames = array_map(
            function (string $carrierCode) use ($storeId) {
                return $this->carrierConfig->getTitle($carrierCode, $storeId);
            },
            $carrierCodes
        );

        return array_filter(array_combine($carrierCodes, $carrierNames));
    }
}
