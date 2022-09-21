import { usePage } from '@inertiajs/inertia-vue3';
import { isBlank } from '@/Libs/utils';
import { isArray } from 'lodash';

export default {
    methods: {
        error(field, errorBag = 'default', errors = null) {
            if (!errors) {
                errors = usePage().props.value.errors;
            }

            if (isBlank(errors)) {
                return null;
            }

            let errorContainer = null;

            if (isBlank(errorBag)) {
                errorContainer = errors;
            } else if (errors.hasOwnProperty(errorBag)) {
                errorContainer = errors[errorBag];
            }

            if (errorContainer && errorContainer.hasOwnProperty(field)) {
                if (
                    isArray(errorContainer[field])
                    && errorContainer[field].length == 1
                ) {
                    return errorContainer[field][0];
                }

                return errorContainer[field];
            }

            return null;
        }
    }
}
