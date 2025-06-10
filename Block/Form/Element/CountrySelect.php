<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

namespace Netresearch\ShippingUi\Block\Form\Element;

use Magento\Directory\Block\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Html\Select;

class CountrySelect extends Data
{
    /**
     * Obtain a select view element with country options initialized.
     *
     * @param string|null $value
     * @param string $name
     * @param string $id
     * @return Select
     * @throws LocalizedException
     *
     * @see getCountryHtmlSelect
     */
    public function getCountrySelect(
        ?string $value = null,
        string $name = 'country_id',
        string $id = 'country'
    ): Select {
        if ($value === null) {
            $value = $this->getCountryId();
        }

        $options = $this->getCountryCollection()
                        ->setForegroundCountries($this->getTopDestinations())
                        ->toOptionArray();

        /** @var Select $block */
        $block = $this->getLayout()->createBlock(Select::class);
        return $block
            ->setId($id)
            ->setTitle($this->escapeHtmlAttr(__('Country')))
            ->setOptions($options)
            ->setData('name', $name)
            ->setData('value', $value)
            ->setData('extra_params', 'data-validate="{\'validate-select\':true}"')
        ;
    }
}
