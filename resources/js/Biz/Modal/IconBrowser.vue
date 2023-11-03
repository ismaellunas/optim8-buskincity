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
                                    @keydown.enter.prevent
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
                    <fieldset :disabled="isProcessing">
                        <div
                            v-if="filteredIcon.length > 0"
                            class="column"
                        >
                            <biz-button-icon
                                v-for="icon in displayedIcons"
                                :key="selectedType+'_'+icon.class"
                                :icon="iconFormatter(icon.class)"
                                class="mr-1 mb-1"
                                type="button"
                                @click.prevent="onSelectedIcon(iconFormatter(icon.class))"
                            />
                        </div>
                        <div
                            v-else
                            class="column"
                        >
                            <p>Icon not found.</p>
                        </div>

                        <div
                            v-if="showAll"
                            class="column is-full mt-2"
                        >
                            <biz-button
                                v-show="canShowMoreIcons"
                                :disabled="isProcessing"
                                class="is-fullwidth"
                                type="button"
                                @click.stop="showMoreIcons"
                            >
                                Show more
                            </biz-button>
                        </div>
                    </fieldset>
                </div>
            </div>
        </template>
    </biz-modal-card>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizInput from '@/Biz/Input.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import BizSelect from '@/Biz/Select.vue';
    import { debounce, filter, find, isEmpty, upperFirst } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { ref } from 'vue';

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

        setup(props) {
            return {
                filteredIcon: ref([]),
                maxShown: ref(52),
                fontStyles: [
                    'solid',
                    'regular',
                    'light',
                    'thin',
                    'duotone',
                ],
                selectedType: ref(null),
                showAll: ref(false),
                iconDelay: 100,
                term: ref(""),
                isProcessing: ref(false),
            };
        },

        computed: {
            typeOptions() {
                const availableStyles = filter(this.fontStyles, (fontStyle) => {
                    if (process.env.fontawesomeFree) {
                        return fontStyle == 'solid';
                    }
                    return fontStyle;
                });

                return availableStyles.map((fontStyle) => ({
                    value: 'fa-' + fontStyle,
                    name: upperFirst(fontStyle),
                }));
            },

            availableIconClasses() {
                return process.env.fontawesomeFree
                    ? filter(
                        this.iconClasses,
                        (icon) => icon?.free && icon.free.includes('solid')
                    )
                    : this.iconClasses
            },

            displayedIcons() {
                return this.filteredIcon.slice(0, this.maxShown);
            },

            canShowMoreIcons() {
                return (
                    this.showAll
                    && (this.maxShown <= this.availableIconClasses.length)
                );
            },
        },

        mounted() {
            this.selectedType = this.typeOptions[0].value;
            this.filteredIcon = this.iconSlice(this.availableIconClasses);
        },

        methods: {
            clearTerm() {
                this.term = "";
                this.searchIcon(this.term);
            },

            searchIcon: debounce(function(term) {
                if (!isEmpty(term) && term.length > 2) {
                    this.filteredIcon = this.iconSlice(
                        filter(this.availableIconClasses, function (icon) {
                            return new RegExp(term, 'i').test(icon.name);
                        })
                    );
                } else {
                    this.filteredIcon = this.iconSlice(
                        this.availableIconClasses
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
                            this.availableIconClasses
                        );
                    }

                    if (this.showAll) {
                        this.maxShown = this.maxShown + (52 * 4);
                    } else {
                        this.maxShown = 52;
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

            showMoreIcons() {
                this.isProcessing = true;

                setTimeout(() => {
                    this.maxShown = this.maxShown + (52 * 4);
                    setTimeout(() => { this.isProcessing = false}, 1500);
                }, this.iconDelay);
            },
        },
    };
</script>
