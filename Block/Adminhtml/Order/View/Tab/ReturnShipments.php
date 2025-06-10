<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Text\ListText;
use Netresearch\ShippingCore\Model\ResourceModel\ReturnShipment\TrackCollectionFactory;

class ReturnShipments extends ListText implements TabInterface
{
    /**
     * @var TrackCollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Context $context,
        TrackCollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $data);
    }

    #[\Override]
    public function getTabLabel()
    {
        return __('Return Shipments');
    }

    #[\Override]
    public function getTabTitle()
    {
        return __('Return Shipments');
    }

    #[\Override]
    public function canShowTab()
    {
        $trackCollection = $this->collectionFactory->create();
        $trackCollection->setOrderIdFilter((int) $this->getRequest()->getParam('order_id'));

        return (bool) $trackCollection->getSize();
    }

    #[\Override]
    public function isHidden()
    {
        return false;
    }
}
