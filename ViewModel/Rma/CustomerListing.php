<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Tracking\Result\Status;
use Netresearch\ShippingCore\Api\Data\ReturnShipment\DocumentInterface;
use Netresearch\ShippingCore\Api\Data\ReturnShipment\TrackInterface;
use Netresearch\ShippingCore\Api\ReturnShipment\DocumentDownloadInterface;
use Netresearch\ShippingCore\Model\ResourceModel\ReturnShipment\TrackCollection;
use Netresearch\ShippingCore\Model\ResourceModel\ReturnShipment\TrackCollectionFactory;

/**
 * View model class for displaying the return labels listing in customer account.
 */
class CustomerListing implements ArgumentInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var TrackCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var DocumentDownloadInterface
     */
    private $download;

    /**
     * @var Collection
     */
    private $trackCollection;

    public function __construct(
        Session $customerSession,
        TrackCollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        CarrierFactory $carrierFactory,
        DocumentDownloadInterface $download
    ) {
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->carrierFactory = $carrierFactory;
        $this->download = $download;

        $this->trackCollection = null;
    }

    public function getTrackCollection(): Collection
    {
        if (!$this->trackCollection instanceof TrackCollection) {
            $customerId = (int) $this->customerSession->getCustomerId();
            $this->trackCollection = $this->collectionFactory->create();
            $this->trackCollection->setOrder(TrackInterface::CREATED_AT, Collection::SORT_ORDER_DESC);
            $this->trackCollection->setCustomerIdFilter($customerId);
        }

        return $this->trackCollection;
    }

    public function getOrderIncrementId(DataObject $track): string
    {
        return $track->getData('order_increment_id');
    }

    public function getOrderViewUrl(TrackInterface $track): string
    {
        return $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $track->getOrderId()]);
    }

    public function getTrackingLink(TrackInterface $track): string
    {
        $carrier = $this->carrierFactory->create($track->getCarrierCode());
        if ($carrier instanceof AbstractCarrierOnline) {
            $trackingInfo = $carrier->getTrackingInfo($track->getTrackNumber());
            if ($trackingInfo instanceof Status && $trackingInfo->getData('url')) {
                return sprintf(
                    '<a href="%s" target="_blank" rel="noreferrer">%s</a>',
                    $trackingInfo->getData('url'),
                    $track->getTrackNumber()
                );
            }
        }

        return $track->getTrackNumber();
    }

    public function getDownloadUrl(DocumentInterface $document, TrackInterface $track): string
    {
        return $this->download->getUrl($document, $track);
    }
}
