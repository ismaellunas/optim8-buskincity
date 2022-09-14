<template>
    <div class="booking-time columns">
        <div class="column is-half">
            <div class="field is-grouped is-grouped-centered">
                <p class="control">
                    <biz-date-time
                        v-model="form.date"
                        type="date"
                        inline
                        :options="options"
                    />
                </p>
            </div>

            <div class="field is-grouped is-grouped-centered">
                <div class="field is-horizontal">
                    <div class="field-label is-normal">
                        <label class="label">Timezone</label>
                    </div>
                    <div class="field-body">
                        <div class="field">
                            <p class="control is-expanded">
                                <input
                                    class="input"
                                    type="text"
                                    :value="form.timezone"
                                    readonly
                                    disabled
                                >
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            id="reschedule-available-time-list-wrapper"
            class="column is-half"
        >
            <div
                id="reschedule-available-time-list"
                class="columns is-multiline"
            >
                <div
                    v-for="(time, index) in availableTimes"
                    :key="index"
                    class="column is-full py-1"
                >
                    <div class="buttons">
                        <biz-button
                            class="px-5"
                            type="button"
                            :class="getTimeClasses(index)"
                            @click="toggleSelectedIndex(index)"
                        >
                            {{ time }}
                        </biz-button>

                        <biz-button
                            v-if="isIndexSelected(index)"
                            class="button is-link px-5"
                            type="button"
                            @click="confirmTime(time)"
                        >
                            Confirm
                        </biz-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizDateTime from '@/Biz/DateTime';
    import moment from 'moment';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizButton,
            BizDateTime,
        },

        props: {
            modelValue: { type: Object, required: true },
            options: { type: Object, required: true },
            availableTimes: { type: Array, default: () => [] },
        },

        emits: [
            'get-available-times',
            'on-time-confirmed'
        ],

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                selectedIndex: null,
            };
        },

        watch: {
            'form.date': function (newVal, oldVal) {
                this.resetSelectedIndex();
                this.getAvailableTime();
            },
        },

        methods: {
            getAvailableTime() {
                this.$emit('get-available-times');
            },

            isIndexSelected(index) {
                return this.selectedIndex == index;
            },

            resetSelectedIndex() {
                this.selectedIndex = null;
            },

            toggleSelectedIndex(index) {
                if (this.selectedIndex == index) {
                    this.resetSelectedIndex();
                } else {
                    this.selectedIndex = index
                }
            },

            getTimeClasses(index) {
                return {
                    'is-danger': this.isIndexSelected(index),
                    'has-text-weight-bold': this.isIndexSelected(index),
                };
            },

            confirmTime(time) {
                this.form.time = time;
                this.$emit('on-time-confirmed');
            },
        },
    };
</script>
