define([
    'ko',
    'Netresearch_ShippingUi/js/action/util/hash',
    'underscore'
], function (ko, hash, _) {
    'use strict';

    /**
     * @callback NrShippingSettingsObservable
     * @param {NrShippingSettings} [value]
     * @return {NrShippingSettings}
     *
     * @var {NrShippingSettingsObservable} settings
     */
    var settings = ko.observable({});

    /**
     * Here come the type definitions of the NrShippingSettings coming from the Magento REST endpoint.
     * They are defined in PHP at \Netresearch\ShippingCore\Api\Data\ShippingSettings\CarrierDataInterface
     *
     * @typedef {{carriers: NrShippingCarrier[]}} NrShippingSettings
     *
     * @typedef {{
     *     code: string,
     *     compatibility_data: NrShippingCompatibility[],
     *     service_options: NrShippingOption[],
     *     package_options: NrShippingOption[],
     *     item_options: NrShippingItemOption[],
     *     metadata: {
     *         comments_after: NrShippingComment[],
     *         comments_before: NrShippingComment[],
     *         logo_url: string,
     *         logo_width: string,
     *         title: string,
     *         color: string,
     *         footnotes: NrShippingFootnote[],
     *     }
     * }} NrShippingCarrier
     *
     * @typedef {{
     *     content: string,
     *     id: string,
     *     subjects: string[],
     *     subjects_must_be_selected: boolean,
     *     subjects_must_be_available: boolean,
     * }} NrShippingFootnote
     *
     * @typedef {{
     *     subjects: string[],
     *     error_message: string,
     *     masters: string[],
     *     trigger_value: string,
     *     action: string
     * }} NrShippingCompatibility
     *
     * @typedef {{
     *     exclude_destinations: string[],
     *     include_destinations: string[],
     *     origin: string
     * }} NrShippingRoute
     *
     * @typedef {{
     *     available_at_postal_facility: boolean,
     *     code: string,
     *     inputs: NrShippingInput[],
     *     label: string,
     *     packaging_readonly: boolean,
     *     sort_order: int,
     *     routes: NrShippingRoute[]
     * }} NrShippingOption
     *
     * @typedef {{
     *     code: string,
     *     comment: NrShippingComment,
     *     default_value: string,
     *     input_type: string,
     *     label: string,
      *    label_visible: bool,
     *     options: {label: string, value: string, disabled: boolean},
     *     disabled: boolean,
     *     placeholder: string,
     *     sort_order: int,
     *     tooltip: string,
     *     validation_rules: {name: string, params: mixed}[],
     *     item_combination_rule: NrShippingItemCombinationRule,
     *     value_maps: NrShippingValueMap[]
     * }} NrShippingInput
     *
     * @typedef {{
     *     source_item_input_code: string,
     *     additional_source_input_codes: string[],
     *     action: string,
     * }} NrShippingItemCombinationRule
     *
     * @typedef {{
     *     content: string,
     *     footnote_id: string,
     * }} NrShippingComment
     *
     * @typedef {{
     *     source_value: string,
     *     input_values: {code: string, value: string}[],
     * }} NrShippingValueMap
     *
     * @typedef {{
     *     item_id: int,
     *     shipping_options: NrShippingOption[]
     * }} NrShippingItemOption
     */

    return {
        /**
         * @return {NrShippingSettingsObservable}
         */
        get: function () {
            return settings;
        },

        /**
         * @param {NrShippingSettings} data
         */
        set: function (data) {
            settings(data);
        },

        /**
         * @param {string} carrierName
         * @return {NrShippingCarrier|boolean}
         */
        getByCarrier: function (carrierName) {
            var carrierData;

            if (!('carriers' in settings())) {
                return false;
            }

            carrierData = _.find(settings().carriers, function (carrier) {
                return carrier.code === carrierName;
            });

            return carrierData ? carrierData : false;
        },

        /**
         * Generates a numeric hash from the JSON string of the available data
         * @return {number}
         */
        getHash: function () {
            var jsonString = JSON.stringify(settings());
            return hash(jsonString);
        }
    };
});
