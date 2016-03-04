<?php
function wrap_rex_output($title,$string) {
  $pieces = array ();
  $pieces [] = '<div id="rex-output">';
  $pieces [] = wrap_rex_out($title,$string);
  $pieces [] = '</div>';
  return join ( "\n", $pieces );
}
function wrap_rex_out($title,$string,$id) {
  $div_id = "";
  if ($id) {
    $div_id = 'id="'.$id.'" ';
  }
  $pieces = array ();
  $pieces [] = '<div '.$div_id.'class="rex-addon-output">';
  $pieces [] = '  <h2 class="rex-hl2">'.$title.'</h2>';
  $pieces [] = '  <div class="rex-addon-content">';
  $pieces [] = $string;
  $pieces [] = '  </div>';
  $pieces [] = '</div>';
  return join ( "\n", $pieces );
}