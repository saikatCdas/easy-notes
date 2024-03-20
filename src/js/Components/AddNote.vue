<template>
    <div>
        <WpEditor v-model="editorContent" @change="handleEditorChange"></WpEditor>
    </div>
</template>
<script>
import WpEditor from './WpEditor.vue';

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
            this.editorContent = val;
            this.debounce(this.updatedNote, 600, val);
        },

        updatedNote(val) {
            let option = {}
            option['action'] = 'easy-notes_admin_ajax';
            option['route'] = 'update_note'
            option['data'] = val;
            this.$adminPost(option)
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
