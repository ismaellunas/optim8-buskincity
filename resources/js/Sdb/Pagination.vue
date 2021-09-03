<template>
    <div v-if="links.length > 3">
        <nav class="pagination is-centered" role="navigation" aria-label="pagination">
            <template v-if="isAjax">
                <sdb-button
                    class="pagination-previous"
                    :disabled="isBlank(previousLink.url) ? 1 : null"
                    v-html="previousLink.label"
                    @click.prevent="$emit('on-clicked-pagination', previousLink.url)"
                />
                <sdb-button
                    class="pagination-next"
                    :disabled="isBlank(nextLink.url) ? 1 : null"
                    v-html="nextLink.label"
                    @click.prevent="$emit('on-clicked-pagination', nextLink.url)"
                />
            </template>
            <template v-else>
                <a
                    class="pagination-previous"
                    :href="previousLinkUrl"
                    :disabled="isBlank(previousLinkUrl) ? 1 : null"
                    v-html="previousLink.label"
                    />
                <a
                    class="pagination-next"
                    :disabled="isBlank(nextLinkUrl) ? 1 : null"
                    :href="nextLinkUrl"
                    v-html="nextLink.label"
                    />
            </template>
            <ul class="pagination-list">
                <li v-for="link in paginationLinks">
                    <span
                        v-if="isBlank(link.url)"
                        class="pagination-ellipsis"
                    >
                        &hellip;
                    </span>
                    <template v-else>
                        <sdb-button v-if="isAjax"
                            class="pagination-link"
                            :class="{'is-current': link.active}"
                            v-html="link.label"
                            @click.prevent="$emit('on-clicked-pagination', link.url)"
                            type="button"
                            />
                        <sdb-link v-else
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
    import SdbButton from '@/Sdb/Button';
    import SdbLink from '@/Sdb/Link';
    import { isBlank, serialize } from '@/Libs/utils';

    export default {
        components: {
            SdbButton,
            SdbLink,
        },
        emits: ['on-clicked-pagination'],
        props: {
            links: Array,
            isAjax: { type: Boolean, default: false, },
            queryParams: { type: Object, default: {} },
        },
        methods: {
            isBlank: isBlank,
        },
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
            serializedParams() {
                if (!isBlank(this.queryParams)) {
                    return '&'+serialize( this.queryParams );
                }
                return '';
            },
        }
    }
</script>
