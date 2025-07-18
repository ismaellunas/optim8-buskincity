<template>
    <div class="box">
        <biz-error-notifications
            :errors="$page.props.errors"
        />

        <div class="columns">
            <div class="column is-two-thirds">
                <biz-table
                    class="is-bordered"
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

            <div class="column">
                <biz-card>
                    <template #headerTitle>
                        {{ i18n.entry }}
                    </template>

                    <biz-table is-fullwidth>
                        <tr>
                            <th>{{ i18n.entry_id }}</th>
                            <td>{{ entry.id }}</td>
                        </tr>
                        <tr>
                            <th>{{ i18n.user_ip }}</th>
                            <td>{{ entry?.meta_data?.ip_address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ i18n.user }}</th>
                            <td v-if="isUserExists">
                                <template
                                    v-if="can.user.redirectUser"
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
                            <th>{{ i18n.submitted_on }}</th>
                            <td>{{ moment(entry.created_at).format('YYYY-MM-DD [at] h:mm a') }}</td>
                        </tr>
                        <tr>
                            <th>{{ i18n.timezone }}</th>
                            <td>{{ entry?.meta_data?.timezone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ i18n.page_url }}</th>
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
                            <th>{{ i18n.browser }}</th>
                            <td>{{ entry?.meta_data?.browser ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ i18n.device }}</th>
                            <td>{{ entry?.meta_data?.device ?? '-' }}</td>
                        </tr>
                    </biz-table>
                </biz-card>

                <biz-card
                    v-if="hasActions"
                    class="mt-2 has-text-centered"
                >
                    <template #headerTitle>
                        {{ i18n.actions }}
                    </template>

                    <div
                        v-if="entry.meta_data.can.mark_as_read"
                        class="m-2"
                    >
                        <biz-button
                            class="is-fullwidth"
                            type="button"
                            @click.prevent="actionRequest(baseRouteName+'.mark-as-read')"
                        >
                            {{ i18n.mark_as_read }}
                        </biz-button>
                    </div>
                    <div
                        v-if="entry.meta_data.can.mark_as_unread"
                        class="m-2"
                    >
                        <biz-button
                            class="is-fullwidth"
                            type="button"
                            @click.prevent="actionRequest(baseRouteName+'.mark-as-unread')"
                        >
                            {{ i18n.mark_as_unread }}
                        </biz-button>
                    </div>
                    <div
                        v-if="entry.meta_data.can.archive"
                        class="m-2"
                    >
                        <biz-button
                            class="is-fullwidth is-danger"
                            type="button"
                            @click.prevent="archive(baseRouteName+'.archive')"
                        >
                            {{ i18n.archive }}
                        </biz-button>
                    </div>
                    <div
                        v-if="entry.meta_data.can.restore"
                        class="m-2"
                    >
                        <biz-button
                            class="is-fullwidth"
                            type="button"
                            @click.prevent="restore(baseRouteName+'.restore')"
                        >
                            {{ i18n.restore }}
                        </biz-button>
                    </div>
                    <div
                        v-if="entry.meta_data.can.force_delete"
                        class="m-2"
                    >
                        <biz-button
                            class="is-fullwidth is-danger"
                            type="button"
                            @click.prevent="forceDelete(baseRouteName+'.force-delete')"
                        >
                            {{ i18n.delete }}
                        </biz-button>
                    </div>
                    <div
                        v-if="can.automate_user_creation"
                        class="m-2"
                    >
                        <biz-button
                            class="is-fullwidth is-primary"
                            type="button"
                            @click.prevent="createOrUpdateUser()"
                        >
                            {{ i18n.create_or_update_user }}
                        </biz-button>
                    </div>
                </biz-card>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizCard from '@/Biz/Card.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizLink from '@/Biz/Link.vue';
    import BizTable from '@/Biz/Table.vue';
    import MediaGallery from './EntryDisplay/MediaGallery.vue';
    import icon from '@/Libs/icon-class';
    import moment from 'moment';
    import { oops as oopsAlert, success as successAlert, confirmDelete, confirm as confirmAlert } from '@/Libs/alert';
    import { router, usePage } from '@inertiajs/vue3';

    export default {
        name: 'FormBuilderEntryDetail',

        components: {
            BizLink,
            BizButton,
            BizButtonLink,
            BizTable,
            BizCard,
            BizErrorNotifications,
            MediaGallery,
        },

        mixins: [
            MixinHasLoader,
        ],

        layout: AppLayout,

        props: {
            baseRouteName: { type: String, required: true },
            can: { type: Object, default: () => {} },
            entry: { type: Object, default: () => {} },
            entryDisplay: { type: Object, default: () => {} },
            fieldLabels: { type: Object, default: () => {} },
            formBuilder: { type: Object, required: true },
            i18n: { type: Object, default: () => ({
                entry: 'Entry',
                entry_id: 'Entry ID',
                user_ip: 'User IP',
                user: 'User',
                submitted_on: 'Submitted on',
                timezone: 'Timezone',
                page_url: 'Page URL',
                browser: 'Browser',
                device: 'Device',
                actions: 'Actions',
                mark_as_read: 'Mark as read',
                mark_as_unread: 'Mark as unread',
                archive: 'Archive',
                restore: 'Restore',
                delete: 'Delete',
                confirm_archive: 'Confirm archive',
                are_you_sure: 'Are you sure?',
                confirm_restore: 'Confirm restore',
                confirm_deletion: 'Confirm deletion',
                confirm_deletion_message: 'Once the resources are deleted, they will be permanently deleted.',
                create_or_update_user: 'Create or update user',
            }) }
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

            hasActions() {
                return (
                    this.entry.meta_data.can.mark_as_read
                    || this.entry.meta_data.can.mark_as_unread
                    || this.entry.meta_data.can.archive
                    || this.entry.meta_data.can.restore
                    || this.entry.meta_data.can.force_delete
                );
            },
        },

        methods: {
            moment,

            actionRequest(routeName, entry, afterSuccess) {
                const self = this;

                this.$inertia.post(
                    route(routeName, { form_builder: this.formBuilder.id, form_entry: this.entry.id }),
                    {},
                    {
                        onStart: () => this.onStartLoadingOverlay(),
                        onFinish: (visit) => {
                            self.onEndLoadingOverlay();
                        },
                        onError: (errors) => {
                            oopsAlert();
                        },
                        onSuccess: (page) => {
                            successAlert(page.props.flash.message);

                            if (_.isFunction(afterSuccess)) {
                                afterSuccess(page);
                            }
                        },
                    }
                );
            },

            archive(routeName) {
                confirmDelete(
                    this.i18n.confirm_archive,
                    this.i18n.are_you_sure
                ).then(result => {
                    if (result.isConfirmed) {
                        this.actionRequest(routeName, this.entry);
                    }
                });
            },

            restore(routeName) {
                confirmDelete(
                    this.i18n.confirm_restore,
                    this.i18n.are_you_sure
                ).then(result => {
                    if (result.isConfirmed) {
                        this.actionRequest(routeName, this.entry);
                    }
                });
            },

            forceDelete(routeName) {
                confirmDelete(
                    this.i18n.are_you_sure,
                    this.i18n.confirm_deletion_message,
                    this.i18n.confirm_deletion
                ).then(result => {
                    if (result.isConfirmed) {
                        this.actionRequest(routeName, this.entry);
                    }
                });
            },

            async createOrUpdateUser() {
                this.onStartLoadingOverlay();

                let response = null;

                try {
                    response = await axios.get(route(
                        'admin.api.automate-user-creation.confirmation',
                        this.entry.id
                    ));
                } catch (error) {
                    let messages = _.map(error.response.data, (message) => message);

                    messages = _.join(messages, '</li><li>');

                    oopsAlert({ html: '<ul><li>'+messages+'</li></ul>' });

                    return;
                } finally {
                    this.onEndLoadingOverlay();
                }

                confirmAlert(
                    this.i18n.are_you_sure,
                    response.data?.message ?? null,
                    this.i18n.yes,
                    { icon: response.data.isExists ? 'warning': '' },
                ).then(result => {
                    if (result.isConfirmed) {
                        const url = route(
                            'admin.form-builders.entries.automate-user-creation.create-or-update',
                            [
                                this.formBuilder.id,
                                this.entry.id
                            ]
                        );

                        router.post(url, {}, {
                            onStart: () => this.onStartLoadingOverlay(),
                            onSuccess: (page) => successAlert(page.props.flash?.message ?? ''),
                            onError: () => {
                                oopsAlert({
                                    text: usePage().props.flash?.message ?? ''
                                });
                            },
                            onFinish: () => this.onEndLoadingOverlay(),
                        });
                    }
                });
            },
        },
    };
</script>
