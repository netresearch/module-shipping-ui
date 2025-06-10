<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Component\Listing\Column\Rma;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Netresearch\ShippingCore\Api\ReturnShipment\DocumentDownloadInterface;
use Netresearch\ShippingCore\Api\ReturnShipment\TrackRepositoryInterface;

class Documents extends Column
{
    /**
     * @var TrackRepositoryInterface
     */
    private $trackRepository;

    /**
     * @var DocumentDownloadInterface
     */
    private $download;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TrackRepositoryInterface $trackRepository,
        DocumentDownloadInterface $download,
        array $components = [],
        array $data = []
    ) {
        $this->trackRepository = $trackRepository;
        $this->download = $download;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    #[\Override]
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $track = $this->trackRepository->get((int) $item['entity_id']);
                $linkItems = '';

                foreach ($track->getDocuments() as $document) {
                    $linkItems .= sprintf(
                        '<li><a href="%s">%s</a></li>',
                        $this->download->getUrl($document, $track),
                        $document->getTitle()
                    );
                }

                $item[$fieldName] = '<ul class="document-links">' . $linkItems . '</ul>';
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}
