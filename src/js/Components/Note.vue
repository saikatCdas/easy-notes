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
            let options = {
                route: 'create_note',
                title: 'Untitled note'
            };

            this.$adminPost(options)
                .then((res) => {
                    // Ensure the response contains the expected data
                    if (res.data && res.data.id && res.data.title) {
                        const noteId = res.data.id;
                        const noteTitle = res.data.title.replace(' ', '-');
                        // Navigate to the newly created note
                        this.$router.replace({ path: `/note/${noteId}`, query: { name: noteTitle } });
                    } else {
                        console.error('Invalid response data:', res.data);
                    }
                })
                .catch((error) => {
                    console.error('Error creating note:', error);
                });
        },

        getNote() {
            console.log(this.$route);
            let options = {}
            options['route'] = 'get_note';
            options['note_id'] = this.$route.params.id
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
            let options = {
                'route': 'update_note',
                'data': {
                    title: this.note.title,
                    'description': val
                }
            }
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
            if (to.name == 'create') {
                this.createNote()
            }
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
