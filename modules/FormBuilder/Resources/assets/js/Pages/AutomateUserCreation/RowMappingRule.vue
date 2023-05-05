<template>
    <tr>
        <td>{{ formFieldLabel }}</td>
        <td>{{ userFieldLabel }}</td>
        <td>
            <div class="buttons">
                <biz-button-icon
                    class="is-ghost has-text-black ml-1"
                    type="button"
                    :icon="iconRemove"
                    @click.prevent="deleteRule"
                />
            </div>
        </td>
    </tr>
</template>

<script>
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import { computed } from "vue";
    import { confirmDelete } from '@/Libs/alert';
    import { remove as iconRemove } from '@/Libs/icon-class';

    export default {
        name: 'RowMappingRule',

        components: {
            BizButtonIcon,
        },

        props: {
            formFields: { type: Array, required: true },
            rule: { type: Object, required: true },
            userFields: { type: Array, required: true },
        },

        emits: [
            'delete-rule',
        ],

        setup(props, { emit }) {
            const formFieldLabel = computed(() => {
                const formField = props
                    .formFields
                    .find((field) => field.id == props.rule.from.id);

                return formField ? formField.label : null;
            });

            const userFieldLabel = computed(() => {
                const userField = props
                    .userFields
                    .find((field) => field.name == props.rule.to.name);

                return userField ? userField.label : null;
            });

            const deleteRule = () => {
                confirmDelete('Are you sure?').then((result) => {
                    if (result.isConfirmed) {
                        emit('delete-rule', props.rule.id);
                    }
                });
            };

            return {
                deleteRule,
                formFieldLabel,
                iconRemove,
                userFieldLabel,
            };
        },
    };
</script>
