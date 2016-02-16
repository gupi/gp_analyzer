<?php
echo "<div id='rex-title'>";
echo "<div class='rex-title-row'>";
echo "<h1>Analyse</h1>";
echo "</div>";
echo '<div class="rex-title-row rex-title-row-sub rex-title-row-empty"><p>&nbsp;</p></div>';
echo '</div>';
$analyze =  new rex_analyzer;
echo $analyze->getFullAnalyze();
echo $analyze->showTemplates();
echo $analyze->showModules();
echo $analyze->showElements();