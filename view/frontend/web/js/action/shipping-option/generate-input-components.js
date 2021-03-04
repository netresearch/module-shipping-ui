define([
    'underscore',
    'uiLayout',
    'Netresearch_ShippingUi/js/model/shipping-option/validation-map',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/model/shipping-option/input-types'
], function (_, layout, validationMap, selections, inputTypes) {
    'use strict';

    /**
     * @param {NrShippingInput} shippingOptionInput
     */
    var buildValidationData = function (shippingOptionInput) {
        var validationData = {};

        _.each(shippingOptionInput.validation_rules, function (rule) {
            var validatorName = validationMap.getValidatorName(rule.name);

            if (validatorName) {
                validationData[validatorName] = rule.param ? rule.param : true;
            } else {
                console.warn('Shipping option validation rule ' + rule.name + ' is not defined.');
            }
        });

        return validationData;
    };

    /**
     * Load default shipping option input value either from cache or from Input model.
     *
     * @param {NrShippingOption} shippingOption
     * @param {NrShippingInput} shippingOptionInput
     * @return {string}
     */
    var getDefaultValue = function (shippingOption, shippingOptionInput) {
        var cachedValue = selections.getShippingOptionValue(
            shippingOption.code,
            shippingOptionInput.code
        );

        if (cachedValue !== null) {
            return cachedValue;
        }

        return shippingOptionInput.default_value;
    };

    /**
     * @param {NrShippingOption} shippingOption
     * @param {string} parentName
     */
    return function (shippingOption, parentName) {
        var shippingOptionInputLayout = _.map(
            shippingOption.inputs,
            function (/** @type {NrShippingInput} */ shippingOptionInput) {
                var inputType = inputTypes.get(shippingOptionInput.input_type);
                return {
                    component: inputType.component,
                    parent: parentName,
                    shippingOptionInput: shippingOptionInput,
                    shippingOption: shippingOption,
                    shippingOptionCode: shippingOption.code,
                    inputCode: shippingOptionInput.code,
                    inputType: shippingOptionInput.input_type,
                    options: shippingOptionInput.options,
                    tooltip: shippingOptionInput.tooltip ? {description: shippingOptionInput.tooltip} : false,
                    comment: shippingOptionInput.comment,
                    elementTmpl: inputType.template,
                    value: getDefaultValue(shippingOption, shippingOptionInput),
                    validation: buildValidationData(shippingOptionInput),
                };
            }
        );

        layout(shippingOptionInputLayout);
    };
});
