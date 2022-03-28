<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Component\Listing\Column\Rma;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$fieldName]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'nrshipping/order_rma/delete',
                        ['track_id' => $item['entity_id']]
                    ),
                    'label' => __('Delete'),
                    'hidden' => false,
                    '__disableTmpl' => true
                ];

                $item[$fieldName]['send_label'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'nrshipping/order_rma/sendEmail',
                        ['track_id' => $item['entity_id']]
                    ),
                    'label' => __('Send Return Label'),
                    'hidden' => false,
                    '__disableTmpl' => true
                ];
            }
        }
        return parent::prepareDataSource($dataSource);
    }
}
