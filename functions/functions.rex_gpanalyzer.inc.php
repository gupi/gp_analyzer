<?php
function wrap_rex_output($title,$string) {
  $pieces = array ();
  $pieces [] = '<div id="rex-output">';
  $pieces [] = '<div class="rex-addon-output">';
  $pieces [] = '<h2 class="rex-hl2">'.$title.'</h2>';
  $pieces [] = '<div class="rex-addon-content">';
  $pieces [] = $string;
  $pieces [] = '</div>';
  $pieces [] = '</div>';
  $pieces [] = '</div>';
  return join ( "\n", $pieces );
  
}