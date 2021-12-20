<template>
    <sdb-modal-card @close="$emit('close')">
        <template #header>
            <p class="modal-card-title has-text-weight-bold">
                Find Icon
            </p>
            <button
                class="delete"
                aria-label="close"
                @click.prevent="$emit('close')"
            />
        </template>

        <template #default>
            <div>
                <div class="field has-addons">
                    <p class="control has-icons-left">
                        <sdb-input
                            v-model="term"
                            placeholder="Search..."
                            @keyup.prevent="searchIcon(term)"
                        />
                        <span class="icon is-small is-left">
                            <i class="fas fa-search" />
                        </span>
                    </p>
                    <div class="control">
                        <a
                            class="button"
                            @click="clearTerm"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-times" />
                            </span>
                        </a>
                    </div>
                </div>
                <div v-if="filteredIcon.length > 0">
                    <template
                        v-for="(icon, index) in filteredIcon"
                        :key="index"
                    >
                        <sdb-button-icon
                            :icon="icon.class"
                            class="mr-1 mb-1"
                            type="button"
                            @click="onSelectedIcon(icon.class)"
                        />
                    </template>
                </div>
                <div v-else>
                    <p>Icon not found.</p>
                </div>
            </div>
        </template>
    </sdb-modal-card>
</template>

<script>
    import SdbButtonIcon from '@/Sdb/ButtonIcon';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbInput from '@/Sdb/Input';
    import { debounce, filter, isEmpty } from 'lodash';

    export default {
        name: 'IconBrowser',

        components: {
            SdbButtonIcon,
            SdbModalCard,
            SdbInput,
        },

        props: {
            iconClasses: {
                type: Array,
                default:() => [],
            },
        },

        emits: [
            'close',
            'on-selected-icon',
        ],

        data() {
            return {
                filteredIcon: [],
                term: "",
            };
        },

        mounted() {
            this.filteredIcon = this.iconClasses.slice(0, 65);
        },

        methods: {
            clearTerm() {
                this.term = "";
                this.searchIcon(this.term);
            },

            searchIcon: debounce(function(term) {
                if (!isEmpty(term) && term.length > 2) {
                    this.filteredIcon = filter(this.iconClasses, function (icon) {
                        return new RegExp(term, 'i').test(icon.name);
                    }).slice(0, 65);
                } else {
                    this.filteredIcon = this.iconClasses.slice(0, 65);
                }
            }, 750),

            onSelectedIcon(icon) {
                this.$emit('close');
                this.$emit('on-selected-icon', icon);
            },
        },
    }
</script>