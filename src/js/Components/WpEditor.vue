<template>
    <div v-loading="editorLoading" class="easy-notes-editor">
        <textarea v-if="hasWpEditor" :id="editor_id" :value="modelValue" class="wp_vue_editor"
            :style="editorLoading ? 'visibility: hidden;' : 'visibility: visible;'"></textarea>
        <textarea v-else-if="!hasWpEditor && !editorLoading" v-model="plain_content"
            class="wp_vue_editor wp_vue_editor_plain" @click="updateCursorPos"></textarea>
        <div id="easy-notes-table-actions">
            <el-button text><i class="el-icon-plus"></i>Insert row above</el-button>
            <el-button text><i class="el-icon-plus"></i>Insert row below</el-button>
            <el-button text><i class="el-icon-plus"></i>Insert column above</el-button>
            <el-button text><i class="el-icon-plus"></i>Insert column below</el-button>
            <hr>
            <el-button type="danger" text><i class="el-icon-delete"></i>Delete row</el-button>
            <el-button type="danger" text><i class="el-icon-delete"></i>Delete column</el-button>
            <el-button type="danger" text><i class="el-icon-delete"></i>Delete table</el-button>
        </div>
    </div>
</template>
<script>
import insertTable from '../custom_tinymcey'
export default {
    name: 'WpEditor',
    emits: ["update:modelValue", "change"],
    props: {
        editor_id: {
            type: String,
            default: () => "wp_editor_" + Date.now() + parseInt(Math.random() * 1000),
        },
        modelValue: {
            type: String,
            default: "",
        },
        height: {
            type: Number,
            default: 250,
        },
        extra_style: {
            default: "",
        },
    },
    data() {
        return {
            hasWpEditor: (!!window.wp.editor && !!wp.editor.autop) || !!window.wp.oldEditor,
            editor: window.wp.oldEditor || window.wp.editor,
            plain_content: this.modelValue,
            cursorPos: this.modelValue ? this.modelValue.length : 0,
            editorLoading: true,
            isTableOptionsOpen: false
        };
    },
    methods: {
        initEditor() {
            if (!this.hasWpEditor) {
                return;
            }

            const formFormats = [];
            this.editor.remove(this.editor_id);
            const that = this;
            this.editor.initialize(this.editor_id, {
                mediaButtons: false,
                tinymce: {
                    themes: 'inlite',
                    // wpautop: true,
                    // fontsize_formats: '8px 10px 12px 14px 16px 18px 24px 30px 36px 45px',
                    plugins: 'charmap compat3x colorpicker hr media paste tabfocus textcolor fullscreen wordpress image wpautoresize wpeditimage wpemoji wpgallery wplink wpdialogs wptextpattern wpview',
                    toolbar1: 'formatselect fontselect fontsizeselect | customtable bold italic underline strikethrough hr | bullist numlist | blockquote wp_code | link unlink wp_add_media | forecolor backcolor | align outdent indent | charmap pastetext removeformat | undo redo | wp_help markdowncopy',
                    formats: {
                        // Changes the alignment buttons to add a class to each of the matching selector elements
                        alignleft: {
                            selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                            classes: 'align-left',
                            styles: { 'text-align': 'left' }
                        },
                        aligncenter: {
                            selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                            classes: 'align-center',
                            styles: { 'text-align': 'center' },
                            attributes: { align: 'center' }
                        },
                        alignright: {
                            selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
                            classes: 'align-right',
                            styles: { 'text-align': 'right' },
                            attributes: { align: 'right' }
                        }
                    },

                    // height: that.plain_content ? 300 : that.height,
                    relative_urls: true,
                    remove_script_host: true,
                    convert_urls: true,
                    entity_encoding: 'raw',
                    keep_styles: true,
                    menubar: 'edit insert format table',
                    branding: true,
                    table: true,

                    // Limit the preview styles in the menu/toolbar.
                    // preview_styles: 'font-family font-size font-weight font-style text-decoration text-transform',
                    wp_keep_scroll_position: true,
                    setup(editor) {
                        editor.on("change", function (event) {
                            event.preventDefault();
                            that.changeContentEvent();
                        });
                        editor.on('click', function (event) {
                            if (that.isTableOptionsOpen) {
                                that.hideTableOptions()
                            }
                        });

                        editor.on('contextmenu', function (event) {
                            event.preventDefault();
                            that.tableCustomize(event, editor)
                        })

                        editor.addButton('customtable', {
                            classes: 'easy-notes-add-custom-table',
                            icon: 'table',
                            tooltip: 'Insert table',
                            onclick: function (event) {
                                // console.log(editor.dom.select);
                                insertTable(editor, 4, 3);
                            }
                        });
                        editor.addButton('markdowncopy', {
                            classes: 'easy-notes-add-custom-table',
                            icon: 'copy',
                            tooltip: 'Copy as markdown',
                            onclick: function (event) {
                                console.log('object');;
                            }
                        });

                    },
                    content_style: that.extra_style,
                },
                quicktags: true,
            });


            jQuery("#" + this.editor_id).on("change", function (e) {
                that.changeContentEvent();
            });
            this.editorLoading = false
        },

        changeContentEvent() {
            const content = this.editor.getContent(this.editor_id);
            this.$emit("update:modelValue", content);
            this.$emit("change", content);
        },
        updateCursorPos() {
            const cursorPos =
                jQuery(".wp_vue_editor_plain").prop("selectionStart");
            this.cursorPos = cursorPos;
        },
        tableCustomize(event, editor) {
            // table content update
            const tableBody = event.target.closest('tbody');
            const targetRow = event.target.closest('tr');

            if (targetRow && tableBody.contains(targetRow)) {
                const tableOptions = document.getElementById('easy-notes-table-actions');
                this.isTableOptionsOpen = true;
                tableOptions.style.left = event.clientX + 297 + 'px';
                tableOptions.style.top = event.clientY + 181 + 'px';
                tableOptions.style.display = 'block';

                // Save the current cursor position
                const selection = editor.selection.getRng();

                // Create a new row and insert it after the clicked row
                const newRow = tableBody.insertRow(targetRow.rowIndex + 1);

                // Iterate through each cell in the clicked row and create corresponding cells in the new row
                targetRow.querySelectorAll('td').forEach((cell) => {
                    const newCell = newRow.insertCell();
                    newCell.innerHTML = ''; // Optionally, copy content from the clicked cell
                    // Clone the styles from the clicked cell
                    newCell.style.cssText = cell.style.cssText;
                });

                // Update the TinyMCE editor's content
                editor.setContent(editor.getContent());

                // Restore the cursor position
                editor.selection.setRng(selection);

                // Set focus back to the editor
                editor.focus();

                // Set the cursor to the first cell of the new row
                const cellElm = editor.dom.select('td', newRow)[0];
                editor.selection.setCursorLocation(cellElm, 0);
            }
        },
        hideTableOptions() {
            const tableOptions = document.getElementById('easy-notes-table-actions');
            tableOptions.style.display = 'none';
            this.isTableOptionsOpen = false;
        }

    },
    mounted() {
        setTimeout(() => {
            this.initEditor();
        }, 300);
    }

}
</script>

<style lang="scss">
.easy-notes-editor {
    position: relative;

    .wp-editor-wrap {
        .wp-editor-tools {
            .wp-editor-tabs {
                display: none;
            }

            .wp-editor-container {
                background: none !important;
                border: none;
                box-shadow: none;

                .mce-tinymce {
                    background: none !important;
                    box-shadow: none;
                    border: none;

                    >.mce-container-body {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }

                    .mce-container-body {
                        .mce-top-part {
                            width: 100%;
                        }

                        .mce-edit-area {
                            border: none;
                            display: flex;
                            width: 816px !important;
                            min-height: calc(100vh - 152px) !important;
                            padding: 0 20px;
                            background: #fff;

                            iframe {
                                height: auto !important;
                            }
                        }

                        .mce-statusbar {
                            display: none;
                        }
                    }
                }
            }
        }
    }
}

#easy-notes-custom-table {
    border: 1px solid black;
    border-collapse: collapse;
    background: red;
}

#easy-notes-table-actions {
    z-index: 100;
    position: fixed;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 300px;
    display: none;
    // transform: translate(177%, 51%);

    .el-button {
        background: none;
        border: none;
        box-shadow: none;
        outline: none;
        margin: 0 ;
        width: 100%;
        padding: 14px;
        color: #606266;
        &:hover,
        &:active,
        &:focus {
            color: #409eff;
            border: none;
            box-shadow: none;
            outline: none;
        }
        > span {
            display: flex;
            gap: 6px;
        }
    }
    .el-button--danger {
        &:hover {
            color: red;
        }
    }
}
</style>