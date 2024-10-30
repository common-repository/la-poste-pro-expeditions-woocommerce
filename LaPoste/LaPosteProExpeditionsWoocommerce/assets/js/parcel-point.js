(function () {
    const Components = {};

    Components.api = {
        ajaxUrl: null,
        getShippingMethodExtraLabelNonce: null,
        getPointsNonce: null,
        setPointNonce: null,

        setApiConfiguration: function(ajaxUrl, getShippingMethodExtraLabelNonce, getPointsNonce, setPointNonce) {
            const self = this;
            self.ajaxUrl = ajaxUrl;
            self.getShippingMethodExtraLabelNonce = getShippingMethodExtraLabelNonce;
            self.getPointsNonce = getPointsNonce;
            self.setPointNonce = setPointNonce;
        },

        selectPoint: function (carrier, packageKey, code, name, network, address, zipcode, city, country, openingHours, distance, resolve, reject) {
            const self = this;
            const setPointRequest = new XMLHttpRequest();
            setPointRequest.onreadystatechange = function() {
                if (setPointRequest.readyState === 4) {
                    const response = self.getRequestResponse(setPointRequest);
                    if (self.isValidResponse(response)) {
                        resolve({ data: response.data, name, address, zipcode, city, distance });
                    } else {
                        reject(response);
                    }
                }
            };
            setPointRequest.open('POST', self.ajaxUrl);
            setPointRequest.setRequestHeader(
                'Content-Type',
                'application/x-www-form-urlencoded'
            );
            setPointRequest.responseType = 'json';
            setPointRequest.send('action=laposteproexp_set_point'
            + '&carrier=' + encodeURIComponent(carrier)
            + '&code=' + encodeURIComponent(code)
            + '&name=' + encodeURIComponent(name)
            + '&address=' + encodeURIComponent(address)
            + '&zipcode=' + encodeURIComponent(zipcode)
            + '&city=' + encodeURIComponent(city)
            + '&country=' + encodeURIComponent(country)
            + '&openingHours=' + encodeURIComponent(openingHours)
            + '&network=' + encodeURIComponent(network)
            + '&packageKey=' + encodeURIComponent(packageKey)
            + '&_wpnonce=' + encodeURIComponent(self.setPointNonce));
        },

        getParcelPoints: function(carrier, packageKey, resolve, reject) {
            const self = this;
            const httpRequest = new XMLHttpRequest();
            httpRequest.onreadystatechange = function() {
                if (httpRequest.readyState === 4) {
                    const response = self.getRequestResponse(httpRequest);
                    if (self.isValidResponse(response)) {
                        resolve(response.data);
                    } else {
                        reject(response);
                    }
                }
            };
            httpRequest.open('POST', self.ajaxUrl);
            httpRequest.setRequestHeader(
                'Content-Type',
                'application/x-www-form-urlencoded'
            );
            httpRequest.responseType = 'json';
            httpRequest.send('action=laposteproexp_get_points'
                + '&carrier=' + encodeURIComponent(carrier)
                + '&packageKey=' + encodeURIComponent(packageKey)
                + '&_wpnonce=' + encodeURIComponent(self.getPointsNonce)
            );
        },

        getShippingMethodExtraLabel: function(shippingMethod, packageKey, resolve, reject) {
            const self = this;
            const httpRequest = new XMLHttpRequest();
            httpRequest.onreadystatechange = function() {
                if (httpRequest.readyState === 4) {
                    const response = self.getRequestResponse(httpRequest);
                    if (self.isValidResponse(response)) {
                        resolve(response.data);
                    } else {
                        reject(response);
                    }
                }
            };
            httpRequest.open('POST', self.ajaxUrl);
            httpRequest.setRequestHeader(
                'Content-Type',
                'application/x-www-form-urlencoded'
            );
            httpRequest.responseType = 'json';
            httpRequest.send('action=laposteproexp_get_shipping_method_extra_label'
                + '&shippingMethod=' + encodeURIComponent(shippingMethod)
                + '&packageKey=' + encodeURIComponent(packageKey)
                + '&_wpnonce=' + encodeURIComponent(self.getShippingMethodExtraLabelNonce)
            );
        },

        isValidResponse: function(response) {
            return typeof response === 'object'
                && response !== null 
                && true === response.success 
                && 'data' in response;
        },

        getRequestResponse: function (request) {
            return typeof request.response === 'object' && request.response !== null
                ? request.response
                : JSON.parse(request.response);

        },
    }

    Components.util = {
        on: function(elSelector, eventName, selector, fn) {

            if (typeof jQuery !== 'undefined') {
                jQuery(elSelector).on(eventName, selector, fn);
            } else {
                const element = document.querySelector(elSelector);
    
                element.addEventListener(eventName, function(event) {
                    const possibleTargets = element.querySelectorAll(selector);
                    const target = event.target;
    
                    for (let i = 0, l = possibleTargets.length; i < l; i++) {
                        let el = target;
                        const p = possibleTargets[i];
    
                        while(el && el !== element) {
                            if (el === p) {
                                return fn.call(p, event);
                            }
    
                            el = el.parentNode;
                        }
                    }
                });
            }
        },

        observeDom: function(target, match, callback) {
            let observer;
            observer = new MutationObserver((mutations) => {
                for (const mutation of mutations) {
                    if (match(mutation)) {
                        setTimeout(() => callback());
                        break;
                    }
                }
            })
            
            observer.observe(target, {
                childList: true,
                subtree: true,
                attributes: true,
                characterData: false,
            });

            return observer;
        },

        formatDistance: function(distance) {
            const self = this;

            var kmAway = typeof wp !== 'undefined' && 'i18n' in wp
                ? wp.i18n.__('%skm away', 'la-poste-pro-expeditions-woocommerce' )
                : Components.util.translate( '%skm away' );
            let result = null;
            if ( null !== distance ) {
                distance = Math.round( distance / 100 ) / 10;
                if (!isNaN(distance)) {
                    result  = ' (' + self.sprintf( kmAway, distance ) + ')';
                }
            }
            return result;
        },

        formatParcelPoingAddress: function( address, city, zipcode, distance ) {
            const self = this;
            const ziptown = [zipcode, city].filter(v => v !== null).join(', ');

            let result = [address, ziptown].join(' ');

            distance = Components.util.formatDistance(distance);
            if ( null !== distance ) {
                result += ' ' + distance;
            }

            return result;
        },

        fillSpaces(value, wantedSize) {
            while(value.length < wantedSize) {
                value += ' ';
            }
            return value;
        },

        formatOpeningDays(openingDays) {
            var parsedDays = [];
            var emptyPeriod = Components.util.fillSpaces('', 11);

            for (var i = 0; i < openingDays.length; i++) {
                var openingDay = openingDays[i];

                if (openingDay.weekday) {
                    var parsedDay = openingDay.weekday[0] + ' ';
                    var openingPeriods = openingDay.openingPeriods;
                    var parsedPeriods = [];

                    for (var j = 0; j < openingPeriods.length; j++) {
                        var openingPeriod = openingPeriods[j];
                        var open = openingPeriod.openingTime === undefined ? '' : openingPeriod.openingTime;
                        var close = openingPeriod.closingTime === undefined ? '' : openingPeriod.closingTime;

                        if (open !== '' && close !== '') {
                            parsedPeriods.push(open + '-' + close);
                        } else {
                            parsedPeriods.push(emptyPeriod);
                        }
                    }

                    parsedDay += parsedPeriods.join(' ');

                    if (i % 2 === 1) {
                      parsedDay = '<span style="background-color: #d8d8d8;">' + parsedDay + '</span>';
                    }

                    parsedDays.push(parsedDay);
                }
            }

            return '<pre class="' + 'laposteproexp-parcel-point-schedule">' + parsedDays.join('\n') + '</pre>';
        },

        formatHours: function(time) {
            const explode = time.split(':');
            if (3 === explode.length) {
                time = explode[0]+':'+explode[1];
            }
            return time;
        },

        isWoocommerceBlocks() {
            return 'wc' in window
                && 'blocksCheckout' in window.wc
                && 'wcSettings' in window.wc
                && window.wc.wcSettings.getSetting('la-poste-pro-expeditions-woocommerce-parcel-point_data');
        },

        isI18nEnabled() {
            return typeof wp !== 'undefined' && 'i18n' in wp;
        },

        translate(key) {
            let result = key;
            
            if (typeof translations !== 'undefined' && key in translations) {
                result = translations[key];
            }

            return result;
        },

        sprintf(text, ...values) {
            if (typeof sprintf !== 'undefined') {
                return sprintf( text, ...values );
            } else {
                for(const value of values) {
                    text = text.replace('%s', value);
                }
                return text;
            }
        }
    }

    Components.map = {
        mapContainer: null,
        map: null,
        markers: [],
        mapUrl: null,
        mapLogoImageUrl: null,
        mapLogoHrefUrl: null,

        setMapConfiguration: function(mapUrl, mapLogoImageUrl, mapLogoHrefUrl) {
            const self = this;
            self.mapUrl = mapUrl;
            self.mapLogoImageUrl = mapLogoImageUrl;
            self.mapLogoHrefUrl = mapLogoHrefUrl;
        },

        init: function() {
            const self = this;
            const mapClose = document.createElement('div');
            self.mapContainer = document.querySelector('#laposteproexp-map');
            if (self.mapContainer) {
                return;
            }

            var mapCloseTitle = Components.util.isI18nEnabled()
                ? wp.i18n.__( 'Close map', 'la-poste-pro-expeditions-woocommerce' )
                : Components.util.translate('Close map');

            mapClose.setAttribute('class', 'laposteproexp-close');
            mapClose.setAttribute('title', mapCloseTitle);
            mapClose.addEventListener('click', function() {
                self.close()
            });

            const mapCanvas = document.createElement('div');
            mapCanvas.setAttribute('id', 'laposteproexp-map-canvas');

            const mapInner = document.createElement('div');
            mapInner.setAttribute('id', 'laposteproexp-map-container');
            mapInner.appendChild(mapCanvas);

            const mapPPContainer = document.createElement('div');
            mapPPContainer.setAttribute('id', 'laposteproexp-pp-container');

            const mapOuter = document.createElement('div');
            mapOuter.setAttribute('id', 'laposteproexp-map-inner');
            mapOuter.appendChild(mapClose);
            mapOuter.appendChild(mapInner);
            mapOuter.appendChild(mapPPContainer);

            self.mapContainer = document.createElement('div');
            self.mapContainer.setAttribute('id', 'laposteproexp-map');
            self.mapContainer.appendChild(mapOuter);
            document.body.appendChild(self.mapContainer);

            self.map = new mapboxgl.Map({
                container: 'laposteproexp-map-canvas',
                style: self.mapUrl,
                zoom: 14,
				accessToken: 'whatever'
            });
            self.map.addControl(new mapboxgl.NavigationControl());

            const logoImg = document.createElement('img');
            logoImg.setAttribute('src', self.mapLogoImageUrl);
            const logoLink = document.createElement('a');
            logoLink.setAttribute('href', self.mapLogoHrefUrl);
            logoLink.setAttribute('target', '_blank');
            logoLink.appendChild(logoImg);
            const logoContainer = document.createElement('div');
            logoContainer.setAttribute('id', 'laposteproexp-logo');
            logoContainer.appendChild(logoLink);

            const mapTopLeftCorner = document.querySelector('.mapboxgl-ctrl-top-left');
            if (mapTopLeftCorner) {
                mapTopLeftCorner.appendChild(logoContainer);
            }
        },

        open: function() {
            this.mapContainer.classList.add('laposteproexp-modal-show');
            let offset = window.pageYOffset + (window.innerHeight - this.mapContainer.offsetHeight)/2;
            if (offset < window.pageYOffset) {
                offset = window.pageYOffset;
            }
            this.mapContainer.style.top = offset + 'px';
            this.map.resize();
        },

        close: function() {
            this.mapContainer.classList.remove('laposteproexp-modal-show');
            this.clearMarkers();
        },

        addParcelPointMarkers: function(parcelPoints) {
            for (let i = 0; i < parcelPoints.length; i++) {
                parcelPoints[i].index = i;
                this.addParcelPointMarker(parcelPoints[i]);
            }
        },

        addParcelPointMarker: function(point) {
            const self = this;

            var chooseParcelPoint = Components.util.isI18nEnabled()
                ? wp.i18n.__( 'Choose this parcel point', 'la-poste-pro-expeditions-woocommerce' )
                : Components.util.translate('Choose this parcel point');
            var openingHours = Components.util.isI18nEnabled()
                ? wp.i18n.__( 'Opening hours', 'la-poste-pro-expeditions-woocommerce' )
                : Components.util.translate('Opening hours');

            let info ='<div class="laposteproexp-marker-popup"><b>'+point.parcelPoint.name+'</b><br/>'+
                '<a href="#" class="laposteproexp-parcel-point-button" ' + this.generateParcelPointTagData(point) + '><b>'+ chooseParcelPoint +'</b></a><br/>' +
                point.parcelPoint.location.street+', '+point.parcelPoint.location.zipCode+' '+point.parcelPoint.location.city+'<br/><b>' + openingHours +
                '</b><br/>';

            info += Components.util.formatOpeningDays(point.parcelPoint.openingDays);

            const el = this.getMarkerHtmlElement(point.index + 1);

            const popup = new mapboxgl.Popup({ offset: 25 })
                .setHTML(info);

            const marker = new mapboxgl.Marker({
                element: el,
				anchor: 'bottom'
            })
                .setLngLat(new mapboxgl.LngLat(parseFloat(point.parcelPoint.location.position.longitude), parseFloat(point.parcelPoint.location.position.latitude)))
                .setPopup(popup)
                .addTo(self.map);
            
            self.markers.push(marker);

            self.addRightColMarkerEvent(marker, point.parcelPoint.code);
        },

        generateParcelPointTagData: function(point) {
            return ' data-code="'    + point.parcelPoint.code + '" ' +
                    'data-name="'    + encodeURIComponent(point.parcelPoint.name) + '" ' +
                    'data-network="' + point.parcelPoint.network + '" ' +
                    'data-zipcode="' + encodeURIComponent(point.parcelPoint.location.zipCode) + '" ' +
                    'data-country="' + encodeURIComponent(point.parcelPoint.location.country) + '" ' +
                    'data-city="'    + encodeURIComponent(point.parcelPoint.location.city) + '" ' +
                    'data-street="'  + encodeURIComponent(point.parcelPoint.location.street) + '" ' +
                    'data-openinghours="'  + encodeURIComponent(JSON.stringify(point.parcelPoint.openingDays)) + '" ' +
                    'data-distance="'  + encodeURIComponent(JSON.stringify(point.distanceFromSearchLocation)) + '" ';
        },

        addRightColMarkerEvent: function(marker, code) {
            Components.util.on('body', 'click', '.laposteproexp-show-info-' + code, function(){
                marker.togglePopup();
            });
        },

        addRecipientMarker: function(location) {
            const self = this;

            const el = document.createElement('div');
            el.className = 'laposteproexp-marker-recipient';

            const marker = new mapboxgl.Marker({
                element: el,
				anchor: 'bottom'
            })
                .setLngLat(new mapboxgl.LngLat(parseFloat(location.position.longitude), parseFloat(location.position.latitude)))
                .addTo(self.map);

            self.markers.push(marker);
        },

        setMapBounds: function() {

            let bounds = new mapboxgl.LngLatBounds();

            for (let i = 0; i < this.markers.length; i++) {
                const marker = this.markers[i];
                bounds = bounds.extend(marker.getLngLat());
            }

            this.map.fitBounds(
                bounds,
                {
                    padding: 30,
                    linear: true
                }
            );
        },

        fillParcelPointPanel: function(parcelPoints) {
            const self = this;

            var chooseParcelPoint = Components.util.isI18nEnabled()
                ? wp.i18n.__( 'Choose this parcel point', 'la-poste-pro-expeditions-woocommerce' )
                : Components.util.translate('Choose this parcel point');

            let html = '';
            html += '<table><tbody>';
            for (let i = 0; i < parcelPoints.length; i++) {
                const point = parcelPoints[i];
                const distance = Components.util.formatDistance(point.distanceFromSearchLocation);
                html += '<tr>';
                html += '<td>' + self.getMarkerHtmlElement(i+1).outerHTML;
                html += '<div class="' + 'laposteproexp-parcel-point-title"><a class="' + 'laposteproexp-show-info-' + point.parcelPoint.code + '">' + point.parcelPoint.name + '</a></div><br/>';
                html += point.parcelPoint.location.street + '<br/>';
                html += point.parcelPoint.location.zipCode + ' ' + point.parcelPoint.location.city + (distance !== null ? distance : '') + '<br/>';
                html += '<a class="' + 'laposteproexp-parcel-point-button" ' + this.generateParcelPointTagData(point) + '><b>'+ chooseParcelPoint + '</b></a>';
                html += '</td>';
                html += '</tr>';
            }
            html += '</tbody></table>';
            document.querySelector('#' + 'laposteproexp-pp-container').innerHTML = html;
        },

        getMarkerHtmlElement: function(index) {
            const el = document.createElement('div');
            el.className = 'laposteproexp-marker';
            el.innerHTML = index;
            return el;
        },

        clearMarkers: function() {
            for (let i = 0; i < this.markers.length; i++) {
                this.markers[i].remove();
            }
            this.markers = [];
        },

        getPoints: function(carrier, packageKey, reject) {
            const self = this;

            Components.api.getParcelPoints(
                carrier,
                packageKey,
                function(parcelPointResponse) {
                    self.addParcelPointMarkers(parcelPointResponse['nearbyParcelPoints']);
                    self.fillParcelPointPanel(parcelPointResponse['nearbyParcelPoints']);
                    self.addRecipientMarker(parcelPointResponse['searchLocation']);
                    self.setMapBounds();
                },
                function(err) {
                    if (typeof err === 'object' && 'data' in err) {
                        self.showError(err.data.message);
                    }
                }
            );
        },

    }

    Components.blocks = {
        cache: {},

        loading: false,

        init: function() {
            const self = this;

            // const { useEffect } = window.wp.element;
            // const { select } = window.wp.data;
            const { getSetting } = window.wc.wcSettings;
            const settings = getSetting('la-poste-pro-expeditions-woocommerce-parcel-point_data');

            if (settings) {
                Components.api.setApiConfiguration(
                    settings.ajaxurl,
                    settings.getShippingMethodExtraLabelNonce,
                    settings.getPointsNonce,
                    settings.setPointNonce
                );
                Components.map.setMapConfiguration(
                    settings.mapUrl,
                    settings.mapLogoImageUrl,
                    settings.mapLogoHrefUrl
                );

                let first = false;
                self.onCartChange(function() {
                    self.updateSelectedShippingMethodExtraLabel();
                    if (!first) {
                        first = true;
                        jQuery('body').on(
                            'input',
                            self.getShippintMethodInputsSelector(),
                            () => self.updateSelectedShippingMethodExtraLabel()
                        );
                    }
                });
    
                jQuery('body').on('click', '.laposteproexp-select-parcel', function() {
                    Components.map.init();
                    Components.map.open();
                    self.getMapPoints();
                });
    
                jQuery('body').on('click', '.laposteproexp-parcel-point-button', function() {
                    const { __ } = wp.i18n;
                    const shippingMethod = self.getSelectedShippingMethod();
                    const packageKey = self.getSelectedPackageKey();
                    if (!shippingMethod) {
                        self.showError(__( 'Unable to find carrier', 'la-poste-pro-expeditions-woocommerce'));
                    }
                    Components.api.selectPoint(shippingMethod,
                        packageKey,
                        this.getAttribute('data-code'),
                        decodeURIComponent(this.getAttribute('data-name')),
                        this.getAttribute('data-network'),
                        decodeURIComponent(this.getAttribute('data-street')),
                        decodeURIComponent(this.getAttribute('data-zipcode')),
                        decodeURIComponent(this.getAttribute('data-city')),
                        decodeURIComponent(this.getAttribute('data-country')),
                        decodeURIComponent(this.getAttribute('data-openinghours')),
                        decodeURIComponent(this.getAttribute('data-distance')),
                        function({ data }) {
                            self.updateShippingMethodExtraLabelCache(packageKey, shippingMethod, data.label);
                            self.refreshShippingMethodExtraLabel();
                            Components.map.close();
                        },
                        function(err) {
                            if (typeof err === 'object' && 'data' in err) {
                                self.showError(err.data.message);
                            }
                        }
                    );
                });
            } else {
                console.error('[la-poste-pro-expeditions-woocommerce] Failed to load plugin configuration (blocks)')
            }
        },

        getMapPoints: function() {
            const { __ } = wp.i18n;
            const self = this;
            const shippingMethod = self.getSelectedShippingMethod();
            const packageKey = self.getSelectedPackageKey();
            if (!shippingMethod || packageKey === null) {
                self.showError(__( 'Unable to find carrier', 'la-poste-pro-expeditions-woocommerce'));
            }

            Components.map.getPoints(shippingMethod, packageKey, (err) => self.showError(err));
        },

        updateSelectedShippingMethodExtraLabel: function() {
            const { __ } = wp.i18n;
            const self = this;
            self.refreshShippingMethodExtraLabel();

            const shippingMethod = self.getSelectedShippingMethod();
            const packageKey = self.getSelectedPackageKey();
            if (shippingMethod !== undefined && packageKey !== undefined && !self.loading) {
                self.loading = true;
                Components.api.getShippingMethodExtraLabel(shippingMethod, packageKey, function(response) {
                    self.updateShippingMethodExtraLabelCache(packageKey, shippingMethod, response.label);
                    self.refreshShippingMethodExtraLabel();
                    self.loading = false;
                }, function () {
                    self.showError(__( 'Unable to find carrier', 'la-poste-pro-expeditions-woocommerce'));
                    self.loading = false;
                });
            }
        },

        getSelectedShippingMethod: function() {
            return jQuery(this.getShippintMethodInputsSelector()).filter(':checked').val();
        },

        getSelectedPackageKey: function() {
            let packageKey = 0;

            const name = jQuery(this.getShippintMethodInputsSelector()).filter(':checked').attr('name');
            if (name) {
                const split = name.split('-');
                packageKey = split[split.length - 1];
            }

            return packageKey;
        },

        getShippintMethodInputsSelector: function() {
            const self = this;
            return self.getShippingMethodsBlockClasses()
                .map(className => '.' + className + ' ' + self.getShippintMethodsRadioControlSelector())
                .join(', ');
        },

        getShippintMethodsBlockSelector: function() {
            const self = this;
            return self.getShippingMethodsBlockClasses()
                .map(className => '.' + className)
                .join(', ');
        },

        getShippintMethodTextLabelSelector: function() {
            return '.wc-block-components-radio-control__label';
        },

        getShippingMethodsBlockClasses: function() {
            return [
                'wp-block-woocommerce-checkout-shipping-methods-block',
                'wp-block-woocommerce-cart-order-summary-shipping-block'
            ];
        },

        getShippintMethodsRadioControlSelector: function() {
            return '.wc-block-components-radio-control input';
        },

        showError: function(error) {
            console.error(error);
        },

        /** @deprecated This method is here until we can listen to woocommerce blocks events */
        onCartChange: function(callback) {
            const self = this;
            const block = jQuery(self.getShippintMethodsBlockSelector())
                .filter((_, node) => self.isBlockReady(node));
            if (block.length > 0) {
                callback();
            }

            Components.util.observeDom(document.body, (mutation) => {
                let found = false;
                
                if (mutation.addedNodes) {
                    for (let i = 0; i < mutation.addedNodes.length; i++) {
                        const addedNode = mutation.addedNodes[i];
                        if (self.isBlockReady(addedNode)) {
                            found = true;
                            break;
                        }
                    }
                }

                if (mutation.removedNodes && !found) {
                    for (let i = 0; i < mutation.removedNodes.length; i++) {
                        const removedNode = mutation.removedNodes[i];
                        if (self.isLoaderBlock(removedNode)) {
                            found = true;
                            break;
                        }
                    }
                }

                return found;
            }, callback);
        },

        isBlockReady: function(node) {
            const self = this;
            return self.getShippingMethodsBlockClasses().filter(className => node.classList && node.classList.contains(className)).length > 0
                && jQuery(node).find(self.getShippintMethodsRadioControlSelector()).has(':checked');
        },

        isLoaderBlock: function(node) {
            const self = this;
            return node.classList && node.classList.contains('wc-block-components-spinner')
        },

        updateShippingMethodExtraLabelCache: function(packageKey, shippingMethod, label) {
            const self = this;
            if (!(packageKey in self.cache)) {
                self.cache[packageKey] = {};
            }
            self.cache[packageKey][shippingMethod] = label;
        },

        getShippingMethodCachedExtraLabel: function(packageKey, shippingmethod) {
            const self = this;
            return packageKey in self.cache && shippingmethod in self.cache[packageKey]
                ? self.cache[packageKey][shippingmethod] : null;
        },

        refreshShippingMethodExtraLabel: function() {
            const self = this;
            const shippingMethod = self.getSelectedShippingMethod();
            const packageKey = self.getSelectedPackageKey();
            const label = self.getShippingMethodCachedExtraLabel(packageKey, shippingMethod);
            const className = 'laposteproexp-extra-label';

            jQuery(self.getShippintMethodsBlockSelector())
                .find('label ' + self.getShippintMethodTextLabelSelector())
                .find('.' + className)
                .remove();

            if (label !== null) {
                const checkedInputLabel = jQuery(self.getShippintMethodsBlockSelector())
                    .find('label')
                    .has('input:checked')
                    .find(self.getShippintMethodTextLabelSelector())
                    .first();

                checkedInputLabel.append('<span class="' + className + '"><br/>' + label + '</span>');
            }
        }
    }

    Components.legacy = {
        packageKey: null,

        init: function () {
            const self = this;

            const data = self.getFrontendData(); 

            if (data !== null) {

                Components.api.setApiConfiguration(
                    data.ajaxurl,
                    data.getShippingMethodExtraLabelNonce,
                    data.getPointsNonce,
                    data.setPointNonce
                );
                Components.map.setMapConfiguration(
                    data.mapUrl,
                    data.mapLogoImageUrl,
                    data.mapLogoHrefUrl
                );
    
                Components.util.on('body', 'click', '.laposteproexp-select-parcel', function(e) {
                    self.setPackageKey(e);
                    Components.map.init();
                    Components.map.open();
                    self.getMapPoints();
                });
    
                Components.util.on('body', 'click', '.laposteproexp-parcel-point-button', function() {
                    var carrierNotFound = Components.util.isI18nEnabled()
                        ? wp.i18n.__( 'Unable to find carrier', 'la-poste-pro-expeditions-woocommerce' )
                        : Components.util.translate('Unable to find carrier');
    
                    const carrier = self.getSelectedCarrier();
                    if (!carrier) {
                        self.showError(carrierNotFound);
                    }
                    Components.api.selectPoint(carrier,
                        self.packageKey,
                        this.getAttribute('data-code'),
                        decodeURIComponent(this.getAttribute('data-name')),
                        this.getAttribute('data-network'),
                        decodeURIComponent(this.getAttribute('data-street')),
                        decodeURIComponent(this.getAttribute('data-zipcode')),
                        decodeURIComponent(this.getAttribute('data-city')),
                        decodeURIComponent(this.getAttribute('data-country')),
                        decodeURIComponent(this.getAttribute('data-openinghours')),
                        decodeURIComponent(this.getAttribute('data-distance')),
                        function({ name, address, zipcode, city, distance }) {
                            self.initSelectedParcelPoint();
                            const addressElement = document.querySelector('.laposteproexp-parcel-address-' + self.packageKey);
                            const nameElement    = document.querySelector('.laposteproexp-parcel-name-' + self.packageKey);
                            if (addressElement) {
                                addressElement.innerHTML = Components.util.formatParcelPoingAddress(address, city, zipcode, distance);
                            }
                            if (nameElement) {
                                nameElement.innerHTML = name;
                            }
                            Components.map.close();
                        },
                        function(err) {
                            if (typeof err === 'object' && 'data' in err) {
                                self.showError(err.data.message);
                            }
                        }
                    );
                });
            } else {
                console.error('[la-poste-pro-expeditions-woocommerce] Failed to load plugin configuration (legacy)')
            }
        },

		setPackageKey: function(e) {
			this.packageKey = e.target.attributes.getNamedItem('data-package_key').value;
		},

        getFrontendData: function() {
            let result = null;

            if (typeof laposteproexpData !== 'undefined') {
                result = laposteproexpData;
            } else if('wc' in window && 'wcSettings' in window.wc) {
                const data = window.wc.wcSettings.getSetting('la-poste-pro-expeditions-woocommerce-parcel-point_data');
                if (data) {
                    result = data;
                }
            }

            return result;
        },

        initSelectedParcelPoint: function() {
            var yourParcelPoint = Components.util.isI18nEnabled()
                ? wp.i18n.__( 'Your parcel point:', 'la-poste-pro-expeditions-woocommerce' )
                : Components.util.translate('Your parcel point:');
            const selectParcelPoint = document.querySelector('.laposteproexp-parcel-client-' + this.packageKey);
            selectParcelPoint.innerHTML = yourParcelPoint + ' ';
            const selectParcelPointContent = document.createElement('span');
            selectParcelPointContent.setAttribute('class', 'laposteproexp-parcel-name-' + this.packageKey);
            selectParcelPoint.appendChild(selectParcelPointContent);
        },

        getMapPoints: function() {
            const self = this;

            var carrierNotfound = Components.util.isI18nEnabled()
                ? wp.i18n.__( 'Unable to find carrier', 'la-poste-pro-expeditions-woocommerce' )
                : Components.util.translate('Unable to find carrier');
            const carrier = self.getSelectedCarrier();
            if (!carrier) {
                self.showError(carrierNotfound);
            }

            Components.map.getPoints(carrier, self.packageKey, (err) => self.showError(err));
        },

        getSelectedCarrier: function() {
            let carrier;
            const uniqueCarrier = document.querySelector('input[type="hidden"].shipping_method');
            if (uniqueCarrier) {
                carrier = uniqueCarrier.getAttribute('value');
            } else {
                const selectedCarrier = document.querySelector('input.shipping_method:checked');
                carrier = selectedCarrier.getAttribute('value');
            }
            return carrier;
        },

        showError: function(error) {
            Components.map.close();
            alert(error);
        },

    }

    document.addEventListener(
        'DOMContentLoaded', function() {
            if (Components.util.isWoocommerceBlocks()) { // se déclenche pour legacy, ça ne devrait pas
                Components.blocks.init();
            } else {
                Components.legacy.init();
            }
        }
    );

})();
