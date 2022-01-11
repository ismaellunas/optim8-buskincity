import { oops as oopsAlert } from '@/Libs/alert';

export default {
    name: 'MediaLibraryMixin',
    data() {
        return {
            media: [],
            imageListQueryParams: {},
            mediaListRouteName: 'admin.media.lists',
        };
    },
    methods: {
        getImagesList(url) {
            const self = this;
            axios.get(url, {params: this.imageListQueryParams})
                .then(function (response) {
                    self.setMedia(response.data);
                    self.onImageListLoadedSuccess(response);
                })
                .catch(function (error) {
                    oopsAlert();
                    self.onImageListLoadedFail(error);
                });
        },
        setTerm(term) {
            this.imageListQueryParams['term'] = term;
        },
        setView(view) {
            this.imageListQueryParams['view'] = view;
        },
        search(term) {
            this.setTerm(term);
            this.getImagesList(route(this.imageListRouteName));
        },
        setMedia(media) {
            this.media = media;
        },
        onImageListLoadedFail() {},
        onImageListLoadedSuccess() {},
    },
}
