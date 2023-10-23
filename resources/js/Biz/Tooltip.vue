<template>
    <v-tooltip
        style="display: inline"
        :placement="placement"
        :triggers="triggers"
        :auto-hide="true"
    >
        <template v-if="$slots.trigger">
            <slot name="trigger" />
        </template>

        <template v-else>
            <a>
                <biz-icon :icon="circleQuestion" />
            </a>
        </template>

        <template #popper>
            <p
                class="p-3"
                style="max-width: 20em"
            >
                {{ message }}
            </p>
        </template>
    </v-tooltip>
</template>

<script>
    import BizIcon from '@/Biz/Icon.vue';
    import { circleQuestion } from '@/Libs/icon-class';
    import { Tooltip as VTooltip } from 'floating-vue';
    import 'floating-vue/dist/style.css';
    export default {
        name: "BizTooltip",

        components: {
            BizIcon,
            VTooltip,
        },

        props: {
            message: { type: String, default: null },
            placement: { type: String, default: 'right' },
            triggers: {
                type: Array,
                validator(value) {
                    let valid = true;

                    value.forEach(function (trigger) {
                        if (! [
                            'click',
                            'hover',
                            'focus',
                            'touch',
                        ].includes(trigger)) {
                            valid = false;
                        }
                    })

                    return valid;
                },
                default() {
                    return ['click'];
                },
            }
        },

        data() {
            return {
                circleQuestion,
            };
        },
    }
</script>