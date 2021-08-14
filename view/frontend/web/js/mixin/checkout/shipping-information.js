define([
    'underscore',
    'mage/translate',
    'Magento_Checkout/js/model/quote',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/model/shipping-settings'
], function (_, $t, quote, selections, checkoutData) {
    'use strict';

    /**
     * @param {string|boolean} rawValue
     * @param {string} inputCode
     * @param {NrShippingOption} optionData
     * @return {string}
     */
    var deriveHumanReadableSelectionValue = function (rawValue, inputCode, optionData) {
        var inputData, inputOption;

        if (typeof rawValue === "boolean") {
            return rawValue ? $t('Yes') : $t('No');
        }
        inputData = _.findWhere(optionData.inputs, {code: inputCode});
        if (inputData) {
            inputOption = _.findWhere(inputData.options, {value: rawValue});
            if (inputOption) {
                return inputOption.label;
            }
        }
        return rawValue;
    };

    /**
     * @param {NrShippingCarrier} carrierData
     * @param {*[][]} currentSelections
     * @return {[{label: string, value: string}]}
     */
    var buildDisplayData = function (carrierData, currentSelections) {
        var data = [];

        _.each(currentSelections, function (inputValues, optionCode) {
            var optionData = _.findWhere(carrierData.service_options, {code: optionCode}),
                valueData = currentSelections[optionCode],
                valueParts = [];

            _.each(valueData, function (selectionValue, inputCode) {
                var inputData = _.findWhere(optionData.inputs, {code: inputCode});
                if (!inputData) {
                    return;
                }
                valueParts.push(deriveHumanReadableSelectionValue(
                    selectionValue,
                    inputCode,
                    optionData
                ));
            });

            data.push({
                label: optionData.label,
                value: valueParts.join(', '),
            });
        });
        return data;
    };

    var mixin = {
        defaults: {
            template: 'Netresearch_ShippingUi/checkout/shipping-information', // override core template
            displayData: [],
            displayTitle: '',
            displayColor: '',
        },

        /**
         * A computed observable to wait until all dependencies for the
         * shipping selection display are ready.
         *
         * @see https://knockoutjs.com/documentation/computedObservables.html
         * @return {boolean}
         */
        loadDisplayData: function () {
            var shippingMethod = quote.shippingMethod(),
                fullCheckoutData = checkoutData.get(),
                fullSelectionData = selections.get(),
                carrierSelections,
                carrierCheckoutData,
                success = false;

            if (shippingMethod && fullCheckoutData && fullSelectionData) {
                carrierSelections = selections.getByCarrier(shippingMethod.carrier_code);
                carrierCheckoutData = checkoutData.getByCarrier(shippingMethod.carrier_code);
                if (carrierSelections && carrierCheckoutData) {
                    if (carrierCheckoutData.hasOwnProperty('metadata')) {
                        this.displayTitle(carrierCheckoutData.metadata.title);
                        this.displayColor(carrierCheckoutData.metadata.color);
                    }
                    this.displayData(buildDisplayData(
                        carrierCheckoutData,
                        carrierSelections
                    ));
                    success = true;
                }
            }

            return success;
        },

        /**
         * Initializes observable properties of instance
         */
        initObservable: function () {
            return this._super().observe(['displayData', 'displayTitle', 'displayColor']);
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
