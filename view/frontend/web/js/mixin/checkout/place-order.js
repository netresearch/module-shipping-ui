define([
    'jquery',
    'mage/translate',
    'mage/utils/wrapper',
    'Netresearch_ShippingUi/js/model/checkout/storage',
    'Netresearch_ShippingUi/js/action/shipping-option/validation/validate-selection',
    'Netresearch_ShippingUi/js/action/shipping-option/validation/validate-compatibility',
    'Netresearch_ShippingUi/js/action/checkout/webapi/update-shipping-option-selection'
], function ($, $t, wrapper, storage, validateSelection, validateCompatibility, updateSelection) {
    'use strict';

    /**
     * Clear the checkout storage when placing the order to reset
     * the customer's shipping option selections for their next quote.
     *
     * @see 'Magento_Checkout/js/action/place-order'
     * */
    return function (placeOrder) {
        return wrapper.wrap(placeOrder, function (originalAction, paymentData, messageContainer) {
            var isValid = validateSelection() && validateCompatibility();

            return $.Deferred(function (deferred) {
                if (isValid) {
                    updateSelection()
                        .fail(function () {
                            messageContainer.addErrorMessage(
                                {'message': $t('Your shipping option selections could not be saved.')}
                            );
                            deferred.reject();
                        })
                        .done(function () {
                            originalAction(paymentData, messageContainer)
                                .done(function (response) {
                                    if (response.responseType !== 'error') {
                                        storage.clear();
                                    }
                                    deferred.resolve(response);
                                })
                                .fail(deferred.reject);
                        });
                } else {
                    messageContainer.addErrorMessage(
                        {'message': $t('Your shipping option selection is invalid. Please review the shipping information.')}
                    );
                    deferred.reject();
                }
            }).promise();
        });
    };
});
