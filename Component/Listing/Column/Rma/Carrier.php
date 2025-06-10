<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Component\Listing\Column\Rma;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Netresearch\ShippingCore\Api\Config\CarrierConfigInterface;

class Carrier extends Column
{
    /**
     * @var CarrierConfigInterface
     */
    private $config;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CarrierConfigInterface $config,
        array $components = [],
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    #[\Override]
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName] = $this->config->getTitle($item['carrier_code']);
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}
