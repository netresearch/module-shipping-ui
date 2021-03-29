define([
    'uiComponent',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Magento_Customer/js/customer-data'
], function (Component, selections, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Netresearch_ShippingUi/checkout/address-renderer/delivery-location',
            deliveryLocationData: {}
        },

        initialize: function () {
            this._super();
            this.deliveryLocationData(selections.getShippingOptionValue('deliveryLocation'));

            selections.get().subscribe(function () {
                this.deliveryLocationData(selections.getShippingOptionValue('deliveryLocation'));
            }.bind(this));
        },

        initObservable: function () {
            this._super();
            this.observe('deliveryLocationData');
            return this;
        },

        getCountryName: function () {
            var countryData = customerData.get('directory-data')();

            return countryData[this.deliveryLocationData().countryCode]
                ? countryData[this.deliveryLocationData().countryCode].name
                : this.deliveryLocationData().countryCode;
        }
    });
});
