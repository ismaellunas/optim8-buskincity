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
                v-else-if="isCollapsed"
                class="card-header-icon"
                aria-label="more options"
                type="button"
                @click="onClickHeaderButton()"
            >
                <biz-icon :icon="iconCollapseOrExpand" />
            </button>
        </header>

        <div
            v-if="isContentShown"
            class="card-content"
            :class="classCardContent"
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
    import BizIcon from '@/Biz/Icon.vue';
    import { angleUp as iconAngleUp, angleDown as iconAngleDown } from '@/Libs/icon-class';
    import { ref } from 'vue';

    export default {
        name: 'BizCard',

        components: {
            BizIcon,
        },

        props: {
            iconExpand: { type: String, default: null },
            iconCollapse: { type: String, default: null },
            isCollapsed: { type: Boolean, default: false },
            isExpandingOnLoad: { type: Boolean, default: false },
            classCardContent: { type: [String, Object, Array], default: ''},
        },

        emits: [
            'on-click-header-card',
        ],

        setup(props) {
            let isContentShown = props.isCollapsed ? false : true;

            if (!isContentShown && props.isExpandingOnLoad) {
                isContentShown = true;
            }

            return {
                isContentShown: ref(isContentShown),
                iconAngleUp,
                iconAngleDown,
            };
        },

        computed: {
            iconCollapseOrExpand() {
                if (!this.isContentShown) {
                    return this.iconCollapse ?? iconAngleDown;
                }

                return this.iconExpand ?? iconAngleUp;
            },

            cardHeaderStyle() {
                return {
                    cursor: this.isCollapsed ? 'pointer' : 'auto',
                };
            },
        },

        methods: {
            onClickHeaderButton() {
                if (this.isCollapsed) {
                    this.isContentShown = !this.isContentShown

                    this.$emit('on-click-header-card', this.isContentShown);
                }
            },
        },
    }
</script>
