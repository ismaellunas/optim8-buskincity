<template>
    <ul
        class="help ml-0"
        :class="typeClass"
        style="list-style-type: none;"
    >
        <li
            v-for="(note, index) in notes"
            :key="index"
        >
            {{ note }}
        </li>
    </ul>
</template>

<script>
    export default {
        name: 'BizFieldNotes',

        props: {
            type: {
                type: String,
                default: null,
                validator(value) {
                    return [
                        'primary',
                        'link',
                        'info',
                        'success',
                        'warning',
                        'danger',
                    ].includes(value)
                }
            },

            notes: { type: Array, default: () => [] },
        },

        computed: {
            typeClass() {
                if (this.type) {
                    return `is-${this.type}`;
                }

                return null;
            },
        },
    }
</script>