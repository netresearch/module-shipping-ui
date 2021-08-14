define([
    'Netresearch_ShippingUi/js/model/shipping-option/selections'
], function (selections) {
    'use strict';

    var virtualInputCodes =  [
        'type',
        'id',
        'number',
        'displayName',
        'company',
        'countryCode',
        'postalCode',
        'city',
        'street'
    ];

    /**
     * This model can be used to sync a NrShippingLocation
     * with the customer's service selections.
     *
     * It creates virtual input selections as needed.
     */
    return {
        /**
         * @param {string} optionCode
         * @return {NrShippingLocation|null}
         */
        get: function (optionCode) {
            if (selections.getShippingOptionValue(optionCode, 'id')) {
                return {
                    shop_type: selections.getShippingOptionValue(optionCode, 'type'),
                    shop_id: selections.getShippingOptionValue(optionCode, 'id'),
                    shop_number: selections.getShippingOptionValue(optionCode, 'number'),
                    display_name: selections.getShippingOptionValue(optionCode, 'displayName'),
                    address: {
                        company: selections.getShippingOptionValue(optionCode, 'company'),
                        country_code: selections.getShippingOptionValue(optionCode, 'countryCode'),
                        postal_code: selections.getShippingOptionValue(optionCode, 'postalCode'),
                        city: selections.getShippingOptionValue(optionCode, 'city'),
                        street: selections.getShippingOptionValue(optionCode, 'street')
                    }
                };
            }

            return null;
        },

        /**
         * @param {string} optionCode
         * @param {NrShippingLocation} location
         */
        set: function (optionCode, location) {
            selections.addSelection(optionCode, 'type', location.shop_type);
            selections.addSelection(optionCode, 'id', location.shop_id);
            selections.addSelection(optionCode, 'number', location.shop_number);
            selections.addSelection(optionCode, 'displayName', location.display_name);
            selections.addSelection(optionCode, 'company', location.address.company);
            selections.addSelection(optionCode, 'countryCode', location.address.country_code);
            selections.addSelection(optionCode, 'postalCode', location.address.postal_code);
            selections.addSelection(optionCode, 'city', location.address.city);
            selections.addSelection(optionCode, 'street', location.address.street);
        },

        /**
         * @param {string} optionCode
         */
        reset: function (optionCode) {
            virtualInputCodes.forEach(function (inputCode) {
                selections.removeSelection(optionCode, inputCode);
            });
        }

    };
});
