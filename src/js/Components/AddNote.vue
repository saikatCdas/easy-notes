<template>
    <div>
        <WpEditor v-model="editorContent" @change="handleEditorChange"></WpEditor>
    </div>
</template>
<script>
import WpEditor from './WpEditor.vue';
import easyNotes from '../plugin_main_js_file'

export default {
    name: 'AddNote',
    components: {
        WpEditor
    },
    data() {
        return {
            editorContent: '',
            timer: null
        }
    },
    methods: {
        handleEditorChange(val) {
            console.log(val);
            this.editorContent = val;
            this.debounce(this.updatedNote, 600, val);
        },

        updatedNote(val) {
            return
            let option = {}
            option['action'] = 'update-note';
            option['data'] = val;
            this.$post(option)
                .then((res) => {
                    console.log(res);
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        debounce(func, delay, ...args) {
            if (this.timer) {
                clearTimeout(this.timer);
            }
            this.timer = setTimeout(() => {
                func(...args);
            }, delay);
        },


    }
}
</script>
