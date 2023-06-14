<template>
    <div class="card mb-5">
        <header
            v-if="group.title"
            class="card-header"
        >
            <slot
                name="title"
                :title="group.title"
            >
                <h3 class="card-header-title">
                    {{ group.title }}
                </h3>
            </slot>
        </header>

        <div class="card-content">
            <div class="columns is-multiline is-mobile">
                <template
                    v-for="(field, name) in group.fields"
                    :key="name"
                >
                    <div
                        class="column"
                        :class="getColumnSizeClass(field.column)"
                    >
                        <component
                            :is="field.type"
                            v-if="!field.is_translated"
                            :ref="'field__' + name"
                            v-model="form[ name ]"
                            :errors="formErrors"
                            :schema="field"
                        />

                        <component
                            :is="field.type"
                            v-else
                            :ref="'field__' + name"
                            v-model="form[ name ][ selectedLocale ]"
                            :errors="formErrors"
                            :schema="field"
                            :selected-locale="selectedLocale"
                        />
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import Checkbox from './Checkbox.vue';
    import CheckboxGroup from './CheckboxGroup.vue';
    import Email from './Email.vue';
    import File from './File.vue';
    import FileDragDrop from './FileDragDrop.vue';
    import Number from './Number.vue';
    import Phone from './Phone.vue';
    import Radio from './Radio.vue';
    import Select from './Select.vue';
    import Text from './Text.vue';
    import Textarea from './Textarea.vue';
    import Video from './Video.vue';
    import { useModelWrapper, isEmpty } from '@/Libs/utils';

    export default {
        name: 'FormFieldGroup',

        components: {
            Checkbox,
            CheckboxGroup,
            Email,
            File,
            FileDragDrop,
            Number,
            Phone,
            Radio,
            Select,
            Text,
            Textarea,
            Video,
        },

        props: {
            group: {
                type: Object,
                default: () => {}
            },
            modelValue: {
                type: Object,
                required: true
            },
            selectedLocale: {
                type: String,
                default: null,
            },
            errors: {
                type: Object,
                default: () => {},
            },
        },

        setup(props, { emit }) {
            return {
                form: useModelWrapper(props, emit),
            };
        },

        computed: {
            formErrors() {
                if (this.form.errors) {
                    return this.form.errors;
                }

                return this.errors;
            },
        },

        methods: {
            getColumnSizeClass(size) {
                if (size) {
                    return [
                        size + '-desktop',
                        size + '-tablet',
                        'is-12-mobile',
                    ].join(' ');
                }
                return 'is-12-mobile is-12-tablet is-12-desktop';
            },
        },
    };
</script>
