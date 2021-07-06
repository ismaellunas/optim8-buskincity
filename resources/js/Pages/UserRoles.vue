<template>
    <app-layout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ user.name }}
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

                    <form action="">
                        <div class="field">
                            <label class="label">Name</label>
                            <div class="control">
                            </div>

                            <div class="columns my-3">
                                <div class="column" v-for="role in roles">
                                    <label class="checkbox">
                                        <input type="checkbox" v-bind:value="role.id" v-model="assignedRoles">
                                        <span class="ml-2">
                                            {{ role.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="field is-grouped">
                            <div class="control">
                                <button wire:click.prevent="store()" @click="update()" type="button" class="button is-link">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="box has-background-primary" v-if="can.blog_create">
            Blog.create
        </div>
        <div class="box has-background-info" v-if="can.blog_update">
            Blog.update
        </div>
        <div class="box has-background-success" v-if="can.blog_delete">
            Blog.delete
        </div>
        <div class="box has-background-warning" v-if="can.profile_view">
            Profile.view
        </div>
        <div class="box has-background-danger" v-else>
            NONE
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from './../Layouts/AppLayout'

    export default {
        components: {
            AppLayout,
        },
        props: ['roles', 'user', 'errors', 'assignedRoles', 'can'],
        data() {
            return {
                editMode: false,
                isOpen: false,
                form: {
                    roles: [],//this.user.roles.map(obj => obj.id),
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
                    roles: [],
                }
            },
            /*
            save: function (data) {
                this.$inertia.post('/roles', data)
                this.reset();
                this.closeModal();
                this.editMode = false;
            },
            */
            edit: function (data) {
                this.form = Object.assign({}, data);
                this.form.roles = data.roles.map(obj => obj.id);
                this.editMode = true;
                //this.openModal();
            },
            update: function () {
                let data = {'roles': this.assignedRoles};
                data._method = 'PUT';
                this.$inertia.post('/user-roles/' + this.user.id, data)
                //this.reset();
                //this.closeModal();
            },
            deleteRow: function (data) {
                if (!confirm('Are you sure want to remove?')) return;
                data._method = 'DELETE';
                this.$inertia.post('/roles/' + data.id, data)
                //this.reset();
                //this.closeModal();
            }
        }
    }
</script>
