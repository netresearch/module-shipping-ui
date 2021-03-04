define([
    'jquery',
    'Netresearch_ShippingUi/js/view/shipping-option-input',
    'Netresearch_ShippingUi/js/action/checkout/webapi/get-locations',
    'Netresearch_ShippingUi/js/model/checkout/country-options',
    'Netresearch_ShippingUi/js/model/map',
    'Netresearch_ShippingUi/js/model/current-carrier',
    'Netresearch_ShippingUi/js/model/shipping-option/selections',
    'Netresearch_ShippingUi/js/model/selected-location',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote'
], function ($, Component, getLocations, countryOptions,  map, currentCarrier, selections, selectedLocation, modal, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            modal: null,
            modalId: "modal-locationfinder",
            modalMapId: "modal-locationfinder-map",
            buttonLabel: '${ $.shippingOptionInput.label }',
            selectedLocation: null,
            searchCity: null,
            searchStreet: null,
            searchZip: null,
            searchCountry: null,
            searchCountryOptions: [],
            errorMessage: '',
            listens: {
                selectedLocation: "updateSelections"
            }
        },

        initObservable: function () {
            this._super();
            this.observe([
                'selectedLocation',
                'searchCity',
                'searchStreet',
                'searchZip',
                'searchCountry',
                'searchCountryOptions',
                'errorMessage'
            ]);

            return this;
        },

        initialize: function () {
            this._super();

            this.updateSearchValues(quote.shippingAddress());
            quote.shippingAddress.subscribe(function (address) {
                this.updateSearchValues(address);
            }.bind(this));

            this.selectedLocation(selectedLocation.get(this.shippingOption.code));
            this.searchCountryOptions(countryOptions.get(currentCarrier.get(), this.shippingOptionCode));

            return this;
        },

        /**
         * Initialize the locationfinder modal als jQuery UI plugin.
         * Method is executed from template on afterRender event
         * to make sure the container is available in the DOM.
         */
        initModal: function () {
            this.modal = modal(
                {responsive: true, buttons: []},
                $('#' + this.modalId)
            );
        },

        updateSearchValues: function (address) {
            this.searchCity(typeof address.city === 'undefined' ? '' : address.city);
            this.searchCountry(address.countryId);
            this.searchStreet(typeof address.street === 'undefined' ? '' : address.street.join(' '));
            this.searchZip(address.postcode);
        },

        /**
         * Make sure to deinitialize the modal on destroy
         *
         * @param {Boolean} skipUpdate
         */
        destroy: function (skipUpdate) {
            if (this.modal) {
                this.modal.modal.remove();
            }

            this._super(skipUpdate);
        },

        /**
         * Update the selections model when the selectedLocation changes
         *
         * @param {NrShippingLocation|null} location
         */
        updateSelections: function (location) {
            if (location === null) {
                this.value(false);
                selectedLocation.reset(this.shippingOption.code);
            } else {
                this.value(true);
                selections.addSelection(this.shippingOption.code, 'type', location.shop_type);
                selections.addSelection(this.shippingOption.code, 'id', location.shop_id);
                selections.addSelection(this.shippingOption.code, 'number', location.shop_number);
                selections.addSelection(this.shippingOption.code, 'displayName', location.display_name);
                selections.addSelection(this.shippingOption.code, 'company', location.address.company);
                selections.addSelection(this.shippingOption.code, 'countryCode', location.address.country_code);
                selections.addSelection(this.shippingOption.code, 'postalCode', location.address.postal_code);
                selections.addSelection(this.shippingOption.code, 'city', location.address.city);
                selections.addSelection(this.shippingOption.code, 'street', location.address.street);
            }
        },

        /**
         * @public
         * @param {NrShippingLocation} location
         */
        selectLocation: function (location) {
            this.selectedLocation(location);
            if (this.modal) {
                this.modal.closeModal();
            }
        },

        /**
         * Reset selected location
         */
        removeLocation: function () {
            this.selectedLocation(null);
        },

        /**
         * Open the modal, initialize the map and load locations.
         */
        openModal: function () {
            this.modal.openModal();
            map.init(this.modalMapId, 51.163375, 10.447683, 6, this.errorMessage);
            this.updateLocations();
        },

        /**
         * update locations
         */
        updateLocations: function () {
            this.errorMessage('');
            getLocations(
                currentCarrier.get(),
                {
                    country_code: this.searchCountry(),
                    postal_code: this.searchZip(),
                    city: this.searchCity(),
                    street: this.searchStreet()
                }
            )
            .done(/** @param {NrShippingLocation[]} locations */ function (locations) {
                map.setLocations(locations);
            })
            .fail(function (response) {
                this.errorMessage(response.responseJSON.message);
            }.bind(this));
        }
    });
});
