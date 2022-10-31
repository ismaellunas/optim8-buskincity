<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div class="box is-shadowless">
            <div class="field">
                <label class="label">First Name</label>
                <div class="control">
                    <input
                        class="input"
                        type="text"
                        placeholder="Text input"
                        disabled
                    >
                </div>
            </div>

            <div class="field">
                <label class="label">Last Name</label>
                <div class="control">
                    <input
                        class="input"
                        type="text"
                        placeholder="Text input"
                        disabled
                    >
                </div>
            </div>

            <div class="field">
                <label class="label">Email</label>
                <div class="control has-icons-left">
                    <input
                        class="input"
                        type="email"
                        placeholder="Email input"
                        value="hello@"
                        disabled
                    >
                    <span class="icon is-small is-left">
                        <i class="fas fa-envelope" />
                    </span>
                </div>
            </div>

            <div class="field is-grouped">
                <div class="control">
                    <button
                        class="button is-link"
                        disabled
                    >
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormBuilder',

        components: {
            BizToolbarContent,
        },

        mixins: [
            MixinContentHasDimension,
            MixinDeletableContent,
            MixinDuplicableContent
        ],

        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
        },

        setup(props, { emit }) {
            return {
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
            };
        },
    }
</script>