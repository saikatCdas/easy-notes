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
                    themes: 'inlite',
                    fontsize_formats: '8px 10px 12px 14px 16px 18px 24px 30px 36px 45px',
                    plugins: 'charmap colorpicker compat3x directionality hr lists media paste tabfocus textcolor wordpress image wpautoresize wpeditimage wpemoji wpgallery wplink wpdialogs wptextpattern wpview',
                    toolbar1: 'formatselect fontselect fontsizeselect | customtable wp_add_media | bold italic underline forecolor backcolor | blockquote wp_code | link wp_link_remove wp_link_advanced | align bullist numlist outdent indent | charmap pastetext removeformat | undo redo | markdowncopy wp_help',
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
                    keep_styles: true,
                    menubar: 'edit insert format',
                    setup(editor) {
                        that.currentEditor = editor;
                        editor.on('click', function (event) {
                            console.log('event', event);
                            that.hideTableOptions()
                        });

                        editor.on('contextmenu', function (event) {
                            event.preventDefault();
                            that.tableCustomize(event);
                        })
                        editor.on("change", function (event) {
                            event.preventDefault();
                            that.changeContentEvent();
                        });

                        editor.addButton('customtable', {
                            classes: 'easy-notes-add-custom-table',
                            icon: 'table',
                            tooltip: 'Insert table',
                            onclick: function (event) {
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
        tableCustomize(event) {
            // Prevent the default context menu from appearing
            event.preventDefault();

            // Get the target table and row from the event
            const table = event.target.closest('table');
            const targetRow = event.target.closest('tr');

            if (targetRow && table.contains(targetRow)) {
                // get the mouse position
                const iframeElement = document.getElementById(this.editor_id + '_ifr');
                const iframeBoundingRect = iframeElement.getBoundingClientRect();
                const tableOptions = document.getElementById('easy-notes-table-actions');
                this.isTableOptionsOpen = true;
                tableOptions.style.left = iframeBoundingRect.left + event.clientX + 'px';
                tableOptions.style.top = iframeBoundingRect.top + event.clientY + 'px';
                tableOptions.style.display = 'block';
                tableOptions.style.position = "fixed"
                this.currentEvent = event;
            }
        },
        hideTableOptions() {
            if (!this.isTableOptionsOpen) return;
            const tableOptions = document.getElementById('easy-notes-table-actions');
            tableOptions.style.display = 'none';
            tableOptions.style.position = "static"
            this.isTableOptionsOpen = false;
            this.currentEvent = null;
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
            const table = this.currentEvent.target.closest('table');
            const targetRow = this.currentEvent.target.closest('tr');
            const newRow = table.insertRow(targetRow.rowIndex + parseInt(direction));
            // Clone the cells from the clicked row
            targetRow.querySelectorAll('td').forEach((cell) => {
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
            const table = this.currentEvent.target.closest('table');
            const cellIndex = this.currentEvent.target.cellIndex; // Get the index of the clicked cell
            table.querySelectorAll('tr').forEach((row) => {
                const newCell = row.insertCell(cellIndex + parseInt(direction)); // Insert cell at the next index
                newCell.innerHTML = ''; 
                // Clone the styles from the clicked cell
                newCell.style.cssText = this.currentEvent.target.style.cssText;
            })
            this.updateEditor();
        },
        deleteColumn() {
            const table = this.currentEvent.target.closest('table');
            const targetCell = this.currentEvent.target.closest('td');
            if (targetCell && table.contains(targetCell)) {
                const columnIndex = targetCell.cellIndex; // Get the index of the clicked cell's column

                // Remove the corresponding cell in each row
                table.querySelectorAll('tr').forEach((row) => {
                    row.deleteCell(columnIndex); // Delete the cell at the specified column index
                });
                this.updateEditor();
            }
        },
        deleteRow() {
            const table = this.currentEvent.target.closest('table');
            const targetRow = this.currentEvent.target.closest('tr');
            table.deleteRow(targetRow.rowIndex);
            this.updateEditor();
        },
        deleteTable() {
            const table = this.currentEvent.target.closest('table');
            table.remove(); 
            this.updateEditor();
        },

        updateEditor() {
            let currentEditor = window.tinymce.editors[this.editor_id];
            const selection = currentEditor.selection.getRng();
            currentEditor.setContent(currentEditor.getContent());
            currentEditor.selection.setRng(selection);
            currentEditor.focus();
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
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 300px;
    display: none;

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