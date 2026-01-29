<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div class="box is-shadowless">
            <div class="columns is-flex is-vcentered">
                <div class="column is-half has-text-centered">
                    <div class="box">
                        <i :class="[icon.mapLocationDot, 'fa-8x']" />
                    </div>
                </div>

                <div class="column is-half">
                    <div class="columns">
                        <div class="column">
                            <div class="control has-icons-left">
                                <div class="select is-small is-fullwidth">
                                    <select v-model="filters.location">
                                        <option :value="null">
                                            Any Location
                                        </option>
                                        <option
                                            v-for="option in locationOptions"
                                            :key="option.id"
                                            :value="option.id"
                                        >
                                            {{ option.value }}
                                        </option>
                                    </select>
                                </div>
                                <biz-icon
                                    class="is-small is-left"
                                    :icon="icon.globe"
                                />
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <p class="control has-icons-left">
                                    <input
                                        v-model="filters.dateFrom"
                                        class="input is-small"
                                        type="date"
                                        placeholder="From"
                                    >
                                    <biz-icon
                                        class="is-small is-left"
                                        :icon="icon.calendarCirclePlus"
                                    />
                                </p>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <p class="control has-icons-left">
                                    <input
                                        v-model="filters.dateTo"
                                        class="input is-small"
                                        type="date"
                                        placeholder="To"
                                    >
                                    <span class="icon is-small is-left">
                                        <biz-icon :icon="icon.calendarCirclePlus" />
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="column is-narrow">
                            <button
                                class="button is-small is-primary"
                                @click="loadEvents"
                            >
                                Search
                            </button>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div
                        v-if="loading"
                        class="notification is-info is-light"
                    >
                        <p>Loading events...</p>
                    </div>

                    <!-- No Events Message -->
                    <div
                        v-else-if="!loading && events.length === 0"
                        class="notification is-warning is-light"
                    >
                        <p>No events found. Try adjusting your filters.</p>
                    </div>

                    <!-- Events List -->
                    <article
                        v-for="event in paginatedEvents"
                        :key="event.id"
                        class="media"
                    >
                        <figure class="media-left">
                            <p class="image is-64x64">
                                <img
                                    v-if="event.photo_url"
                                    class="is-rounded"
                                    :src="event.photo_url"
                                    :alt="event.title"
                                >
                                <i
                                    v-else
                                    :class="[icon.camera, 'fa-3x', 'has-text-grey-lighter']"
                                />
                            </p>
                        </figure>
                        <div class="media-content">
                            <div class="content">
                                <p>
                                    <strong>{{ event.title }}</strong>
                                    <br>
                                    <small v-if="event.address">
                                        <i :class="icon.locationMark" /> {{ event.address }}
                                    </small>
                                    <br>
                                    <small v-if="event.city || event.country">
                                        <i :class="icon.city" /> {{ [event.city, event.country].filter(Boolean).join(', ') }}
                                    </small>
                                    <br>
                                    <small v-if="event.formatted_started_date">
                                        <i :class="icon.calendar" /> {{ event.formatted_started_date }}
                                        <template v-if="event.started_time">
                                            , {{ event.started_time }}
                                        </template>
                                        <template v-if="event.duration">
                                            ({{ event.duration }})
                                        </template>
                                        <template v-else-if="event.ended_time">
                                            - {{ event.ended_time }}
                                        </template>
                                    </small>
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            <div class="columns">
                <div class="column">
                    <nav
                        v-if="totalPages > 1"
                        class="mt-4 pagination is-centered is-small"
                        role="navigation"
                        aria-label="pagination"
                    >
                        <a
                            class="pagination-previous"
                            :class="{ 'is-disabled': currentPage === 1 }"
                            @click.prevent="changePage(currentPage - 1)"
                        >
                            Previous
                        </a>
                        <a
                            class="pagination-next"
                            :class="{ 'is-disabled': currentPage === totalPages }"
                            @click.prevent="changePage(currentPage + 1)"
                        >
                            Next
                        </a>
                        <ul class="pagination-list">
                            <li
                                v-for="pageNumber in visiblePages"
                                :key="pageNumber"
                            >
                                <span
                                    v-if="pageNumber === '...'"
                                    class="pagination-ellipsis"
                                >&hellip;</span>
                                <a
                                    v-else
                                    class="pagination-link"
                                    :class="{ 'is-current': pageNumber === currentPage }"
                                    @click.prevent="changePage(pageNumber)"
                                >
                                    {{ pageNumber }}
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizIcon from '@/Biz/Icon.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import { calendarCirclePlus, mapLocationDot, globe, camera, locationMark, city, calendar } from '@/Libs/icon-class';
    import { useModelWrapper } from '@/Libs/utils';
    import { ref, computed, onMounted } from 'vue';
    import { sortBy, each } from 'lodash';
    import axios from 'axios';

    export default {
        name: 'EventsCalendar',

        components: {
            BizToolbarContent,
            BizIcon,
        },

        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],

        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        setup(props, { emit }) {
            const events = ref([]);
            const availableLocations = ref([]);
            const loading = ref(false);
            const currentPage = ref(1);
            const perPage = 5;
            
            const today = new Date();
            const nextWeek = new Date();
            nextWeek.setDate(today.getDate() + 7);
            
            const filters = ref({
                location: null,
                dateFrom: today.toISOString().split('T')[0],
                dateTo: nextWeek.toISOString().split('T')[0],
            });

            const locationOptions = computed(() => {
                const options = [];

                sortBy(availableLocations.value, [(location) => location.country])
                    .forEach((location) => {
                        options.push({
                            id: location.country_code,
                            value: location.country,
                        });

                        if (location.cities) {
                            location.cities.sort();
                            each(location.cities, (cityName) => {
                                options.push({
                                    id: location.country_code + '-' + cityName,
                                    value: ' - ' + cityName,
                                });
                            });
                        }
                    });

                return options;
            });

            const locationParts = computed(() => {
                if (!filters.value.location) {
                    return { country: null, city: null };
                }

                const parts = filters.value.location.split('-');
                return {
                    country: parts[0] ?? null,
                    city: parts[1] ?? null,
                };
            });

            const paginatedEvents = computed(() => {
                const start = (currentPage.value - 1) * perPage;
                const end = start + perPage;
                return events.value.slice(start, end);
            });

            const totalPages = computed(() => {
                return Math.ceil(events.value.length / perPage);
            });

            const visiblePages = computed(() => {
                const pages = [];
                const total = totalPages.value;
                const current = currentPage.value;

                if (total <= 7) {
                    for (let i = 1; i <= total; i++) {
                        pages.push(i);
                    }
                } else {
                    if (current <= 3) {
                        for (let i = 1; i <= 4; i++) {
                            pages.push(i);
                        }
                        pages.push('...');
                        pages.push(total);
                    } else if (current >= total - 2) {
                        pages.push(1);
                        pages.push('...');
                        for (let i = total - 3; i <= total; i++) {
                            pages.push(i);
                        }
                    } else {
                        pages.push(1);
                        pages.push('...');
                        for (let i = current - 1; i <= current + 1; i++) {
                            pages.push(i);
                        }
                        pages.push('...');
                        pages.push(total);
                    }
                }

                return pages;
            });

            const changePage = (page) => {
                if (page >= 1 && page <= totalPages.value) {
                    currentPage.value = page;
                }
            };

            const loadEvents = async () => {
                loading.value = true;
                try {
                    const params = {
                        dates: [filters.value.dateFrom, filters.value.dateTo].filter(Boolean),
                    };

                    if (locationParts.value.country) {
                        params.country = locationParts.value.country;
                    }
                    if (locationParts.value.city) {
                        params.city = locationParts.value.city;
                    }

                    const response = await axios.get('/api/booking/events-calendar', { params });
                    
                    if (response.data && response.data.pagination && response.data.pagination.data) {
                        events.value = response.data.pagination.data;
                        currentPage.value = 1;
                    } else {
                        events.value = [];
                    }
                } catch (error) {
                    console.error('Error loading events:', error);
                    events.value = [];
                } finally {
                    loading.value = false;
                }
            };

            const loadLocationOptions = async () => {
                try {
                    const response = await axios.get('/api/booking/events-calendar/location-options');
                    availableLocations.value = response.data || [];
                } catch (error) {
                    console.error('Error loading location options:', error);
                    availableLocations.value = [];
                }
            };

            onMounted(async () => {
                await loadLocationOptions();
                await loadEvents();
            });

            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
                icon: {
                    calendarCirclePlus,
                    mapLocationDot,
                    globe,
                    camera,
                    locationMark,
                    city,
                    calendar,
                },
                events,
                filters,
                loading,
                locationOptions,
                paginatedEvents,
                currentPage,
                totalPages,
                visiblePages,
                changePage,
                loadEvents,
            };
        },
    }
</script>
