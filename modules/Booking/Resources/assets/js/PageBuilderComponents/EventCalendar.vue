<template>
    <div class="event-calendar">
        <div class="columns">
            <div class="column">
                <div class="columns">
                    <div class="column">
                        <input
                            class="input"
                            type="text"
                            placeholder="Where"
                        >
                    </div>
                    <div class="column">
                        <div class="field">
                            <p class="control has-icons-left">
                                <input
                                    class="input"
                                    type="text"
                                    placeholder="From"
                                >
                                <span class="icon is-small is-left">
                                    <biz-icon :icon="iconCalendar" />
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <p class="control has-icons-left">
                                <input
                                    class="input"
                                    type="text"
                                    placeholder="To"
                                >
                                <span class="icon is-small is-left">
                                    <biz-icon :icon="iconCalendar" />
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="column">
                        <biz-button
                            class="is-primary"
                            type="button"
                        >
                            Search Event
                        </biz-button>
                    </div>
                </div>
                <div>
                    <article
                        v-for="event in events"
                        :key="event.id"
                        class="media"
                    >
                        <figure class="media-left">
                            <p class="image is-64x64">
                                <img :src="event.profile_photo_url">
                            </p>
                        </figure>
                        <div class="media-content">
                            <div class="content">
                                <p class="has-text-justified">
                                    <template v-if="event.stage_name">
                                        <strong>{{ event.stage_name }}</strong> - <small>{{ event.name }}</small>
                                    </template>
                                    <template v-else>
                                        <strong>{{ event.name }}</strong>
                                    </template>
                                    <br>
                                    {{ event.location }}
                                </p>
                            </div>
                            <div class="buttons">
                                <biz-button type="button">
                                    Direction
                                </biz-button>

                                <a
                                    class="button is-info"
                                    :href="event.profile_page_url"
                                    target="_blank"
                                >
                                    Performer Detail
                                </a>
                            </div>
                        </div>
                    </article>

                    <nav
                        class="mt-4 pagination is-centered"
                        role="navigation"
                        aria-label="pagination"
                    >
                        <a
                            class="pagination-previous"
                            @click.stop
                        >
                            Previous
                        </a>
                        <a
                            class="pagination-next"
                            @click.stop
                        >
                            Next
                        </a>
                        <ul class="pagination-list ">
                            <li><span class="pagination-ellipsis">&hellip;</span></li>
                            <li
                                v-for="pageNumber in 3"
                                :key="pageNumber"
                            >
                                <a
                                    class="pagination-link"
                                    @click.stop
                                >
                                    {{ pageNumber }}
                                </a>
                            </li>
                            <li><span class="pagination-ellipsis">&hellip;</span></li>
                        </ul>
                    </nav>
                </div>
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
    import BizIcon from '@/Biz/Icon';
    import { Loader } from '@googlemaps/js-api-loader';
    import { calendarCirclePlus as iconCalendar } from '@/Libs/icon-class';
    import { computed, onMounted, onUnmounted, reactive, ref, toRaw } from 'vue';
    import { useGeolocation } from '@/Libs/map';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        name: 'EventCalendar',

        components: {
            BizButton,
            BizIcon,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            apiKey: { type: String, default: null },
            initPosition: { type: Object, default: null },
            isDraggable: { type: Boolean, default: true },
            mapStyle: { type: [String, Array, Object], default: () => ["width: 100%", "height: 95vh"] },
        },

        setup(props) {
            let map = ref(null);
            let initPos = null;

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

            const currPos = computed(() => (initPos));
            const markers = ref([]);
            const mapDiv = ref(null);

            const loader = new Loader({
                apiKey: props.apiKey,
                version: "weekly",
                libraries: ["geometry", "drawing", "places"],
            });

            onMounted(async () => {
                await loader.load();

                map.value = new google.maps.Map(mapDiv.value, {
                    center: currPos.value,
                    zoom: 10,
                    draggable: props.isDraggable,
                    styles: [{"featureType":"all","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"all","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative.neighborhood","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"administrative.land_parcel","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"hue":"#ffbb00"},{"saturation":43.400000000000006},{"lightness":37.599999999999994},{"gamma":1}]},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#f0f2f6"}]},{"featureType":"poi","elementType":"all","stylers":[{"hue":"#00ff6a"},{"saturation":-1.0989010989011234},{"lightness":11.200000000000017},{"gamma":1},{"visibility":"simplified"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"hue":"#FFC200"},{"saturation":-61.8},{"lightness":45.599999999999994},{"gamma":1}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"weight":"1.50"},{"color":"#ee7a23"},{"lightness":"25"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"hue":"#ff0300"},{"saturation":-100},{"lightness":51.19999999999999},{"gamma":1}]},{"featureType":"road.local","elementType":"all","stylers":[{"hue":"#FF0300"},{"saturation":-100},{"lightness":52},{"gamma":1}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"transit.station","elementType":"labels","stylers":[{"visibility":"simplified"},{"lightness":"31"},{"gamma":"1.58"}]},{"featureType":"transit.station.airport","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.airport","elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"transit.station.rail","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"hue":"#0078FF"},{"saturation":-13.200000000000003},{"lightness":2.4000000000000057},{"gamma":1}]}],
                });
            });

            return {
                currPos,
                mapDiv,
                events: ref([]),
            };
        },

        data() {
            return {
                errorMessage: null,
                flash: {
                    message: null
                },
                form: reactive({}),
                formErrors: {},
                isShown: true,
                iconCalendar,
            };
        },

        computed: {
        },

        mounted() {
            this.getEvents();
        },

        methods: {
            getEvents() {
                axios
                    .get('/booking/search-events')
                    .then((response) => {
                        this.events = response.data;
                    });
            },
        },
    };
</script>
