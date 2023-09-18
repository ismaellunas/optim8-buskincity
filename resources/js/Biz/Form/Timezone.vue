<template>
    <biz-form-select
        v-model="timezone"
    >
        <option
            v-for="timezoneOption in options"
            :key="timezoneOption.id"
            :value="timezoneOption.id"
        >
            {{ timezoneOption.value }}
        </option>
    </biz-form-select>
</template>

<script>
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import { ref } from 'vue';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizFormSelect,
        },

        props: {
            modelValue: { type: [String, Number, null], required: true },
        },

        setup(props, { emit }) {
            return {
                timezone: useModelWrapper(props, emit),
                options: ref([]),
            };
        },

        beforeMount() {
            this.loadOptions();
        },

        methods: {
            loadOptions() {
                axios
                    .get(route('admin.api.options.timezones'))
                    .then((response) => {
                        this.options = response?.data ?? [];
                    });
            },
        },
    };
</script>
