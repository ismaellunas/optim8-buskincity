<template>
    <div>
        <div class="card" v-for="(group, groupName) in configOptions">
            <header class="card-header">
                <p class="card-header-title">
                    {{ group.label }}
                </p>
                <!--
                <button class="card-header-icon" aria-label="more options">
                    <span class="icon">
                        <i class="fas fa-angle-down" aria-hidden="true"></i>
                    </span>
                </button>
                -->
            </header>
            <div class="card-content">
                <div class="content">
                    <template v-for="(config, key) in group.config">
                        <sdb-form-select
                            v-if="config.type === 'select'"
                            v-model="entity.config[ groupName ][ key ]"
                            :label="config.label"
                        >
                            <option
                                v-for="option in config.options"
                                :value="option.value"
                            >
                                {{ option.name }}
                            </option>
                        </sdb-form-select>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import SdbFormSelect from '@/Sdb/Form/Select';
    import configs from '@/ComponentStructures/configs';
    import { camelCase } from "lodash";
    import { useModelWrapper } from '@/Libs/utils'

    export default {
        components: {
            SdbFormSelect,
        },
        props: ['modelValue'],
        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
            };
        },
        computed: {
            configOptions() {
                return configs[ camelCase(this.entity.componentName) ];
            }
        },
    }
</script>
