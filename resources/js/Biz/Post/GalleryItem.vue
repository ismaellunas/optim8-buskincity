<template>
    <div class="card card-equal-height">
        <div class="card-image has-text-centered">
            <biz-image
                v-if="hasCover"
                :src="record.thumbnail_url"
            />
            <span
                v-else
                class="icon is-large"
            >
                <span class="fa-stack fa-lg">
                    <i :class="['fas fa-image', 'fa-5x']"></i>
                </span>
            </span>
        </div>

        <div class="card-content p-2">
            <div class="media">
                <div class="media-content">
                    <p class="subtitle is-6">
                        <span :class="{ 'mr-2': hasCategory }">
                            {{ firstCategoryName }}
                        </span>
                        <biz-tag class="is-info">
                            {{ record.locale.toUpperCase() }}
                        </biz-tag>
                    </p>
                    <p class="title is-4">
                        <a :href="previewLink" target="_blank">
                            <strong>{{ record.title }}</strong>
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
                    <i class="fas fa-pen"></i>
                </span>
            </biz-button-link>

            <biz-button-icon
                v-if="isDeleteEnabled"
                icon="far fa-trash-alt"
                title="Delete"
                type="button"
                :class="[actionClass, 'is-ghost', 'has-text-black', 'ml-1']"
                @click="$emit('on-delete-clicked', record)"
            />

            <slot name="actions" :record="record"></slot>
        </footer>
    </div>
</template>

<script>
    import MixinPostItem from '@/Mixins/PostItem';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizImage from '@/Biz/Image';
    import BizLink from '@/Biz/Link';
    import BizTag from '@/Biz/Tag';

    export default {
        name: 'PostGalleryItem',
        components: {
            BizButtonIcon,
            BizButtonLink,
            BizImage,
            BizLink,
            BizTag,
        },
        mixins: [
            MixinPostItem,
        ],
        props: {
            editLink: String,
            previewLink: String,
            isDeleteEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            record: Object,
        },
        emits: [
            'on-delete-clicked',
        ],
        data() {
            return {
                actionClass: "card-footer-item p-2 is-borderless is-shadowless is-inverted",
            };
        },
    }
</script>
