import { usePage } from '@inertiajs/vue3';
import { isBlank } from '@/Libs/utils';
import { isArray } from 'lodash';

export default {
    methods: {
        error(field, errorBag = 'default', errors = null) {
            const self = this;

            if (!errors) {
                errors = usePage().props.errors;
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

            if (! errorContainer) {
                return null;
            }

            let messages = [];

            if (isArray(field)) {
                field.forEach(function (currentField) {
                    self.getErrorFromContainer(errorContainer, currentField, messages);
                });
            } else {
                self.getErrorFromContainer(errorContainer, field, messages);
            }

            return isBlank(messages.filter(Boolean)) ? null : messages;
        },

        getErrorFromContainer(errorContainer, field, messages) {
            if (
                isArray(errorContainer[field])
                && errorContainer[field].length == 1
            ) {
                messages.push(errorContainer[field][0]);
            } else if (
                isArray(errorContainer[field])
                && errorContainer[field].length > 1
            ) {
                errorContainer[field].forEach(function (error) {
                    messages.push(error);
                });
            } else if (errorContainer.hasOwnProperty(field)) {
                messages.push(errorContainer[field])
            }
        },
    }
}
