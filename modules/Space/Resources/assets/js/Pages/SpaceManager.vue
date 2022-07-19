<template>
    <div>
        <biz-form-dropdown-search
            label="Choose Manager"
            :close-on-click="true"
            @search="searchManager($event)"
        >
            <template #trigger>
                <span :style="{'min-width': '4rem'}">
                    Search
                </span>
            </template>

            <biz-dropdown-item
                v-for="option in managerOptions"
                :key="option.id"
                @click="addManager(option)"
            >
                {{ option.value }}
            </biz-dropdown-item>
        </biz-form-dropdown-search>

        <div
            v-show="currentManagers.length > 0"
            class="box"
        >
            <span
                v-for="manager in currentManagers"
                :key="manager.id"
                class="tag is-medium mx-1"
            >
                {{ manager.value }}
                <button
                    class="delete is-small"
                    @click="removeManager(manager)"
                />
            </span>
        </div>

        <slot name="action" />
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
        },

        setup(props, { emit }) {
            return {
                currentManagers: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                filteredManagers: [],
                term: null,
            };
        },

        computed: {
            managerIds() {
                return this.currentManagers.map(manager => manager.id);
            },

            managerOptions() {
                return this.filteredManagers
                    .filter((manager) => !this.managerIds.includes(manager.id));
            },
        },

        mounted() {
            this.getManagers(null, this.managerIds);
        },

        methods: {
            getManagers(term, excludedIds) {
                const self = this;
                const url = route('admin.spaces.search-managers');

                axios.get(url, {
                    params: {term: term, excluded: excludedIds},
                }).then((response) => {
                    self.filteredManagers = response.data;
                });
            },

            searchManager: debounce(function(term) {
                this.term = term;
                this.getManagers(term, this.managerIds);
            }, debounceTime),

            addManager(manager) {
                if (! this.managerIds.includes(manager.id)) {
                    this.currentManagers.push(manager);
                }
            },

            removeManager(manager) {
                if (this.managerIds.includes(manager.id)) {
                    remove(this.currentManagers, (currentManager) => {
                        return currentManager.id == manager.id;
                    });

                    if (this.currentManagers.length <= 0) {
                        this.getManagers(this.term, this.managerIds);
                    }
                }
            },
        }
    };
</script>
