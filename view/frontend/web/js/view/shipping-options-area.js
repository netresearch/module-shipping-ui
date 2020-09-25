define([
    'underscore',
    'uiCollection',
    'Netresearch_ShippingUi/js/model/checkout/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Netresearch_ShippingUi/js/model/checkout/checkout-data-refresh',
    'Netresearch_ShippingUi/js/action/shipping-option/generate-components',
    'Netresearch_ShippingUi/js/action/checkout/webapi/get-checkout-data',
    'Netresearch_ShippingUi/js/action/shipping-option/validation/enforce-compatibility',
    'Netresearch_ShippingUi/js/action/shipping-option/validation/validate-compatibility',
    'Netresearch_ShippingUi/js/action/shipping-option/validation/validate-selection',
    'Netresearch_ShippingUi/js/model/shipping-settings',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/model/checkout/footnotes',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Netresearch_ShippingUi/js/action/checkout/webapi/update-shipping-option-selection'
], function (
    _,
    UiCollection,
    storage,
    quote,
    additionalValidators,
    dataRefresh,
    generateShippingOptions,
    getCheckoutData,
    enforceCompatibility,
    validateCompatibility,
    validateSelection,
    checkoutData,
    selections,
    footnotes,
    defaultTotalsProcessor,
    updateSelection
) {
    'use strict';

    /**
     * @param {string} SHIPPING_OPTION_CACHE_KEY
     */
    var SHIPPING_OPTION_CACHE_KEY = 'nrShippingOptionSettingsHash';

    /**
     * @param {NrShippingCarrier} carrierData
     */
    var carrierData;

    return UiCollection.extend({
        defaults: {
            template: 'Netresearch_ShippingUi/checkout/shipping-options-area',
            errors: [],
            image: '',
            title: '',
            displayColor: '',
            commentsBefore: [],
            commentsAfter: [],
            footnotes: [],
            visible: false,
            shippingSettingsController: true,
            lastCarrierCode: '',
            lastDataHash: 0
        },

        initObservable: function () {
            this._super();
            this.observe('errors image title displayColor commentsBefore commentsAfter footnotes visible isLoading');
            this.elems.extend({rateLimit: {timeout: 50, method: "notifyWhenChangesStop"}});

            return this;
        },

        initialize: function () {
            this._super();
            this.lastDataHash = storage.get(SHIPPING_OPTION_CACHE_KEY);

            additionalValidators.registerValidator({validate: validateSelection});
            additionalValidators.registerValidator({validate: validateCompatibility});

            checkoutData.get().subscribe(this.refresh, this);
            quote.shippingMethod.subscribe(this.refresh, this);

            quote.shippingAddress.subscribe(function (shippingAddress) {
                if (dataRefresh.shouldRefresh(shippingAddress.countryId, shippingAddress.postcode)) {
                    getCheckoutData(shippingAddress.countryId, shippingAddress.postcode);
                }
            });

            return this;
        },

        /**
         * @private
         */
        refresh: function () {
            var shippingMethod = quote.shippingMethod();

            if (!shippingMethod) {
                return;
            }

            carrierData = checkoutData.getByCarrier(shippingMethod.carrier_code);
            if (!carrierData || carrierData.service_options.length === 0) {
                this.visible(false);
                return;
            }

            if (
                shippingMethod.carrier_code === this.lastCarrierCode
                && this.visible()
                && this.lastDataHash === checkoutData.getHash()
            ) {
                return;
            }

            if (this.lastDataHash !== checkoutData.getHash()) {
                // reset selections if the shipping option settings have changed
                selections.reset();
            }

            if (carrierData.hasOwnProperty('metadata')) {
                this.image(carrierData.metadata.image_url);
                this.title(carrierData.metadata.title);
                this.displayColor(carrierData.metadata.color);
                this.commentsBefore(carrierData.metadata.comments_before);
                this.commentsAfter(carrierData.metadata.comments_after);
            }

            // set visible and memorize current carrier
            this.visible(true);
            this.lastCarrierCode = shippingMethod.carrier_code;
            this.lastDataHash = checkoutData.getHash();
            storage.set(SHIPPING_OPTION_CACHE_KEY, this.lastDataHash);

            this.updateFootnotes();

            selections.get().subscribe(function () {
                this.updateFootnotes();
                this.recalculateTotals();
            }.bind(this));

            this.destroyChildren();
            generateShippingOptions(carrierData.service_options, this.name);

            enforceCompatibility();
        },

        /**
         * Sync selections with Magento server and recalculate totals.
         *
         * @private
         */
        recalculateTotals: function () {
            updateSelection().done(function () {
                defaultTotalsProcessor.estimateTotals(quote.shippingAddress());
            });
        },

        /**
         * Update footnotes which may depend on current service selection.
         *
         * @private
         */
        updateFootnotes: function () {
            if (carrierData.hasOwnProperty('metadata')) {
                this.footnotes(footnotes.filterAvailable(
                    carrierData.metadata.footnotes
                ));
            }
        }
    });
});
