define([
    'underscore',
    'mage/translate',
    'Magento_Ui/js/lib/validation/utils'
], function (_, $t, utils) {
    'use strict';

    /**
     * This mixin adds custom input validation routines to the core validation flow.
     */
    return function (validator) {

        /**
         * @type {string[]}
         */
        var specialChars = ['<', '>', '\\n', '\\r', '\\', '\'', '"', ';', '+'];

        /**
         * @param {string} value
         * @param {string[]} denyList
         * @return {boolean}
         */
        var isOnDenyList = function (value, denyList) {
            return undefined !== _.find(denyList, function (denyListItem) {
                return value.toLowerCase().indexOf(denyListItem) !== -1;
            });
        };

        /**
         * Validator to disallow special chars in the input value.
         */
        validator.addRule(
            'nrshipping-validate-no-special-chars',
            function (value) {
                return !isOnDenyList(value, specialChars);
            },
            $t('Your input must not include one of the following: ') + specialChars.join(' ')
        );

        return validator;
    };
});
