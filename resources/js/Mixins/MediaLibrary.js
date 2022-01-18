import { oops as oopsAlert } from '@/Libs/alert';

export default {
    name: 'MediaLibraryMixin',
    data() {
        return {
            media: [],
            mediaListQueryParams: {
                type: ['image'],
            },
            mediaListRouteName: 'admin.media.lists',
        };
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
            this.getMediaList(route(this.mediaListRouteName));
        },
        setMedia(media) {
            this.media = media;
        },
        onMediaListLoadedFail() {},
        onMediaListLoadedSuccess() {},
    },
}
