import { onMounted, onUnmounted, ref } from 'vue';

export function useGeolocation(options = {}) {
    const coords = ref({ latitude: null, longitude: null, accuracy: null });
    const isSupported = 'navigator' in window && 'geolocation' in navigator;
    const isLoading = ref(true);
    const error = ref(null);
    const isSecureContext = window.isSecureContext || window.location.protocol === 'https:' || window.location.hostname === 'localhost';

    let watcher = null;

    // Detect if mobile device
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    const defaultOptions = {
        enableHighAccuracy: isMobile, // Use high accuracy on mobile for better GPS lock
        timeout: isMobile ? 60000 : 30000, // 60s for mobile, 30s for desktop
        maximumAge: 0, // Don't use cached position on first request
        ...options
    };

    onMounted(() => {
        // Check if geolocation is available in a secure context
        if (!isSecureContext && window.location.protocol !== 'http:') {
            error.value = 'Geolocation requires HTTPS';
            isLoading.value = false;
            return;
        }

        if (isSupported) {
            // First, get current position immediately
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    coords.value = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    isLoading.value = false;
                    error.value = null;
                },
                (err) => {
                    // Handle different error codes
                    let errorMessage = 'Unable to get location';
                    switch(err.code) {
                        case 1: // PERMISSION_DENIED
                            errorMessage = 'Location permission denied';
                            break;
                        case 2: // POSITION_UNAVAILABLE
                            errorMessage = 'Location unavailable';
                            break;
                        case 3: // TIMEOUT
                            errorMessage = 'Location request timeout';
                            break;
                    }
                    error.value = errorMessage;
                    isLoading.value = false;
                },
                defaultOptions
            );

            // Then watch for changes (with cached positions allowed)
            watcher = navigator.geolocation.watchPosition(
                (position) => {
                    coords.value = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: position.coords.accuracy
                    };
                    isLoading.value = false;
                    error.value = null;
                },
                (err) => {
                    // Don't override error if we already have coords
                    if (!coords.value.latitude) {
                        error.value = 'Location watch failed';
                    }
                    isLoading.value = false;
                },
                {
                    ...defaultOptions,
                    maximumAge: 60000, // Allow cached positions for watch
                }
            );
        } else {
            isLoading.value = false;
            error.value = 'Geolocation not supported';
        }
    });

    onUnmounted(() => {
        if (watcher) {
            navigator.geolocation.clearWatch(watcher);
        }
    });

    return { 
        coords, 
        isSupported, 
        isLoading, 
        error,
        isSecureContext,
        isMobile
    };
};

export const mapStyle = [{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"all","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative.neighborhood","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"administrative.land_parcel","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"hue":"#ffbb00"},{"saturation":43.400000000000006},{"lightness":37.599999999999994},{"gamma":1}]},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#f0f2f6"}]},{"featureType":"poi","elementType":"all","stylers":[{"hue":"#00ff6a"},{"saturation":-1.0989010989011234},{"lightness":11.200000000000017},{"gamma":1},{"visibility":"simplified"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"hue":"#FFC200"},{"saturation":-61.8},{"lightness":45.599999999999994},{"gamma":1}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"weight":"1.50"},{"color":"#ee7a23"},{"lightness":"25"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"hue":"#ff0300"},{"saturation":-100},{"lightness":51.19999999999999},{"gamma":1}]},{"featureType":"road.local","elementType":"all","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":52},{"gamma":1}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.station","elementType":"labels","stylers":[{"visibility":"simplified"},{"lightness":"31"},{"gamma":"1.58"}]},{"featureType":"transit.station.airport","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.airport","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.rail","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"hue":"#0078FF"},{"saturation":-13.200000000000003},{"lightness":2.4000000000000057},{"gamma":1}]}];
