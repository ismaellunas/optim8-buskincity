<template>
    <button
        class="button"
        :class="classes"
        :type="type"
    >
        <slot />
    </button>
</template>

<script>
    export default {
        name: 'SdbButtonOption',

        props: {
            type: {
                type: String,
                default: 'submit',
            },
            selected: {
                type: Boolean,
                default: null,
            },
            selectedClasess: {
                type: Array,
                default: () => ['is-link'],
            },
            unselectedClasess: {
                type: Array,
                default: () => ['is-outlined'],
            },
            option: {
                type: Object,
                default: () => {}
            },
            selectedAttribute: {
                type: String,
                default: 'selected'
            },
        },

        computed: {
            isSelected() {
                if (this.selected === null) {
                    const checkedValue = this.option[ this.selectedAttribute ] ?? false;
                    return checkedValue && checkedValue == true;
                }

                return this.selected;
            },

            classes() {
                return this.isSelected ? this.selectedClasess : this.unselectedClasess;
            }
        },
    };
</script>
