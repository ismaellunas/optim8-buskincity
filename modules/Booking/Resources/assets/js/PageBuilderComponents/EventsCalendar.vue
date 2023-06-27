<template>
    <div class="events-calendar">
        <div class="columns is-multiline is-mobile">
            <div class="column is-6-desktop is-6-tablet is-12-mobile">
                <div class="columns is-multiline is-mobile">
                    <div class="column is-6-desktop is-12-tablet is-12-mobile">
                        <div class="control has-icons-left">
                            <biz-select
                                v-model="selectedLocation"
                                class="is-fullwidth"
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

                <div
                    v-for="record in events.data"
                    :key="record.id"
                    class="box pb-1 mb-2"
                >
                    <div class="columns is-mobile is-multiline">
                        <div class="column is-4-desktop is-12-tablet is-12-mobile">
                            <div class="columns is-mobile is-multiline level">
                                <div class="column is-12-desktop is-6-tablet is-6-mobile level-left">
                                    <div>
                                        <figure class="image is-128x128 level-item">
                                            <img
                                                class="is-rounded"
                                                :src="record.user?.profile_photo_url ?? userImage"
                                            >
                                        </figure>
                                    </div>
                                </div>

                                <div class="column is-12-desktop is-6-tablet is-6-mobile is-hidden-desktop level-right">
                                    <div class="buttons is-right">
                                        <a
                                            v-if="record.user.profile_page_url"
                                            class="button level-item mb-2"
                                            target="_blank"
                                            :class="{'is-small': screenType == 'mobile'}"
                                            :href="record.direction_url"
                                        >
                                            Directions
                                        </a>
                                        <a
                                            v-if="record.user.profile_page_url"
                                            class="button is-primary level-item"
                                            target="_blank"
                                            :class="{'is-small': screenType == 'mobile'}"
                                            :href="record.user.profile_page_url"
                                        >
                                            Performer Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="content mb-1">
                                <p class="has-text-justified mb-1">
                                    <a
                                        target="_blank"
                                        :href="record.user.profile_page_url ?? '#'"
                                    >
                                        <template v-if="record.user.stage_name">
                                            <strong>{{ record.user.stage_name }}</strong> - <small>{{ record.user.name }}</small>
                                        </template>
                                        <template v-else>
                                            <strong>{{ record.user.name }}</strong>
                                        </template>
                                    </a>
                                </p>
                                <table class="table is-narrow is-borderless mb-2">
                                    <tbody>
                                        <tr>
                                            <td class="m-0 py-0 px-0">
                                                <biz-icon :icon="icon.locationMark" />
                                            </td>
                                            <td class="m-0 py-0 px-0">
                                                {{ record.location.address }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="m-0 py-0 px-0">
                                                <biz-icon :icon="bookingIcon.calendar" />
                                            </td>
                                            <td class="m-0 py-0 px-0">
                                                {{ record.event.date }}, {{ record.event.start_end_time }}, {{ record.event.timezone }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="m-0 py-0 px-0">
                                                <biz-icon :icon="bookingIcon.duration" />
                                            </td>
                                            <td class="m-0 py-0 px-0">
                                                {{ record.event.duration }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <nav class="level is-mobile is-hidden-touch">
                                    <div class="level-left">
                                        <a
                                            v-if="record.user.profile_page_url"
                                            class="level-item button my-0 p-2"
                                            target="_blank"
                                            :class="{'is-small': screenType == 'mobile'}"
                                            :href="record.direction_url"
                                        >
                                            Directions
                                        </a>
                                        <a
                                            v-if="record.user.profile_page_url"
                                            class="level-item button is-primary my-0 p-2"
                                            target="_blank"
                                            :class="{'is-small': screenType == 'mobile'}"
                                            :href="record.user.profile_page_url"
                                        >
                                            Performer Detail
                                        </a>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

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

            <div class="column is-6-desktop is-6-tablet is-12-mobile">
                <div
                    ref="mapDiv"
                    :style="screenMapStyle"
                />
            </div>

            <div
                v-if="screenType != 'mobile'"
                class="column is-6-desktop is-12-tablet is-12-mobile"
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
    import bookingIcon from '@mod/Booking/Resources/assets/js/Libs/booking-icon';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import { Loader } from '@googlemaps/js-api-loader';
    import { MarkerClusterer } from "@googlemaps/markerclusterer";
    import { clone, each, find, keys, get, groupBy, merge, map, toString } from 'lodash';
    import { computed, defineAsyncComponent, onMounted, onUnmounted, reactive, ref, toRaw } from 'vue';
    import { useGeolocation, mapStyle as drawMapStyle } from '@/Libs/map';
    import { useModelWrapper, isBlank, useBreakpoints } from '@/Libs/utils';
    import { userImage } from '@/Libs/defaults';

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

            const { screenType } = useBreakpoints();

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
                screenType,
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
                this.queryParams.dates = this.queryParams.dates.filter(Boolean);

                this.onStartLoadingOverlay();

                axios
                    .get(currentUrl, {params: this.queryParams})
                    .then((response) => {
                        this.events = response.data.pagination;
                        this.mapData = response.data.map;

                        this.markerClusterer.clearMarkers(true);

                        this.map.setZoom(response.data.map.zoom);

                        this.map.panTo({
                            lat: parseFloat(response.data.map.center.latitude),
                            lng: parseFloat(response.data.map.center.longitude),
                        });

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
                    .catch(function (error) {
                        console.log(error);
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
