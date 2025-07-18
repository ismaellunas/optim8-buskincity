<template>
    <div class="box mb-2">
        <div class="columns is-mobile is-multiline">
            <div class="column is-4-desktop is-12-tablet is-12-mobile">
                <div class="columns is-mobile is-multiline level">
                    <div class="column is-12-desktop is-6-tablet is-6-mobile level-left">
                        <div :class="{ 'pt-2': !isMobile }">
                            <figure class="image is-128x128 level-item">
                                <img
                                    v-if="record.photo_url"
                                    :data-src="record.photo_url"
                                    width="128"
                                    height="128"
                                    class="is-rounded lazyload"
                                >
                                <biz-icon
                                    v-else
                                    class="has-text-primary"
                                    :icon="[icon.camera, 'fa-6x']"
                                />
                            </figure>
                        </div>
                    </div>

                    <div class="column is-12-desktop is-6-tablet is-6-mobile is-hidden-desktop level-right">
                        <div class="buttons is-right">
                            <a
                                v-if="record.direction_url"
                                class="button level-item mb-2"
                                target="_blank"
                                :class="{'is-small': isMobile}"
                                :href="record.direction_url"
                            >
                                {{ textDirections }}
                            </a>
                            <a
                                v-if="record.page_url"
                                class="button is-primary level-item"
                                target="_blank"
                                :class="{'is-small': isMobile}"
                                :href="record.page_url"
                            >
                                {{ textDetail }}
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
                            :href="record.page_url ?? '#'"
                        >
                            <strong>{{ record.title }}</strong>
                        </a>
                    </p>
                    <table class="table is-narrow is-borderless mb-2">
                        <tbody>
                            <tr v-if="record.address">
                                <td :class="tdLabelClass">
                                    <biz-icon :icon="icon.locationMark" />
                                </td>
                                <td :class="tdDescriptionClass">
                                    {{ record.address }}
                                </td>
                            </tr>

                            <tr v-if="cityCountry">
                                <td :class="tdLabelClass">
                                    <biz-icon :icon="icon.city" />
                                </td>
                                <td :class="tdDescriptionClass">
                                    {{ cityCountry }}
                                </td>
                            </tr>

                            <tr v-if="isStartDateRowDisplayed">
                                <td :class="tdLabelClass">
                                    <biz-icon :icon="icon.calendar" />
                                </td>
                                <td :class="tdDescriptionClass">
                                    <template v-if="record.duration">
                                        <span>{{ record.formatted_started_date }}</span>
                                        <span>, {{ record.started_time }}</span>
                                    </template>

                                    <template v-else>
                                        <span>{{ record.formatted_started_date }}, {{ record.started_time }}</span>
                                    </template>

                                    <span v-if="record.timezone">, ({{ record.formatted_timezone }})</span>
                                </td>
                            </tr>

                            <tr v-if="isEndDateRowDisplayed">
                                <td :class="tdLabelClass">
                                    <biz-icon :icon="icon.duration" />
                                </td>
                                <td :class="tdDescriptionClass">
                                    <span v-if="record.duration">
                                        {{ record.duration }}
                                    </span>
                                    <span v-else>
                                        {{ record.formatted_ended_date }}, {{ record.ended_time }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="columns is-mobile is-hidden-touch">
                        <div class="column is-full">
                            <div class="buttons">
                                <a
                                    v-if="record.direction_url"
                                    class="level-item button my-0 p-2"
                                    target="_blank"
                                    :class="{'is-small': isMobile}"
                                    :href="record.direction_url"
                                >
                                    {{ textDirections }}
                                </a>
                                <a
                                    v-if="record.page_url"
                                    class="level-item button is-primary my-0 p-2"
                                    target="_blank"
                                    :class="{'is-small': isMobile}"
                                    :href="record.page_url"
                                >
                                    {{ textDetail }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { camera, city, locationMark, calendar, duration, timezone } from '@/Libs/icon-class';
    import { computed, defineAsyncComponent } from 'vue';

    export default {
        name: 'EventsCalendarItem',

        components: {
            BizIcon: defineAsyncComponent(() =>
                import('./../../../../../../resources/js/Biz/Icon.vue')
            ),
        },

        props: {
            record: { type: Object, required: true },
            screenType: { type: String, required: true },
            textDetail: { type: String, default: 'Detail' },
            textDirections: { type: String, default: 'Directions' },
        },

        setup(props, { emit }) {
            return {
                icon: { camera, city, locationMark, calendar, duration, timezone },
                tdDescriptionClass: "m-0 py-0 px-0",
                tdLabelClass: "m-0 py-0 pl-0 pr-1",
            };
        },

        computed: {
            isCityRowDisplayed() {
                return this.record.city || this.record.country;
            },

            isEndDateRowDisplayed() {
                return this.record.duration || this.record.ended_time;
            },

            isMobile() {
                return this.screenType == 'mobile';
            },

            isStartDateRowDisplayed() {
                return this.record.formatted_started_date
            },

            cityCountry() {
                const texts = [];

                if (!!this.record?.city) {
                    texts.push(this.record.city);
                }
                if (!!this.record?.country) {
                    texts.push(this.record.country);
                }

                return texts.join(', ');
            },
        },
    };
</script>
