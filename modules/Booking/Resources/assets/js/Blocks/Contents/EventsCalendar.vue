<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div class="box is-shadowless">
            <div class="columns is-flex is-vcentered">
                <div class="column">
                    <div class="columns">
                        <div class="column">
                            <input
                                class="input is-small"
                                type="text"
                                placeholder="Where"
                                disabled
                            >
                        </div>
                        <div class="column">
                            <div class="field">
                                <p class="control has-icons-left">
                                    <input
                                        class="input is-small"
                                        type="text"
                                        placeholder="From"
                                        disabled
                                    >
                                    <span class="icon is-small is-left">
                                        <biz-icon :icon="iconCalendar" />
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field">
                                <p class="control has-icons-left">
                                    <input
                                        class="input is-small"
                                        type="text"
                                        placeholder="To"
                                        disabled
                                    >
                                    <span class="icon is-small is-left">
                                        <biz-icon :icon="iconCalendar" />
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <article
                        v-for="index in 3"
                        :key="index"
                        class="media"
                    >
                        <figure class="media-left">
                            <p class="image is-64x64">
                                <img src="https://bulma.io/images/placeholders/128x128.png">
                            </p>
                        </figure>
                        <div class="media-content">
                            <div class="content has-text-grey-light">
                                <p>
                                    <strong>Title</strong>
                                    <br>
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin ornare magna eros, eu pellentesque tortor vestibulum ut. Maecenas non massa sem. Etiam finibus odio quis feugiat facilisis.
                                </p>
                            </div>
                        </div>
                    </article>

                    <nav
                        class="mt-4 pagination is-centered is-small"
                        role="navigation"
                        aria-label="pagination"
                    >
                        <a
                            class="pagination-previous is-disabled"
                            @click.stop
                        >
                            Previous
                        </a>
                        <a
                            class="pagination-next is-disabled"
                            @click.stop
                        >
                            Next
                        </a>
                        <ul class="pagination-list ">
                            <li><span class="pagination-ellipsis">&hellip;</span></li>
                            <li
                                v-for="pageNumber in 3"
                                :key="pageNumber"
                            >
                                <a
                                    class="pagination-link is-disabled"
                                    @click.stop
                                >
                                    {{ pageNumber }}
                                </a>
                            </li>
                            <li><span class="pagination-ellipsis">&hellip;</span></li>
                        </ul>
                    </nav>
                </div>

                <div class="column has-text-centered">
                    <div class="box">
                        <i class="fa-light fa-map-location-dot fa-10x" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import BizIcon from '@/Biz/Icon';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { calendarCirclePlus as iconCalendar } from '@/Libs/icon-class';
    import { concat } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'EventsCalendar',

        components: {
            BizToolbarContent,
            BizIcon,
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
                iconCalendar,
            };
        },
    }
</script>
