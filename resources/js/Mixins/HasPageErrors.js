import { usePage } from '@inertiajs/inertia-vue3';
import { isBlank } from '@/Libs/utils';

export default {
    methods: {
        error(field, errorBag = 'default') {
            const errors = usePage().props.value.errors;
            if (
                isBlank(errors)
                || !errors.hasOwnProperty(errorBag)
            ) {
                return null;
            }
            if (errors[errorBag].hasOwnProperty(field)) {
                return errors[errorBag][field];
            }
            return null;
        }
    }
}
