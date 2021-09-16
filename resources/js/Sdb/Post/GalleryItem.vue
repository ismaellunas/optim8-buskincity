<template>
    <div class="card card-equal-height">
        <div class="card-image has-text-centered">
            <sdb-image
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
                        <sdb-tag class="is-info">
                            {{ record.locale.toUpperCase() }}
                        </sdb-tag>
                    </p>
                    <p class="title is-4">
                        <sdb-link :href="editLink">
                            <strong>{{ record.title }}</strong>
                        </sdb-link>
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

            <sdb-button-link
                title="Edit"
                :class="[actionClass,'is-ghost', 'has-text-black']"
                :href="editLink"
            >
                <span class="icon is-small">
                    <i class="fas fa-pen"></i>
                </span>
            </sdb-button-link>

            <sdb-button-icon
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
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbImage from '@/Sdb/Image';
    import SdbLink from '@/Sdb/Link';
    import SdbTag from '@/Sdb/Tag';

    export default {
        name: 'PostGalleryItem',
        components: {
            SdbButtonIcon,
            SdbButtonLink,
            SdbImage,
            SdbLink,
            SdbTag,
        },
        mixins: [
            MixinPostItem,
        ],
        props: {
            editLink: String,
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
