<template>
    <tr>
        <td>
            <article class="media">
                <sdb-image
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
                            <sdb-tag class="is-info">
                                {{ record.locale.toUpperCase() }}
                            </sdb-tag>
                            <br>
                            <sdb-link :href="editLink">
                                <strong>{{ record.title }}</strong>
                            </sdb-link>
                            <br>
                            <span>{{ record.excerpt }}</span>
                        </p>
                    </div>
                </div>

                <div class="media-right">
                    <sdb-button-link
                        v-if="isEditEnabled"
                        class="is-ghost has-text-black"
                        title="Edit"
                        type="button"
                        :href="editLink"
                    >
                        <span class="icon is-small">
                            <i class="fas fa-pen"></i>
                        </span>
                    </sdb-button-link>

                    <sdb-button-icon
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
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbImage from '@/Sdb/Image';
    import SdbLink from '@/Sdb/Link';
    import SdbTag from '@/Sdb/Tag';
    import { head } from 'lodash';

    export default {
        name: 'PostListItem',
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
            isDeleteEnabled: {type: Boolean, default: true},
            isEditEnabled: {type: Boolean, default: true},
            record: Object,
        },
        emits: [
            'on-delete-clicked',
        ],
    };
</script>
