<template>
    <biz-modal-card @close="$emit('close')">
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
            <div class="container">
                <div class="columns">
                    <div class="column">
                        <div class="field has-addons">
                            <div
                                v-if="hasType"
                                class="control"
                            >
                                <biz-select
                                    v-model="selectedType"
                                    @change="searchIcon(term)"
                                >
                                    <option
                                        v-for="(type, index) in typeOptions"
                                        :key="index"
                                        :value="type.value"
                                    >
                                        {{ type.name }}
                                    </option>
                                </biz-select>
                            </div>
                            <p class="control has-icons-left">
                                <biz-input
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
                    </div>

                    <div class="column">
                        <biz-button
                            v-if="!showAll"
                            type="button"
                            @click="showIconBlock()"
                        >
                            Show All Icons
                        </biz-button>

                        <biz-button
                            v-else
                            type="button"
                            @click="showIconBlock()"
                        >
                            Show Minimal Icon
                        </biz-button>
                    </div>
                </div>
                <div
                    v-if="canRemove"
                    class="columns"
                >
                    <div class="column">
                        <biz-button
                            type="button"
                            @click="removeIcon()"
                        >
                            Remove Icon
                        </biz-button>
                    </div>
                </div>
                <div class="columns">
                    <div
                        v-if="filteredIcon.length > 0"
                        class="column"
                    >
                        <template
                            v-for="(icon, index) in filteredIcon"
                            :key="index"
                        >
                            <biz-button-icon
                                :icon="iconFormatter(icon.class)"
                                class="mr-1 mb-1"
                                type="button"
                                @click="onSelectedIcon(iconFormatter(icon.class))"
                            />
                        </template>
                    </div>
                    <div
                        v-else
                        class="column"
                    >
                        <p>Icon not found.</p>
                    </div>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizInput from '@/Biz/Input';
    import BizModalCard from '@/Biz/ModalCard';
    import BizSelect from '@/Biz/Select';
    import { debounceTime } from '@/Libs/defaults';
    import { debounce, filter, isEmpty } from 'lodash';

    export default {
        name: 'IconBrowser',

        components: {
            BizButton,
            BizButtonIcon,
            BizModalCard,
            BizInput,
            BizSelect,
        },

        props: {
            canRemove: {
                type: Boolean,
                default: false,
            },
            hasType: {
                type: Boolean,
                default: false,
            },
            iconClasses: {
                type: Array,
                default:() => [],
            },
        },

        emits: [
            'close',
            'on-selected-icon',
            'remove-icon',
        ],

        data() {
            return {
                filteredIcon: [],
                selectedType: null,
                showAll: false,
                iconDelay: 100,
                term: "",
                typeOptions: [
                    { value: 'fa-solid', name: 'Solid' },
                    { value: 'fa-regular', name: 'Regular' },
                    { value: 'fa-light', name: 'Light' },
                    { value: 'fa-thin', name: 'Thin' },
                    { value: 'fa-duotone', name: 'Duotone' },
                ],
            };
        },

        mounted() {
            this.selectedType = this.typeOptions[0].value;
            this.filteredIcon = this.iconSlice(this.iconClasses);
        },

        methods: {
            clearTerm() {
                this.term = "";
                this.searchIcon(this.term);
            },

            searchIcon: debounce(function(term) {
                if (!isEmpty(term) && term.length > 2) {
                    this.filteredIcon = this.iconSlice(
                        filter(this.iconClasses, function (icon) {
                            return new RegExp(term, 'i').test(icon.name);
                        })
                    );
                } else {
                    this.filteredIcon = this.iconSlice(
                        this.iconClasses
                    );
                }
            }, debounceTime),

            onSelectedIcon(icon) {
                setTimeout(() => {
                    this.$emit('close');
                    this.$emit('on-selected-icon', icon);
                }, this.iconDelay);
            },

            removeIcon() {
                this.$emit('close');
                this.$emit('remove-icon');
            },

            showIconBlock() {
                setTimeout(() => {
                    this.showAll = !this.showAll;

                    if (this.term) {
                        this.searchIcon(this.term);
                    } else {
                        this.filteredIcon = this.iconSlice(
                            this.iconClasses
                        );
                    }
                }, this.iconDelay);
            },

            iconSlice(icons) {
                if (this.showAll) {

                    return icons.slice(0);

                } else {

                    return icons.slice(0, 52);

                }
            },

            iconFormatter(iconClass) {
                return this.hasType ? this.selectedType + ' ' + iconClass : iconClass
            },
        },
    }
</script>