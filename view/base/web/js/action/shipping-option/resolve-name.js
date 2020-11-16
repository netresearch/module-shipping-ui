define([
    'underscore',
    'Netresearch_ShippingUi/js/model/shipping-settings',
    'Netresearch_ShippingUi/js/model/shipping-option/shipping-option-codes',
    'Netresearch_ShippingUi/js/model/current-carrier',
], function (_, shippingSettings, codes, currentCarrier) {
    'use strict';

    /**
     * Resolve a shipping option code to a human-readable shipping option label.
     *
     * @param {string} code
     * @return {string|boolean} - will return false if the requested shipping option does not exist
     */
    return function (code) {
        var carrier = currentCarrier.get(),
            shippingSettingsData,
            matchingOption,
            matchingInput,
            label;

        if (!carrier) {
            return false;
        }

        shippingSettingsData = shippingSettings.getByCarrier(carrier);

        if (!shippingSettingsData) {
            return false;
        }

        /** @var {NrShippingOption} matchingOption */
        matchingOption = _.find(shippingSettingsData.service_options, function (shippingOption) {
            return shippingOption.code === codes.getShippingOptionCode(code);
        });
        if (!matchingOption) {
            return false;
        }

        label = matchingOption.label;

        if (codes.isCompoundCode(code)) {
            matchingInput = _.find(
                matchingOption.inputs,
                function (input) {
                    return input.code === codes.getInputCode(code);
                }
            );
            if (matchingInput) {
                label += ' – ' + matchingInput.label;
            }
        }

        return label;
    };
});
