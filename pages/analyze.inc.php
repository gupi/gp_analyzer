<?php
$analyze =  new rex_analyzer;

$pieces = array();
$pieces [] =  "<div id='rex-title'>";
$pieces [] = "<div class='rex-title-row'>";
$pieces [] = "<h1>Analyse</h1>";
$pieces [] = "</div>";
$pieces [] = '<div class="rex-title-row rex-title-row-sub rex-title-row-empty"><p>&nbsp;</p></div>';
$pieces [] = '</div>';
$pieces [] = $analyze->getFullAnalyze();
$pieces [] = $analyze->showLanguages();
$pieces [] = $analyze->showAddons();
$pieces [] = $analyze->showTemplates();
$pieces [] = $analyze->showModules();
$pieces [] = $analyze->showElements();

echo join("\n", $pieces);