const copyContentAdMarkdown = () => {
    var { TurndownService } = require("turndown");
    // Create a TurndownService instance
    const turndownService = new TurndownService();

    return turndownService.turndown(htmlContent);
};

const insertTable = function (editor, cols, rows) {
    insertTableHtml(editor, cols, rows);
};
const insertTableHtml = function (editor, cols, rows) {
    editor.undoManager.transact(function () {
        let tableElm, cellElm;
        editor.insertContent(createTableHtml(cols, rows));
        tableElm = getInsertedElement(editor);
        tableElm.removeAttribute("data-mce-id");
        cellElm = editor.dom.select("td,th", tableElm);
        editor.selection.setCursorLocation(cellElm[0], 0);
    });
};

const createTableHtml = function (cols, rows) {
    let x, y, html;
    html =
        '<table data-mce-id="mce" style="width: 100%; border: 1px solid black; border-collapse: collapse; max-width: 796px;">';
    html += "<tbody>";
    for (y = 0; y < rows; y++) {
        html += `<tr style='
            border: 1px solid black;
            border - collapse: collapse;
            padding: 8px;
            '>`;
        for (x = 0; x < cols; x++) {
            html += `<td style='
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
            min-width: 100px;
            position: relative;
            margin:0;
            min-height: 30px;
            class='easy-notes-table-data'
            '>
            </br>
            </td>
            `;
        }
        html += "</tr>";
    }
    html += "</tbody>";
    html += "</table>";
    return html;
};
const getInsertedElement = function (editor) {
    let elms = editor.dom.select("*[data-mce-id]");
    return elms[0];
};

export default insertTable;
