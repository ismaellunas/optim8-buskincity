<template> <!-- ROLES -->
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Roles
            </h2>
        </template>
        <div class="py-12">
            <div class="">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
                    <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3" role="alert" v-if="$page.props.flash.message">
                      <div class="flex">
                        <div>
                          <p class="text-sm">{{ $page.props.flash.message }}</p>
                        </div>
                      </div>
                    </div>

                    <sdb-button @click="openModal()" type="button">Create New Role</sdb-button>

                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr class="">
                                <th class="px-4 py-2 w-20">Name</th>
                                <th class="px-4 py-2 w-20">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in roles">
                                <td class="border px-4 py-2">{{ row.name }}</td>
                                <td class="border px-4 py-2">
                                    <sdb-button @click="edit(row)">Edit</sdb-button>
                                    <sdb-button-link :href="`/roles/${row.id}/edit`"> Edit</sdb-button-link>
                                    <sdb-button @click="deleteRow(row)" class="is-danger">Delete</sdb-button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <sdb-modal v-show="isOpen" @close="closeModal()">
                        <form action="">
                            <div class="field">
                                <label class="label">Name</label>
                                <div class="control">
                                    <input class="input" type="text" placeholder="Text input" v-model="form.name">
                                </div>

                                <div class="columns my-3">
                                    <div class="column" v-for="permission in permissions">
                                        <label class="checkbox">
                                            <input type="checkbox" v-bind:value="permission.id" v-model="form.permissions">
                                            <span class="ml-2">
                                                {{ permission.name }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="field is-grouped">
                                <div class="control">
                                    <button wire:click.prevent="store()" v-show="!editMode" @click="save(form)" type="button" class="button is-link">Save</button>
                                </div>
                                <div class="control">
                                    <button wire:click.prevent="store()" v-show="editMode" @click="update(form)" type="button" class="button is-link">Update</button>
                                </div>
                                <div class="control">
                                    <button @click="closeModal()" type="button" class="button is-link is-light">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </sdb-modal>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from './../Layouts/AppLayout'
    import SdbButton from '@/Sdb/Button';
    import SdbButtonLink from '@/Sdb/ButtonLink';
    import SdbModal from '@/Sdb/Modal'

    export default {
        components: {
            AppLayout,
            SdbButton,
            SdbButtonLink,
            SdbModal,
        },
        props: ['roles', 'permissions', 'errors'],
        data() {
            return {
                editMode: false,
                isOpen: false,
                form: {
                    name: null,
                    permissions: [],
                },
            }
        },
        methods: {
            openModal: function () {
                this.isOpen = true;
            },
            closeModal: function () {
                this.isOpen = false;
                this.reset();
                this.editMode=false;
            },
            reset: function () {
                this.form = {
                    name: null,
                }
            },
            save: function (data) {
                this.$inertia.post('/roles', data)
                this.reset();
                this.closeModal();
                this.editMode = false;
            },
            edit: function (data) {
                this.form = Object.assign({}, data);
                this.form.permissions = data.permissions.map(obj => obj.id);
                this.editMode = true;
                this.openModal();
            },
            update: function (data) {
                data._method = 'PUT';
                this.$inertia.post('/roles/' + data.id, data)
                this.reset();
                this.closeModal();
            },
            deleteRow: function (data) {
                if (!confirm('Are you sure want to remove?')) return;
                data._method = 'DELETE';
                this.$inertia.post('/roles/' + data.id, data)
                this.reset();
                this.closeModal();
            }
        }
    }
</script>
