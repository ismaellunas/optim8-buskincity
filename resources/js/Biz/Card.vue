<template>
    <div class="card">
        <header
            v-if="$slots.headerTitle || $slots.headerButton"
            class="card-header"
        >
            <p
                class="card-header-title"
                :style="cardHeaderStyle"
                @click="onClickHeaderButton()"
            >
                <slot name="headerTitle" />
            </p>

            <button
                v-if="$slots.headerButton"
                class="card-header-icon"
                aria-label="more options"
                type="button"
                @click="onClickHeaderButton()"
            >
                <slot name="headerButton" />
            </button>

            <button
                v-else-if="isCollapses"
                class="card-header-icon"
                aria-label="more options"
                type="button"
                @click="onClickHeaderButton()"
            >
                <biz-icon :icon="iconCollapse" />
            </button>
        </header>

        <div
            v-if="isContentShown"
            class="card-content"
        >
            <slot />
        </div>

        <footer
            v-if="$slots.footer && isContentShown"
            class="card-footer"
        >
            <slot name="footer" />
        </footer>
    </div>
</template>

<script>
    import BizIcon from '@/Biz/Icon';
    import icon from '@/Libs/icon-class';
    import { ref } from 'vue';

    export default {
        name: 'BizCard',

        components: {
            BizIcon,
        },

        props: {
            iconCloseCollapse: { type: String, default: null },
            iconOpenCollapse: { type: String, default: null },
            isCollapses: { type: Boolean, default: false },
            openCollapseOnLoad: { type: Boolean, default: false },
        },

        setup(props) {
            let isContentShown = props.isCollapses ? false : true;

            if (!isContentShown && props.openCollapseOnLoad) {
                isContentShown = true;
            }

            return {
                isContentShown: ref(isContentShown),
            };
        },

        data () {
            return {
                icon,
            };
        },

        computed: {
            iconCollapse() {
                if (!this.isContentShown) {
                    return this.iconOpenCollapse ?? icon.angleDown;
                }

                return this.iconCloseCollapse ?? icon.angleUp;
            },

            cardHeaderStyle() {
                return {
                    cursor: this.isCollapses ? 'pointer' : 'auto',
                };
            },
        },

        methods: {
            onClickHeaderButton() {
                if (this.isCollapses) {
                    this.isContentShown = !this.isContentShown
                }
            },
        },
    }
</script>
