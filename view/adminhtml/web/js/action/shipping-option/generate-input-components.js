define([
    'underscore',
    'uiLayout',
    'Netresearch_ShippingUi/js/model/shipping-option/validation-map',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/model/shipping-option/input-types',
    'Netresearch_ShippingUi/js/model/shipping-settings',
    'Netresearch_ShippingUi/js/model/current-carrier'
], function (_, layout, validationMap, selections, inputTypes, shippingSettings, currentCarrier) {
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
     * Calculate default visibility of input field dependent on existing show/hide compatibility rules
     *
     * @param {string} section
     * @param {NrShippingOption} shippingOption
     * @param {NrShippingInput} shippingOptionInput
     * @param {integer|false} itemId
     * @return {string|boolean}
     */
    var determineDefaultVisibility = function (section, shippingOption, shippingOptionInput, itemId) {
        // get all rules that affect the current input
        var affectedRules = shippingSettings.getByCarrier(currentCarrier.get()).compatibility_data
            .filter(function (compatibilityRule) {
                return ['show', 'hide'].includes(compatibilityRule.action)
                    && compatibilityRule.subjects.includes([shippingOption.code, shippingOptionInput.code].join('.'));
            })
        if (affectedRules.length === 0 || itemId !== false) {
            // no rules relevant or item input -> show the field
            return true;
        }
        // map rule to boolean value if fulfilled and require all to apply successfully
        return affectedRules.map(function (compatibilityRule) {
            // convert masters to their default value
            let masterValues = compatibilityRule.masters.map(function (name) {
                let [masterOptionName, masterInputName] = name.split('.'),
                    masterSection = 'service',
                    /** @var {NrShippingOption} option */
                    option = shippingSettings.getByCarrier(currentCarrier.get()).service_options
                        .filter((option) => option.code === masterOptionName)
                        .first();
                if (option === undefined) {
                    option = shippingSettings.getByCarrier(currentCarrier.get()).package_options
                        .filter((option) => option.code === masterOptionName)
                        .first()
                    masterSection = 'package';
                }
                // check if there is an existing selection value for master
                let selectionValue = selections.getShippingOptionValue(
                    masterSection,
                    masterOptionName,
                    masterInputName,
                    false
                );
                if (selectionValue !== null) {
                    return selectionValue;
                }
                // otherwise continue determining default value and return that
                /** @var {NrShippingInput} input */
                let input = option.inputs.filter((input) => input.code === masterInputName).first();
                return input.input_type === "checkbox" ? !!Number(input.default_value) : input.default_value;
            });
            let apply = function (value) {
                if (compatibilityRule.trigger_value === '*') {
                    // The '*' value matches any "truthy" value
                    return !!value;
                }
                if (compatibilityRule.trigger_value.startsWith('/') && compatibilityRule.trigger_value.endsWith('/')) {
                    // Regex value
                    return value.search(new RegExp(compatibilityRule.trigger_value.slice(1, -1))) !== -1;
                }
                // Otherwise, we need an exact match */
                return value === compatibilityRule.trigger_value;
            };
            let notApply = function (value) {
                return !apply(value);
            }

            if (compatibilityRule.action === 'show') {
                return masterValues.every(apply);
            }
            return masterValues.every(notApply);
            // determine if master values fulfill trigger_value
        }).every((v) => v);
    }

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
                    visible: determineDefaultVisibility(section, shippingOption, shippingOptionInput, itemId)
                };
            }
        );

        layout(shippingOptionInputLayout);
    };
});
