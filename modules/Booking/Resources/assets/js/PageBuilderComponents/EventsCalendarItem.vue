<template>
    <div class="box mb-2">
        <div class="columns is-mobile is-multiline">
            <div class="column is-4-desktop is-12-tablet is-12-mobile">
                <div class="columns is-mobile is-multiline level">
                    <div class="column is-12-desktop is-6-tablet is-6-mobile level-left">
                        <div :class="{ 'pt-2': !isMobile }">
                            <figure class="image is-128x128 level-item">
                                <img
                                    :data-src="photoUrl"
                                    width="128"
                                    height="128"
                                    class="is-rounded lazyload"
                                >
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

                            <tr v-if="isCityRowDisplayed">
                                <td :class="tdLabelClass">
                                    <biz-icon :icon="icon.city" />
                                </td>
                                <td :class="tdDescriptionClass">
                                    <span v-if="record.city">
                                        {{ record.city }}
                                    </span>
                                    <span v-if="record.country">
                                        , {{ record.country }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="isStartDateRowDisplayed">
                                <td :class="tdLabelClass">
                                    <biz-icon :icon="bookingIcon.calendar" />
                                </td>
                                <td :class="tdDescriptionClass">
                                    <template v-if="record.duration">
                                        <span>{{ record.formated_started_date }}</span>
                                        <span>, {{ record.started_time }}</span>
                                    </template>

                                    <template v-else>
                                        <span>{{ record.formated_started_date }}, {{ record.started_time }}</span>
                                    </template>

                                    <span v-if="record.timezone">, {{ record.timezone }}</span>
                                </td>
                            </tr>

                            <tr v-if="isEndDateRowDisplayed">
                                <td :class="tdLabelClass">
                                    <biz-icon :icon="bookingIcon.duration" />
                                </td>
                                <td :class="tdDescriptionClass">
                                    <span v-if="record.duration">
                                        {{ record.duration }}
                                    </span>
                                    <span v-else>
                                        {{ record.formated_ended_date }}, {{ record.ended_time }}
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
    import bookingIcon from '@mod/Booking/Resources/assets/js/Libs/booking-icon';
    import { city, locationMark } from '@/Libs/icon-class';
    import { computed, defineAsyncComponent } from 'vue';
    import { userImage } from '@/Libs/defaults';

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
                bookingIcon,
                icon: { city, locationMark },
                isCityRowDisplayed: computed(() => props.record.city || props.record.country),
                isEndDateRowDisplayed: computed(() => props.record.duration || props.record.ended_time),
                isMobile: computed(() => props.screenType == 'mobile'),
                isStartDateRowDisplayed: computed(() => props.record.formated_started_date),
                photoUrl: computed(() => props.record.photo_url ?? userImage),
                tdDescriptionClass: "m-0 py-0 px-0",
                tdLabelClass: "m-0 py-0 pl-0 pr-1",
            };
        },
    };
</script>
