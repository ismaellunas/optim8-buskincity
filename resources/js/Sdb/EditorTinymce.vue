<template>
    <editor
        v-model="content"
        :api-key="apiKey"
        :init="editorConfig"
    >
    </editor>
</template>

<script>
  import Editor from '@tinymce/tinymce-vue';
  import { textComponent as editorConfig } from '@/Libs/tinymce-configs';

  export default {
    components: {
      editor: Editor
    },
    data() {
        return {
            apiKey: "gumlypbnhr1ushb1hnnynxp4n6osqza60b3hn7hdtfa6khjb",
            editorConfig: editorConfig,
        };
    }
  }
</script>
