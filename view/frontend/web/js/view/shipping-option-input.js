define([
    'Magento_Ui/js/form/element/abstract',
    'Netresearch_ShippingUi/js/model/checkout/footnotes',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/action/shipping-option/validation/enforce-compatibility',
], function (Component, footnotes, selections, enforceCompatibility) {
    'use strict';

    return Component.extend({
        /**
         * @property {NrShippingOption} shippingOption
         */
        shippingOption: {},

        /**
         * @property {NrShippingInput} shippingOptionInput
         */
        shippingOptionInput: {},

        defaults: {
            template: 'Netresearch_ShippingUi/form/field',
            isInputComponent: true,
            shippingOptionCode: '',
            inputCode: '${ $.shippingOptionInput.code }',
            elementTmpl: '',
            value: '',
            options: [],
            comment: {},
            label: '${ $.shippingOptionInput.label }',
            labelVisible: '${ $.shippingOptionInput.label_visible }',
            description: '${ $.shippingOptionInput.label }',
            inputName: '${ $.shippingOptionInput.code }',
            autocomplete: '${ $.shippingOptionInput.code }',
            placeholder: '${ $.shippingOptionInput.placeholder }',
        },

        initObservable: function () {
            this._super();
            this.value.extend({rateLimit: {timeout: 50, method: 'notifyWhenChangesStop'}});

            return this;
        },

        /**
         * Update the shipping option selections model and trigger additional validation.
         * Automatically executed when this.value changes.
         *
         * @protected
         * @param {string} newValue
         */
        onUpdate: function (newValue) {
            this._super();

            if (newValue) {
                selections.addSelection(this.shippingOption.code, this.shippingOptionInput.code, newValue);
            } else {
                selections.removeSelection(this.shippingOption.code, this.shippingOptionInput.code);
            }
            enforceCompatibility();
        },

        /**
         * @param {NrShippingComment} comment
         * @return {boolean}
         */
        showAsterisk: function (comment) {
            if (!comment.footnote_id) {
                return false;
            }
            return footnotes.isFootnoteVisible(comment.footnote_id);
        }
    });
});
