<template>
    <div class="field is-horizontal">
        <div class="field-label" :class="fieldLabelClasses">
            <label class="label">
                <slot name="label" />
            </label>
        </div>
        <div class="field-body">
            <div class="field">
                <slot />
            </div>
        </div>
    </div>
</template>

<script>
    import { isEmpty, isString, trim } from 'lodash';

    export default {
        props: {
            fieldLabelClass: {}
        },
        computed: {
            fieldLabelClasses() {
                if (!isEmpty(this.fieldLabelClass)) {
                    if (isString(this.fieldLabelClass)) {
                        return trim(this.fieldLabelClass).split(' ');
                    } else if (isArrray(this.fieldLabelClass)) {
                        return this.fieldLabelClass;
                    }
                }
                return ['is-normal'];
            }
        }
    }
</script>
