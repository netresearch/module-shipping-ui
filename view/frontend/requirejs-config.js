var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Netresearch_ShippingUi/js/mixin/checkout/set-shipping-information': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Netresearch_ShippingUi/js/mixin/checkout/shipping-information': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Netresearch_ShippingUi/js/mixin/checkout/place-order': true
            },
            'Magento_Customer/js/model/customer/address': {
                'Netresearch_ShippingUi/js/mixin/checkout/ship-to-address': true
            },
            'Magento_Checkout/js/model/new-customer-address': {
                'Netresearch_ShippingUi/js/mixin/checkout/ship-to-address': true
            }
        }
    },
    paths: {
        leaflet: 'Netresearch_ShippingUi/lib/leaflet/leaflet'
    }
};
