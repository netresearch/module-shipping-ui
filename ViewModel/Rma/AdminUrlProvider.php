<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma;

use Magento\Framework\UrlInterface;
use Netresearch\ShippingCore\Api\Util\OrderProviderInterface;

/**
 * Page action url provider for admin panel.
 */
class AdminUrlProvider
{
    /**
     * @var OrderProviderInterface
     */
    private $orderProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        OrderProviderInterface $orderProvider,
        UrlInterface $urlBuilder
    ) {
        $this->orderProvider = $orderProvider;
        $this->urlBuilder = $urlBuilder;
    }

    public function getBackUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'sales/order/view',
            ['order_id' => (int) $this->orderProvider->getOrder()->getEntityId()]
        );
    }

    public function getSubmitUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'nrshipping/order_rma/save',
            ['order_id' => (int) $this->orderProvider->getOrder()->getEntityId()]
        );
    }
}
