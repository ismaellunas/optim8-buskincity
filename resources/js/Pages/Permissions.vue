<template> <!-- PERMISSIONS -->
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Permisions
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

                    <button @click="openModal()" type="button" class="button">Create New Permission</button>

                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr class="">
                                <th class="px-4 py-2 w-20">Name</th>
                                <th class="px-4 py-2 w-20">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in data">
                                <td class="border px-4 py-2">{{ row.name }}</td>
                                <td class="border px-4 py-2">
                                    <button @click="edit(row)" class="button">Edit</button>
                                    <button @click="deleteRow(row)" class="button">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <biz-modal v-show="isOpen" @close="closeModal()">
                        <div class="box">
                            <form action="">
                                <div class="field">
                                    <label class="label">Name</label>
                                    <div class="control">
                                        <input class="input" type="text" placeholder="Text input" v-model="form.name">
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
                        </div>
                    </biz-modal>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from './../Layouts/AppLayout'
    import BizModal from '@/Biz/Modal'

    export default {
        components: {
            AppLayout,
            BizModal,
        },
        props: ['data', 'errors'],
        data() {
            return {
                editMode: false,
                isOpen: false,
                form: {
                    name: null,
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
                this.$inertia.post('/permissions', data)
                this.reset();
                this.closeModal();
                this.editMode = false;
            },
            edit: function (data) {
                this.form = Object.assign({}, data);
                this.editMode = true;
                this.openModal();
            },
            update: function (data) {
                data._method = 'PUT';
                this.$inertia.post('/permissions/' + data.id, data)
                this.reset();
                this.closeModal();
            },
            deleteRow: function (data) {
                if (!confirm('Are you sure want to remove?')) return;
                data._method = 'DELETE';
                this.$inertia.post('/permissions/' + data.id, data)
                this.reset();
                this.closeModal();
            }
        }
    }
</script>
