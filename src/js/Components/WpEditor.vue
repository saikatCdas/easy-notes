<template>
    <div v-loading="editorLoading" class="easy-notes-editor">
        <textarea v-if="hasWpEditor" :id="editor_id" :value="modelValue" class="wp_vue_editor"
            :style="editorLoading ? 'visibility: hidden;' : 'visibility: visible;'"></textarea>
        <textarea v-else-if="!hasWpEditor && !editorLoading" v-model="plain_content"
            class="wp_vue_editor wp_vue_editor_plain" @click="updateCursorPos"></textarea>
        <div id="easy-notes-table-actions">
            <el-button @click="insertTableRow(0)" text><i class="el-icon-plus"></i>Insert row above</el-button>
            <el-button @click="insertTableRow(1)" text><i class="el-icon-plus"></i>Insert row below</el-button>
            <el-button @click="insertTableColumn(0)" text><i class="el-icon-plus"></i>Insert column left</el-button>
            <el-button @click="insertTableColumn(1)" text><i class="el-icon-plus"></i>Insert column right</el-button>
            <hr>
            <el-button @click="deleteRow" type="danger" text><i class="el-icon-delete"></i>Delete row</el-button>
            <el-button @click="deleteColumn" type="danger" text><i class="el-icon-delete"></i>Delete column</el-button>
            <el-button @click="deleteTable" type="danger" text><i class="el-icon-delete"></i>Delete table</el-button>
        </div>
    </div>
</template>
<script>
import insertTable from '../custom_tinymcey'
import TurndownService from 'turndown';

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
            isTableOptionsOpen: false,
            targetTable: null,
            targetRow: null,
            currentEditor: null,
            currentEvent: null,
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
                    wpautop: true,
                    fontsize_formats: '8px 10px 12px 14px 16px 18px 24px 30px 36px 45px',
                    plugins: 'charmap compat3x colorpicker hr media paste tabfocus textcolor fullscreen wordpress image wpautoresize wpeditimage wpemoji wpgallery wplink wpdialogs wptextpattern wpview',
                    toolbar1: 'formatselect fontselect fontsizeselect | customtable bold italic underline strikethrough hr | bullist numlist | blockquote wp_code | link unlink wp_add_media | forecolor backcolor | align outdent indent | charmap pastetext removeformat | undo redo | markdowncopy wp_help',
                    formats: {
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

                    relative_urls: true,
                    remove_script_host: true,
                    convert_urls: true,
                    entity_encoding: 'raw',
                    keep_styles: true,
                    menubar: 'edit insert format table',
                    branding: true,
                    table: true,
                    wp_keep_scroll_position: true,
                    setup(editor) {
                        that.currentEditor = editor;
                        window['saikat'] = editor;
                        editor.on("change", function (event) {
                            event.preventDefault();
                            that.changeContentEvent();
                            that.hideTableOptions();
                        });
                        editor.on('click', function (event) {
                            that.hideTableOptions()
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
                                that.markdownAndCopy()
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
            // Prevent the default context menu from appearing
            event.preventDefault();

            // Get the target table and row from the event
            const table = event.target.closest('table');
            const targetRow = event.target.closest('tr');

            if (targetRow && table.contains(targetRow)) {
                // Show the options for adding rows or columns
                const tableOptions = document.getElementById('easy-notes-table-actions');
                this.isTableOptionsOpen = true;
                tableOptions.style.left = event.clientX + 'px';
                tableOptions.style.top = event.clientY + 'px';
                tableOptions.style.display = 'block';
                this.targetTable = table;
                this.targetRow = targetRow;
                this.currentEvent = event;
            }
        },
        hideTableOptions() {
            if (!this.isTableOptionsOpen) return;
            const tableOptions = document.getElementById('easy-notes-table-actions');
            tableOptions.style.display = 'none';
            this.isTableOptionsOpen = false;
        },
        markdownAndCopy(text) {
            const content = this.editor.getContent(this.editor_id);
            const turndownService = new TurndownService();

            // Customize Turndown to handle HTML tables
            turndownService.addRule('table', {
                filter: 'table',
                replacement: function (content, node) {
                    const rows = Array.from(node.querySelectorAll('tr'));
                    let markdownTable = '';

                    // Construct the header row
                    const headerRow = rows.shift(); // Remove the header row from the rows array
                    const headerCells = Array.from(headerRow.children);
                    const headerRowContent = headerCells.map(cell => cell.textContent.trim()).join(' | ');
                    markdownTable += `| ${headerRowContent} |\n`;

                    // Construct the separator row
                    const separatorRow = headerCells.map(() => '---').join(' | ');
                    markdownTable += `| ${separatorRow} |\n`;

                    // Construct the rest of the rows
                    rows.forEach(row => {
                        const cells = Array.from(row.children);
                        const rowContent = cells.map(cell => cell.textContent.trim()).join(' | ');
                        markdownTable += `| ${rowContent} |\n`;
                    });

                    return markdownTable;
                }
            });

            // Convert HTML content to Markdown
            const markdownText = turndownService.turndown(content);
            console.log(markdownText);


            // Create a temporary textarea element
            const textarea = document.createElement('textarea');
            textarea.value = markdownText;

            // Make the textarea out of the viewport to ensure it's not visible
            textarea.style.position = 'fixed';
            textarea.style.left = '-999999px';

            document.body.appendChild(textarea);

            // Select and copy the text inside the textarea
            textarea.select();
            document.execCommand('copy');

            // Remove the textarea from the DOM
            document.body.removeChild(textarea);
        },
        insertTableRow(direction) {
            // direction = 0 add to the above  
            // direction = 1 add to the below
            const newRow = this.targetTable.insertRow(this.targetRow.rowIndex + parseInt(direction));
            // Clone the cells from the clicked row
            this.targetRow.querySelectorAll('td').forEach((cell) => {
                const newCell = newRow.insertCell();
                newCell.innerHTML = ''; // Optionally, copy content from the clicked cell
                // Clone the styles from the clicked cell
                newCell.style.cssText = cell.style.cssText;
            });
            this.updateEditor();
        },
        insertTableColumn(direction) {
            // direction = 0 add to the left  
            // direction = 1 add to the right
            const cellIndex = this.currentEvent.target.cellIndex; // Get the index of the clicked cell
            this.targetTable.querySelectorAll('tr').forEach((row) => {
                const newCell = row.insertCell(cellIndex + parseInt(direction)); // Insert cell at the next index
                newCell.innerHTML = ''; 
                // Clone the styles from the clicked cell
                newCell.style.cssText = this.currentEvent.target.style.cssText;
            })
            this.updateEditor();
        },
        deleteColumn() {
            console.log('object');
            const targetCell = this.currentEvent.target.closest('td');
            if (targetCell && this.targetTable.contains(targetCell)) {
                const columnIndex = targetCell.cellIndex; // Get the index of the clicked cell's column

                // Remove the corresponding cell in each row
                this.targetTable.querySelectorAll('tr').forEach((row) => {
                    row.deleteCell(columnIndex); // Delete the cell at the specified column index
                });
                this.updateEditor();
            }
        },
        deleteRow() {
            this.targetTable.deleteRow(this.targetRow.rowIndex);
            this.updateEditor();
        },
        deleteTable() {
            this.targetTable.remove(); 
            this.updateEditor();
        },

        updateEditor() {
            const selection = this.currentEditor.selection.getRng();
            this.currentEditor.setContent(this.currentEditor.getContent());
            this.currentEditor.selection.setRng(selection);
            this.currentEditor.focus();
            this.hideTableOptions();
        },
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
    transform: translate(207%, 49%);

    .el-button {
        background: none;
        border: none;
        box-shadow: none;
        outline: none;
        margin: 0;
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

        >span {
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