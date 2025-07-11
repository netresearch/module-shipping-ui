<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Netresearch\ShippingCore\Api\SplitAddress\RecipientStreetRepositoryInterface;

class AddRecipientStreetInfoBlock implements ObserverInterface
{
    /**
     * @var RecipientStreetRepositoryInterface
     */
    private $recipientStreetRepository;

    /**
     * @var RequestInterface|Http
     */
    private $request;

    public function __construct(
        RecipientStreetRepositoryInterface $recipientStreetRepository,
        RequestInterface $request
    ) {
        $this->recipientStreetRepository = $recipientStreetRepository;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    #[\Override]
    public function execute(Observer $observer)
    {
        $applicableActions = [
            'sales_order_view',
            'adminhtml_order_shipment_new',
            'adminhtml_order_shipment_view'
        ];

        $action = $this->request->getFullActionName();
        if (!in_array($action, $applicableActions, true)) {
            // not the order details page
            return;
        }

        $block = $observer->getData('block');
        if (!$block instanceof \Magento\Sales\Block\Adminhtml\Order\View\Info) {
            return;
        }

        $order = $block->getOrder();
        $shippingAddress = $order->getShippingAddress();
        if (!$order instanceof OrderInterface || !$shippingAddress) {
            // wrong type, virtual or corrupt order
            return;
        }

        try {
            $recipientStreet = $this->recipientStreetRepository->get((int) $shippingAddress->getId());
        } catch (NoSuchEntityException) {
            // no recipient street for this order
            return;
        }

        $recipientAddressBlock = $block->getChildBlock('nrshipping_recipient_street');
        $recipientAddressBlock->setData('recipient_street', $recipientStreet);
        $recipientAddressBlock->setData('order_id', (int) $order->getId());

        $transport = $observer->getData('transport');
        $html = $transport->getData('html');
        $html.= $recipientAddressBlock->toHtml();
        $transport->setData('html', $html);
    }
}
