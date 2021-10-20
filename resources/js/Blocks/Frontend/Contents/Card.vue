<template>
    <div :class="wrapperClass">
        <div class="card">
            <div class="card-image" :class="cardImageClass">
                <sdb-image
                    v-if="hasImage"
                    :src="imageSrc"
                    :alt="altText"
                    :ratio="this.config?.image?.ratio"
                    :rounded="this.config?.image?.rounded"
                    :square="this.config?.image?.fixedSquare"
                />
            </div>
            <div
                v-if="isCardContentDisplayed"
                class="card-content"
            >
                <div
                    class="content"
                    :class="cardContentClass"
                >
                    <div v-html="entity.content.cardContent.content.html"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasImageContent from '@/Mixins/HasImageContent';
    import SdbButton from '@/Sdb/Button';
    import SdbImage from '@/Sdb/Image';
    import { concat, isEmpty } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Card',
        components: {
            SdbButton,
            SdbImage,
        },
        mixins: [
            MixinHasImageContent,
        ],
        props: {
            id: String,
            entity: {type: Object, default: {}},
            selectedLocale: String,
        },
        setup(props) {
            return {
                config: props.entity?.config,
            };
        },
        data() {
            return {
                entityImage: this.entity.content.cardImage.figure.image,
                images: usePage().props.value.images ?? {},
            };
        },
        computed: {
            cardImageClass() {
                let classes = [];
                const suffix = {top: 't', right: 'r', bottom: 'b', left: 'l'};
                if (this.config?.image?.padding) {
                    for (const [key, value] of Object.entries(this.config.image.padding)) {
                        classes.push( 'p'+suffix[key]+'-'+value );
                    }
                }
                return classes;
            },
            cardContentClass() {
                return concat(
                    this.config.content?.size,
                    this.config.content?.alignment
                ).filter(Boolean);
            },
            wrapperClass() {
                return concat(
                    createPaddingClasses(this.config.wrapper?.padding),
                    createMarginClasses(this.config.wrapper?.margin)
                ).filter(Boolean);
            },
            isCardContentDisplayed() {
                return !isEmpty(this.entity.content.cardContent.content.html);
            },
        }
    }
</script>
