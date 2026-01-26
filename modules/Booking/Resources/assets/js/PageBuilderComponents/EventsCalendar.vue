<template>
    <div class="events-calendar">
        <!-- Geolocation notification for mobile -->
        <div v-if="geoError && isMobile" class="notification is-warning mb-3">
            <p>📍 {{ geoError }}</p>
            <p class="is-size-7 mt-2">Showing events from all locations. For location-based filtering, please enable location services.</p>
        </div>
        
        <div v-if="!isSecureContext && isMobile" class="notification is-danger mb-3">
            <p>🔒 Location services require HTTPS</p>
            <p class="is-size-7 mt-2">Please access this site via HTTPS to use location-based features.</p>
        </div>

        <div class="columns is-multiline is-mobile">
            <div class="column is-6-desktop is-6-tablet is-12-mobile">
                <div
                    ref="mapDiv"
                    :style="screenMapStyle"
                />
            </div>

            <div class="column is-6-desktop is-6-tablet is-12-mobile">
                <div class="columns is-multiline is-mobile">
                    <div class="column is-6-desktop is-12-tablet is-12-mobile">
                        <div class="control has-icons-left">
                            <biz-select
                                v-model="selectedLocation"
                                class="is-fullwidth"
                            >
                                <option
                                    v-for="location in locationOptions"
                                    :key="location.id"
                                    :value="location.id"
                                >
                                    {{ location.value }}
                                </option>
                            </biz-select>

                            <biz-icon
                                class="is-small is-left"
                                :icon="icon.globe"
                            />
                        </div>
                    </div>

                    <div class="column is-6-desktop is-12-tablet is-12-mobile">
                        <biz-filter-date-range
                            v-model="queryParams.dates"
                            auto-apply
                            input-class-name="input"
                            max-range="7"
                            :clearable="false"
                            :format="'MMM d'"
                            :max-date="maxDate"
                            :min-date="minDate"
                            :year-range="yearRange"
                        />
                    </div>

                    <div class="column is-12-desktop is-12-tablet is-12-mobile">
                        <biz-button
                            class="is-primary"
                            type="button"
                            @click.prevent="getEvents(null, $event)"
                        >
                            Search Events
                        </biz-button>
                    </div>
                </div>

                <!-- Debug information (remove in production) -->
                <div
                    v-if="debugMode"
                    class="notification is-info mb-4"
                >
                    <h4>Debug Information:</h4>
                    <p><strong>Events Data:</strong> {{ events }}</p>
                    <p><strong>Events Count:</strong> {{ events.data ? events.data.length : 0 }}</p>
                    <p><strong>Query Params:</strong> {{ queryParams }}</p>
                    <p><strong>Selected Location:</strong> {{ selectedLocation }}</p>
                    <p><strong>Location Parts:</strong> {{ locationParts }}</p>
                </div>

                <!-- No results message -->
                <div
                    v-if="events.data && events.data.length === 0"
                    class="notification is-warning"
                >
                    <h4>No Events Found</h4>
                    <p>No events were found for the selected criteria. Try adjusting your search parameters.</p>
                </div>

                <!-- Loading state -->
                <div
                    v-if="isLoading"
                    class="notification is-info"
                >
                    <p>Loading events...</p>
                </div>

                <!-- Error state -->
                <div
                    v-if="hasError"
                    class="notification is-danger"
                >
                    <h4>Error Loading Events</h4>
                    <p>{{ errorMessage }}</p>
                </div>

                <events-calendar-item
                    v-for="record in events.data"
                    :key="record.id"
                    :record="record"
                    :screen-type="screenType"
                />

                <biz-pagination
                    v-if="screenType == 'mobile'"
                    :is-ajax="true"
                    :links="events.links"
                    :query-params="queryParams"
                    :size="paginationSize"
                    :current-page="events.current_page"
                    :last-page="events.last_page"
                    @on-clicked-pagination="getEvents"
                />
            </div>

            <div
                v-if="screenType != 'mobile'"
                class="column is-12-desktop is-12-tablet is-12-mobile"
            >
                <biz-pagination
                    class="mt-4"
                    :is-ajax="true"
                    :links="events.links"
                    :query-params="queryParams"
                    :size="paginationSize"
                    :current-page="events.current_page"
                    :last-page="events.last_page"
                    @on-clicked-pagination="getEvents"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import moment from 'moment';
    import { Loader } from '@googlemaps/js-api-loader';
    import { MarkerClusterer } from "@googlemaps/markerclusterer";
    import { clone, each, find, keys, get, groupBy, merge, map, sortBy, toString } from 'lodash';
    import { computed, defineAsyncComponent, ref } from 'vue';
    import { globe } from '@/Libs/icon-class';
    import { isBlank, useBreakpoints } from '@/Libs/utils';
    import { useGeolocation, mapStyle as drawMapStyle } from '@/Libs/map';

    export default {
        name: 'EventsCalendar',

        components: {
            BizButton: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Button.vue')
            ),
            BizFilterDateRange: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Filter/DateRange.vue')
            ),
            BizIcon: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Icon.vue')
            ),
            BizPagination: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Pagination.vue')
            ),
            BizSelect: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Select.vue')
            ),
            EventsCalendarItem: defineAsyncComponent(() =>
                import('./EventsCalendarItem.vue')
            ),
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            apiKey: { type: String, default: null },
            initPosition: { type: Object, default: null },
            isDraggable: { type: Boolean, default: true },
            mapStyle: { type: Object, default: () => ({ width: "100%", height: "95vh"}) },
            maxDate: { type: String, required: true },
            minDate: { type: String, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            urls : { type: Object, required: true },
            userCity: { type: String, required: true },
            userCountryCode: { type: String, required: true },
            yearRange: { type: Array, required: true },
        },

        setup(props) {
            let availableLocations = ref({});
            let events = ref({});
            let map = ref(null);
            let mapData = ref(null);
            let mapDiv = ref(null);
            let selectedLocation = ref(null);

            // Use browser geolocation ONLY (no server IP location!)
            const { coords, isLoading: geoLoading, error: geoError, isSecureContext, isMobile } = useGeolocation();

            // Compute position - ONLY from browser, never from server IP
            const initPos = computed(() => {
                // Use ONLY browser geolocation
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

                // If browser geolocation not available yet or failed, default to world view
                return {
                    lat: 0,
                    lng: 0,
                };
            });

            const queryParams = computed(() => merge(
                {dates: [
                    moment().format('YYYY-MM-DD'),
                    moment().add(6, 'day').format('YYYY-MM-DD'),
                ]},
                props.pageQueryParams
            ));

            const { screenType } = useBreakpoints();

            return {
                availableLocations,
                dateRange: clone(queryParams.value.dates),
                events,
                icon: { globe },
                initPos,
                mapData,
                mapDiv,
                queryParams: ref(queryParams),
                selectedLocation,
                infoWindow: ref(null),
                screenType,
                geoLoading,
                geoError,
                isSecureContext,
                isMobile,
            };
        },

        data() {
            return {
                isShown: true,
                mapLoader: null,
                markerClusterer: null,
                map: null,
                markers: [],
                debugMode: false, // Set to true to enable debug information
                isLoading: false,
                hasError: false,
                errorMessage: '',
                hasInitializedWithBrowserLocation: false, // Track if we've set position from browser
            };
        },

        computed: {
            locationOptions() {
                const options = [
                    // Add "Any" option at the top to show all events
                    {
                        id: null,
                        value: 'Any',
                    }
                ];

                sortBy(this.availableLocations, [(location, key) => {
                    return location.country;
                }])
                    .forEach((location) => {
                        options.push({
                            id: location.country_code,
                            value: location.country,
                        });

                        location.cities.sort();

                        each(location.cities, (city) => {
                            options.push({
                                id: location.country_code +'-'+ city,
                                value: ' - '+ city,
                            });
                        });
                    });

                return options;
            },

            locationParts() {
                const countryCity = {
                    country: null,
                    city: null,
                };

                if (!this.selectedLocation) {
                    return countryCity;
                }

                const locationParts = this.selectedLocation.split('-');

                return {
                    country: locationParts[0] ?? "",
                    city: locationParts[1] ?? "",
                };
            },

            screenMapStyle() {
                let screenMapStyle = {
                    width: this.mapStyle.width,
                    height: this.mapStyle.height,
                };

                const res = this.mapStyle.height.match(/(-?[\d.]+)([a-z%]*)/);

                if (this.screenType == 'mobile') {
                    screenMapStyle['height'] = toString(res[1] / 2) + res[2];
                } else if (this.screenType == 'tablet') {
                    screenMapStyle['height'] = toString(res[1] * (60 / 100)) + res[2];
                } else if (this.screenType == 'desktop') {
                    screenMapStyle['height'] = toString(res[1]) + res[2];
                }

                return screenMapStyle;
            },

            paginationSize() {
                if (this.screenType == 'mobile') {
                    return "small";
                }
                return "normal";
            },
        },

        async mounted() {
            this.mapLoader = new Loader({
                apiKey: this.apiKey,
                version: "weekly",
                libraries: ["geometry", "drawing", "places"],
            });

            await this.mapLoader.load();

            // Wait for browser geolocation to resolve
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Get center position with safety checks
            const center = this.initPos || { lat: 0, lng: 0 };
            const lat = typeof center.lat === 'number' ? center.lat : 0;
            const lng = typeof center.lng === 'number' ? center.lng : 0;

            // Track if we're using browser location
            if (lat !== 0 && lng !== 0) {
                this.hasInitializedWithBrowserLocation = true;
            }

            this.map = new google.maps.Map(this.mapDiv, {
                center: { lat, lng },
                zoom: (lat === 0 && lng === 0) ? 2 : 11, // Changed from 4 to 11 for city/municipal level
                draggable: this.isDraggable,
                streetViewControl: false,
                styles: drawMapStyle,
                maxZoom: 17,
                minZoom: 3,
            });

            // Watch for geolocation updates ONLY if we started with world view
            if (lat === 0 && lng === 0) {
                // Watch for when browser location becomes available
                const stopWatch = this.$watch('initPos', (newPos) => {
                    if (newPos && newPos.lat !== 0 && newPos.lng !== 0 && !this.hasInitializedWithBrowserLocation) {
                        this.map.setCenter(newPos);
                        this.map.setZoom(11);
                        this.hasInitializedWithBrowserLocation = true;
                        
                        // Reload events based on new location
                        this.getLocationOptions((results) => {
                            this.setLocationFromCoordinates(newPos, results);
                            // If no location was auto-selected, selectedLocation will be null
                            // This will cause getEvents() to load all events
                            this.getEvents();
                        });
                        
                        stopWatch();
                    }
                }, { deep: true });
            }

            this.infoWindow = new google.maps.InfoWindow({
                content: "",
                disableAutoPan: true,
            });

            this.markerClusterer = new MarkerClusterer({
                map: this.map,
            });

            this.getLocationOptions((results) => {
                // ONLY use browser geolocation (GPS) - DO NOT use server IP detection
                // Server is in Sweden, so IP detection always shows Sweden regardless of user location
                if (this.hasInitializedWithBrowserLocation && this.initPos) {
                    // Use browser GPS coordinates to detect location
                    this.setLocationFromCoordinates(this.initPos, results);
                }
                
                // If browser geolocation failed or was denied, selectedLocation stays null
                // This will show "Any" and load all events from all locations
                this.getEvents();
            });
        },

        methods: {
            setLocationFromCoordinates(coords, locationOptions) {
                // Find the closest country/city based on coordinates
                // For Philippines coordinates (8-18°N, 116-127°E)
                const lat = coords.lat;
                const lng = coords.lng;
                
                // Simple region detection for Philippines
                if (lat >= 4 && lat <= 22 && lng >= 116 && lng <= 127) {
                    // User is in Philippines - check if PH locations exist in available locations
                    if (keys(locationOptions).includes('PH')) {
                        const cities = get(locationOptions, 'PH.cities', []);
                        if (cities.length > 0) {
                            // Only auto-select if there are events in this city
                            this.selectedLocation = 'PH-' + cities[0];
                        } else {
                            // Country exists but no cities, just select country
                            this.selectedLocation = 'PH';
                        }
                        return; // Exit early since we found a match
                    }
                }
                
                // Add more region detection here for other countries
                // For example: Sweden (55-69°N, 11-24°E)
                if (lat >= 55 && lat <= 69 && lng >= 11 && lng <= 24) {
                    if (keys(locationOptions).includes('SE')) {
                        const cities = get(locationOptions, 'SE.cities', []);
                        if (cities.length > 0) {
                            this.selectedLocation = 'SE-' + cities[0];
                        } else {
                            this.selectedLocation = 'SE';
                        }
                        return;
                    }
                }
                
                // Add detection for United States (25-49°N, -125 to -66°E)
                if (lat >= 25 && lat <= 49 && lng >= -125 && lng <= -66) {
                    if (keys(locationOptions).includes('US')) {
                        const cities = get(locationOptions, 'US.cities', []);
                        if (cities.length > 0) {
                            this.selectedLocation = 'US-' + cities[0];
                        } else {
                            this.selectedLocation = 'US';
                        }
                        return;
                    }
                }
                
                // If we reach here, no matching region was found with available events
                // Leave selectedLocation as null to show all events (fallback to "Any")
                if (this.debugMode) {
                    console.log('No matching region with events found for coordinates:', { lat, lng });
                    console.log('Available locations:', keys(locationOptions));
                }
            },
            
            getEvents(url) {
                let currentUrl = url ?? this.urls.getEvents;

                // Clear previous errors
                this.hasError = false;
                this.errorMessage = '';

                // Prepare query parameters
                this.queryParams.country = this.locationParts.country;
                this.queryParams.city = this.locationParts.city;
                this.queryParams.dates = this.queryParams.dates.filter(Boolean);

                this.isLoading = true;
                this.onStartLoadingOverlay();

                axios
                    .get(currentUrl, {params: this.queryParams})
                    .then((response) => {
                        // Check if response has the expected structure
                        if (!response.data || !response.data.pagination) {
                            throw new Error('Invalid API response structure');
                        }

                        this.events = response.data.pagination;
                        this.mapData = response.data.map;

                        // Update map markers
                        this.markerClusterer.clearMarkers(true);

                        // ONLY recenter map if we don't have browser location
                        if (response.data.map && response.data.map.center && !this.hasInitializedWithBrowserLocation) {
                            this.map.setZoom(response.data.map.zoom);
                            this.map.panTo({
                                lat: parseFloat(response.data.map.center.latitude),
                                lng: parseFloat(response.data.map.center.longitude),
                            });
                        }

                        // Process markers for events with geolocation
                        if (this.events.data && this.events.data.length > 0) {
                            const filteredRecords = this.events.data.filter((record) => {
                                return (
                                    get(record, 'geolocation')
                                    && !isBlank(get(record, 'geolocation.latitude'))
                                    && !isBlank(get(record, 'geolocation.longitude'))
                                );
                            });

                            const coordinateGroups = groupBy(filteredRecords, (record) => {
                                return record.geolocation.latitude+';'+record.geolocation.longitude;
                            });

                            this.markers = map(coordinateGroups, (records, key) => {
                                const record = records[0];
                                const label = "" + records.length;

                                const marker = new google.maps.Marker({
                                    position: {
                                        lat: record.geolocation.latitude,
                                        lng: record.geolocation.longitude
                                    },
                                    label,
                                });

                                marker.addListener("click", () => {
                                    this.map.panTo(marker.getPosition());
                                    this.infoWindow.setContent(this.infoWindowContent(records));
                                    this.infoWindow.open(this.map, marker);
                                });

                                return marker;
                            });

                            this.markerClusterer.addMarkers(this.markers, true);
                        }

                        if (this.debugMode) {
                            console.log('EventsCalendar: Processed events:', this.events.data?.length || 0);
                        }
                    })
                    .catch((error) => {
                        console.error('EventsCalendar: API Error:', error);
                        
                        this.hasError = true;
                        
                        if (error.response) {
                            // Server responded with error status
                            this.errorMessage = `Server error: ${error.response.status} - ${error.response.data?.message || error.response.statusText}`;
                        } else if (error.request) {
                            // Request was made but no response received
                            this.errorMessage = 'Network error: Unable to connect to server';
                        } else {
                            // Something else happened
                            this.errorMessage = `Error: ${error.message}`;
                        }
                    })
                    .finally(() => {
                        this.isLoading = false;
                        this.onEndLoadingOverlay();
                    });
            },

            getLocationOptions(after) {
                if (this.debugMode) {
                    console.log('EventsCalendar: Getting location options from:', this.urls.getLocationOptions);
                }

                axios
                    .get(this.urls.getLocationOptions)
                    .then((response) => {
                        if (this.debugMode) {
                            console.log('EventsCalendar: Location options response:', response.data);
                        }

                        this.availableLocations = response.data;

                        if (after) {
                            after(this.availableLocations);
                        }
                    })
                    .catch((error) => {
                        console.error('EventsCalendar: Error getting location options:', error);
                        
                        // Set empty locations if API fails
                        this.availableLocations = {};
                        
                        if (after) {
                            after(this.availableLocations);
                        }
                    });
            },

            infoWindowContent(records) {
                const record = records[0];

                let content = (
                    '<div class="content">'+
                    '<h2>'+record.title+'</h2>'+
                    '<ul>'
                );
                records.forEach((record) => {
                    content += (
                        '<li>'+
                        '<b>'+record.title+'</b>'+
                        (
                            record.is_ended_on_same_date
                                ? (
                                    ', '+record.formatted_started_date+
                                    ', '+record.started_time
                                )
                                : (
                                    ', '+record.formatted_started_date+' '+record.started_time+
                                    ' - '+record.formatted_ended_date+' '+record.ended_time
                                )
                        ) + (record.timezone ? ' ('+record.formatted_timezone+')' : '') +
                        '</li>'
                    );
                });
                content += (
                    '</ul>'+
                    '</div>'
                );

                return content;
            },
        },
    };
</script>
