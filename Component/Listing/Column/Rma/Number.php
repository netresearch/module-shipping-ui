<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Component\Listing\Column\Rma;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\Ui\Component\Listing\Columns\Column;

class Number extends Column
{
    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CarrierFactory $carrierFactory,
        array $components = [],
        array $data = []
    ) {
        $this->carrierFactory = $carrierFactory;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (empty($item[$fieldName])) {
                    continue;
                }

                $carrier = $this->carrierFactory->create($item['carrier_code']);
                if ($carrier instanceof AbstractCarrierOnline) {
                    $trackingInfo = $carrier->getTrackingInfo($item[$fieldName]);
                    if ($trackingInfo instanceof Status && $trackingInfo->getData('url')) {
                        $item[$fieldName] = sprintf(
                            '<a href="%s" target="_blank" rel="noreferrer">%s</a>',
                            $trackingInfo->getData('url'),
                            $item[$fieldName]
                        );
                    }
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}
