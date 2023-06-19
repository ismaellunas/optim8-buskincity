<template>
    <div v-if="isPaginationDisplayed">
        <nav
            class="pagination is-centered"
            role="navigation"
            aria-label="pagination"
            :class="[sizeClass]"
        >
            <template v-if="isAjax">
                <biz-button
                    class="pagination-previous"
                    :disabled="isBlank(previousLink.url) ? 1 : null"
                    @click.prevent="$emit('on-clicked-pagination', previousLink.url)"
                    v-html="previousLink.label"
                />
                <biz-button
                    class="pagination-next"
                    :disabled="isBlank(nextLink.url) ? 1 : null"
                    @click.prevent="$emit('on-clicked-pagination', nextLink.url)"
                    v-html="nextLink.label"
                />
            </template>

            <template v-else>
                <biz-link
                    class="pagination-previous"
                    :href="previousLinkUrl"
                    :disabled="isBlank(previousLinkUrl) ? 1 : null"
                    v-html="previousLink.label"
                />
                <biz-link
                    class="pagination-next"
                    :disabled="isBlank(nextLinkUrl) ? 1 : null"
                    :href="nextLinkUrl"
                    v-html="nextLink.label"
                />
            </template>

            <ul class="pagination-list">
                <li
                    v-for="(link, index) in pageLinks"
                    :key="index"
                >
                    <span
                        v-if="isBlank(link.url)"
                        class="pagination-ellipsis"
                    >
                        &hellip;
                    </span>
                    <template v-else>
                        <biz-button
                            v-if="isAjax"
                            class="pagination-link"
                            :class="{'is-current': link.active}"
                            type="button"
                            @click.prevent="$emit('on-clicked-pagination', link.url)"
                            v-html="link.label"
                        />
                        <biz-link
                            v-else
                            :href="link.url+serializedParams"
                            class="pagination-link"
                            :class="{'is-current': link.active}"
                            v-html="link.label"
                        />
                    </template>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    import BizButton from '@/Biz/Button.vue';
    import BizLink from '@/Biz/Link.vue';
    import { isBlank, serialize } from '@/Libs/utils';
    import { first, last, isNull, omit } from 'lodash';

    export default {
        name: 'BizPagination',

        components: {
            BizButton,
            BizLink,
        },

        props: {
            links: { type: Array, default: () => [] },
            isAjax: { type: Boolean, default: false },
            queryParams: { type: Object, default:() => {} },
            currentPage: { type: [Number, null], default: null },
            lastPage: { type: [Number, null], default: null },
            pagePropertyName: { type: String, default: 'page' },
            size: {
                type: String,
                default: 'normal',
                validator(value) {
                    return ['small', 'normal', 'medium', 'large'].includes(value)
                }
            },
        },

        emits: ['on-clicked-pagination'],

        computed: {
            previousLink() {
                return this.links[0];
            },

            previousLinkUrl() {
                if (!isBlank(this.previousLink.url)) {
                    return this.previousLink.url + this.serializedParams;
                }
                return '';
            },

            nextLink() {
                return this.links[ this.links.length - 1 ];
            },

            nextLinkUrl() {
                if (!isBlank(this.nextLink.url)) {
                    return this.nextLink.url + this.serializedParams;
                }
                return '';
            },

            paginationLinks() {
                const paginationLinks = this.links.slice();

                paginationLinks.pop();
                paginationLinks.shift();

                return paginationLinks;
            },

            sanitizedQueryParams() {
                return omit(this.queryParams, [this.pagePropertyName]);
            },

            serializedParams() {
                if (!isBlank(this.sanitizedQueryParams)) {
                    return '&'+serialize( this.sanitizedQueryParams );
                }
                return '';
            },

            isPaginationDisplayed() {
                if (
                    (!isNull(this.currentPage) && !isNull(this.lastPage))
                    && this.currentPage > this.lastPage
                ) {
                    return true;
                }

                return this.links.length > 3
            },

            sizeClass() {
                return 'is-' + this.size;
            },

            rangedLinks() {
                const paginationLinks = this.paginationLinks.filter((link) => {
                    const excludedNumbers = [];

                    if (this.currentPage > 3) {
                        excludedNumbers.push(1);
                    }

                    if (this.currentPage < this.lastPage - 2) {
                        excludedNumbers.push(this.lastPage);
                    }

                    return !(excludedNumbers.includes(parseInt(link.label)));
                });

                const ellipsisIndexes = this
                    .paginationLinks
                    .map((link, index) => isNull(link.url) ? index : null)
                    .filter(Number);

                if (ellipsisIndexes.length == 2) {
                    return paginationLinks.slice(
                        first(ellipsisIndexes) + 1,
                        last(ellipsisIndexes)
                    );
                }

                if (ellipsisIndexes.length == 1) {
                    const firstEllipsisIndex = first(ellipsisIndexes);

                    if (firstEllipsisIndex == 2) {
                        return paginationLinks.slice(3);
                    }

                    return paginationLinks.slice(0, firstEllipsisIndex);
                }

                return paginationLinks;
            },

            pageLinks() {
                const links = [];

                const ellipsis = { url: null, label: null };

                this.rangedLinks.forEach((link) => {
                    const pageNumber = parseInt(link.label);

                    let additionalRight = 0;
                    let additionalLeft = 0;

                    if (this.currentPage <= 3) {
                        additionalRight = 3 - this.currentPage;
                    }

                    if (
                        (this.currentPage >= this.lastPage - 3)
                        && (this.currentPage + 3 - this.lastPage) > 1
                    ) {
                        additionalLeft = this.currentPage - this.lastPage + 2;
                    }

                    if (
                        pageNumber >= (this.currentPage - 2 - additionalLeft)
                        && pageNumber <= (this.currentPage + 2 + additionalRight)
                    ) {
                        links.push(link);
                    }
                });

                const firstRangedLink = first(links);
                const lastRangedLink = last(links);

                if (
                    this.currentPage > 4
                    && ! (firstRangedLink && firstRangedLink?.label == '2')
                ) {
                    links.unshift(ellipsis);
                }

                if (this.currentPage > 3) {
                    links.unshift(first(this.paginationLinks));
                }

                if (
                    this.currentPage < this.lastPage - 3
                    && !(lastRangedLink && lastRangedLink?.label == (this.lastPage - 1))
                ) {
                    links.push(ellipsis);
                }

                if (this.currentPage < this.lastPage - 2) {
                    links.push(last(this.paginationLinks));
                }

                return links;
            },
        },

        methods: {
            isBlank: isBlank,
        },
    }
</script>
