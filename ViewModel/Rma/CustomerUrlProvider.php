<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma;

use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Netresearch\ShippingCore\Api\Util\OrderProviderInterface;

/**
 * Page action url provider for storefront.
 */
class CustomerUrlProvider
{
    /**
     * @var OrderProviderInterface
     */
    private $orderProvider;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        OrderProviderInterface $orderProvider,
        SessionManagerInterface $customerSession,
        UrlInterface $urlBuilder
    ) {
        $this->orderProvider = $orderProvider;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
    }

    public function getBackUrl(): string
    {
        if ($this->customerSession->isLoggedIn()) {
            $routePath = 'sales/order/view';
        } else {
            $routePath = 'sales/guest/view';
        }

        return $this->urlBuilder->getUrl(
            $routePath,
            ['order_id' => (int) $this->orderProvider->getOrder()->getEntityId()]
        );
    }

    public function getSubmitUrl(): string
    {
        if ($this->customerSession->isLoggedIn()) {
            $routePath = 'nrshipping/rma/label';
        } else {
            $routePath = 'nrshipping/rma_label/guest';
        }

        return $this->urlBuilder->getUrl(
            $routePath,
            ['order_id' => (int) $this->orderProvider->getOrder()->getEntityId()]
        );
    }
}
