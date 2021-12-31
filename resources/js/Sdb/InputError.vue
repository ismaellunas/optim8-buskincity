<template>
    <div v-show="message">
        <p
            v-for="msg, index in messages"
            :key="index"
            class="help is-danger"
        >
            {{ msg }}
        </p>
    </div>
</template>

<script>
    import { isBlank } from '@/Libs/utils';

    export default {
        name: 'SdbInputError',

        props: {
            message: {
                type: [String, Array],
                default: undefined
            }
        },

        computed: {
            isArrayMessage() {
                return (!isBlank(this.message) && Array.isArray(this.message));
            },

            messages() {
                if (!this.isArrayMessage) {
                    if (this.message) {
                        return [ this.message ];
                    } else {
                        return [];
                    }
                }
                return this.message;
            }
        }
    };
</script>
