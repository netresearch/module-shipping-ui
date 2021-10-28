<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma;

use Netresearch\ShippingCore\Api\Util\LabelDataProviderInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Netresearch\ShippingCore\Api\Util\OrderProviderInterface;
use Netresearch\ShippingCore\Model\Util\CarrierDataProvider;

/**
 * View model class for displaying return shipment label data.
 */
class CustomerLabel implements ArgumentInterface
{
    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var OrderProviderInterface
     */
    private $orderProvider;

    /**
     * @var LabelDataProviderInterface
     */
    private $labelDataProvider;

    public function __construct(
        AssetRepository $assetRepository,
        OrderProviderInterface $orderProvider,
        LabelDataProviderInterface $labelDataProvider
    ) {
        $this->assetRepository = $assetRepository;
        $this->orderProvider = $orderProvider;
        $this->labelDataProvider = $labelDataProvider;
    }

    /**
     * @param string $fileExt
     * @return string
     */
    public function getFileName(string $fileExt): string
    {
        $filename = sprintf(
            '%s-%s-(%s).%s',
            $this->orderProvider->getOrder()->getStore()->getFrontendName(),
            $this->orderProvider->getOrder()->getRealOrderId(),
            $this->getTrackingNumber(),
            $fileExt
        );

        return str_replace(' ', '_', $filename);
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        $labelResponse = $this->labelDataProvider->getLabelResponse();
        return $labelResponse ? $labelResponse->getTrackingNumber() : '';
    }

    /**
     * @return string
     */
    public function getTrackingUrl(): string
    {
        $trackingNumber = $this->getTrackingNumber();
        if (!$trackingNumber) {
            return '';
        }

        return 'https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html&idc=' . $trackingNumber;
    }

    /**
     * @return string
     */
    public function getShippingLabel(): string
    {
        $labelResponse = $this->labelDataProvider->getLabelResponse();
        foreach ($labelResponse->getDocuments() as $document) {
            if ($document->getMimeType() === 'application/pdf') {
                return base64_encode($document->getLabelData());
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getQrLabel(): string
    {
        $labelResponse = $this->labelDataProvider->getLabelResponse();
        foreach ($labelResponse->getDocuments() as $document) {
            if ($document->getMimeType() === 'image/png') {
                return base64_encode($document->getLabelData());
            }
        }

        return '';
    }

    /**
     * Get the PDF sample image.
     *
     * @return string
     */
    public function getPdfSampleImage(): string
    {
        return $this->assetRepository->getUrl('Netresearch_ShippingUi::images/return-label.png');
    }
}
