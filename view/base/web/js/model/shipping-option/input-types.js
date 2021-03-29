define([], function () {
    'use strict';

    /**
     * @typedef {{template: string, component: string}} InputType
     * @type {Object.<string, InputType>}
     */
    var templates = {
        hidden: {
            template: 'Netresearch_ShippingUi/form/element/hidden',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        text: {
            template: 'Netresearch_ShippingUi/form/element/input',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        number: {
            template: 'Netresearch_ShippingUi/form/element/number',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        prefixed: {
            template: 'Netresearch_ShippingUi/form/element/prefixed-text',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        suffixed: {
            template: 'Netresearch_ShippingUi/form/element/suffixed-text',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        checkbox: {
            template: 'Netresearch_ShippingUi/form/element/checkbox',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        radio: {
            template: 'Netresearch_ShippingUi/form/element/radio',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        radioset: {
            template: 'Netresearch_ShippingUi/form/element/radio',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        time: {
            template: 'Netresearch_ShippingUi/form/element/radio-styled',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        date: {
            template: 'Netresearch_ShippingUi/form/element/radio-styled',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        textarea: {
            template: 'Netresearch_ShippingUi/form/element/textarea',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        select: {
            template: 'Netresearch_ShippingUi/form/element/select',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        static: {
            template: 'Netresearch_ShippingUi/form/element/static',
            component: 'Netresearch_ShippingUi/js/view/shipping-option-input'
        },
        locationfinder: {
            template: 'Netresearch_ShippingUi/form/element/locationfinder',
            component: 'Netresearch_ShippingUi/js/view/input/locationfinder'
        }
    };

    return {
        /**
         * Retrieve a template path for a type of template.
         *
         * @param {string} type
         * @return {InputType} - Will return false if no template path is configured for this type.
         */
        get: function (type) {
            if (templates[type]) {
                return templates[type];
            }

            if (type === false) {
                throw 'This type is not defined: ' + type;
            }
        }
    };
});
