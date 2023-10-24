import { usePage } from '@inertiajs/vue3';

export default {
    computed: {
        i18n() {
            return usePage().props.i18n;
        },
    },
};
