<template>
    <div class="column is-6">
        <biz-panel class="is-white">
            <template #heading>
                <div class="columns">
                    <div class="column">
                        {{ title }}
                    </div>
                    <div class="column">
                        <biz-button-link
                            v-if="data.permissions.add"
                            class="is-primary is-small is-pulled-right"
                            :href="route(data.baseRouteName+'.create')"
                        >
                            <span class="icon is-small">
                                <i :class="icon.add" />
                            </span>
                            <span>Add New</span>
                        </biz-button-link>
                    </div>
                </div>
            </template>

            <template #default>
                <template v-if="data.records.length > 0">
                    <biz-panel-block
                        v-for="record in data.records"
                        :key="record.id"
                    >
                        <div
                            class="media"
                            style="width: 100%"
                        >
                            <biz-image
                                class="media-left"
                                ratio="is-64x64"
                                :src="record.thumbnail_url"
                            />

                            <div class="media-content">
                                <div class="content">
                                    <p>
                                        <span
                                            v-if="record.categories.length > 0"
                                            class="mr-2"
                                        >
                                            {{ getFirstCategories(record.categories) }}
                                        </span>

                                        <biz-tag class="is-info">
                                            {{ record.locale.toUpperCase() }}
                                        </biz-tag>
                                        <br>

                                        <strong>{{ record.title }}</strong>
                                        <br>

                                        <span v-if="record.excerpt">
                                            {{ stringLimit(record.excerpt, 50) }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="showAction"
                                class="media-right"
                            >
                                <biz-dropdown class-button="is-ghost">
                                    <template #trigger>
                                        <span class="icon">
                                            <i :class="icon.ellipsis" />
                                        </span>
                                    </template>

                                    <biz-dropdown-item>
                                        <biz-link
                                            v-if="data.permissions.edit"
                                            :href="route(data.baseRouteName+'.edit', {id: record.id})"
                                        >
                                            <span class="icon is-small mr-2">
                                                <i :class="icon.edit" />
                                            </span>
                                            <span>Edit</span>
                                        </biz-link>
                                    </biz-dropdown-item>
                                    <biz-dropdown-item>
                                        <biz-link
                                            v-if="data.permissions.delete"
                                            @click.prevent="deleteRow(record)"
                                        >
                                            <span class="icon is-small mr-2">
                                                <i :class="icon.remove" />
                                            </span>
                                            <span>Delete</span>
                                        </biz-link>
                                    </biz-dropdown-item>
                                </biz-dropdown>
                            </div>
                        </div>
                    </biz-panel-block>
                </template>

                <template v-else>
                    <biz-panel-block>
                        Data empty.
                    </biz-panel-block>
                </template>

                <biz-panel-block>
                    <div
                        class="level"
                        style="width: 100%"
                    >
                        <div class="level-left" />
                        <div class="level-right">
                            <biz-button-link
                                class="is-primary is-outlined is-small"
                                :href="route(data.baseRouteName+'.index')"
                            >
                                View All
                            </biz-button-link>
                        </div>
                    </div>
                </biz-panel-block>
            </template>
        </biz-panel>
    </div>
</template>

<script>
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizImage from '@/Biz/Image';
    import BizLink from '@/Biz/Link';
    import BizPanel from '@/Biz/Panel';
    import BizPanelBlock from '@/Biz/PanelBlock';
    import BizTag from '@/Biz/Tag';
    import { confirmDelete } from '@/Libs/alert';
    import { head } from 'lodash';
    import icon from '@/Libs/icon-class';

    export default {
        name: 'BizWidgetPost',

        components: {
            BizButtonLink,
            BizDropdown,
            BizDropdownItem,
            BizImage,
            BizLink,
            BizPanel,
            BizPanelBlock,
            BizTag,
        },

        props: {
            data: {
                type: Object,
                required: true,
            },

            title: {
                type: String,
                default: "",
            },
        },

        data() {
            return {
                icon,
            };
        },

        computed: {
            showAction() {
                return this.data.permissions.edit
                    || this.data.permissions.delete;
            }
        },

        methods: {
            getFirstCategories(categories) {
                return head(categories);
            },

            deleteRow(post) {
                const self = this;
                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.$inertia.delete(
                            route(self.data.baseRouteName+'.destroy', post.id)
                        );
                    }
                });
            },

            stringLimit(string, limit) {
                return string.substring(0, limit)
                    + (string.length > limit ? ".." : "");
            }
        }
    }
</script>