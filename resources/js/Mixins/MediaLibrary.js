import { oops as oopsAlert } from '@/Libs/alert';
import { find } from 'lodash';
import { emitter } from '@/Libs/utils';

export default {
    name: 'MediaLibraryMixin',

    props: {
        mediaTypes: { type: Array, default: () => ['image'] },
    },

    data() {
        return {
            media: [],
            mediaListQueryParams: {
                type: this.mediaTypes,
            },
            mediaListRouteName: 'admin.media.lists',
        };
    },

    mounted() {
        const self = this;

        emitter.on('on-save-as-image', () => {
            self.refreshMediaList();
        });
    },

    methods: {
        getMediaList(url) {
            const self = this;
            axios.get(url, {params: this.mediaListQueryParams})
                .then(function (response) {
                    self.setMedia(response.data);
                    self.onMediaListLoadedSuccess(response);
                })
                .catch(function (error) {
                    oopsAlert();
                    self.onMediaListLoadedFail(error);
                });
        },
        refreshMediaList() {
            this.getMediaList(route(this.mediaListRouteName));
        },
        setTerm(term) {
            this.mediaListQueryParams['term'] = term;
        },
        setView(view) {
            this.mediaListQueryParams['view'] = view;
        },
        setType(type) {
            this.mediaListQueryParams['type'] = type;
        },
        search(term) {
            this.setTerm(term);
            this.refreshMediaLists();
        },
        setMedia(media) {
            this.media = media;
        },
        onMediaListLoadedFail() {},
        onMediaListLoadedSuccess() {},
        refreshMediaListByPageActive() {
            let url = find(this.media.links, 'active').url ?? null;

            if (url) {
                this.getMediaList(url);
            } else {
                this.refreshMediaList();
            }
        },
    },
}
