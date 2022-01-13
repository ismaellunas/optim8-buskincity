<template>
    <tr>
        <td>
            <article class="media">
                <biz-image
                    v-if="record.thumbnail_url"
                    class="media-left"
                    ratio="is-64x64"
                    :src="record.thumbnail_url"
                />

                <div
                    v-else
                    class="media-left"
                    style="width: 64px;"
                >
                </div>

                <div class="media-content">
                    <div class="content">
                        <p>
                            <span :class="{ 'mr-2': hasCategory }">
                                {{ firstCategoryName }}
                            </span>
                            <biz-tag class="is-info">
                                {{ record.locale.toUpperCase() }}
                            </biz-tag>
                            <br>
                            <biz-link :href="editLink">
                                <strong>{{ record.title }}</strong>
                            </biz-link>
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
                            <i class="fas fa-pen"></i>
                        </span>
                    </biz-button-link>

                    <biz-button-icon
                        v-if="isDeleteEnabled"
                        class="is-ghost has-text-black ml-1"
                        icon="far fa-trash-alt"
                        type="button"
                        @click="$emit('on-delete-clicked', record)"
                    />

                    <slot name="actions" :record="record"></slot>
                </div>
            </article>
        </td>
    </tr>
</template>

<script>
    import MixinPostItem from '@/Mixins/PostItem';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizImage from '@/Biz/Image';
    import BizLink from '@/Biz/Link';
    import BizTag from '@/Biz/Tag';
    import { head } from 'lodash';

    export default {
        name: 'PostListItem',
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
            isDeleteEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            record: Object,
        },
        emits: [
            'on-delete-clicked',
        ],
    };
</script>
