define([
    'underscore',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/model/shipping-settings',
    'uiRegistry'
], function (_, selections, shippingSettings, registry) {
    'use strict';

    var getInputValueFromShippingSettings = function (optionCode, inputCode, itemId) {
        try {
            return shippingSettings.get()().carriers[0].item_options.find(function (optionSet) {
                return optionSet.item_id === Number(itemId);
            }).shipping_options.find(function (option) {
                return option.code === optionCode;
            }).inputs.find(function (input) {
                return input.code === inputCode;
            }).default_value;
        } catch (e) {
            return "";
        }
    };

    /**
     * Extract an array of string and/or number values
     * from inputs that match the given rule.
     *
     * @param {NrShippingItemCombinationRule} combinationRule
     * @param {string} section  The package id of the input
     * @return {Array<string|number>}
     */
    var extractSourceValues = function (combinationRule, section) {
        var sourceValues = [],
            sourceItemOptionCode = combinationRule.source_item_input_code.split('.')[0],
            sourceItemInputCode = combinationRule.source_item_input_code.split('.')[1],
            itemSelections = selections.getCurrentItems()();

        /**
         * Collect values from source item inputs.
         */
        _.each(Object.keys(itemSelections), function (itemId) {
            var item = itemSelections[itemId];
            var value;

            /**
             * Items without quantity are not considered
             */
            if (_.contains([0, "0", 0.0], item.itemDetails.qty)) {
                return;
            }

            /**
             * Source items that are not selected are not considered
             */
            if (sourceItemOptionCode in item && sourceItemInputCode in item[sourceItemOptionCode]) {
                value = item[sourceItemOptionCode][sourceItemInputCode];
            } else {
                /* Check if a value is set in shipping settings
                   that the selections model doesn't know about yet. */
                value = getInputValueFromShippingSettings(sourceItemOptionCode, sourceItemInputCode, itemId);
                if (!value) {
                    return;
                }
            }

            /** Multiply with item qty if we have an "add" rule. */
            if (combinationRule.action === 'add') {
                value = Number(value) * Number(item.itemDetails.qty);
            }

            sourceValues.push(value);
        });

        /**
         * Collect additional source input values.
         */
        _.each(combinationRule.additional_source_input_codes, function (compoundCode) {
            var optionCode = compoundCode.split('.')[0],
                inputCode = compoundCode.split('.')[1],
                value = selections.getShippingOptionValue(
                    section,
                    optionCode,
                    inputCode,
                    false
                );

            if (value) {
                sourceValues.unshift(value);
            }
        });

        return sourceValues;
    };

    /**
     * @param {NrShippingItemCombinationRule} combinationRule
     * @param {string} section
     * @return {string}
     */
    var computeNewValue = function (combinationRule, section) {
        /**
         * @var {string} newValue
         */
        var newValue = '';
        var sourceValues = extractSourceValues(combinationRule, section);

        if (combinationRule.action === 'add') {
            /**
             * For "add" rules, create the sum of the source values.
             */
            newValue = String(sourceValues.reduce(
                function (carry, value) {
                    return (Number(carry) + Number(value)).toFixed(2);
                },
                0
            ));
        } else if (combinationRule.action === 'concat') {
            /**
             * For "concat" rules, concatenate the source values.
             */
            newValue = sourceValues.filter(Boolean).join(', ');
        } else {
            console.error('Invalid item combination rule action "' + combinationRule.action + '"');
        }

        return newValue;
    };

    /**
     * Load component by input and option code
     * and update value according to item combination rules.
     *
     * @param {string} inputCode    To identify the input to modify.
     * @param {string} optionCode   To identify the input to modify.
     * @param {NrShippingItemCombinationRule} combinationRule  The rule to apply.
     */
    var processCombinationRule = function (
        inputCode,
        optionCode,
        combinationRule
    ) {
        registry.get({inputCode: inputCode, shippingOptionCode: optionCode}, function (component) {
            component.value(
                computeNewValue(combinationRule, component.section)
            );
        });
    };

    /**
     * @param {NrShippingItemCombinationRule} rule
     * @param {string} currentOptionCode
     * @param {string} currentInputCode
     * @return {boolean}
     */
    var ruleAffectedByCurrentInputChange = function (rule, currentOptionCode, currentInputCode) {
        var effectedInputs = [rule.source_item_input_code].concat(rule.additional_source_input_codes);

        if (currentOptionCode + currentInputCode === "") {
            // If no current input is given, all rules should be applied
            return true;
        }

        if (currentOptionCode === "itemDetails" && currentInputCode === "qty") {
            // A changing item amount means that all rules must be applied again.
            return true;
        }

        // Assumes that all codes are in compound format
        return _.includes(effectedInputs, currentOptionCode + "." + currentInputCode);
    };

    return {
        /**
         * Process every package option's combination rules by
         * combining values from current item selections and updating the package
         * option components value.
         *
         * @param {NrShippingOption[]} shippingOptions     List of shipping options whose item
         *                                                  combination rules to process.
         * @param {string|undefined} [currentOptionCode]    The shipping option that triggered the rule
         * @param {string|undefined} [currentInputCode]     The shipping option input that triggered the rule
         */
        apply: function (shippingOptions, currentOptionCode, currentInputCode) {
            currentOptionCode = currentOptionCode ? currentOptionCode : "";
            currentInputCode = currentInputCode ? currentInputCode : "";

            _.each(shippingOptions, /** @param {NrShippingOption} shippingOption */ function (shippingOption) {
                _.each(shippingOption.inputs, /** @param {NrShippingInput} input */ function (input) {
                    var rule = input.item_combination_rule;

                    if (!rule) {
                        // No combination rule, nothing to do
                        return;
                    }
                    if (shippingOption.code === currentOptionCode && input.code === currentInputCode) {
                        // This input is the one that was modified, new value should not be overridden by rule
                        return;
                    }
                    if (!ruleAffectedByCurrentInputChange(rule, currentOptionCode, currentInputCode)) {
                        // This rule is not affected by the modified input
                        return;
                    }

                    processCombinationRule(
                        input.code,
                        shippingOption.code,
                        rule
                    );
                });
            });
        }
    };
});
