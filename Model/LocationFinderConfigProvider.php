<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Netresearch\ShippingCore\Model\Config\MapBoxConfig;

class LocationFinderConfigProvider implements ConfigProviderInterface
{
    /**
     * @var MapBoxConfig
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(MapBoxConfig $config, StoreManagerInterface $storeManager)
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getConfig(): array
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException) {
            $storeId = 0;
        }

        $token = $this->config->getApiToken($storeId);
        $url = $this->config->getMapTileUrl($storeId);

        return  [
            'locationFinder' => [
                'maptileApiToken' => $token,
                'maptileUrl' => str_replace('{api_token}', $token, $url),
                'mapAttribution' => $this->config->getMapAttribution($storeId)
            ]
        ];
    }
}
