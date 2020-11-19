define([
    'underscore',
    'mage/translate',
    'uiRegistry',
    'Netresearch_ShippingUi/js/model/shipping-settings',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/action/shipping-option/resolve-name',
    'Netresearch_ShippingUi/js/model/shipping-option/shipping-option-codes',
    'Netresearch_ShippingUi/js/model/current-carrier',
], function (_, $t, registry, shippingSettings, serviceSelection, resolveName, serviceCodes, currentCarrier) {
    'use strict';

    /**
     * @param {NrShippingCompatibility} rule
     * @return {string}
     */
    var buildErrorMessage = function (rule) {
        var subjectNames = _.map(rule.subjects, function (subject) {
            return '"' + resolveName(subject) + '"';
        });

        return rule.error_message.replace(
            '%1',
            subjectNames.join(' ' + $t('and') + ' ')
        );
    };

    /**
     * @param {NrShippingCompatibility} rule
     */
    var markRelatedInputsWithError = function (rule) {
        _.each(rule.subjects, function (subject) {
            var serviceInputs = [];

            if (serviceCodes.getInputCode(subject)) {
                serviceInputs = [registry.get({
                    isInputComponent: true,
                    inputCode: serviceCodes.getInputCode(subject),
                })];
            } else {
                serviceInputs = registry.get({
                    component: 'Netresearch_ShippingUi/js/view/shipping-option',
                    shippingOptionCode: subject
                }).elems();
            }
            _.each(serviceInputs, function (input) {
                if (!input.error()) {
                    input.error(' ');
                }
            });
        });
    };

    /**
     *
     * @param {NrShippingCompatibility} rule
     * @param {int} serviceDifference - The number of selected services that are subjects of the compatibility rule.
     * @return {boolean}
     */
    var isIncompatibleServiceCombination = function (rule, serviceDifference) {
        return (rule.action === 'disable' || rule.action === 'hide') && serviceDifference === 0;
    };

    /**
     * Checks if there are any services selected that require another service that is missing.
     *
     * @param {NrShippingCompatibility} rule
     * @param {int} serviceDifference - The number of selected services that are subjects of the compatibility rule.
     * @return {boolean}
     */
    var isMissingRequiredServices = function (rule, serviceDifference) {
        if (rule.action === 'require') {
            /** Either all or none of the services may be selected. */
            return !_.contains([0, rule.subjects.length], serviceDifference);
        }

        return false;
    };

    /**
     * Check for unavailable service combinations and display errors on shipping settings view.
     *
     * @TODO: Consider the trigger_value property when validating.
     *
     * @return {boolean} - whether there were any compatibility errors.
     */
    return function () {
        var carrier = currentCarrier.get(),
            compatibilityInfo,
            selectedServiceCodes,
            shippingSettingsView;

        if (!carrier) {
            return true;
        }

        compatibilityInfo = shippingSettings.getByCarrier(carrier).compatibility_data;
        selectedServiceCodes = serviceSelection.getSelectionsInCompoundFormat();
        shippingSettingsView = registry.get({shippingSettingsController: true});

        shippingSettingsView.errors([]);

        _.each(compatibilityInfo, function (compatibility) {
            var serviceDifference, errorMessage;

            if (compatibility.masters.length === 0
                || _.intersection(selectedServiceCodes, compatibility.masters).length) {
                serviceDifference = _.difference(compatibility.subjects, selectedServiceCodes).length;

                if (isIncompatibleServiceCombination(compatibility, serviceDifference)
                    || isMissingRequiredServices(compatibility, serviceDifference)
                ) {
                    errorMessage = buildErrorMessage(compatibility);
                    if (errorMessage) {
                        shippingSettingsView.errors.push();
                    }
                    markRelatedInputsWithError(compatibility);
                }
            }
        });

        return shippingSettingsView.errors().length === 0;
    };
});
