define([
    "Magento_Ui/js/form/components/fieldset",
    "Netresearch_ShippingUi/js/action/shipping-option/generate-components",
], function (Component, generateComponents) {
    'use strict';

    return Component.extend({
        defaults: {
            selectedOrderItems: [],
            shippingOptions: [],
            itemId: false,
            additionalClasses: 'nrshipping-item',
            listens: {
                selectedOrderItems: 'handleChangedItemSelection',
            }
        },

        /**
         * @constructor
         * @return {exports}
         */
        initialize: function () {
            return this._super()
                .initChildComponents();
        },

        /**
         * Automatically create child components from a configuration json.
         *
         * @private
         */
        initChildComponents: function () {
            generateComponents(this.shippingOptions, this.name, this.itemId);
        },

        /**
         * Helper function to toggle visibility when selected order items change.
         * Is triggered depending on external component config.
         *
         * @param {string[]} items
         */
        handleChangedItemSelection: function (items) {
            if (this.orderItemId) {
                this.visible(items.includes(this.orderItemId));
            }
        }
    });
});
