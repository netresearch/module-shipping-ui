<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Text\ListText;
use Magento\Sales\Api\Data\ShipmentInterface;
use Netresearch\ShippingCore\Api\ReturnShipment\TrackRepositoryInterface;

class ReturnShipments extends ListText implements TabInterface
{
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var TrackRepositoryInterface
     */
    private $trackRepository;

    public function __construct(
        Context $context,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilder $filterBuilder,
        TrackRepositoryInterface $trackRepository,
        array $data = []
    ) {
        $this->trackRepository = $trackRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilder = $filterBuilder;

        parent::__construct($context, $data);
    }

    public function getTabLabel()
    {
        return __('Return Shipments');
    }

    public function getTabTitle()
    {
        return __('Return Shipments');
    }

    public function canShowTab()
    {
        $orderIdFilter = $this->filterBuilder
            ->setField(ShipmentInterface::ORDER_ID)
            ->setValue($this->getRequest()->getParam('order_id'))
            ->setConditionType('eq')
            ->create();

        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter($orderIdFilter)->create();
        return (bool) $this->trackRepository->getList($searchCriteria)->getTotalCount();
    }

    public function isHidden()
    {
        return false;
    }
}
