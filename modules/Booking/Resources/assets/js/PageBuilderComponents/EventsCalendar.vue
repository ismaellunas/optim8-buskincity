<template>
    <div class="events-calendar">
        <div class="columns">
            <div class="column">
                <div class="columns is-multiline">
                    <div class="column">
                        <div class="control has-icons-left">
                            <biz-select
                                v-model="selectedLocation"
                                placeholder="Any"
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

                    <div class="column is-5">
                        <biz-filter-date-range
                            v-model="queryParams.dates"
                            input-class-name="input"
                            max-range="7"
                            :max-date="maxDate"
                            :min-date="minDate"
                            :year-range="yearRange"
                            :clearable="false"
                        />
                    </div>

                    <div class="column is-3">
                        <biz-button
                            class="is-primary"
                            type="button"
                            @click.prevent="getEvents(null, $event)"
                        >
                            Search Events
                        </biz-button>
                    </div>
                </div>

                <div
                    v-if="events.data"
                    class="block"
                >
                    <article
                        v-for="record in events.data"
                        :key="record.id"
                        class="media"
                    >
                        <figure class="media-left">
                            <p class="image is-64x64">
                                <img :src="record.user?.profile_photo_url ?? userImage">
                            </p>
                        </figure>
                        <div class="media-content">
                            <div class="content">
                                <p class="has-text-justified">
                                    <template v-if="record.user.stage_name">
                                        <strong>{{ record.user.stage_name }}</strong> - <small>{{ record.user.name }}</small>
                                    </template>
                                    <template v-else>
                                        <strong>{{ record.user.name }}</strong>
                                    </template>
                                    <br>
                                    <biz-icon :icon="icon.locationMark" />
                                    {{ record.location.address }}
                                    <br>
                                    <biz-icon :icon="bookingIcon.calendar" />
                                    {{ record.event.date }}, {{ record.event.start_end_time }}, {{ record.event.timezone }}
                                    <br>
                                    <biz-icon :icon="bookingIcon.duration" />
                                    {{ record.event.duration }}
                                </p>
                            </div>
                            <div class="buttons">
                                <a
                                    v-if="record.user.profile_page_url"
                                    class="button my-0"
                                    target="_blank"
                                    :href="record.direction_url"
                                >
                                    Direction
                                </a>

                                <a
                                    v-if="record.user.profile_page_url"
                                    class="button is-primary my-0"
                                    target="_blank"
                                    :href="record.user.profile_page_url"
                                >
                                    Performer Detail
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else>
                    No Event
                </div>

                <biz-pagination
                    class="mt-6"
                    :is-ajax="true"
                    :links="events.links"
                    :query-params="queryParams"
                    @on-clicked-pagination="getEvents"
                />
            </div>

            <div class="column">
                <div
                    ref="mapDiv"
                    :style="mapStyle"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizButton from '@/Biz/Button';
    import BizFilterDateRange from '@/Biz/Filter/DateRange';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizSelect from '@/Biz/Select';
    import bookingIcon from '@mod/Booking/Resources/assets/js/Libs/booking-icon';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import { Loader } from '@googlemaps/js-api-loader';
    import { MarkerClusterer } from "@googlemaps/markerclusterer";
    import { clone, each, find, keys, get, groupBy, merge, map } from 'lodash';
    import { computed, onMounted, onUnmounted, reactive, ref, toRaw } from 'vue';
    import { useGeolocation, mapStyle as drawMapStyle } from '@/Libs/map';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { userImage } from '@/Libs/defaults';

    export default {
        name: 'EventsCalendar',

        components: {
            BizButton,
            BizIcon,
            BizSelect,
            BizPagination,
            BizFilterDateRange,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            apiKey: { type: String, default: null },
            initPosition: { type: Object, default: null },
            isDraggable: { type: Boolean, default: true },
            mapStyle: { type: [String, Array, Object], default: () => ["width: 100%", "height: 95vh"] },
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
            let initPos = null;
            let map = ref(null);
            let mapData = ref(null);
            let mapDiv = ref(null);
            let selectedLocation = ref(null);

            if (!isBlank(props.initPosition)) {

                initPos = {
                    lat: props.initPosition.latitude,
                    lng: props.initPosition.longitude,
                };

            } else {

                const { coords } = useGeolocation();

                initPos = {
                    lat: coords.value.latitude,
                    lng: coords.value.longitude,
                };
            }

            const queryParams = merge(
                {dates: [
                    moment().format('YYYY-MM-DD'),
                    moment().add(6, 'day').format('YYYY-MM-DD'),
                ]},
                props.pageQueryParams
            );

            return {
                availableLocations,
                bookingIcon,
                dateRange: clone(queryParams.dates),
                events,
                icon,
                mapData,
                mapDiv,
                queryParams: ref(queryParams),
                selectedLocation,
                userImage,
                infoWindow: ref(null),
            };
        },

        data() {
            return {
                isShown: true,
                mapLoader: null,
                markerClusterer: null,
                map: null,
                markers: [],
            };
        },

        computed: {
            locationOptions() {
                const options = [];

                each(this.availableLocations, (location, key) => {
                    options.push({
                        id: key,
                        value: location.country,
                    });

                    each(location.cities, (city) => {
                        options.push({
                            id: key +'-'+ city,
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
                    country: locationParts[0],
                    city: locationParts[1],
                };
            },
        },

        async mounted() {
            this.mapLoader = new Loader({
                apiKey: this.apiKey,
                version: "weekly",
                libraries: ["geometry", "drawing", "places"],
            });

            await this.mapLoader.load();

            this.map = new google.maps.Map(this.mapDiv, {
                center: {
                    lat: this.initPosition.latitude,
                    lng: this.initPosition.longitude,
                },
                zoom: 4,
                draggable: this.isDraggable,
                streetViewControl: false,
                styles: drawMapStyle,
                maxZoom: 17,
                minZoom: 3,
            });

            this.infoWindow = new google.maps.InfoWindow({
                content: "",
                disableAutoPan: true,
            });

            this.markerClusterer = new MarkerClusterer({
                map: this.map,
            });

            this.getLocationOptions((results) => {
                if (keys(results).includes(this.userCountryCode)) {
                    this.selectedLocation = this.userCountryCode;

                    const foundedCity = find(
                        get(results, this.userCountryCode+'.cities', []),
                        (city) => city == this.userCity
                    );

                    if (foundedCity) {
                        this.selectedLocation += "-" + foundedCity;
                    }
                }

                this.getEvents();
            });
        },

        methods: {
            getEvents(url) {
                let currentUrl = url ?? this.urls.getEvents;

                this.queryParams.country = this.locationParts.country;
                this.queryParams.city = this.locationParts.city;

                this.onStartLoadingOverlay();

                axios
                    .get(currentUrl, {params: this.queryParams})
                    .then((response) => {
                        this.events = response.data.pagination;
                        this.mapData = response.data.map;

                        this.markerClusterer.clearMarkers(true);

                        this.map.setZoom(response.data.map.zoom);

                        this.map.panTo({
                            lat: parseFloat(response.data.map.center.lat),
                            lng: parseFloat(response.data.map.center.long),
                        })

                        const filteredRecords = response.data.pagination.data.filter((record) => {
                            return (
                                get(record, 'location')
                                && !isBlank(get(record, 'location.latitude'))
                                && !isBlank(get(record, 'location.longitude'))
                            );
                        });

                        const coordinateGroups = groupBy(filteredRecords, (record) => {
                            return record.location.latitude+';'+record.location.longitude;
                        });

                        this.markers = map(coordinateGroups, (records, key) => {
                            const record = records[0];
                            const label = "" + records.length;

                            const marker = new google.maps.Marker({
                                position: {
                                    lat: record.location.latitude,
                                    lng: record.location.longitude
                                },
                                label,
                            });

                            marker.addListener("click", () => {
                                this.map.panTo(marker.getPosition());
                                this.infoWindow.setContent(this.infoWindowContent(records));
                                this.infoWindow.open(this.map, marker);
                            });

                            return marker;
                        })

                        this.markerClusterer.addMarkers(this.markers, true);
                    })
                    .then(() => {
                        this.onEndLoadingOverlay();
                    });
            },

            getLocationOptions(after) {
                axios
                    .get(this.urls.getLocationOptions)
                    .then((response) => {
                        this.availableLocations = response.data;

                        if (after) {
                            after(this.availableLocations);
                        }
                    });
            },

            infoWindowContent(records) {
                const record = records[0];

                let content = (
                    '<div class="content">'+
                    '<h2>'+record.product_name+'</h2>'+
                    '<h4>'+record.location.address+'</h4>'+
                    '<ul>'
                );
                records.forEach((record) => {
                    content += (
                        '<li>'+
                        '<b>'+(record.user.stage_name ?? record.user.name)+'</b>'+
                        ', '+record.event.date+
                        ', '+record.event.start_end_time+
                        ' ('+record.event.timezone+')'+
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
