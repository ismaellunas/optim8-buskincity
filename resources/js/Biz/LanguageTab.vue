<template>
    <div class="buttons">
        <biz-button
            v-for="locale in localeOptions"
            :key="locale.id"
            type="button"
            :class="{'is-small': true, 'is-link': true, 'is-rounded': true, 'is-light': locale.id != selectedLocale }"
            @click="$emit('on-change-locale', locale.id)"
        >
            {{ locale.name }}
        </biz-button>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button';

    export default {
        name: 'LanguageTab',

        components: {
            BizButton,
        },

        props: {
            localeOptions: { type: Array, default:() => [] },
            selectedLocale: { type: String, default: null },
        },

        emits: [
            'on-change-locale',
        ],
    }
</script>