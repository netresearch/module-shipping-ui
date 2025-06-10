<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductRepository;
use Magento\Directory\Block\Data as DirectoryBlock;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment\Item;
use Magento\Shipping\Block\Items;
use Netresearch\ShippingCore\Api\Config\CarrierConfigInterface;
use Netresearch\ShippingCore\Api\SplitAddress\RecipientStreetLoaderInterface;
use Netresearch\ShippingCore\Api\Util\OrderProviderInterface;
use Netresearch\ShippingCore\Model\BulkShipment\ReturnShipmentConfiguration;

/**
 * View model class for creating a return shipment in storefront.
 */
class CustomerForm extends AbstractForm implements ArgumentInterface
{
    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var CustomerUrlProvider
     */
    private $urlProvider;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Image
     */
    private $imageHelper;

    public function __construct(
        OrderProviderInterface $orderProvider,
        RecipientStreetLoaderInterface $recipientStreetLoader,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        ShipmentRepositoryInterface $shipmentRepository,
        DirectoryHelper $directoryHelper,
        CarrierConfigInterface $carrierConfig,
        ReturnShipmentConfiguration $returnShipmentConfig,
        CustomerUrlProvider $urlProvider,
        LayoutInterface $layout,
        ProductRepository $productRepository,
        Image $imageHelper
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->urlProvider = $urlProvider;
        $this->layout = $layout;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;

        parent::__construct(
            $orderProvider,
            $recipientStreetLoader,
            $searchCriteriaBuilderFactory,
            $filterBuilder,
            $shipmentRepository,
            $directoryHelper,
            $carrierConfig,
            $returnShipmentConfig
        );
    }

    /**
     * Obtain the country select markup, optionally initialized with given country.
     *
     * @param string $defaultCountry
     * @param string $name
     * @return string
     */
    #[\Override]
    public function getCountrySelectHtml(string $defaultCountry = '', string $name = 'country_id'): string
    {
        /** @var DirectoryBlock $block */
        $block = $this->layout->createBlock(DirectoryBlock::class);
        return $block->getCountryHtmlSelect($defaultCountry, $name);
    }

    #[\Override]
    public function getBackUrl(): string
    {
        return $this->urlProvider->getBackUrl();
    }

    #[\Override]
    public function getSubmitUrl(): string
    {
        return $this->urlProvider->getSubmitUrl();
    }

    public function getViewUrl(): string
    {
        return $this->urlProvider->getViewUrl();
    }

    /**
     * Obtain countries for which postal code is optional as JSON data.
     *
     * @return string
     */
    public function getCountriesWithOptionalZipJson(): string
    {
        return $this->directoryHelper->getCountriesWithOptionalZip(true);
    }

    /**
     * Obtain the preview image URL for the given shipment item.
     *
     * @param ShipmentItemInterface|Item $item
     * @return string
     */
    public function getProductThumbnailUrl(ShipmentItemInterface $item): string
    {
        try {
            $product = $this->productRepository->get($item->getSku());
        } catch (NoSuchEntityException) {
            $product = $item->getOrderItem()->getProduct();
        }

        if (!$product) {
            return $this->imageHelper->getDefaultPlaceholderUrl();
        }

        return $this->imageHelper->init($product, 'cart_page_product_thumbnail')->getUrl();
    }

    /**
     * Obtain item details (name, sku, options) markup for the given shipment item.
     *
     * @param ShipmentItemInterface|Item $item
     * @return string
     */
    public function getItemDetailsHtml(ShipmentItemInterface $item): string
    {
        /** @var Items $block */
        $block = $this->layout->createBlock(
            Items::class,
            '',
            ['data' => ['renderer_list_name' => 'sales.order.shipment.renderers', 'viewModel' => $this]]
        );

        return $block->getItemHtml($item);
    }
}
