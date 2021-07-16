<template>
    <div v-if="links.length > 3">
        <nav class="pagination is-centered" role="navigation" aria-label="pagination">
            <a
                class="pagination-previous"
                :href="previousLink.url"
                :disabled="isBlank(previousLink.url) ? 1 : null"
                v-html="previousLink.label"
                />
            <a
                class="pagination-next"
                :disabled="isBlank(nextLink.url) ? 1 : null"
                :href="nextLink.url"
                v-html="nextLink.label"
                />
            <ul class="pagination-list">
                <li v-for="link in paginationLinks">
                    <span class="pagination-ellipsis" v-if="isBlank(link.url)">&hellip;</span>
                    <sdb-link v-else
                        :href="link.url"
                        class="pagination-link"
                        :class="{'is-current': link.active}"
                        v-html="link.label"
                        />
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    import SdbLink from '@/Sdb/Link';
    import { isBlank } from '@/Libs/utils';

    export default {
        components: {
            SdbLink,
        },
        props: {
            links: Array,
        },
        methods: {
            isBlank: isBlank,
        },
        computed: {
            previousLink() {
                return this.links[0];
            },
            nextLink() {
                return this.links[ this.links.length - 1 ];
            },
            paginationLinks() {
                const paginationLinks = this.links.slice();

                paginationLinks.pop();
                paginationLinks.shift();

                return paginationLinks;
            },
        }
    }
</script>
