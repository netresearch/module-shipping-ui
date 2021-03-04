define([
    "uiElement",
    "uiRegistry"
], function (Component, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            location: null
        },

        handleLocationSelect: function () {
            registry.get({component:'Netresearch_ShippingUi/js/view/input/locationfinder'}, function (locationFinder) {
                locationFinder.selectLocation(this.location);
            }.bind(this));
        }
    });
});
