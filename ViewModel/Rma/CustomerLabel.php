<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma;

use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Netresearch\ShippingCore\Api\Data\ReturnShipment\DocumentInterface;
use Netresearch\ShippingCore\Api\Data\ReturnShipment\TrackInterface;
use Netresearch\ShippingCore\Api\ReturnShipment\CurrentTrackInterface;
use Netresearch\ShippingCore\Api\ReturnShipment\DocumentDownloadInterface;
use Netresearch\ShippingCore\Api\Util\OrderProviderInterface;

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
     * @var CurrentTrackInterface
     */
    private $trackProvider;

    /**
     * @var DocumentDownloadInterface
     */
    private $download;

    public function __construct(
        AssetRepository $assetRepository,
        OrderProviderInterface $orderProvider,
        CurrentTrackInterface $trackProvider,
        DocumentDownloadInterface $download
    ) {
        $this->assetRepository = $assetRepository;
        $this->orderProvider = $orderProvider;
        $this->trackProvider = $trackProvider;
        $this->download = $download;
    }

    /**
     * @return DocumentInterface[]
     */
    public function getDocuments(): array
    {
        $track = $this->trackProvider->get();
        if (!$track instanceof TrackInterface) {
            return [];
        }

        return $track->getDocuments();
    }

    /**
     * @return DocumentInterface[]
     */
    public function getPdfDocuments(): array
    {
        return array_filter(
            $this->getDocuments(),
            function (DocumentInterface $document) {
                return ($document->getMediaType() === 'application/pdf');
            }
        );
    }

    /**
     * @return DocumentInterface[]
     */
    public function getPNGDocuments(): array
    {
        return array_filter(
            $this->getDocuments(),
            function (DocumentInterface $document) {
                return ($document->getMediaType() === 'image/png');
            }
        );
    }

    public function getFileName(DocumentInterface $document): string
    {
        return $this->download->getFileName($document, $this->trackProvider->get(), $this->orderProvider->getOrder());
    }

    public function getTrackingNumber(): string
    {
        $track = $this->trackProvider->get();
        if (!$track instanceof TrackInterface) {
            return '';
        }

        return $track->getTrackNumber();
    }

    public function getTrackingUrl(): string
    {
        $trackingNumber = $this->getTrackingNumber();
        if (!$trackingNumber) {
            return '';
        }

        return 'https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html&idc=' . $trackingNumber;
    }

    /**
     * Get the PDF sample image.
     *
     * @return string
     *
     * @todo(nr): This is actually a DHL Paket preview. It would be better to let the carrier provide the preview image.
     */
    public function getPdfSampleImageUrl(): string
    {
        return $this->assetRepository->getUrl('Netresearch_ShippingUi::images/return-label.png');
    }
}
