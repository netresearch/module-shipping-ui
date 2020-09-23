define([
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/full-screen-loader',
    'Netresearch_ShippingUi/js/model/checkout/storage',
    'Netresearch_ShippingUi/js/model/shipping-settings'
], function (
    urlBuilder,
    request,
    quote,
    shippingService,
    fullScreenLoader,
    storage,
    checkoutData
) {
    'use strict';

    /**
     * Retrieve checkout data from Magento 2 REST endpoint and update checkoutData model.
     *
     * @param {string} countryId
     * @param {string} postalCode
     */
    return function (countryId, postalCode) {
        var fromCache = storage.get(countryId + postalCode),
            serviceUrl = urlBuilder.createUrl('/nrshipping/checkout-data/get', {}),
            payload = {countryId: countryId, postalCode: postalCode};

        if (fromCache) {
            checkoutData.set(fromCache);
            return;
        }

        shippingService.isLoading(true);
        request.post(
            serviceUrl,
            JSON.stringify(payload)
        ).success(
            function (response) {
                storage.set(countryId + postalCode, response);
                checkoutData.set(response);
                shippingService.isLoading(false);
            }
        ).fail(
            function () {
                shippingService.isLoading(false);
            }
        );
    };
});
