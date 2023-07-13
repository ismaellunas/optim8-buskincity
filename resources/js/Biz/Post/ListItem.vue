<template>
    <tr>
        <td>
            <article class="media">
                <biz-image
                    v-if="hasThumbnail"
                    class="media-left"
                    ratio="is-64x64"
                    :src="record.thumbnail_url"
                />

                <div
                    v-else
                    class="media-left"
                    style="width: 64px;"
                />

                <div class="media-content">
                    <div class="content">
                        <p>
                            <span :class="{ 'mr-2': hasCategory }">
                                {{ record.category_names }}
                            </span>
                            <biz-tag class="is-info">
                                {{ record.locale.toUpperCase() }}
                            </biz-tag>
                            <br>
                            <a
                                :href="previewLink"
                                target="_blank"
                            >
                                <strong>{{ record.title }}</strong>
                            </a>
                            <br>
                            <span>{{ record.excerpt }}</span>
                        </p>
                    </div>
                </div>

                <div class="media-right">
                    <biz-button-link
                        v-if="isEditEnabled"
                        class="is-ghost has-text-black"
                        title="Edit"
                        type="button"
                        :href="editLink"
                    >
                        <span class="icon is-small">
                            <i :class="icon.edit" />
                        </span>
                    </biz-button-link>

                    <biz-button-icon
                        v-if="isDeleteEnabled"
                        class="is-ghost has-text-black ml-1"
                        :icon="icon.remove"
                        type="button"
                        @click="$emit('on-delete-clicked', record)"
                    />

                    <slot
                        name="actions"
                        :record="record"
                    />
                </div>
            </article>
        </td>
    </tr>
</template>

<script>
    import MixinPostItem from '@/Mixins/PostItem';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizTag from '@/Biz/Tag.vue';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'PostListItem',
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
                icon,
            };
        },
    };
</script>
