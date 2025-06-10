<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Netresearch\ShippingCore\Api\Config\CarrierConfigInterface;
use Netresearch\ShippingCore\Api\SplitAddress\RecipientStreetLoaderInterface;
use Netresearch\ShippingCore\Api\Util\OrderProviderInterface;
use Netresearch\ShippingCore\Model\BulkShipment\ReturnShipmentConfiguration;
use Netresearch\ShippingUi\Block\Form\Element\CountrySelect;

/**
 * View model class for creating a return shipment in admin panel.
 */
class AdminForm extends AbstractForm implements ArgumentInterface
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var AdminUrlProvider
     */
    private $urlProvider;

    public function __construct(
        OrderProviderInterface $orderProvider,
        RecipientStreetLoaderInterface $recipientStreetLoader,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        ShipmentRepositoryInterface $shipmentRepository,
        DirectoryHelper $directoryHelper,
        CarrierConfigInterface $carrierConfig,
        ReturnShipmentConfiguration $returnShipmentConfig,
        LayoutInterface $layout,
        AdminUrlProvider $urlProvider
    ) {
        $this->layout = $layout;
        $this->urlProvider = $urlProvider;

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
     * Obtain the country select markup for use in admin panel.
     *
     * @param string $defaultCountry
     * @param string $name
     * @return string
     * @throws LocalizedException
     */
    #[\Override]
    public function getCountrySelectHtml(string $defaultCountry = '', string $name = 'country_id'): string
    {
        /** @var CountrySelect $block */
        $block = $this->layout->createBlock(CountrySelect::class);
        $selectBlock = $block->getCountrySelect($defaultCountry, $name);
        $selectBlock->setClass('admin__control-select required-entry');
        return $selectBlock->getHtml();
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
}
