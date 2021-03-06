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
                console.warn('NrShipping shipping option validation rule ' + rule.name + ' is not defined.');
            }
        });
        return validationData;
    };

    /**
     * Load default shipping option input value either from cache or from Input model.
     *
     * @param {string} section
     * @param {NrShippingOption} shippingOption
     * @param {NrShippingInput} shippingOptionInput
     * @param {integer|false} itemId
     * @return {string|boolean}
     */
    var getDefaultValue = function (section, shippingOption, shippingOptionInput, itemId) {
        var result = selections.getShippingOptionValue(
            section,
            shippingOption.code,
            shippingOptionInput.code,
            itemId
        );

        if (result === null) {
            result = shippingOptionInput.default_value;
        }

        if (shippingOptionInput.input_type === 'checkbox') {
            return !!Number(result);
        }
        return result;
    };

    /**
     * @param {NrShippingOption} shippingOption
     * @param {string} parentName
     * @param {integer|false} itemId
     */
    return function (shippingOption, parentName, itemId) {
        /**
         * In the packaging popup we want all inputs to save their selections into their section separately.
         * We can evaluate the component names for that and fetch the first containers name after the root container.
         */
        var section = parentName.match(new RegExp('([a-z]+)(\\.[0-9]+)?\\.' + shippingOption.code))[1] || '';
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
                    value: getDefaultValue(section, shippingOption, shippingOptionInput, itemId),
                    validation: buildValidationData(shippingOptionInput),
                    itemId: itemId,
                    name: shippingOptionInput.code,
                    disabled: shippingOptionInput.disabled,
                    section: section,
                };
            }
        );

        layout(shippingOptionInputLayout);
    };
});
