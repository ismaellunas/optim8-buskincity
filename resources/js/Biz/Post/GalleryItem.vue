<template>
    <div class="card card-equal-height">
        <div
            class="card-image has-text-centered"
        >
            <biz-image
                v-if="hasCover"
                :src="record.thumbnail_url"
            />
            <span
                v-else
                class="icon is-large p-4"
            >
                <span class="fa-stack fa-lg">
                    <i :class="['fas fa-image', 'fa-5x']" />
                </span>
            </span>
        </div>

        <div class="card-content p-2">
            <div class="media">
                <div class="media-content">
                    <p class="subtitle is-6">
                        <span :class="{ 'mr-2': hasCategory }">
                            {{ record.category_names }}
                        </span>
                        <biz-tag class="is-info">
                            {{ record.locale.toUpperCase() }}
                        </biz-tag>
                    </p>
                    <p
                        class="title is-5"
                        style="min-height: 45px"
                    >
                        <a
                            :href="previewLink"
                            target="_blank"
                        >
                            <strong>{{ recordTitle }}</strong>
                        </a>
                    </p>
                </div>
            </div>
            <div
                class="content"
                style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;"
            >
                <p>{{ record.excerpt }}</p>
            </div>
        </div>

        <footer class="card-footer">
            <biz-button-link
                v-if="isEditEnabled"
                title="Edit"
                type="button"
                :class="[actionClass,'is-ghost', 'has-text-black']"
                :href="editLink"
            >
                <span class="icon is-small">
                    <i :class="icon.edit" />
                </span>
            </biz-button-link>

            <biz-button-icon
                v-if="isDeleteEnabled"
                :icon="icon.remove"
                title="Delete"
                type="button"
                :class="[actionClass, 'is-ghost', 'has-text-black', 'ml-1']"
                @click="$emit('on-delete-clicked', record)"
            />

            <slot
                name="actions"
                :record="record"
            />
        </footer>
    </div>
</template>

<script>
    import MixinPostItem from '@/Mixins/PostItem';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizTag from '@/Biz/Tag.vue';
    import icon from '@/Libs/icon-class';
    import { truncate } from 'lodash';

    export default {
        name: 'PostGalleryItem',

        components: {
            BizButtonIcon,
            BizButtonLink,
            BizImage,
            BizTag,
        },

        mixins: [
            MixinPostItem,
        ],

        props: {
            editLink: { type: String, default: null },
            isDeleteEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            previewLink: { type: String, default: null },
            record: { type: Object, required: true },
        },

        emits: [
            'on-delete-clicked',
        ],

        data() {
            return {
                actionClass: "card-footer-item p-2 is-borderless is-shadowless is-inverted",
                icon,
            };
        },

        computed: {
            recordTitle() {
                return truncate(this.record.title, {
                    'length': 40,
                    'separator': ' '
                })
            },
        },
    }
</script>
