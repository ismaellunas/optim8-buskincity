<template>
    <div class="columns">
        <div class="column is-two-thirds">
            <div class="box">
                <div class="columns">
                    <div class="column">
                        <biz-button-link
                            class="is-link is-pulled-left mr-5 mb-2"
                            :href="route(baseRouteName + '.entries', formBuilder.id)"
                        >
                            <span class="icon-text">
                                <span class="icon">
                                    <i :class="icon.back" />
                                </span>
                                <span>Back</span>
                            </span>
                        </biz-button-link>
                    </div>
                </div>

                <div class="columns">
                    <biz-table
                        class="is-striped"
                        is-fullwidth
                    >
                        <tbody>
                            <tr
                                v-for="(label, key, index) in fieldLabels"
                                :key="index"
                            >
                                <td
                                    class="has-text-weight-bold"
                                    width="20%"
                                >
                                    {{ label }}
                                </td>

                                <template
                                    v-if="entryDisplay[key]?.component"
                                >
                                    <td>
                                        <component
                                            :is="entryDisplay[key]?.component"
                                            :value="entryDisplay[key]?.value"
                                        >
                                            <template
                                                #itemActions="{ mediumItem }"
                                            >
                                                <slot
                                                    name="itemActions"
                                                    :medium-item="mediumItem"
                                                />
                                            </template>
                                        </component>
                                    </td>
                                </template>

                                <template
                                    v-else
                                >
                                    <td
                                        v-html="entryDisplay[key]?.value"
                                    />
                                </template>
                            </tr>
                        </tbody>
                    </biz-table>
                </div>
            </div>
        </div>

        <div class="column">
            <biz-card>
                <template #headerTitle>
                    Entry
                </template>

                <biz-table is-fullwidth>
                    <tr>
                        <th>Entry ID</th>
                        <td>{{ entry.id }}</td>
                    </tr>
                    <tr>
                        <th>User IP</th>
                        <td>{{ entry?.meta_data?.ip_address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td v-if="isUserExists">
                            <template
                                v-if="canRedirectUser"
                            >
                                <biz-link :href="route('admin.users.edit', entry.user.id)">
                                    {{ entry.user.full_name }}
                                </biz-link>
                            </template>

                            <template
                                v-else
                            >
                                {{ entry.user.full_name }}
                            </template>
                        </td>

                        <td v-else>
                            -
                        </td>
                    </tr>
                    <tr>
                        <th>Submitted on</th>
                        <td>{{ moment(entry.created_at).format('YYYY-MM-DD [at] h:mm a') }}</td>
                    </tr>
                    <tr>
                        <th>Timezone</th>
                        <td>{{ entry?.meta_data?.timezone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Page Url</th>
                        <td>
                            <template v-if="entry?.meta_data?.page_url">
                                <a :href="entry.meta_data.page_url">
                                    ..{{ urlPath }}
                                </a>
                            </template>

                            <template v-else>
                                -
                            </template>
                        </td>
                    </tr>
                    <tr>
                        <th>Browser</th>
                        <td>{{ entry?.meta_data?.browser ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Device</th>
                        <td>{{ entry?.meta_data?.device ?? '-' }}</td>
                    </tr>
                </biz-table>
            </biz-card>
        </div>
    </div>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizTable from '@/Biz/Table';
    import BizCard from '@/Biz/Card';
    import BizLink from '@/Biz/Link';
    import MediaGallery from './EntryDisplay/MediaGallery';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';

    export default {
        name: 'FormBuilderEntryDetail',

        components: {
            BizLink,
            BizButtonLink,
            BizTable,
            BizCard,
            MediaGallery,
        },

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, default: () => {} },
            entry: { type: Object, default: () => {} },
            entryDisplay: { type: Object, default: () => {} },
            fieldLabels: { type: Object, default: () => {} },
            formBuilder: { type: Object, required: true },
        },

        data() {
            return {
                icon
            };
        },

        computed: {
            urlPath() {
                if (this.entry?.meta_data?.page_url) {
                    let url = new URL(this.entry.meta_data.page_url);

                    return url.pathname;
                }

                return null;
            },

            isUserExists() {
                return !!this.entry?.user;
            },

            canRedirectUser() {
                if (this.isUserExists) {
                    if (
                        !this.entry.user.isSuperAdministrator
                        && this.can.user.edit
                    ) {
                        return true;
                    }
                }

                return false;
            }
        },

        methods: {
            moment,
        },
    };
</script>