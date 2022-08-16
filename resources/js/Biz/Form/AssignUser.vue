<template>
    <div>
        <biz-form-dropdown-search
            :close-on-click="true"
            :label="label"
            @search="searchUser($event)"
        >
            <template #trigger>
                <span :style="{'min-width': '4rem'}">
                    Search
                </span>
            </template>

            <biz-dropdown-item
                v-for="option in userOptions"
                :key="option.id"
                @click="addUser(option)"
            >
                {{ option.value }}
            </biz-dropdown-item>
        </biz-form-dropdown-search>

        <div
            v-show="currentUsers.length > 0"
            class="box"
        >
            <span
                v-for="user in currentUsers"
                :key="user.id"
                class="tag is-medium mx-1"
            >
                {{ user.value }}
                <button
                    class="delete is-small"
                    @click="removeUser(user)"
                />
            </span>
        </div>
    </div>
</template>

<script>
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import { debounceTime } from '@/Libs/defaults';
    import { debounce, remove } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            BizDropdownItem,
            BizFormDropdownSearch,
        },

        props: {
            modelValue: { type: Array, default: () => [] },
            label: { type: String, default: "Choose User" },
            getUsersUrl: { type: String, required: true },
        },

        setup(props, { emit }) {
            return {
                currentUsers: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                filteredUsers: [],
                term: null,
            };
        },

        computed: {
            userIds() {
                return this.currentUsers.map(user => user.id);
            },

            userOptions() {
                return this.filteredUsers
                    .filter((user) => !this.userIds.includes(user.id));
            },
        },

        mounted() {
            this.getUsers(null, this.userIds);
        },

        methods: {
            getUsers(term, excludedIds) {
                const self = this;
                const url = this.getUsersUrl;

                axios.get(url, {
                    params: {term: term, excluded: excludedIds},
                }).then((response) => {
                    self.filteredUsers = response.data;
                });
            },

            searchUser: debounce(function(term) {
                this.term = term;
                this.getUsers(term, this.userIds);
            }, debounceTime),

            addUser(user) {
                if (! this.userIds.includes(user.id)) {
                    this.currentUsers.push(user);
                }
            },

            removeUser(user) {
                if (this.userIds.includes(user.id)) {
                    remove(this.currentUsers, (currentUser) => {
                        return currentUser.id == user.id;
                    });

                    if (this.currentUsers.length <= 0) {
                        this.getUsers(this.term, this.userIds);
                    }
                }
            },
        }
    };
</script>
