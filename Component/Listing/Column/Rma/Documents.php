<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Component\Listing\Column\Rma;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Netresearch\ShippingCore\Api\Data\ReturnShipment\DocumentLinkInterface;
use Netresearch\ShippingCore\Api\ReturnShipment\GetDocumentLinksInterface;

class Documents extends Column
{
    /**
     * @var GetDocumentLinksInterface
     */
    private $getDocumentLinks;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        GetDocumentLinksInterface $getDocumentLinks,
        array $components = [],
        array $data = []
    ) {
        $this->getDocumentLinks = $getDocumentLinks;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $linkItems = array_map(
                    static function (DocumentLinkInterface $link) {
                        return sprintf('<li><a href="%s">%s</a></li>', $link->getUrl(), $link->getTitle());
                    },
                    $this->getDocumentLinks->execute((int) $item['order_id'], (int) $item['entity_id'])
                );

                $html = sprintf('<ul class="document-links">%s</ul>', implode('', $linkItems));
                $item[$fieldName] = $html;
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}
