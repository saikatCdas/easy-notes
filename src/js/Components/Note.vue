<template>
    <div>
        <div style="display: flex; gap: 6px;">
            <el-dropdown placement="bottom-start" trigger="click" @command="handleNotesCommand">
                <h3 style="font-size: 48px; color: #0090ffb8; margin: 0;"><i class="el-icon-document"></i></h3>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item command="/">Show all notes</el-dropdown-item>
                    <el-dropdown-item command="/create">Create a new note</el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
            <div>
                <h3 v-if="!editNoteTitle" @click="editNoteTitle = true">{{ note.title }}</h3>
                <el-input v-else v-model="note.title" placeholder=""></el-input>
            </div>
        </div>
        <WpEditor v-model="editorContent" @change="handleEditorChange"></WpEditor>
    </div>
</template>
<script>
import { onMounted } from 'vue';
import WpEditor from './WpEditor.vue';

export default {
    name: 'Note',
    components: {
        WpEditor
    },
    data() {
        return {
            note: {
                title: ''
            },
            editorContent: '',
            timer: null,
            editNoteTitle: false
        }
    },
    methods: {
        handleEditorChange(val) {
            this.editorContent = val;
            this.debounce(this.updatedNote, 600, val);
        },
        createNote() {
            let options = {}
            options['route'] = 'create_note'
            options['title'] = 'Untitled note';
            this.$adminPost(options)
                .then((res) => {
                    this.$router.replace({ path: '/note/', query: { id: res.data.id, name: res.data.title.replace(' ', '-') } });
                })
                .catch((error) => {
                    console.log(error);
                });
        },

        getNote() {
            console.log(this.$route);
            let options = {}
            options['route'] = 'get_note';
            options['note_id'] = this.$route.query.id
            this.$adminGet(options)
                .then((res) => {
                    this.note = res.data
                    this.editorContent = this.note.description
                })
                .catch((error) => { 
                    console.log(error);
                })
        },
        updatedNote(val) {
            let options = {}
            options['route'] = 'update_note'
            options['data'] = val;
            this.$adminPost(options)
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
        handleNotesCommand(command) {
            this.$router.push({ path: command})
        }
    },
    watch: {
        $route(to, from) {
            console.log('to', to);
            console.log('from', from);
            if (to.name == 'note') {
                this.getNote()
            }
        }
    },
    mounted() {
        if (this.$route.name == "create") {
            this.createNote()
        }
        if (this.$route.name == 'note') {
            this.getNote()
        }
    }
}
</script>
