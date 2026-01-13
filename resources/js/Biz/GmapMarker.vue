<template>
    <div>
        <input
            v-if="enableSearchBox"
            id="pac-input"
            ref="searchInput"
            type="text"
            placeholder="Search Box"
            class="input"
            :class="searchBoxClass"
            :style="searchBoxStyle"
            @keydown.enter.prevent
        >

        <div
            ref="mapDiv"
            :style="mapStyle"
        />
    </div>
</template>

<script>
    import { Loader } from '@googlemaps/js-api-loader';
    import { computed, onMounted, onUnmounted, ref, toRaw, watch } from 'vue';
    import { isFunction } from 'lodash';
    import { useGeolocation } from '@/Libs/map';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        props: {
            modelValue: { type: Object, default: () => {} },
            searchBoxStyle: { type: [String, Array, Object], default: "width: 300px" },
            searchBoxClass: { type: [String, Array, Object], default: "m-2" },
            mapStyle: { type: [String, Array, Object], default: () => ["width: 100%", "height: 50vh"] },
            initPosition: { type: Object, default: null },
            apiKey: { type: String, default: null },
            enableSearchBox: { type: Boolean, default: true },
            enableMarkerMove: { type: Boolean, default: true },
            isDraggable: { type: Boolean, default: true },
        },

        setup(props, { emit }) {
            const markerPosition = useModelWrapper(props, emit);

            // Use browser geolocation with loading state
            const { coords, isLoading, error } = useGeolocation();

            // Compute initial position - ONLY from user action or browser
            const currPos = computed(() => {
                // Priority 1: Existing model value (user has already set a location)
                if (
                    !isBlank(props.modelValue)
                    && !isBlank(props.modelValue.longitude)
                    && !isBlank(props.modelValue.latitude)
                ) {
                    return {
                        lat: markerPosition.value.latitude,
                        lng: markerPosition.value.longitude,
                    };
                }

                // Priority 2: Browser geolocation (ONLY accurate source!)
                if (
                    !isBlank(coords.value.latitude)
                    && !isBlank(coords.value.longitude)
                    && coords.value.latitude !== null
                    && coords.value.longitude !== null
                ) {
                    return {
                        lat: coords.value.latitude,
                        lng: coords.value.longitude,
                    };
                }

                // Priority 3: Default center (world view)
                return {
                    lat: 0,
                    lng: 0,
                };
            });
            const markers = ref([]);
            const mapDiv = ref(null);
            const searchInput = ref(null);
            const searchBox = ref(null);

            const loader = new Loader({
                apiKey: props.apiKey,
                version: "weekly",
                libraries: ["geometry", "drawing", "places"],
            });

            let map = ref(null);
            let clickListener = null;
            let searchListener = null;

            const addMarker = function (position) {
                markers.value.push(
                    new google.maps.Marker({
                        position: position,
                        map: map.value,
                    })
                );

                markerPosition.value.latitude = isFunction(position.lat) ? position.lat() : position.lat;
                markerPosition.value.longitude = isFunction(position.lng) ? position.lng() : position.lng;
            };

            // Sets the map on all markers in the array.
            const setMapOnAll = function (map) {
                for (let i = 0; i < markers.value.length; i++) {
                    toRaw(markers.value[i]).setMap(map);
                }
            };

            const hideMarkers = function () {
                setMapOnAll(null);
            };

            const deleteMarkers = function () {
                hideMarkers();
                markers.value = [];
            };

            const showMarkers = function () {
                setMapOnAll(map.value);
            };

            onMounted(async () => {
                await loader.load();

                // Wait longer for geolocation to resolve
                await new Promise(resolve => setTimeout(resolve, 1000));

                const initialPos = currPos.value;
                const hasValidPosition = initialPos.lat !== 0 || initialPos.lng !== 0;

                map.value = new google.maps.Map(mapDiv.value, {
                    center: initialPos,
                    zoom: hasValidPosition ? 11 : 2,
                    draggable: props.isDraggable,
                    streetViewControl: false,
                });

                // Only watch for position updates if we started without a valid position
                if (!hasValidPosition) {
                    let hasUpdated = false;
                    
                    const stopWatch = watch(currPos, (newPos) => {
                        if ((newPos.lat !== 0 || newPos.lng !== 0) && !hasUpdated) {
                            map.value.setCenter(newPos);
                            map.value.setZoom(11);
                            hasUpdated = true;
                            stopWatch();
                        }
                    });
                }

                if (props.enableSearchBox) {
                    searchBox.value = new google.maps.places.SearchBox(searchInput.value);

                    map.value.controls[google.maps.ControlPosition.TOP_LEFT].push(searchInput.value);

                    map.value.addListener("bounds_changed", () => {
                        searchBox.value.setBounds(map.value.getBounds());
                    });

                    searchListener = searchBox.value.addListener("places_changed", () => {
                        const places = searchBox.value.getPlaces();

                        if (places.length == 0) {
                            return;
                        }

                        deleteMarkers();

                        const bounds = new google.maps.LatLngBounds();

                        places.forEach((place) => {
                            if (!place.geometry || !place.geometry.location) {
                                console.log("Returned place contains no geometry");
                                return;
                            }

                            const icon = {
                                url: place.icon,
                                size: new google.maps.Size(71, 71),
                                origin: new google.maps.Point(0, 0),
                                anchor: new google.maps.Point(17, 34),
                                scaledSize: new google.maps.Size(25, 25),
                            };

                            addMarker(place.geometry.location);

                            if (place.geometry.viewport) {
                                // Only geocodes have viewport.
                                bounds.union(place.geometry.viewport);
                            } else {
                                bounds.extend(place.geometry.location);
                            }
                        });

                        map.value.fitBounds(bounds);
                    });
                }

                clickListener = map.value.addListener('click', (event) => {
                    if (! props.enableMarkerMove) {
                        return false;
                    }

                    deleteMarkers();
                    addMarker(event.latLng);
                    showMarkers();
                });

                addMarker(currPos.value);

                showMarkers();
            });

            onUnmounted(() => {
                if (clickListener) {
                    clickListener.remove;
                }
                if (searchListener) {
                    searchListener.remove;
                }
            });

            return {
                currPos,
                markers,
                mapDiv,
                searchInput,
                isLoading,
                error,

                hideMarkers,
                showMarkers,
                deleteMarkers,
                addMarker,
            };
        },
    };
</script>
