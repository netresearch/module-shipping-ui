<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\ViewModel\Order\Totals;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use Netresearch\ShippingCore\Api\AdditionalFee\TaxConfigInterface;
use Netresearch\ShippingCore\Model\AdditionalFee\DisplayObject;
use Netresearch\ShippingCore\Model\AdditionalFee\Total;

/**
 * Utility model class for displaying data for total rendering.
 */
class AdditionalFee implements ArgumentInterface
{
    /**
     * @var TaxConfigInterface
     */
    private $taxConfig;

    /**
     * @var Total
     */
    private $total;

    /**
     * @var DisplayObject|null
     */
    private $displayObject;

    public function __construct(TaxConfigInterface $taxConfig, Total $total)
    {
        $this->taxConfig = $taxConfig;
        $this->total = $total;
    }

    /**
     * @param Order|Order\Invoice|Order\Creditmemo $source
     * @return bool
     */
    public function displayBoth($source): bool
    {
        return $this->taxConfig->displaySalesBothPrices($source->getStoreId());
    }

    /**
     * @param Order|Order\Invoice|Order\Creditmemo $source
     * @return bool
     */
    public function displayIncludeTax($source): bool
    {
        return $this->taxConfig->displaySalesPriceIncludingTax($source->getStoreId());
    }

    /**
     * @param Order|Order\Invoice|Order\Creditmemo $source
     * @return DisplayObject
     */
    private function getDisplayObject($source): DisplayObject
    {
        if ($this->displayObject === null) {
            /** @var DisplayObject displayObject */
            $this->displayObject = $this->total->createTotalDisplayObject($source);
        }

        return $this->displayObject;
    }

    /**
     * @param Order|Order\Invoice|Order\Creditmemo $source
     * @param float $value
     * @return string
     */
    private function formatPrice($source, float $value): string
    {
        $order = $source;
        if ($source instanceof Order\Invoice || $source instanceof Order\Creditmemo) {
            $order = $source->getOrder();
        }

        return $order->formatPrice($value);
    }

    /**
     * @param Order|Order\Invoice|Order\Creditmemo $source
     * @return string
     */
    public function getPriceInclTax($source): string
    {
        return $this->formatPrice($source, $this->getDisplayObject($source)->getValueInclTax());
    }

    /**
     * @param Order|Order\Invoice|Order\Creditmemo $source
     * @return string
     */
    public function getPriceExclTax($source): string
    {
        return $this->formatPrice($source, $this->getDisplayObject($source)->getValueExclTax());
    }

    /**
     * @param Order|Order\Invoice|Order\Creditmemo $source
     * @return string
     */
    public function getLabel($source): string
    {
        return $this->getDisplayObject($source)->getLabel();
    }
}
