/**
 * See LICENSE.md for license details.
 */
define([
    'knockout',
    'jquery',
    'leaflet',
    'Netresearch_ShippingUi/js/model/template-renderer',
    'text!Netresearch_ShippingUi/template/location/popup.html',
    'uiRegistry'
], function (ko, $, leaflet, tmplRenderer, popupHtml, registry) {
    'use strict';

    /**
     * Let location popup template be parsed by Magento
     * to init component.
     *
     * @param {NrShippingLocation} location
     * @param {leaflet.Popup} popup
     */
    var initTemplateBindings = function (location, popup) {
        var containerId = 'map-popup-container-' + location.shop_id,
            componentName = 'locationfinder-map-popup-' + location.shop_id;

        // parse x-magento-init script
        $(document.getElementById(containerId)).trigger('contentUpdated');
        registry.get(componentName, function (component) {
            // apply ko bindings on template
            var element = document.getElementById(containerId);

            try {
                ko.applyBindings(component, element);
            } catch (e) {
                // if the binding is already applied everything is fine.
            }
            popup.update();
        });
    };

    return {
        /**
         * Popups are initialized as custom JavaScript components
         * with full knockout.js functionality.
         *
         * @param {NrLocation} location
         * @return {leaflet.Marker}
         */
        createPopupMarker: function (location) {
            var marker = leaflet.marker(leaflet.latLng(
                location.latitude,
                location.longitude
            ));

            if (location.icon) {
                marker.setIcon(
                    leaflet.icon({
                        iconUrl: location.icon,
                        iconAnchor: [23, 23]
                    })
                );
            }

            marker.bindPopup(
                leaflet.popup({
                    minWidth: 200
                }).setContent(tmplRenderer.render(
                    {location: location},
                    popupHtml
                ))
            );

            marker.on('popupopen', function (event) {
                initTemplateBindings(location, event.popup);
            });

            return marker;
        }
    };
});
