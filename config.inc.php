<?php
$mypage = 'gp_analyzer';

$REX['ADDON']['page'][$mypage] = $mypage; 
$REX['ADDON']['name'][$mypage] = 'MyAnalyzer';
$REX['ADDON']['perm'][$mypage] = 'gp_analyzer[]';
$REX['PERM'][] = 'analyzer[]';
$REX['ADDON']['version'][$mypage] = '1.0';
$REX['ADDON']['author'][$mypage] = 'Gunter Pietzsch';
$REX['ADDON']['navigation'][$mypage] = array('block'=>'gp_analyzer');

// ---------- Backend, Perms, Subpages etc.
if ($REX["REDAXO"] && $REX['USER']) {
  $REX['EXTRAPERM'][] = "analyzer[]";
  $REX['ADDON'][$mypage]['SUBPAGES'] = array();
  $REX['ADDON'][$mypage]['SUBPAGES'][] = array( 'analyze' , $I18N->msg("analyzer_overview"));
  $REX['ADDON'][$mypage]['SUBPAGES'][] = array( 'export' , $I18N->msg("analyzer_export"));
}

require_once($REX['INCLUDE_PATH']. '/addons/'.$mypage.'/classes/class.rex_gpanalyze.inc.php');
require_once($REX['INCLUDE_PATH']. '/addons/'.$mypage.'/classes/class.rex_gpexport.inc.php');
require_once($REX['INCLUDE_PATH']. '/addons/'.$mypage.'/functions/functions.rex_gpanalyzer.inc.php');

?>
