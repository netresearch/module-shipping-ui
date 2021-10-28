<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Rma\Info\Button;

use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Netresearch\ShippingCore\Api\ReturnShipment\CanCreateReturnInterface;

/**
 * View model class for adding a returns button to the order info view.
 */
class Returns implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CanCreateReturnInterface
     */
    private $canCreateReturn;

    /**
     * @var SessionManagerInterface|Session
     */
    private $customerSession;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        Registry $registry,
        CanCreateReturnInterface $canCreateReturn,
        SessionManagerInterface $customerSession,
        UrlInterface $urlBuilder
    ) {
        $this->registry = $registry;
        $this->canCreateReturn = $canCreateReturn;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get URL to returns form.
     *
     * @return string
     */
    public function getReturnCreateUrl(): string
    {
        $order = $this->registry->registry('current_order');
        if (!$order instanceof OrderInterface) {
            return '';
        }

        if (!$this->canCreateReturn->execute($order)) {
            return '';
        }

        if ($this->customerSession->isLoggedIn()) {
            $routePath = 'nrshipping/rma/create';
        } else {
            $routePath = 'nrshipping/rma_create/guest';
        }

        return $this->urlBuilder->getUrl($routePath, ['order_id' => (int) $order->getEntityId()]);
    }
}
