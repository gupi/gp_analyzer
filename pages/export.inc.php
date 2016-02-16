<?php
echo "<div id='rex-title'>";
echo "<div class='rex-title-row'>";
echo "<h1>Export</h1>";
echo "</div>";
echo '<div class="rex-title-row rex-title-row-sub rex-title-row-empty"><p>&nbsp;</p></div>';
echo '</div>';
$exp = new rex_exporter();
// echo '<pre>';
// echo print_r($exp->showTableStructure($table),true);
// echo '</pre>';
echo $exp->showDbTables(TRUE);
echo $exp->showDbTables(FALSE);
echo $exp->showTableAdjustments();
echo $exp->showDollarRexAdjustments();
echo $exp->showDollarRexUsage();
$succ = array();
foreach($exp->dest_tables as $v) {
  $succ [] = $exp->exportTable($v);
}
echo $exp->showDetail("db2csv", join("<br>",$succ));
