<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Central Columns view/edit
 *
 * @package PhpMyAdmin
 */
/**
 * Gets some core libraries
 */
require_once 'libraries/common.inc.php';
require_once 'libraries/central_columns.lib.php';

if (isset($_POST['edit_save'])) {
    $col_name = htmlspecialchars($_POST['col_name']);
    $orig_col_name = htmlspecialchars($_POST['orig_col_name']);
    $col_default = htmlspecialchars($_POST['col_default']);
    $col_extra = htmlspecialchars($_POST['col_extra']);
    $col_isNull	= isset($_POST['col_isNull'])?1:0;
    $col_length	= htmlspecialchars($_POST['col_length']);
    $col_type	= htmlspecialchars($_POST['col_type']);
    $collation	= htmlspecialchars($_POST['collation']);
    echo PMA_updateOneColumn(
        $db, $orig_col_name, $col_name, $col_type,
        $col_length, $col_isNull, $collation, $col_extra, $col_default
    );
    exit;
}
if (isset($_POST['populateColumns'])) {
    $selected_tbl = htmlspecialchars($_POST['selectedTable']);
    echo PMA_getHTMLforColumnDropdown($db, $selected_tbl);
    exit;
}
if (isset($_POST['add_column'])) {
    $selected_col = array();
    $selected_tbl = htmlspecialchars($_POST['table-select']);
    $selected_col[] = htmlspecialchars($_POST['column-select']);
    $tmp_msg = PMA_syncUniqueColumns($selected_col, false, $selected_tbl);
}
$response = PMA_Response::getInstance();
$header = $response->getHeader();
$scripts = $header->getScripts();
$scripts->addFile('jquery/jquery.uitablefilter.js');
$scripts->addFile('jquery/jquery.tablesorter.js');
$scripts->addFile('db_central_columns.js');
$cfgCentralColumns = PMA_centralColumnsGetParams();
$pmadb = $cfgCentralColumns['db'];
$pmatable = $cfgCentralColumns['table'];
$max_rows = $GLOBALS['cfg']['MaxRows'];
if (isset($_POST['delete_save'])) {
    $col_name = array();
    $col_name[] = $_REQUEST['col_name'];
    $tmp_msg = PMA_deleteColumnsFromList($col_name, false);
}
if (isset($_REQUEST['total_rows']) && $_REQUEST['total_rows']) {
    $total_rows = $_REQUEST['total_rows'];
} else {
    $result = PMA_getColumnsList($db, 0, 0);
    $total_rows = count($result);
}
if (isset($_REQUEST['pos'])) {
    $pos = $_REQUEST['pos'];
} else {
    $pos = 0;
}
if ($total_rows <= 0) {
    $response->addHTML(
        '<fieldset>There are no columns in central list to display for the '
        . 'current database.</fieldset>'
    );
    $columnAdd = PMA_getHTMLforAddCentralColumn($total_rows, $pos, $db);
    $response->addHTML($columnAdd);
    exit;
}
$table_navigation_html = PMA_getHTMLforTableNavigation($total_rows, $pos, $db);
$response->addHTML($table_navigation_html);
$columnAdd = PMA_getHTMLforAddCentralColumn($total_rows, $pos, $db);
$response->addHTML($columnAdd);
$deleteRowForm = '<form method="post" id="del_form" action="db_central_columns.php">'
        . PMA_URL_getHiddenInputs(
            $db
        )
        . '<input id="del_col_name" type="hidden" name="col_name" value="">'
        . '<input type="hidden" name="pos" value="' . $pos . '">'
        . '<input type="hidden" name="delete_save" value="delete"></form>';
$response->addHTML($deleteRowForm);
$table_struct = '<div id="tableslistcontainer">'
        . '<table id="table_columns" class="tablesorter" '
        . 'style="min-width:100%" class="data">';
$response->addHTML($table_struct);
$tableheader = PMA_getCentralColumnsTableHeader();
$response->addHTML($tableheader);
$result = PMA_getColumnsList($db, $pos, $max_rows);
$odd_row = false;
$row_num=0;
foreach ($result as $row) {
    $tableHtmlRow = PMA_getHTMLforCentralColumnsTableRow(
        $row, $odd_row, $row_num, $db
    );
    $response->addHTML($tableHtmlRow);
    $odd_row = !$odd_row;
    $row_num++;
}
$response->addHTML('</table></div>');
$message = PMA_Message::success(
    sprintf(__('Showing row(s) %1$s - %2$s'), ($pos + 1), ($pos + count($result)))
);
if (isset($tmp_msg) && $tmp_msg != true) {
    $message->addMessage($tmp_msg);
}
?>
