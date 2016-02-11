<?php
$Basedir = dirname(__FILE__);

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

require $REX['INCLUDE_PATH'] . '/layout/top.php';

$subpages = array(
    array('analyze', $I18N->msg('analyzer_overview')),
    array('export', $I18N->msg('analyzer_export')),
);

rex_title($I18N->msg('analyzer_view'), $subpages);

switch ($subpage) {
    case 'export':
        require $Basedir . '/export.inc.php';
    break;
    default:
        require $Basedir . '/analyze.inc.php';
}

require $REX['INCLUDE_PATH'] . '/layout/bottom.php';
