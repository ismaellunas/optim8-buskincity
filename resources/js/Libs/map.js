import { onMounted, onUnmounted, ref } from 'vue';

export function useGeolocation(options = {}) {
    const coords = ref({ latitude: null, longitude: null, accuracy: null });
    const isSupported = 'navigator' in window && 'geolocation' in navigator;
    const isLoading = ref(true);
    const error = ref(null);

    let watcher = null;

    const defaultOptions = {
        enableHighAccuracy: false, // Changed to false for faster response
        timeout: 30000, // 30 seconds (increased from 10)
        maximumAge: 60000, // 60 seconds cache
        ...options
    };

    onMounted(() => {
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
                    error.value = err.message;
                    isLoading.value = false;
                },
                defaultOptions
            );

            // Then watch for changes
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
                    error.value = err.message;
                    isLoading.value = false;
                },
                defaultOptions
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

    return { coords, isSupported, isLoading, error };
};

export const mapStyle = [{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"all","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative.neighborhood","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"administrative.land_parcel","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"hue":"#ffbb00"},{"saturation":43.400000000000006},{"lightness":37.599999999999994},{"gamma":1}]},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#f0f2f6"}]},{"featureType":"poi","elementType":"all","stylers":[{"hue":"#00ff6a"},{"saturation":-1.0989010989011234},{"lightness":11.200000000000017},{"gamma":1},{"visibility":"simplified"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"hue":"#FFC200"},{"saturation":-61.8},{"lightness":45.599999999999994},{"gamma":1}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"weight":"1.50"},{"color":"#ee7a23"},{"lightness":"25"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"hue":"#ff0300"},{"saturation":-100},{"lightness":51.19999999999999},{"gamma":1}]},{"featureType":"road.local","elementType":"all","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":52},{"gamma":1}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.station","elementType":"labels","stylers":[{"visibility":"simplified"},{"lightness":"31"},{"gamma":"1.58"}]},{"featureType":"transit.station.airport","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.airport","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.rail","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"hue":"#0078FF"},{"saturation":-13.200000000000003},{"lightness":2.4000000000000057},{"gamma":1}]}];
