define([
    'underscore',
    'uiRegistry',
    'Netresearch_ShippingUi/js/model/current-carrier',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/model/shipping-settings',
    'Netresearch_ShippingUi/js/model/shipping-option/shipping-option-codes',
], function (_, registry, currentCarrier, selections, shippingSettings, shippingOptionCodes) {
    'use strict';

    var oppositeRuleMap = {
        'hide': 'show',
        'show': 'hide',
        'disable': 'enable',
        'enable': 'disable',
        'require': 'unrequire',
        'unrequire': 'require',
    };

    /**
     * @param {string} code
     * @param {Function} action - Callback to perform on the inputs for the shipping option code.
     */
    var doActionOnInputComponents = function (code, action) {
        if (!shippingOptionCodes.isCompoundCode(code)) {
            /** Unwrap shippingOptionCode into compound codes. */
            _.each(shippingOptionCodes.convertToCompoundCodes(code), function (compoundCode) {
                doActionOnInputComponents(compoundCode, action);
            });
        } else {
            /** Modify specific input defined by compound code */
            registry.get({
                isInputComponent: true,
                shippingOptionCode: shippingOptionCodes.getShippingOptionCode(code),
                inputCode: shippingOptionCodes.getInputCode(code),
            }, action);
        }
    };

    /**
     * @param {NrShippingCompatibility} rule
     * @param {ActionLists} actionLists
     */
    var processRule = function (rule, actionLists) {
        var selectedMasters = [],
            list;

        _.each(
            selections.getSelectionValuesInCompoundFormat(),
            function (selection) {
                var selectionIsMaster = rule.masters.indexOf(selection.code) !== -1,
                    valuesMatch = function () {
                        if (rule.trigger_value === '*') {
                            // The '*' value matches any "truthy" value
                            return !!selection.value;
                        }
                        if (rule.trigger_value.startsWith('/') && rule.trigger_value.endsWith('/')) {
                            // Regex value
                            return selection.value.search(new RegExp(rule.trigger_value.slice(1, -1))) !== -1;
                        }
                        // Otherwise, we need an exact match */
                        return selection.value === rule.trigger_value;
                    }();

                if (selectionIsMaster && valuesMatch) {
                    selectedMasters.push(selection);
                }
            }
        );

        list = selectedMasters.length ? rule.action : oppositeRuleMap[rule.action];
        actionLists[list] = _.union(actionLists[list], rule.subjects);
    };

    /**
     * @param {NrShippingCompatibility[]} rules
     * @return {ActionLists}
     */
    var processRules = function (rules) {
        /**
         * In-between storage for compatibility results.
         *
         * @typedef {{hide: string[], disable: string[], enable: string[], show: string[]}} ActionLists
         * @type ActionLists
         */
        var actionLists = {
            disable: [],
            enable: [],
            hide: [],
            show: [],
            require: [],
            unrequire: []
        };

        _.each(rules, function (rule) {
            processRule(rule, actionLists);
        });

        return actionLists;
    };

    var enforceShippingOptionCompatibility = function () {
        var carrier = currentCarrier.get(),
            carrierData,
            actionLists,
            valuesHaveChanged = false;

        if (!carrier) {
            return;
        }

        carrierData = shippingSettings.getByCarrier(carrier);

        if (!carrierData) {
            return;
        }

        actionLists = processRules(carrierData.compatibility_data);

        /** Don't enable/show shipping options that another rule will disable/hide */
        actionLists.enable = _.difference(actionLists.enable, actionLists.disable);
        actionLists.show = _.difference(actionLists.show, actionLists.hide);
        actionLists.unrequire = _.difference(actionLists.unrequire, actionLists.require);

        /** Set disabled/visible status of individual shipping option inputs */
        _.each(_.uniq(actionLists.enable), function (shippingOptionCode) {
            doActionOnInputComponents(shippingOptionCode, function (input) {
                input.disabled(false);
            });
        });
        _.each(_.uniq(actionLists.disable), function (shippingOptionCode) {
            doActionOnInputComponents(shippingOptionCode, function (input) {
                if (!input.disabled()) {
                    input.disabled(true);
                    if (input.value() !== '') {
                        input.value('');
                        valuesHaveChanged = true;
                    }
                }
            });
        });
        _.each(_.uniq(actionLists.hide), function (shippingOptionCode) {
            doActionOnInputComponents(shippingOptionCode, function (input) {
                if (input.visible()) {
                    input.visible(false);
                    if (input.value() !== '') {
                        input.value('');
                        valuesHaveChanged = true;
                    }
                }
            });
        });
        _.each(_.uniq(actionLists.show), function (shippingOptionCode) {
            doActionOnInputComponents(shippingOptionCode, function (input) {
                input.visible(true);
            });
        });
        _.each(_.uniq(actionLists.require), function (shippingOptionCode) {
            doActionOnInputComponents(shippingOptionCode, function (input) {
                input.required(true);
                input.validation['required-entry'] = true;
            });
        });
        _.each(_.uniq(actionLists.unrequire), function (shippingOptionCode) {
            doActionOnInputComponents(shippingOptionCode, function (input) {
                input.required(false);
                input.validation['required-entry'] = false;
                input.error(false);
            });
        });


        /** Re-run the compatibility enforcer until all data is consistent. */
        if (valuesHaveChanged) {
            enforceShippingOptionCompatibility();
        }
    };

    /**
     * Check for unavailable shipping option combinations,
     * disable shipping option inputs that should not be filled,
     * and reenable inputs that are again available.
     */
    return enforceShippingOptionCompatibility;
});
