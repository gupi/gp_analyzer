<?php
$func = rex_request('func', 'string');
if($func == 'download') {
  $filename = rex_request('file', 'string');
  $path_parts = pathinfo($filename);
  ob_clean();
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-type:application/octet-stream");
  header("Content-Disposition: attachment; filename=".$path_parts['filename'].".".$path_parts['extension']);
  header('Content-Transfer-Encoding: binary');
  readfile($filename);
  exit;
}
$exp = new rex_exporter();
$pieces = array();

$pieces [] =  '<div id="rex-title">
<div class="rex-title-row">
<h1>Export</h1>
</div>
<div class="rex-title-row rex-title-row-sub rex-title-row-empty"><p>&nbsp;</p></div>
</div>';

$pieces [] =  $exp->showDbTables(TRUE);
$pieces [] =  $exp->showDbTables(FALSE);
$pieces [] =  $exp->showTableAdjustments();
$pieces [] =  $exp->showDollarRexAdjustments();
// $pieces [] =  $exp->showDetail("Dollar", "<pre>".print_r($exp->p_count,TRUE)."</pre>");

$ret = $exp->saveFile("r4_export");

$pieces [] =  $exp->showDetail("Download", ($ret[0]?$ret[1]:'<div class="rex-form">
<h2 class="rex-hl2">Export - File</h2>
<form action="index.php" method="post">
<fieldset class="rex-form-col-1">
<div class="rex-form-wrapper">
<input type="hidden" name="page" value="gp_analyzer" />
<input type="hidden" name="subpage" value="export" />
<input type="hidden" name="func" value="download" />
<input type="hidden" name="file" value="'.$ret[1].'" />
<div class="rex-form-row rex-form-element-v2">
<p class="rex-form-text">'.$ret[1].
'  </p>
  </div>
  <div class="rex-form-row rex-form-element-v2">
  <p class="rex-form-submit">
  <input type="submit" class="rex-form-submit" name="sendit" value="Download" />
  </p>
  </div>
  </div>
  </fieldset>
  </form>
  </div>'));

echo join("\n", $pieces);
