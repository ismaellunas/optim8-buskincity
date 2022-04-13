<template>
    <biz-panel class="is-white">
        <template #heading>
            <div class="columns">
                <div class="column">
                    {{ title }}
                </div>
            </div>
        </template>

        <template #default>
            <biz-panel-block>
                <biz-button-link
                    class="is-primary is-outlined is-fullwidth"
                    :href="route(data.baseRouteName+'.create')"
                >
                    <span class="icon-text">
                        <span class="icon">
                            <i class="fas fa-pen" />
                        </span>
                        <span>
                            Fill Application
                        </span>
                    </span>
                </biz-button-link>
            </biz-panel-block>
        </template>
    </biz-panel>
</template>

<script>
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizPanel from '@/Biz/Panel';
    import BizPanelBlock from '@/Biz/PanelBlock';

    export default {
        name: 'PerformerApplicationLink',

        components: {
            BizButtonLink,
            BizPanel,
            BizPanelBlock,
        },

        props: {
            data: {
                type: Object,
                required: true,
            },

            title: {
                type: String,
                default: "",
            },
        },
    }
</script>